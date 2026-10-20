
<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/session.php";

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate");

function respond(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Allow: POST");
    respond(405, [
        "success" => false,
        "message" => "Only POST requests are allowed."
    ]);
}

try {
    /*
     * Identify the logged-in user first.
     */
    $userId = (
        !empty($_SESSION["logged_in"]) &&
        !empty($_SESSION["user_id"])
    )
        ? (int) $_SESSION["user_id"]
        : null;

    /*
     * IP-based rate limiting applies ONLY to anonymous visitors.
     * Anonymous visitors: maximum 20 POST attempts per IP
     * within a rolling hour.
     *
     * Registered users are exempt from this IP-based cap.
     */
    if ($userId === null) {
        $clientIp = $_SERVER["REMOTE_ADDR"] ?? "";

        if ($clientIp === "") {
            respond(400, [
                "success" => false,
                "message" => "Unable to identify the client."
            ]);
        }

        $ipHash = hash("sha256", $clientIp);

        // Record every anonymous POST attempt, including invalid requests.
        $stmt = $pdo->prepare("
            INSERT INTO link_creation_rate_limits (ip_hash)
            VALUES (:ip_hash)
        ");
        $stmt->execute([":ip_hash" => $ipHash]);

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM link_creation_rate_limits
            WHERE ip_hash = :ip_hash
              AND created_at >= NOW() - INTERVAL 1 HOUR
        ");
        $stmt->execute([":ip_hash" => $ipHash]);

        $ipAttempts = (int) $stmt->fetchColumn();

        if ($ipAttempts > 20) {
            header("Retry-After: 3600");

            respond(429, [
                "success" => false,
                "limit_type" => "ip_rate_limit",
                "message" => "Too many anonymous link creation requests from this IP. Please try again later or sign in to your account."
            ]);
        }

        // Remove old records to control table growth.
        $pdo->exec("
            DELETE FROM link_creation_rate_limits
            WHERE created_at < NOW() - INTERVAL 2 DAY
        ");
    }

    /*
     * Read and validate the submitted URL.
     */
    $input = json_decode(file_get_contents("php://input"), true);

    if (
        !is_array($input) ||
        !isset($input["url"]) ||
        !is_string($input["url"])
    ) {
        respond(400, [
            "success" => false,
            "message" => "Please provide a URL."
        ]);
    }

    $url = trim($input["url"]);

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        respond(400, [
            "success" => false,
            "message" => "Please enter a valid URL."
        ]);
    }

    $scheme = strtolower(parse_url($url, PHP_URL_SCHEME) ?? "");

    if (!in_array($scheme, ["http", "https"], true)) {
        respond(400, [
            "success" => false,
            "message" => "Only HTTP and HTTPS URLs are allowed."
        ]);
    }

    /*
     * Prepare anonymous visitor identity if not logged in.
     */
    $anonymousTokenHash = null;

    if ($userId === null) {
        $cookieName = "LINKPULSE_VISITOR";
        $token = $_COOKIE[$cookieName] ?? "";

        if (
            !is_string($token) ||
            !preg_match('/^[a-f0-9]{64}$/', $token)
        ) {
            $token = bin2hex(random_bytes(32));
        }

        $anonymousTokenHash = hash("sha256", $token);

        $isHttps = !empty($_SERVER["HTTPS"]) &&
                   $_SERVER["HTTPS"] !== "off";

        setcookie($cookieName, $token, [
            "expires" => time() + (90 * 24 * 60 * 60),
            "path" => "/LinkPulse/",
            "secure" => $isHttps,
            "httponly" => true,
            "samesite" => "Lax"
        ]);
    }

    /*
     * Daily successful-link creation quota.
     */
    if ($userId !== null) {
        // Registered users: 100 links per day, no IP-based cap.
        $dailyLimit = 100;

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM links
            WHERE user_id = :user_id
              AND created_at >= CURDATE()
              AND created_at < CURDATE() + INTERVAL 1 DAY
        ");

        $stmt->execute([":user_id" => $userId]);
        $dailyCount = (int) $stmt->fetchColumn();

        if ($dailyCount >= $dailyLimit) {
            respond(429, [
                "success" => false,
                "limit_type" => "registered_daily_limit",
                "limit" => $dailyLimit,
                "used" => $dailyCount,
                "message" => "You have reached your limit of 100 links today. Please try again tomorrow."
            ]);
        }
    } else {
        // Anonymous visitors: 5 links per day.
        $dailyLimit = 5;

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM links
            WHERE user_id IS NULL
              AND anonymous_token_hash = :token_hash
              AND created_at >= CURDATE()
              AND created_at < CURDATE() + INTERVAL 1 DAY
        ");

        $stmt->execute([
            ":token_hash" => $anonymousTokenHash
        ]);

        $dailyCount = (int) $stmt->fetchColumn();

        if ($dailyCount >= $dailyLimit) {
            respond(429, [
                "success" => false,
                "limit_type" => "anonymous_daily_limit",
                "limit" => $dailyLimit,
                "used" => $dailyCount,
                "message" => "You have reached your limit of 5 links today. Sign up to create more links."
            ]);
        }
    }

    /*
     * Create and save the short link.
     */
    $shortCode = bin2hex(random_bytes(4));

    $sql = "
        INSERT INTO links (
            user_id,
            anonymous_token_hash,
            original_url,
            short_code
        )
        VALUES (
            :user_id,
            :anonymous_token_hash,
            :original_url,
            :short_code
        )
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ":user_id" => $userId,
        ":anonymous_token_hash" => $anonymousTokenHash,
        ":original_url" => $url,
        ":short_code" => $shortCode
    ]);

    $shortUrl = "http://localhost/LinkPulse/" . $shortCode;

    respond(201, [
        "success" => true,
        "message" => "Short URL created successfully.",
        "short_code" => $shortCode,
        "short_url" => $shortUrl,
        "daily_limit" => $dailyLimit,
        "daily_used" => $dailyCount + 1,
        "daily_remaining" => $dailyLimit - $dailyCount - 1
    ]);

} catch (Throwable $e) {
    error_log("Link creation failed: " . $e->getMessage());

    respond(500, [
        "success" => false,
        "message" => "Unable to create the short URL."
    ]);
}
