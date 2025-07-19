<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['admin_logged_in_thinkplusbd']) || $_SESSION['admin_logged_in_thinkplusbd'] !== true) {
    header("Location: admin_login.php");
    exit();
}

function get_categories() {
    $categories_file_path = __DIR__ . '/categories.json';
    if (!file_exists($categories_file_path)) {
        return [];
    }
    $json_data = file_get_contents($categories_file_path);
    return json_decode($json_data, true);
}

$categories = get_categories();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Admin Panel</title>
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
                    <h1>Add Product</h1>
                </div>
                <a href="admin_dashboard.php?logout=1" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </header>
            <div class="admin-page-content">
                <div class="content-card">
                    <h2 class="card-title">Add a New Product</h2>
                    <form action="create_product.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label for="name">Product Title</label>
                            <input type="text" name="name" id="name" class="form-control" required style="width: 100%; padding: 0.5rem; border-radius: var(--border-radius); border: 1px solid var(--border-color);">
                        </div>

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label for="description">Short Description</label>
                            <textarea name="description" id="description" rows="3" class="form-control" required style="width: 100%; padding: 0.5rem; border-radius: var(--border-radius); border: 1px solid var(--border-color);"></textarea>
                        </div>

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label for="longDescription">Long Description</label>
                            <textarea name="longDescription" id="longDescription" rows="10" class="form-control" style="width: 100%; padding: 0.5rem; border-radius: var(--border-radius); border: 1px solid var(--border-color);"></textarea>
                        </div>

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label for="price">Price</label>
                            <input type="number" step="0.01" name="price" id="price" class="form-control" required style="width: 100%; padding: 0.5rem; border-radius: var(--border-radius); border: 1px solid var(--border-color);">
                        </div>

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label for="category">Category</label>
                            <select name="category" id="category" class="form-control" required style="width: 100%; padding: 0.5rem; border-radius: var(--border-radius); border: 1px solid var(--border-color);">
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo strtolower(htmlspecialchars($category['name'])); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label for="image">Product Image</label>
                            <input type="file" name="image" id="image" class="form-control-file" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label for="isFeatured">Featured Product?</label>
                            <input type="checkbox" name="isFeatured" id="isFeatured" value="true">
                        </div>

                        <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; border: none; background-color: var(--primary-color); color: white; border-radius: var(--border-radius); cursor: pointer;">Add Product</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script src="admin_dashboard.js"></script>
</body>
</html>
