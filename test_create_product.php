<?php
// This script will simulate the form submission to create_product.php

$url = 'https://test-website.great-site.net/create_product.php';

// The data to be sent in the POST request
$data = [
    'name' => 'Test Product',
    'description' => 'This is a test product.',
    'longDescription' => 'This is a long description for the test product.',
    'price' => '10.99',
    'category' => 'software',
    'isFeatured' => 'true',
];

// The image file to be uploaded
$image_path = 'test_image.png';

// Create a dummy image file
$image_content = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
file_put_contents($image_path, base64_decode($image_content));

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


// Set cURL options for POST
$boundary = '--------------------------' . microtime(true);
$content = '';

// Add form fields
foreach ($data as $key => $value) {
    $content .= "--" . $boundary . "\r\n" .
                "Content-Disposition: form-data; name=\"" . $key . "\"\r\n\r\n" .
                $value . "\r\n";
}

// Add image file
$content .= "--" . $boundary . "\r\n" .
            "Content-Disposition: form-data; name=\"image\"; filename=\"" . basename($image_path) . "\"\r\n" .
            "Content-Type: image/png\r\n\r\n" .
            file_get_contents($image_path) . "\r\n";

$content .= "--" . $boundary . "--\r\n";

curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: multipart/form-data; boundary=' . $boundary,
    'Content-Length: ' . strlen($content)
]);

// Execute cURL session
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
}

// Close cURL session
curl_close($ch);

// Clean up the dummy image file
unlink($image_path);

// Print the response from the server
echo $response;
?>
