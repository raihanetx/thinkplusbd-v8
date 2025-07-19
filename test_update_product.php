<?php
// This script will simulate the form submission to update_product.php

$url = 'https://test-website.great-site.net/update_product.php';

// The data to be sent in the POST request
$data = [
    'product_id' => '1',
    'name' => 'CAPCUT PRO (pc version) - EDITED',
    'description' => 'This is an edited description.',
    'longDescription' => 'This is an edited long description.',
    'price' => '299',
];

// cURL initialization
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true); // Get headers

// Execute cURL session
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
}

// Extract cookie from headers
$cookies = [];
if (preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches)) {
    foreach ($matches[1] as $match) {
        list($key, $value) = explode('=', $match, 2);
        $cookies[] = "$key=$value";
    }
}
$cookie_string = implode('; ', $cookies);

// Now make the actual request with the cookie
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false); // Don't get headers this time
curl_setopt($ch, CURLOPT_COOKIE, $cookie_string);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));


// Execute cURL session
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
}

// Close cURL session
curl_close($ch);

// Print the response from the server
echo $response;
?>
