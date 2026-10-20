<?php

require_once __DIR__ . "/../config/db.php";

header("Content-Type: text/plain; charset=UTF-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$shortCode = $_GET["code"] ?? "";

if (!preg_match('/\A[a-f0-9]{8}\z/', $shortCode)) {
    http_response_code(404);
    exit("Short link not found.");
}

try {
    $pdo->beginTransaction();

    // Lock the link record while checking its click limit.
    $stmt = $pdo->prepare(
        "SELECT id, original_url, expires_at, max_clicks,
                click_count, status
         FROM links
         WHERE short_code = :code
         FOR UPDATE"
    );

    $stmt->execute([":code" => $shortCode]);
    $link = $stmt->fetch();

    if (!$link) {
        $pdo->rollBack();
        http_response_code(404);
        exit("Short link not found.");
    }

    if (strtolower(trim((string) $link["status"])) !== "active") {
        $pdo->rollBack();

        http_response_code(410);
        exit("This short link has been disabled.");
    }

    if (
        $link["expires_at"] !== null &&
        strtotime($link["expires_at"]) <= time()
    ) {
        $pdo->rollBack();
        http_response_code(410);
        exit("This short link has expired.");
    }

    if (
        $link["max_clicks"] !== null &&
        (int) $link["click_count"] >= (int) $link["max_clicks"]
    ) {
        $pdo->rollBack();
        http_response_code(410);
        exit("This short link has reached its click limit.");
    }

    // Record the click.
    $clickStmt = $pdo->prepare(
        "INSERT INTO clicks (link_id, referrer)
         VALUES (:link_id, :referrer)"
    );

    $referrer = $_SERVER["HTTP_REFERER"] ?? null;

    $clickStmt->execute([
        ":link_id" => $link["id"],
        ":referrer" => $referrer
    ]);

    // Increment the click count.
    $updateStmt = $pdo->prepare(
        "UPDATE links
         SET click_count = click_count + 1
         WHERE id = :id"
    );

    $updateStmt->execute([":id" => $link["id"]]);

    $originalUrl = $link["original_url"];

    $pdo->commit();

    // Redirect the visitor.
    header("Location: " . $originalUrl, true, 302);
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Redirect failed: " . $e->getMessage());

    http_response_code(500);
    exit("Unable to process this short link.");
}