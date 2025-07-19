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
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

    if ($product_id > 0) {
        $products = get_products();
        $product_index = -1;

        foreach ($products as $index => $product) {
            if ($product['id'] === $product_id) {
                $product_index = $index;
                break;
            }
        }

        if ($product_index !== -1) {
            $products[$product_index]['name'] = $_POST['name'];
            $products[$product_index]['description'] = $_POST['description'];
            $products[$product_index]['longDescription'] = $_POST['longDescription'];
            $products[$product_index]['price'] = (float)$_POST['price'];

            $durations = [];
            if (isset($_POST['durations']) && is_array($_POST['durations'])) {
                foreach ($_POST['durations'] as $duration) {
                    if (!empty($duration['label']) && !empty($duration['price'])) {
                        $durations[] = [
                            'label' => $duration['label'],
                            'price' => (float)$duration['price']
                        ];
                    }
                }
            }
            $products[$product_index]['durations'] = $durations;

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'product_images/';
                $file_name = basename($_FILES['image']['name']);
                $target_file = $upload_dir . $file_name;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $products[$product_index]['image'] = $target_file;
                }
            }

            save_products($products);
            header("Location: edit_products.php?category=" . urlencode($products[$product_index]['category']) . "&status=updated");
            exit();
        }
    }
}

header("Location: edit_products.php?error=update_failed");
exit();
?>
