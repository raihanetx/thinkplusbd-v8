<?php
session_start();
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

if (!isset($_SESSION['admin_logged_in_thinkplusbd']) || $_SESSION['admin_logged_in_thinkplusbd'] !== true) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    session_destroy();
    header("Location: admin_login.php");
    exit();
}

function getStatsForPeriod($orders, $startDate, $endDate) {
    $stats = [
        'total_orders' => 0,
        'confirmed_orders' => 0,
        'cancelled_orders' => 0,
        'pending_orders_in_period' => 0,
        'total_revenue' => 0.0
    ];
    if (!is_array($orders)) $orders = [];
    foreach ($orders as $order) {
        $orderTimestamp = isset($order['timestamp']) ? strtotime($order['timestamp']) : 0;
        $orderStatus = strtolower($order['status'] ?? 'unknown');
        $orderTotalAmount = floatval($order['totalAmount'] ?? 0);
        
        if ($orderTimestamp >= $startDate && $orderTimestamp <= $endDate) {
            $stats['total_orders']++;
            if ($orderStatus === 'confirmed') {
                $stats['confirmed_orders']++;
                if (!isset($order['is_deleted']) || $order['is_deleted'] !== true || (isset($order['confirmed_at']) && (!isset($order['deleted_at']) || strtotime($order['deleted_at']) > strtotime($order['confirmed_at'])) ) ) {
                    $stats['total_revenue'] += $orderTotalAmount;
                }
            } elseif ($orderStatus === 'cancelled') {
                $stats['cancelled_orders']++;
            } elseif ($orderStatus === 'pending') {
                $stats['pending_orders_in_period']++;
            }
        }
    }
    return $stats;
}

function getCurrentTotalPendingOrders($orders) {
    $count = 0;
    if (!is_array($orders)) $orders = [];
    foreach ($orders as $order) {
        if (strtolower($order['status'] ?? 'unknown') === 'pending' && (!isset($order['is_deleted']) || $order['is_deleted'] !== true)) {
            $count++;
        }
    }
    return $count;
}

$orders_file_path = __DIR__ . '/orders.json';
$all_site_orders_for_stats = []; 
$orders_for_display = [];      
$json_load_error = null;

if (file_exists($orders_file_path)) {
    $json_order_data = file_get_contents($orders_file_path);
    if ($json_order_data === false) {
        $json_load_error = "Could not read orders.json file.";
    } elseif (!empty($json_order_data)) {
        $decoded_orders = json_decode($json_order_data, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_orders)) {
            $all_site_orders_for_stats = $decoded_orders; 
            foreach ($all_site_orders_for_stats as $order) {
                if (!isset($order['is_deleted']) || $order['is_deleted'] !== true) {
                    $orders_for_display[] = $order;
                }
            }
            usort($orders_for_display, function($a, $b) { 
                $timeA = isset($a['timestamp']) ? strtotime($a['timestamp']) : 0;
                $timeB = isset($b['timestamp']) ? strtotime($b['timestamp']) : 0;
                return $timeB - $timeA;
            });
        } else {
            $json_load_error = "Critical Error: Could not decode orders.json. Error: " . json_last_error_msg();
        }
    }
}

date_default_timezone_set('Asia/Dhaka'); 
$today_start = strtotime('today midnight');
$today_end = strtotime('tomorrow midnight') - 1;
$week_start = strtotime('-6 days midnight', $today_start);
$month_start = strtotime('-29 days midnight', $today_start);
$ninety_days_start = strtotime('-89 days midnight', $today_start);
$year_start = strtotime('-364 days midnight', $today_start);

$stats_today = getStatsForPeriod($all_site_orders_for_stats, $today_start, $today_end);
$stats_week = getStatsForPeriod($all_site_orders_for_stats, $week_start, $today_end);
$stats_month = getStatsForPeriod($all_site_orders_for_stats, $month_start, $today_end);
$stats_90_days = getStatsForPeriod($all_site_orders_for_stats, $ninety_days_start, $today_end);
$stats_year = getStatsForPeriod($all_site_orders_for_stats, $year_start, $today_end);
$current_total_pending_all_time = getCurrentTotalPendingOrders($all_site_orders_for_stats);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - THINK PLUS BD</title>
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
                    <li><a href="admin_dashboard.php" class="<?php echo (strpos($_SERVER['REQUEST_URI'], 'admin_dashboard.php') !== false && empty($_GET['page']) && strpos($_SERVER['REQUEST_URI'], 'product_code_generator.html') === false) ? 'active' : ''; ?>"><i class="fas fa-chart-pie"></i> <span>Dashboard</span></a></li>
                    <li><a href="admin_dashboard.php?page=categories" class="<?php echo (isset($_GET['page']) && $_GET['page'] === 'categories') ? 'active' : ''; ?>"><i class="fas fa-tags"></i> <span>Manage Categories</span></a></li>
                    <li><a href="edit_products.php"><i class="fas fa-edit"></i> <span>Edit Products</span></a></li>
                    <li><a href="product_code_generator.html" target="_blank"><i class="fas fa-plus-circle"></i> <span>Add Product Helper</span></a></li>
                    <li><a href="admin_dashboard.php?logout=1"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
                </ul>
            </nav>
        </aside>
        <main class="admin-main-content" id="adminMainContent">
            <header class="admin-topbar">
                <div style="display:flex; align-items:center;">
                    <i class="fas fa-bars sidebar-toggle" id="sidebarToggle"></i>
                    <h1>Admin Panel</h1>
                </div>
                <a href="admin_dashboard.php?logout=1" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </header>
            <div class="admin-page-content">
            <?php if (isset($_GET['page']) && $_GET['page'] === 'categories'): ?>
                <div class="content-card">
                    <h2 class="card-title">Manage Categories</h2>
                    <?php
                    if (isset($_GET['status'])) {
                        if ($_GET['status'] == 'added') {
                            echo '<div class="alert-message alert-success">Category successfully added!</div>';
                        } elseif ($_GET['status'] == 'deleted') {
                            echo '<div class="alert-message alert-success">Category successfully deleted!</div>';
                        }
                    }
                    if (isset($_GET['error'])) {
                        if ($_GET['error'] == 'empty_fields') {
                            echo '<div class="alert-message alert-danger">Error: All fields are required.</div>';
                        }
                    }
                    ?>
                    <form method="POST" action="manage_categories.php" style="margin-bottom: 2rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 1rem; align-items: end;">
                            <div>
                                <label for="category_name" style="display:block; margin-bottom: .5rem; font-weight: 500;">Category Name</label>
                                <input type="text" id="category_name" name="category_name" placeholder="e.g., Course" required style="width: 100%; padding: 0.5rem; border-radius: var(--border-radius); border: 1px solid var(--border-color);">
                            </div>
                            <div>
                                <label for="category_icon" style="display:block; margin-bottom: .5rem; font-weight: 500;">Font Awesome Icon</label>
                                <input type="text" id="category_icon" name="category_icon" placeholder="e.g., fas fa-graduation-cap" required style="width: 100%; padding: 0.5rem; border-radius: var(--border-radius); border: 1px solid var(--border-color);">
                            </div>
                            <div>
                                <label for="category_subtitle" style="display:block; margin-bottom: .5rem; font-weight: 500;">Subtitle</label>
                                <input type="text" id="category_subtitle" name="category_subtitle" placeholder="e.g., Premium Courses" required style="width: 100%; padding: 0.5rem; border-radius: var(--border-radius); border: 1px solid var(--border-color);">
                            </div>
                            <button type="submit" name="add_category" style="padding: 0.5rem 1rem; border: none; background-color: var(--primary-color); color: white; border-radius: var(--border-radius); cursor: pointer; height: fit-content;">Add Category</button>
                        </div>
                    </form>

                    <div class="orders-table-container">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Icon</th>
                                    <th>Subtitle</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $categories_file_path = __DIR__ . '/categories.json';
                                if (file_exists($categories_file_path)) {
                                    $categories_json = file_get_contents($categories_file_path);
                                    $categories = json_decode($categories_json, true);
                                    if (is_array($categories)) {
                                        foreach ($categories as $category) {
                                            echo '<tr>';
                                            echo '<td data-label="Name">' . htmlspecialchars($category['name']) . '</td>';
                                            echo '<td data-label="Icon">' . htmlspecialchars($category['icon']) . '</td>';
                                            echo '<td data-label="Subtitle">' . htmlspecialchars($category['subtitle']) . '</td>';
                                            echo '<td data-label="Action">
                                                    <form method="POST" action="manage_categories.php" onsubmit="return confirm(\'Are you sure you want to delete this category?\');">
                                                        <input type="hidden" name="category_name" value="' . htmlspecialchars($category['name']) . '">
                                                        <button type="submit" name="delete_category" class="action-btn action-btn-delete" style="color: #dc3545 !important; border-color: #dc3545;">Delete</button>
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
            <?php else: ?>
                <div class="content-card">
                    <h2 class="card-title">Performance Overview</h2>
                    <div class="stats-period-selector">
                        <label for="period_selector">Showing stats for:</label>
                        <select id="period_selector" onchange="updateStatsDisplay(this.value)">
                            <option value="today" selected>Today</option>
                            <option value="week">Last 7 Days</option>
                            <option value="month">Last 30 Days</option>
                            <option value="ninetydays">Last 90 Days</option>
                            <option value="year">Last 365 Days</option>
                        </select>
                        <p>
                            <strong>Pending (All Time):</strong> 
                            <span id="currentTotalPendingAllTime"><?php echo $current_total_pending_all_time; ?></span>
                        </p>
                    </div>
                    <div id="stats-display-area">
                        <div class="stat-card"><h4>Total Orders</h4><p id="stat_total_orders">0</p></div>
                        <div class="stat-card"><h4>Confirmed</h4><p id="stat_confirmed_orders">0</p></div>
                        <div class="stat-card"><h4>Cancelled</h4><p id="stat_cancelled_orders">0</p></div>
                        <div class="stat-card"><h4>Pending (Period)</h4><p id="stat_pending_orders_in_period">0</p></div>
                        <div class="stat-card" id="stat_total_revenue_card"><h4>Total Revenue</h4><p id="stat_total_revenue">৳0.00</p></div>
                    </div>
                </div>
                <?php endif; ?>
                <div class="content-card">
                    <h2 class="card-title">Manage Orders</h2>
                    <?php if ($json_load_error): ?>
                        <div class="alert-message alert-danger"><?php echo htmlspecialchars($json_load_error); ?></div>
                    <?php endif; ?>
                    <?php
                        // Display success/error messages from GET parameters
                        if (isset($_GET['status_change'])) {
                            $changed_order_id = isset($_GET['orderid']) ? htmlspecialchars($_GET['orderid']) : '';
                            if ($_GET['status_change'] == 'success') {
                                $new_status = isset($_GET['new_status']) ? htmlspecialchars($_GET['new_status']) : 'updated';
                                echo '<div class="alert-message alert-success">Order ' . $changed_order_id . ' successfully marked as ' . $new_status . '!</div>';
                            } elseif ($_GET['status_change'] == 'marked_as_deleted') {
                                echo '<div class="alert-message alert-success">Order ' . $changed_order_id . ' successfully hidden from active list.</div>';
                            }
                        }
                        if (isset($_GET['error'])) {
                             echo '<div class="alert-message alert-danger">Error: ' . htmlspecialchars(str_replace('_', ' ', $_GET['error'])) . '</div>';
                        }
                    ?>
                    <div class="orders-table-container">
                        <?php if (empty($orders_for_display) && !$json_load_error): ?>
                            <p class='no-orders-message'>No active orders to display.</p>
                        <?php elseif (!empty($orders_for_display)): ?>
                            <table class='orders-table'>
                            <thead><tr><th>Order ID</th><th>Date</th><th>Customer</th><th>Contact</th><th>TrxID</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>
                            <?php foreach ($orders_for_display as $single_order): ?>
                                <tr>
                                <td data-label='Order ID' style="font-weight:500;"><?php echo htmlspecialchars($single_order['id']); ?></td>
                                <td data-label='Date'><?php echo htmlspecialchars(date('d M Y, H:i', (isset($single_order['timestamp']) ? strtotime($single_order['timestamp']) : time()))); ?></td>
                                <td data-label='Customer'><strong><?php echo htmlspecialchars($single_order['customer']['name'] ?? 'N/A'); ?></strong><small><?php echo htmlspecialchars($single_order['customer']['email'] ?? 'N/A'); ?></small></td>
                                <td data-label='Contact'><?php echo htmlspecialchars($single_order['customer']['phone'] ?? 'N/A'); ?></td>
                                <td data-label='TrxID'><?php echo htmlspecialchars($single_order['transactionId'] ?? 'N/A'); ?></td>
                                <td data-label='Items'><ul class='order-items-list-admin'>
                                <?php if (isset($single_order['items']) && is_array($single_order['items'])): foreach ($single_order['items'] as $item):
                                    $item_name = htmlspecialchars($item['name'] ?? 'Unknown');
                                    $item_quantity = htmlspecialchars($item['quantity'] ?? 1);
                                    $item_price = htmlspecialchars(number_format(floatval($item['price'] ?? 0), 0)); // Price without decimals for cleaner look
                                    $item_duration = isset($item['selectedDurationLabel']) && !empty($item['selectedDurationLabel']) ? ' (' . htmlspecialchars($item['selectedDurationLabel']) . ')' : '';
                                ?>
                                    <li><?php echo $item_name . $item_duration; ?> (x<?php echo $item_quantity; ?>)</li>
                                <?php endforeach; endif; ?>
                                </ul></td>
                                <td data-label='Total' style="font-weight:600; color:var(--text-color);">৳<?php echo htmlspecialchars(number_format(floatval($single_order['totalAmount'] ?? 0), 0)); ?></td>
                                <td data-label='Payment'><?php echo htmlspecialchars(ucfirst($single_order['paymentMethod'] ?? 'N/A')); ?></td>
                                <?php
                                    $order_status_val = strtolower($single_order['status'] ?? 'unknown');
                                    $status_class_name = 'status-' . str_replace(' ', '-', $order_status_val);
                                    if (!in_array($status_class_name, ['status-pending', 'status-confirmed', 'status-cancelled'])) {
                                        $status_class_name = 'status-unknown';
                                    }
                                ?>
                                <td data-label='Status'><span class='status-badge <?php echo $status_class_name; ?>'><?php echo htmlspecialchars($order_status_val); ?></span></td>
                                <td data-label='Actions'>
                                <div class="action-buttons-group">
                                <?php if ($order_status_val === 'pending'): ?>
                                    <form method='POST' action='confirm_order.php' style='display:inline;'>
                                        <input type='hidden' name='order_id_to_change' value='<?php echo htmlspecialchars($single_order['id']); ?>'>
                                        <input type='hidden' name='new_status' value='Confirmed'><button type='submit' class='action-btn action-btn-confirm'>Confirm</button>
                                    </form>
                                    <form method='POST' action='confirm_order.php' style='display:inline;'>
                                        <input type='hidden' name='order_id_to_change' value='<?php echo htmlspecialchars($single_order['id']); ?>'>
                                        <input type='hidden' name='new_status' value='Cancelled'><button type='submit' class='action-btn action-btn-cancel'>Cancel</button>
                                    </form>
                                <?php elseif ($order_status_val === 'confirmed'): ?>
                                    <span class='action-btn-text confirmed'>Confirmed <small><?php if(isset($single_order['confirmed_at'])) echo htmlspecialchars(date('d M, H:i', strtotime($single_order['confirmed_at']))); ?></small></span>
                                <?php elseif ($order_status_val === 'cancelled'): ?>
                                    <span class='action-btn-text cancelled'>Cancelled <small><?php if(isset($single_order['cancelled_at'])) echo htmlspecialchars(date('d M, H:i', strtotime($single_order['cancelled_at']))); ?></small></span>
                                <?php endif; ?>
                                <?php // Hide button is always available for processed orders if needed, or only for pending if preferred ?>
                                <form method='POST' action='delete_order.php' style='display:inline;' onsubmit="return confirm('Are you sure you want to hide Order ID: <?php echo htmlspecialchars($single_order['id']); ?>?');">
                                    <input type='hidden' name='order_id_to_delete' value='<?php echo htmlspecialchars($single_order['id']); ?>'>
                                    <button type='submit' class='action-btn action-btn-delete' title='Hide this order from the active list'>Hide</button>
                                </form>
                                </div>
                                </td></tr>
                            <?php endforeach; ?>
                            </tbody></table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        const allStatsDataFromPHP = {
            today: <?php echo json_encode($stats_today); ?>,
            week: <?php echo json_encode($stats_week); ?>,
            month: <?php echo json_encode($stats_month); ?>,
            ninetydays: <?php echo json_encode($stats_90_days); ?>,
            year: <?php echo json_encode($stats_year); ?>
        };

        function updateStatsDisplay(period) {
            const selectedStats = allStatsDataFromPHP[period];
            if (selectedStats) {
                document.getElementById('stat_total_orders').textContent = selectedStats.total_orders || 0;
                document.getElementById('stat_confirmed_orders').textContent = selectedStats.confirmed_orders || 0;
                document.getElementById('stat_cancelled_orders').textContent = selectedStats.cancelled_orders || 0;
                document.getElementById('stat_pending_orders_in_period').textContent = selectedStats.pending_orders_in_period || 0;
                document.getElementById('stat_total_revenue').textContent = '৳' + (parseFloat(selectedStats.total_revenue) || 0).toFixed(2);
            } else { 
                document.getElementById('stat_total_orders').textContent = '0';
                document.getElementById('stat_confirmed_orders').textContent = '0';
                document.getElementById('stat_cancelled_orders').textContent = '0';
                document.getElementById('stat_pending_orders_in_period').textContent = '0';
                document.getElementById('stat_total_revenue').textContent = '৳0.00';
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            updateStatsDisplay(document.getElementById('period_selector').value);
        });
    </script>
    <script src="admin_dashboard.js"></script>
</body>
</html>