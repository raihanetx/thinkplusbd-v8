<?php
session_start();

if (!isset($_SESSION['admin_logged_in_thinkplusbd']) || $_SESSION['admin_logged_in_thinkplusbd'] !== true) {
    header("Location: admin_login.php");
    exit();
}

$categories_file_path = __DIR__ . '/categories.json';

function get_categories() {
    global $categories_file_path;
    if (!file_exists($categories_file_path)) {
        return [];
    }
    $json_data = file_get_contents($categories_file_path);
    return json_decode($json_data, true);
}

function save_categories($categories) {
    global $categories_file_path;
    $json_data = json_encode($categories, JSON_PRETTY_PRINT);
    file_put_contents($categories_file_path, $json_data);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $new_category_name = trim($_POST['category_name']);
        $new_category_icon = trim($_POST['category_icon']);
        $new_category_subtitle = trim($_POST['category_subtitle']);

        if (!empty($new_category_name) && !empty($new_category_icon) && !empty($new_category_subtitle)) {
            $categories = get_categories();
            $new_category = [
                'name' => $new_category_name,
                'icon' => $new_category_icon,
                'subtitle' => $new_category_subtitle
            ];
            $categories[] = $new_category;
            save_categories($categories);
            header("Location: admin_dashboard.php?page=categories&status=added");
        } else {
            header("Location: admin_dashboard.php?page=categories&error=empty_fields");
        }
    }

    if (isset($_POST['delete_category'])) {
        $category_name_to_delete = $_POST['category_name'];
        $categories = get_categories();
        $categories = array_filter($categories, function($category) use ($category_name_to_delete) {
            return $category['name'] !== $category_name_to_delete;
        });
        save_categories(array_values($categories));
        header("Location: admin_dashboard.php?page=categories&status=deleted");
    }
}
?>
