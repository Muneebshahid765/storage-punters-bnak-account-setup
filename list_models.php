<?php
$api_key = 'AIzaSyBwvFjOw6fnI2evvJt3tKrQLdKqMNccUEM';
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=$api_key";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>
