
<?php

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../config/session.php";

header("Content-Type: application/json; charset=UTF-8");
header("Cache-Control: no-store");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Only POST requests are allowed."
    ]);

    exit;
}

// Require an authenticated user.
if (
    empty($_SESSION["logged_in"]) ||
    empty($_SESSION["user_id"])
) {
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Please log in to manage your links."
    ]);

    exit;
}

$userId = (int) $_SESSION["user_id"];

$input = json_decode(file_get_contents("php://input"), true);

if (!is_array($input)) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid request data."
    ]);

    exit;
}

$id = filter_var(
    $input["id"] ?? null,
    FILTER_VALIDATE_INT,
    ["options" => ["min_range" => 1]]
);

$status = $input["status"] ?? "";

if ($id === false || $id === null) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "A valid link ID is required."
    ]);

    exit;
}

if (!in_array($status, ["active", "disabled"], true)) {
    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Status must be active or disabled."
    ]);

    exit;
}

try {
    // Update only a link owned by the authenticated user.
    $stmt = $pdo->prepare(
        "UPDATE links
         SET status = :status
         WHERE id = :id
         AND user_id = :user_id"
    );

    $stmt->execute([
        ":status" => $status,
        ":id" => $id,
        ":user_id" => $userId
    ]);

    if ($stmt->rowCount() === 0) {
        // Distinguish a missing/unowned link from an unchanged status.
        $check = $pdo->prepare(
            "SELECT id, status
             FROM links
             WHERE id = :id
             AND user_id = :user_id"
        );

        $check->execute([
            ":id" => $id,
            ":user_id" => $userId
        ]);

        $link = $check->fetch();

        if (!$link) {
            // Do not disclose whether another user's link exists.
            http_response_code(404);

            echo json_encode([
                "success" => false,
                "message" => "Link not found."
            ]);

            exit;
        }
    }

    echo json_encode([
        "success" => true,
        "message" => $status === "disabled"
            ? "Link disabled successfully."
            : "Link enabled successfully.",
        "id" => (int) $id,
        "status" => $status
    ]);

} catch (Throwable $e) {
    error_log("Link status update failed: " . $e->getMessage());

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Unable to update link status."
    ]);
}
