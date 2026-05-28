<?php
require_once '../../config/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$type = $_GET['type'] ?? '';
$id = intval($_GET['id'] ?? 0);
$confirmed = isset($_GET['confirmed']) && $_GET['confirmed'] === '1';

// Require confirmation untuk mencegah accidental delete
if (!$confirmed) {
    header('Location: konfirmasi_hapus.php?type=' . urlencode($type) . '&id=' . $id);
    exit();
}

try {
    if ($type === 'user' && $id > 0) {
        // Prevent admin from deleting themselves
        if ($id == $_SESSION['user_id']) {
            error_log("Admin attempted to delete own account: user_id={$id}");
            header('Location: admin.php');
            exit();
        }
        
        $stmt = $koneksi->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            error_log("Delete user failed: " . $stmt->error);
        }
        $stmt->close();
    } elseif ($type === 'kategori' && $id > 0) {
        $stmt = $koneksi->prepare("DELETE FROM kategori WHERE id = ?");
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            error_log("Delete kategori failed: " . $stmt->error);
        }
        $stmt->close();
    } elseif ($type === 'menu' && $id > 0) {
        // Get image filename first
        $stmt = $koneksi->prepare("SELECT gambar FROM menu WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if ($row['gambar'] !== 'default_menu.png' && file_exists('../../img/' . $row['gambar'])) {
                unlink('../../img/' . $row['gambar']);
            }
        }
        $stmt->close();
        
        // Delete menu record
        $stmt = $koneksi->prepare("DELETE FROM menu WHERE id = ?");
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            error_log("Delete menu failed: " . $stmt->error);
        }
        $stmt->close();
    }
} catch (Exception $e) {
    error_log("Deletion error: " . $e->getMessage());
}

header('Location: admin.php');
exit();
?>
