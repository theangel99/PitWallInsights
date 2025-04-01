<?php
header("Content-Type: application/json");

$driver_number = isset($_GET['driver_number']) ? $_GET['driver_number'] : '44';
$session_key = isset($_GET['session_key']) ? $_GET['session_key'] : '9693';
$api_url = "https://api.openf1.org/v1/car_data?driver_number={$driver_number}&session_key={$session_key}";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code == 200) {
    echo $response;
} else {
    echo json_encode(["error" => "Failed to fetch data from OpenF1 API. HTTP Status Code: $http_code"]);
}
?>
