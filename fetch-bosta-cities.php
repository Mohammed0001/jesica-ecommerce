<?php

require __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$apiKey = $_ENV['BOSTA_API_KEY'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://app.bosta.co/api/v2/cities?countryCode=EG');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: ' . $apiKey,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n\n";
echo "Response:\n";
echo json_encode(json_decode($response), JSON_PRETTY_PRINT);
