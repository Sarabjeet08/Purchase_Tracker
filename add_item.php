<?php
require_once 'db.php';
require_once 'session.php';
requireLogin();

$error = '';
$categories = ['Food', 'Transport', 'Utilities', 'Shopping', 'Entertainment', 'Other'];
// Check if the user has any categories saved in the database
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productName = $_POST['product_name'] ?? '';
    $quantity = intval($_POST['quantity'] ?? 0);
    $unitPrice = floatval(str_replace(['$', ','], '', $_POST['unit_price'] ?? '0'));
    $purchaseDate = $_POST['purchase_date'] ?? date('Y-m-d');
    $category = $_POST['category'] ?? '';
    // Validate inputs
    if (empty($productName) || $quantity <= 0 || $unitPrice <= 0 || empty($category)) {
        $error = 'Please fill in all required fields';
    } else {
        try {
            $receiptPath = '';
            if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'uploads/';
                if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);
                
                $fileExtension = strtolower(pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION));
                if (!in_array($fileExtension, ['jpg', 'jpeg', 'png', 'pdf'])) {
                    throw new Exception('Invalid file type');
                }
                
                $receiptPath = uniqid() . '.' . $fileExtension;
                move_uploaded_file($_FILES['receipt']['tmp_name'], $uploadDir . $receiptPath);
            }
            
            $totalAmount = $quantity * $unitPrice;
            $stmt = $pdo->prepare("INSERT INTO purchases (user_id, product_name, quantity, unit_price, total_amount, purchase_date, receipt_path, category) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $productName, $quantity, $unitPrice, $totalAmount, $purchaseDate, $receiptPath, $category]);
            
            header('Location: dashboard.php');
            exit();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Purchase - Purchase Tracker</title>
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
                <a href="dashboard.php" class="back-btn">Back</a>
            </div>
        </header>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="purchase-form">
                    <h2 class="text-center mb-4">Add New Purchase</h2>

                    <?php if ($error): ?>
                        <div class="error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="add_item.php" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="product_name" class="form-label">Product Name</label>
                                <input type="text" class="form-control" id="product_name" name="product_name" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category</label>
                                <select id="category" name="category" class="form-select" required>
                                    <option value="">Select category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="unit_price" class="form-label">Unit Price ($)</label>
                                <input type="number" class="form-control" id="unit_price" name="unit_price" step="0.01" min="0.01" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Total Amount</label>
                            <input type="text" class="form-control" id="total_amount" readonly>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="purchase_date" class="form-label">Date</label>
                                <input type="date" class="form-control" id="purchase_date" name="purchase_date" 
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="receipt" class="form-label">Receipt (optional)</label>
                                <input type="file" class="form-control" id="receipt" name="receipt" 
                                       accept=".jpg,.jpeg,.png,.pdf">
                                <div class="form-text">Accepted formats: JPG, PNG, PDF</div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Add Purchase</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white text-center py-3">
        <p class="mb-0">Final Exam</p>
        <p class="mb-0">Student ID: 2411801</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()

        // Calculate total amount
        document.getElementById('quantity').addEventListener('input', calculateTotal);
        document.getElementById('unit_price').addEventListener('input', calculateTotal);

        function calculateTotal() {
            const quantity = parseFloat(document.getElementById('quantity').value) || 0;
            const unitPrice = parseFloat(document.getElementById('unit_price').value) || 0;
            document.getElementById('total_amount').value = '$' + (quantity * unitPrice).toFixed(2);
        }
    </script>
</body>
</html> 