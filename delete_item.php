<?php
require_once 'db.php';
require_once 'session.php';
requireLogin();

if (isset($_GET['id'])) {
    $purchaseId = $_GET['id'];
    
    // Verify that the purchase belongs to the current user
    $stmt = $pdo->prepare("SELECT receipt_path FROM purchases WHERE id = ? AND user_id = ?");
    $stmt->execute([$purchaseId, $_SESSION['user_id']]);
    $purchase = $stmt->fetch();
    
    if ($purchase) {
        // Delete the receipt file if it exists
        if ($purchase['receipt_path'] && file_exists('uploads/' . $purchase['receipt_path'])) {
            unlink('uploads/' . $purchase['receipt_path']);
        }
        
        // Delete the purchase record
        $stmt = $pdo->prepare("DELETE FROM purchases WHERE id = ? AND user_id = ?");
        $stmt->execute([$purchaseId, $_SESSION['user_id']]);
    }
}

header('Location: dashboard.php');
exit();
?>