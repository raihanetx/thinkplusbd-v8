<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['admin_logged_in_thinkplusbd']) || $_SESSION['admin_logged_in_thinkplusbd'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Function to get categories from JSON file
function get_categories() {
    $categories_file_path = __DIR__ . '/categories.json';
    if (!file_exists($categories_file_path)) {
        return [];
    }
    $json_data = file_get_contents($categories_file_path);
    return json_decode($json_data, true);
}

// Function to get products from JSON file
function get_products() {
    $products_file_path = __DIR__ . '/products.json';
    if (!file_exists($products_file_path)) {
        return [];
    }
    $json_data = file_get_contents($products_file_path);
    return json_decode($json_data, true);
}


$categories = get_categories();
$selected_category = isset($_GET['category']) ? $_GET['category'] : null;
$products = get_products();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Products - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="admin_dashboard.css">
</head>
<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="logo-admin">
                <img src="https://i.postimg.cc/4NtztqPt/IMG-20250603-130207-removebg-preview-1.png" alt="THINK PLUS BD Logo">
            </div>
            <nav class="admin-nav">
                <ul>
                    <li><a href="admin_dashboard.php"><i class="fas fa-chart-pie"></i> <span>Dashboard</span></a></li>
                    <li><a href="admin_dashboard.php?page=categories"><i class="fas fa-tags"></i> <span>Manage Categories</span></a></li>
                    <li><a href="edit_products.php" class="active"><i class="fas fa-edit"></i> <span>Edit Products</span></a></li>
                    <li><a href="product_code_generator.html" target="_blank"><i class="fas fa-plus-circle"></i> <span>Add Product Helper</span></a></li>
                    <li><a href="admin_dashboard.php?logout=1"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-main-content" id="adminMainContent">
            <header class="admin-topbar">
                <div style="display:flex; align-items:center;">
                    <i class="fas fa-bars sidebar-toggle" id="sidebarToggle"></i>
                    <h1>Edit Products</h1>
                </div>
                <a href="admin_dashboard.php?logout=1" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </header>
            <div class="admin-page-content">
                <div class="content-card">
                    <h2 class="card-title">Select a Category to Edit Products</h2>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <form method="GET" action="edit_products.php" style="margin-bottom: 0;">
                            <select name="category" onchange="this.form.submit()">
                                <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['name']); ?>" <?php if ($selected_category === $category['name']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    <a href="add_product.php" class="action-btn" style="text-decoration: none;">Add New Product</a>
                    </div>
                </div>

                <?php if ($selected_category): ?>
                <div class="content-card">
                    <h2 class="card-title">Editing Products in "<?php echo htmlspecialchars($selected_category); ?>"</h2>
                    <div class="orders-table-container">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($products) {
                                    foreach ($products as $product) {
                                        if (strtolower($product['category']) === strtolower($selected_category)) {
                                            echo '<tr>';
                                            echo '<td data-label="Product Name">' . htmlspecialchars($product['name']) . '</td>';
                                            echo '<td data-label="Actions">
                                                    <a href="product_editor.php?id=' . $product['id'] . '" class="action-btn">Edit</a>
                                                    <form method="POST" action="delete_product.php" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to delete this product?\');">
                                                        <input type="hidden" name="product_id" value="' . $product['id'] . '">
                                                        <button type="submit" class="action-btn action-btn-delete" style="color: #dc3545 !important; border-color: #dc3545;">Delete</button>
                                                    </form>
                                                  </td>';
                                            echo '</tr>';
                                        }
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script src="admin_dashboard.js"></script>
</body>
</html>
