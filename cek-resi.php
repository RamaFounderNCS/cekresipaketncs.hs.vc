<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!isset($data["waybill"]) || !isset($data["courier"])) {
    echo json_encode(["status" => 400, "message" => "Permintaan tidak valid"]);
    exit;
}

$waybill = htmlspecialchars($data["waybill"]);
$courier = htmlspecialchars($data["courier"]);
$token = $data["token"] ?? "";

if (!$token) {
    echo json_encode(["status" => 403, "message" => "Token reCAPTCHA tidak ditemukan"]);
    exit;
}

// Verifikasi reCAPTCHA v3
$secretKey = "6LcOaT4rAAAAAGfKjVBYZ0gX8MfzaON9OZXnQz8_";
$verifyUrl = "https://www.google.com/recaptcha/api/siteverify";
$response = file_get_contents($verifyUrl . "?secret=" . $secretKey . "&response=" . $token);
$responseKeys = json_decode($response, true);

if (!$responseKeys["success"] || $responseKeys["score"] < 0.5) {
    echo json_encode(["status" => 403, "message" => "Verifikasi reCAPTCHA gagal"]);
    exit;
}

// Panggil API Binderbyte
$apiKey = "554b70660402d4755fc7a3bf80e8243b17e73d699e87057b2d211d13d9764bac";
$apiUrl = "https://api.binderbyte.com/v1/track?api_key={$apiKey}&courier={$courier}&awb={$waybill}";

$response = file_get_contents($apiUrl);
if (!$response) {
    echo json_encode(["status" => 500, "message" => "Gagal menghubungi server pelacakan"]);
    exit;
}

echo $response;
?>
