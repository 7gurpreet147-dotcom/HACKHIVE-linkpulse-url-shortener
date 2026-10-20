
<?php

require_once __DIR__ . "/../config/db.php";

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate");

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Only GET requests are allowed."
    ]);
    exit;
}

$token = $_COOKIE["LINKPULSE_VISITOR"] ?? "";

if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/', $token)) {
    echo json_encode([
        "success" => true,
        "overview" => [
            "total_links" => 0,
            "total_clicks" => 0,
            "active_links" => 0,
            "expired_links" => 0
        ],
        "recent_links" => []
    ]);
    exit;
}

$tokenHash = hash("sha256", $token);

try {
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) AS total_links,
            COALESCE(SUM(l.click_count), 0) AS total_clicks,
            COALESCE(SUM(
                CASE
                    WHEN l.status = 'active'
                     AND (l.expires_at IS NULL OR l.expires_at > NOW())
                     AND (l.max_clicks IS NULL OR l.click_count < l.max_clicks)
                    THEN 1 ELSE 0
                END
            ), 0) AS active_links,
            COALESCE(SUM(
                CASE
                    WHEN (l.expires_at IS NOT NULL AND l.expires_at <= NOW())
                      OR (l.max_clicks IS NOT NULL AND l.click_count >= l.max_clicks)
                    THEN 1 ELSE 0
                END
            ), 0) AS expired_links
        FROM links l
        WHERE l.user_id IS NULL
          AND l.anonymous_token_hash = :token_hash
    ");

    $stmt->execute([":token_hash" => $tokenHash]);
    $overview = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT
            id,
            original_url,
            short_code,
            click_count,
            status,
            created_at,
            expires_at,
            max_clicks
        FROM links
        WHERE user_id IS NULL
          AND anonymous_token_hash = :token_hash
        ORDER BY created_at DESC
        LIMIT 100
    ");

    $stmt->execute([":token_hash" => $tokenHash]);
    $links = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "overview" => [
            "total_links" => (int) $overview["total_links"],
            "total_clicks" => (int) $overview["total_clicks"],
            "active_links" => (int) $overview["active_links"],
            "expired_links" => (int) $overview["expired_links"]
        ],
        "recent_links" => $links
    ], JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    error_log("Anonymous dashboard data failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Unable to load dashboard data."
    ]);
}
