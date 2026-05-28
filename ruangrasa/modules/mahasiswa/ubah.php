<?php
require_once '../../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$type = $_GET['type'] ?? '';
$id = intval($_GET['id'] ?? 0);

try {
    if ($type === 'user_role' && $id > 0) {
        // Prevent admin from changing their own role
        if ($id == $_SESSION['user_id']) {
            error_log("Admin attempted to change own role: user_id={$id}");
            header('Location: admin.php');
            exit();
        }
        
        $new_role = $_GET['role'] ?? '';
        if (in_array($new_role, ['admin', 'user'])) {
            $stmt = $koneksi->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $new_role, $id);
            if (!$stmt->execute()) {
                error_log("Update user role failed: " . $stmt->error);
            }
            $stmt->close();
        }
    } elseif ($type === 'order' && $id > 0) {
        $new_status = $_GET['status'] ?? '';
        if (in_array($new_status, ['pending', 'dimasak', 'selesai'])) {
            $stmt = $koneksi->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
            $stmt->bind_param("si", $new_status, $id);
            if (!$stmt->execute()) {
                error_log("Update order status failed: " . $stmt->error);
            }
            $stmt->close();
        }
    }
} catch (Exception $e) {
    error_log("Update error: " . $e->getMessage());
}

header('Location: admin.php');
exit();
?>
