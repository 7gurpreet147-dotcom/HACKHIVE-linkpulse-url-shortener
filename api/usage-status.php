
<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/session.php";

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate");

function sendUsageResponse(int $status, array $data): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    header("Allow: GET");

    sendUsageResponse(405, [
        "success" => false,
        "message" => "Only GET requests are allowed."
    ]);
}

try {
    // Identify the logged-in user.
    $userId = (
        !empty($_SESSION["logged_in"]) &&
        !empty($_SESSION["user_id"])
    ) ? (int) $_SESSION["user_id"] : null;

    /*
     * REGISTERED USERS
     * 100 successful links per day.
     * No IP-based request cap.
     */
    if ($userId !== null) {
        $dailyLimit = 100;

        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM links
            WHERE user_id = :user_id
              AND created_at >= CURDATE()
              AND created_at < CURDATE() + INTERVAL 1 DAY
        ");

        $stmt->execute([":user_id" => $userId]);
        $dailyUsed = (int) $stmt->fetchColumn();

        sendUsageResponse(200, [
            "success" => true,
            "user_type" => "registered",
            "daily" => [
                "limit" => $dailyLimit,
                "used" => $dailyUsed,
                "remaining" => max(0, $dailyLimit - $dailyUsed)
            ],
            "ip" => null,
            "registration_offer" => false
        ]);
    }

    /*
     * ANONYMOUS VISITORS
     * 5 successful links per day.
     * 20 POST attempts per IP per rolling hour.
     */

    // Identify the client's IP address.
    $clientIp = $_SERVER["REMOTE_ADDR"] ?? "";

    if ($clientIp === "") {
        sendUsageResponse(400, [
            "success" => false,
            "message" => "Unable to identify the client."
        ]);
    }

    $ipHash = hash("sha256", $clientIp);

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM link_creation_rate_limits
        WHERE ip_hash = :ip_hash
          AND created_at >= NOW() - INTERVAL 1 HOUR
    ");

    $stmt->execute([":ip_hash" => $ipHash]);

    $ipUsed = (int) $stmt->fetchColumn();
    $ipLimit = 20;

    // Get or create the anonymous visitor token.
    $cookieName = "LINKPULSE_VISITOR";
    $token = $_COOKIE[$cookieName] ?? "";

    if (
        !is_string($token) ||
        !preg_match('/^[a-f0-9]{64}$/', $token)
    ) {
        $token = bin2hex(random_bytes(32));

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

    $tokenHash = hash("sha256", $token);
    $dailyLimit = 5;

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM links
        WHERE user_id IS NULL
          AND anonymous_token_hash = :token_hash
          AND created_at >= CURDATE()
          AND created_at < CURDATE() + INTERVAL 1 DAY
    ");

    $stmt->execute([":token_hash" => $tokenHash]);

    $dailyUsed = (int) $stmt->fetchColumn();

    sendUsageResponse(200, [
        "success" => true,
        "user_type" => "anonymous",
        "daily" => [
            "limit" => $dailyLimit,
            "used" => $dailyUsed,
            "remaining" => max(0, $dailyLimit - $dailyUsed)
        ],
        "ip" => [
            "limit" => $ipLimit,
            "used" => $ipUsed,
            "remaining" => max(0, $ipLimit - $ipUsed),
            "window" => "rolling_hour",
            "shared" => true
        ],
        "registration_offer" => true
    ]);

} catch (Throwable $e) {
    error_log("Usage status failed: " . $e->getMessage());

    sendUsageResponse(500, [
        "success" => false,
        "message" => "Unable to load usage information."
    ]);
}
