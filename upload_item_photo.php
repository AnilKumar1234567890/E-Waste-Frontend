<?php
header("Content-Type: application/json");
include "db.php";

if (!isset($_POST['item_id']) || !isset($_FILES['photo'])) {
    echo json_encode([
        "status" => "error",
        "message" => "item_id or photo missing"
    ]);
    exit;
}

$item_id = $_POST['item_id'];
$file = $_FILES['photo'];

if ($file['error'] !== 0) {
    echo json_encode([
        "status" => "error",
        "message" => "File upload error"
    ]);
    exit;
}

/* create uploads folder if not exists */
$uploadDir = "uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

/* generate file name */
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = "item_" . $item_id . "_" . time() . "." . $ext;
$filepath = $uploadDir . $filename;

/* move file */
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to save file"
    ]);
    exit;
}

/* update database */
$stmt = $conn->prepare(
    "UPDATE items SET image_path = ? WHERE id = ?"
);
$stmt->bind_param("si", $filepath, $item_id);

if ($stmt->execute()) {
    echo json_encode([
        "status" => "success",
        "message" => "Photo uploaded successfully",
        "image_path" => $filepath
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Database update failed"
    ]);
}
?>
