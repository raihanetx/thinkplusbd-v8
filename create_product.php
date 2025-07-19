<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['admin_logged_in_thinkplusbd']) || $_SESSION['admin_logged_in_thinkplusbd'] !== true) {
    header("Location: admin_login.php");
    exit();
}

function get_products() {
    $products_file_path = __DIR__ . '/products.json';
    if (!file_exists($products_file_path)) {
        return [];
    }
    $json_data = file_get_contents($products_file_path);
    return json_decode($json_data, true);
}

function save_products($products) {
    $products_file_path = __DIR__ . '/products.json';
    $json_data = json_encode($products, JSON_PRETTY_PRINT);
    file_put_contents($products_file_path, $json_data);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $products = get_products();
    $new_product_id = count($products) > 0 ? max(array_column($products, 'id')) + 1 : 1;

    $image_path = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'product_images/';
        $file_name = basename($_FILES['image']['name']);
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }

    $new_product = [
        'id' => $new_product_id,
        'name' => $_POST['name'],
        'description' => $_POST['description'],
        'longDescription' => $_POST['longDescription'],
        'category' => $_POST['category'],
        'price' => (float)$_POST['price'],
        'image' => $image_path,
        'isFeatured' => isset($_POST['isFeatured']),
        'durations' => []
    ];

    $products[] = $new_product;
    save_products($products);

    header("Location: edit_products.php?category=" . urlencode($_POST['category']) . "&status=added");
    exit();
}

header("Location: add_product.php?error=failed");
exit();
?>
