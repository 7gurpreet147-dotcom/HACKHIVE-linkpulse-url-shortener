
<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/session.php";

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store");

if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Only GET requests are allowed."
    ]);

    exit;
}

// Determine the current user's scope.
$isLoggedIn = !empty($_SESSION["logged_in"])
    && !empty($_SESSION["user_id"]);

$userId = $isLoggedIn
    ? (int) $_SESSION["user_id"]
    : null;

try {
    // Retrieve click activity only for links in the current scope.
    $sql = "
        SELECT
            DATE(c.clicked_at) AS click_date,
            COUNT(*) AS total_clicks
        FROM clicks c
        INNER JOIN links l ON l.id = c.link_id
        WHERE c.clicked_at >= CURDATE() - INTERVAL 6 DAY
          AND c.clicked_at < CURDATE() + INTERVAL 1 DAY
          AND " . (
              $isLoggedIn
                  ? "l.user_id = :user_id"
                  : "l.user_id IS NULL"
          ) . "
        GROUP BY DATE(c.clicked_at)
        ORDER BY click_date ASC
    ";

    $stmt = $pdo->prepare($sql);

    if ($isLoggedIn) {
        $stmt->execute([
            ":user_id" => $userId
        ]);
    } else {
        $stmt->execute();
    }

    $rows = $stmt->fetchAll();

    // Initialize all seven dates, including days with zero clicks.
    $labels = [];
    $values = [];
    $countsByDate = [];

    foreach ($rows as $row) {
        $countsByDate[$row["click_date"]] =
            (int) $row["total_clicks"];
    }

    for ($i = 6; $i >= 0; $i--) {
        $date = date("Y-m-d", strtotime("-$i days"));

        $labels[] = date("M j", strtotime($date));
        $values[] = $countsByDate[$date] ?? 0;
    }

    echo json_encode([
        "success" => true,
        "labels" => $labels,
        "values" => $values
    ]);

} catch (Throwable $e) {
    error_log("Click activity API failed: " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to load click activity."
    ]);
}
