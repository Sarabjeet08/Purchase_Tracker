<?php
require_once 'db.php';
require_once 'session.php';
requireLogin();

$categoryFilter = $_GET['category'] ?? '';

// Get purchases
$query = "SELECT * FROM purchases WHERE user_id = ?";
$params = [$_SESSION['user_id']];

if ($categoryFilter) {
    $query .= " AND category = ?";
    $params[] = $categoryFilter;
}
// Get the total number of purchases for pagination
$query .= " ORDER BY purchase_date DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$purchases = $stmt->fetchAll();

// Get categories for filter
$stmt = $pdo->prepare("SELECT DISTINCT category FROM purchases WHERE user_id = ? ORDER BY category");
$stmt->execute([$_SESSION['user_id']]);
$userCategories = $stmt->fetchAll();

// Calculate total spending
$stmt = $pdo->prepare("SELECT SUM(total_amount) as total FROM purchases WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$totalSpending = $stmt->fetch()['total'] ?? 0;

// Calculate spending by category
$stmt = $pdo->prepare("SELECT category, SUM(total_amount) as total 
                      FROM purchases 
                      WHERE user_id = ? 
                      GROUP BY category 
                      ORDER BY total DESC");
$stmt->execute([$_SESSION['user_id']]);
$categorySpending = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Purchase Tracker</title>
    <!-- To save time, I've used Copilot for design via Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
    <div class="container">
        <header class="d-flex justify-content-between align-items-center py-3">
            <h1 class="h3 mb-0">Purchase Tracker</h1>
            <div class="header-links">
                <a href="dashboard.php" class="home-btn">Home</a>
                <div class="user-info">
                    Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                    <a href="logout.php" class="logout-btn">Logout</a>
                </div>
            </div>
        </header>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="summary-card">
                    <h3 class="h5 text-muted">Total Spending</h3>
                    <p class="amount h3">$<?php echo number_format($totalSpending, 2); ?></p>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="category-filter">
                    <form method="GET" action="dashboard.php" class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label for="category" class="form-label">Filter by Category:</label>
                            <select name="category" id="category" class="form-select" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                <?php foreach ($userCategories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                            <?php echo $categoryFilter === $cat['category'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['category']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="category-breakdown mb-4">
            <h3 class="h5 mb-3">Spending by Category</h3>
            <div class="row g-3">
                <?php foreach ($categorySpending as $cat): ?>
                    <div class="col-md-4">
                        <div class="category-item">
                            <span class="category-name"><?php echo htmlspecialchars($cat['category']); ?></span>
                            <span class="category-amount">$<?php echo number_format($cat['total'], 2); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="mb-4">
            <a href="add_item.php" class="btn btn-primary">Add Purchase</a>
        </div>

        <div class="purchases-list">
            <?php if (empty($purchases)): ?>
                <div class="alert alert-info">No purchases found. Add your first purchase!</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Receipt</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($purchases as $purchase): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($purchase['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($purchase['category']); ?></td>
                                    <td><?php echo number_format($purchase['quantity']); ?></td>
                                    <td>$<?php echo number_format($purchase['unit_price'], 2); ?></td>
                                    <td>$<?php echo number_format($purchase['total_amount'], 2); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($purchase['purchase_date'])); ?></td>
                                    <td>
                                        <?php if ($purchase['receipt_path']): ?>
                                            <a href="uploads/<?php echo htmlspecialchars($purchase['receipt_path']); ?>" 
                                               target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                        <?php else: ?>
                                            <span class="text-muted">No receipt</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="delete_item.php?id=<?php echo $purchase['id']; ?>" 
                                           onclick="return confirm('Delete this purchase?')"
                                           class="btn btn-sm btn-outline-danger">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3">
        <p class="mb-0">Final Exam</p>
        <p class="mb-0">Student ID: 2411801</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 