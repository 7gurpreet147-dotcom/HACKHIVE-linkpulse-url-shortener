
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

$cookieName = "LINKPULSE_VISITOR";
$token = $_COOKIE[$cookieName] ?? "";

if (
    !is_string($token) ||
    !preg_match('/^[a-f0-9]{64}$/', $token)
) {
    echo json_encode([
        "success" => true,
        "links" => []
    ]);
    exit;
}

$tokenHash = hash("sha256", $token);

try {
    $sql = "
        SELECT
            id,
            original_url,
            short_code,
            title,
            click_count,
            status,
            created_at,
            expires_at,
            max_clicks
        FROM links
        WHERE user_id IS NULL
          AND anonymous_token_hash = :token_hash
        ORDER BY created_at DESC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ":token_hash" => $tokenHash
    ]);

    $links = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "links" => $links
    ], JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    error_log("Anonymous link history failed: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Unable to load your links."
    ]);
}
