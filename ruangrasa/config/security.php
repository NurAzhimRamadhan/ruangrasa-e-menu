<?php
/**
 * Security Helper Functions
 * Centralized security utilities untuk Ruang Rasa
 */

/**
 * Generate CSRF token jika belum ada
 * @return string CSRF token
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token dari POST request
 * @return bool True jika valid, false jika tidak
 */
function verify_csrf_token() {
    if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

/**
 * Print CSRF token sebagai hidden input
 */
function csrf_field() {
    $token = generate_csrf_token();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Safe HTML output (escape special characters)
 * @param string $text Text yang akan di-escape
 * @return string Escaped text
 */
function safe_output($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Validate uploaded image file
 * @param array $file $_FILES array entry
 * @param int $max_size Maximum file size in bytes (default 2MB)
 * @return array ['valid' => bool, 'error' => string, 'extension' => string]
 */
function validate_image_upload($file, $max_size = 2097152) {
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
    
    if (!isset($file) || $file['error'] !== 0) {
        return ['valid' => false, 'error' => 'File upload error', 'extension' => ''];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if ($file['size'] > $max_size) {
        return ['valid' => false, 'error' => 'Ukuran file maksimal ' . ($max_size / 1024 / 1024) . 'MB!', 'extension' => ''];
    }
    
    if (!in_array($ext, $allowed_extensions)) {
        return ['valid' => false, 'error' => 'Format file tidak didukung! Hanya JPG, PNG, GIF.', 'extension' => ''];
    }
    
    if (!in_array(mime_content_type($file['tmp_name']), $allowed_mime_types)) {
        return ['valid' => false, 'error' => 'File bukan gambar valid!', 'extension' => ''];
    }
    
    if (!getimagesize($file['tmp_name'])) {
        return ['valid' => false, 'error' => 'File bukan gambar valid!', 'extension' => ''];
    }
    
    return ['valid' => true, 'error' => '', 'extension' => $ext];
}

/**
 * Check if user is logged in
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if current user is admin
 * @return bool
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Require login or redirect
 * @param string $redirect_to URL to redirect if not logged in
 */
function require_login($redirect_to = '../../auth/login.php') {
    if (!is_logged_in()) {
        header('Location: ' . $redirect_to);
        exit();
    }
}

/**
 * Require admin access or redirect
 * @param string $redirect_to URL to redirect if not admin
 */
function require_admin($redirect_to = 'index.php') {
    if (!is_logged_in() || !is_admin()) {
        header('Location: ' . $redirect_to);
        exit();
    }
}
/**
 * Get image URL dengan fallback ke default jika file tidak ada
 * @param string $filename Nama file gambar
 * @param string $base_path Base path ke folder img (relative)
 * @param string $default Default filename jika tidak ada
 * @return string URL gambar yang valid
 */
function get_image_url($filename, $base_path = '../../img/', $default = 'default.png') {
    if (empty($filename) || !file_exists($base_path . $filename)) {
        return $base_path . $default;
    }
    return $base_path . $filename;
}

/**
 * Get profile image URL dengan fallback
 */
function profile_img($filename, $base_path = '../../img/') {
    return get_image_url($filename, $base_path, 'default.png');
}

/**
 * Get menu image URL dengan fallback
 */
function menu_img($filename, $base_path = '../../img/') {
    return get_image_url($filename, $base_path, 'default_menu.png');
}
?>