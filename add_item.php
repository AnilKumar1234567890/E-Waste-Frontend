<?php
header("Content-Type: application/json");
include "db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status"=>"error","message"=>"Invalid request"]);
    exit;
}

$user_id   = $_POST['user_id'] ?? null;
$item_type = $_POST['item_type'] ?? null;
$item_name = $_POST['item_name'] ?? null;
$quantity  = $_POST['quantity'] ?? null;

if (!$user_id || !$item_type || !$item_name || !$quantity) {
    echo json_encode([
        "status" => "error",
        "message" => "Required fields missing"
    ]);
    exit;
}

// 🔍 Check user exists
$checkUser = $conn->prepare("SELECT id FROM user_login WHERE id=?");
$checkUser->bind_param("i", $user_id);
$checkUser->execute();
$res = $checkUser->get_result();

if ($res->num_rows == 0) {
    echo json_encode([
        "status" => "error",
        "message" => "User not found"
    ]);
    exit;
}

/* 📸 IMAGE UPLOAD */
$imagePath = null;

if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $uploadDir = "uploads/";
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $fileName = "item_" . time() . "_" . rand(100,999) . "." . $ext;
    $target = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        $imagePath = $target;
    }
}

// ✅ Insert item
$stmt = $conn->prepare(
    "INSERT INTO items (user_id, item_type, item_name, quantity, image_path)
     VALUES (?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "issis",
    $user_id,
    $item_type,
    $item_name,
    $quantity,
    $imagePath
);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Item added successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to add item"
    ]);
}
?>
