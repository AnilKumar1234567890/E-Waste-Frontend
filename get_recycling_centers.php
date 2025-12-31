<?php
header("Content-Type: application/json");
include "db.php";

// user location (optional)
$user_lat = isset($_GET['lat']) ? $_GET['lat'] : null;
$user_lng = isset($_GET['lng']) ? $_GET['lng'] : null;

$sql = "SELECT * FROM recycling_centers WHERE is_authorized = 1";
$result = $conn->query($sql);

$centers = [];

while ($row = $result->fetch_assoc()) {

    // default distance
    $distance = null;

    // calculate distance if user location provided
    if ($user_lat && $user_lng) {
        $theta = $user_lng - $row['longitude'];
        $dist = sin(deg2rad($user_lat)) * sin(deg2rad($row['latitude'])) +
                cos(deg2rad($user_lat)) * cos(deg2rad($row['latitude'])) *
                cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $distance = round($dist * 60 * 1.1515 * 1.609344, 2); // KM
    }

    $centers[] = [
        "id" => $row['id'],
        "name" => $row['name'],
        "address" => $row['address'],
        "price" => $row['base_price'],
        "rating" => $row['rating'],
        "distance_km" => $distance,
        "authorized" => true
    ];
}

echo json_encode([
    "status" => "success",
    "data" => $centers
]);
?>
