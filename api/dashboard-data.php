
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

// Determine the current user's link scope.
// Logged-in users see their own links.
// Anonymous visitors see only anonymous links.
$isLoggedIn = !empty($_SESSION["logged_in"])
    && !empty($_SESSION["user_id"]);

$userId = $isLoggedIn
    ? (int) $_SESSION["user_id"]
    : null;

try {
    $scopeSql = $isLoggedIn
        ? "user_id = :user_id"
        : "user_id IS NULL";

    $params = $isLoggedIn
        ? [":user_id" => $userId]
        : [];

    // 1. Get total links in the current user's scope.
    $totalStmt = $pdo->prepare(
        "SELECT COUNT(*) AS total_links
         FROM links
         WHERE $scopeSql"
    );

    $totalStmt->execute($params);
    $totalLinks = (int) $totalStmt->fetch()["total_links"];

    // 2. Get total clicks for links in the current user's scope.
    $clickStmt = $pdo->prepare(
        "SELECT COUNT(*) AS total_clicks
         FROM clicks c
         INNER JOIN links l ON l.id = c.link_id
         WHERE " . (
            $isLoggedIn
                ? "l.user_id = :user_id"
                : "l.user_id IS NULL"
         )
    );

    $clickStmt->execute($params);
    $totalClicks = (int) $clickStmt->fetch()["total_clicks"];

    // 3. Get available active links in the current user's scope.
    $activeStmt = $pdo->prepare(
        "SELECT COUNT(*) AS active_links
         FROM links
         WHERE $scopeSql
         AND status = 'active'
         AND (expires_at IS NULL OR expires_at > NOW())
         AND (max_clicks IS NULL OR click_count < max_clicks)"
    );

    $activeStmt->execute($params);
    $activeLinks = (int) $activeStmt->fetch()["active_links"];

    // 4. Get expired links in the current user's scope.
    $expiredStmt = $pdo->prepare(
        "SELECT COUNT(*) AS expired_links
         FROM links
         WHERE $scopeSql
         AND expires_at IS NOT NULL
         AND expires_at <= NOW()"
    );

    $expiredStmt->execute($params);
    $expiredLinks = (int) $expiredStmt->fetch()["expired_links"];

    // 5. Get recent links in the current user's scope.
    $linksStmt = $pdo->prepare(
        "SELECT
            id,
            short_code,
            original_url,
            click_count,
            status,
            created_at,
            expires_at,
            max_clicks
         FROM links
         WHERE $scopeSql
         ORDER BY created_at DESC
         LIMIT 10"
    );

    $linksStmt->execute($params);
    $links = $linksStmt->fetchAll();

    // Build the response.
    echo json_encode([
        "success" => true,

        "overview" => [
            "total_links" => $totalLinks,
            "total_clicks" => $totalClicks,
            "active_links" => $activeLinks,
            "expired_links" => $expiredLinks
        ],

        "recent_links" => $links
    ], JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    error_log("Dashboard API failed: " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to load dashboard data."
    ]);
}
