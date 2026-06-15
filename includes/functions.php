<?php
if (!function_exists('format_rupiah')) {
    function format_rupiah($number) {
        return "Rp " . number_format($number, 0, ',', '.');
    }
}

/**
 * Escapes HTML output
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Basic Authentication Check
 */
function require_login() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'])) {
        $prefix = (str_starts_with($_SERVER['PHP_SELF'], '/account/') || str_starts_with($_SERVER['PHP_SELF'], '/admin/') || str_starts_with($_SERVER['PHP_SELF'], '/reseller/')) ? '../' : '';
        header("Location: " . $prefix . "login.php");
        exit;
    }
}

/**
 * Admin Authentication Check
 */
function require_admin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        $prefix = (str_starts_with($_SERVER['PHP_SELF'], '/account/') || str_starts_with($_SERVER['PHP_SELF'], '/admin/') || str_starts_with($_SERVER['PHP_SELF'], '/reseller/')) ? '../' : '';
        header("Location: " . $prefix . "login.php");
        exit;
    }
}

function get_all_products($pdo) {
    $stmt = $pdo->query("SELECT * FROM produk");
    return $stmt->fetchAll();
}

function get_featured_products($pdo) {
    $stmt = $pdo->query("SELECT * FROM produk WHERE unggulan = 1");
    return $stmt->fetchAll();
}

function get_product_by_id($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM produk WHERE produk_id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function get_faqs_by_category($pdo) {
    $faqs = $pdo->query("SELECT * FROM faq")->fetchAll();
    $grouped = [];
    foreach ($faqs as $faq) {
        $grouped[$faq['kategori']][] = $faq;
    }
    return $grouped;
}

function get_all_blog_posts($pdo) {
    return $pdo->query("SELECT * FROM artikel ORDER BY dibuat_pada DESC")->fetchAll();
}
?>
