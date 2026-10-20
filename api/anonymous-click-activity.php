
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
        "labels" => [],
        "values" => []
    ]);
    exit;
}

$tokenHash = hash("sha256", $token);

try {
    $stmt = $pdo->prepare("
        SELECT
            DATE(c.clicked_at) AS click_date,
            COUNT(*) AS total
        FROM clicks c
        INNER JOIN links l ON l.id = c.link_id
        WHERE l.user_id IS NULL
          AND l.anonymous_token_hash = :token_hash
          AND c.clicked_at >= CURDATE() - INTERVAL 6 DAY
          AND c.clicked_at < CURDATE() + INTERVAL 1 DAY
        GROUP BY DATE(c.clicked_at)
        ORDER BY click_date
    ");

    $stmt->execute([":token_hash" => $tokenHash]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $counts = [];

    foreach ($rows as $row) {
        $counts[$row["click_date"]] = (int) $row["total"];
    }

    $labels = [];
    $values = [];

    for ($i = 6; $i >= 0; $i--) {
        $date = new DateTimeImmutable("today");
        $date = $date->modify("-" . $i . " days");
        $key = $date->format("Y-m-d");

        $labels[] = $date->format("M d");
        $values[] = $counts[$key] ?? 0;
    }

    echo json_encode([
        "success" => true,
        "labels" => $labels,
        "values" => $values
    ]);

} catch (Throwable $e) {
    error_log("Anonymous click activity failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Unable to load click activity."
    ]);
}
