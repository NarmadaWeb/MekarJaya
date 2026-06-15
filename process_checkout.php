<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/functions.php';
require_once 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['cart'])) {
    header('Location: katalog.php');
    exit;
}

require_login();
$user_id = $_SESSION['user_id'];
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$address = $_POST['address'] ?? '';
$shipping_method = $_POST['shipping_method'] ?? 'Standard';
$payment_method = $_POST['payment_method'] ?? 'Bank Transfer';

// Calculate total
$subtotal = 0;
$ids = array_keys($_SESSION['cart']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM produk WHERE produk_id IN ($placeholders)");
$stmt->execute($ids);
$products = $stmt->fetchAll();

$items_to_save = [];
foreach ($products as $p) {
    $qty = $_SESSION['cart'][$p['produk_id']];
    $total = $p['harga'] * $qty;
    $subtotal += $total;
    $items_to_save[] = [
        'product_id' => $p['produk_id'],
        'quantity' => $qty,
        'price' => $p['harga']
    ];
}

$shipping_cost = 25000;
$admin_fee = 2000;
$grand_total = $subtotal + $shipping_cost + $admin_fee;

try {
    $pdo->beginTransaction();

    // Set initial status based on payment method
    // COD is immediately Processed (Diterima/Diproses), Midtrans starts as Pending
    $status = ($payment_method === 'COD') ? 'Processed' : 'Pending';

    // Insert Order
    $stmt = $pdo->prepare("INSERT INTO pesanan (pengguna_id, total_harga, metode_pengiriman, metode_pembayaran, alamat_pengiriman, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $grand_total, $shipping_method, $payment_method, $address, $status]);
    $order_id = $pdo->lastInsertId();

    // Insert Order Items
    $stmt = $pdo->prepare("INSERT INTO detail_pesanan (pesanan_id, produk_id, jumlah, harga) VALUES (?, ?, ?, ?)");
    foreach ($items_to_save as $item) {
        $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
    }

    // Create notification for admin
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS notifikasi (
            notifikasi_id INTEGER PRIMARY KEY AUTOINCREMENT,
            pesanan_id INTEGER DEFAULT NULL,
            judul TEXT NOT NULL,
            pesan TEXT DEFAULT NULL,
            dibaca INTEGER DEFAULT 0,
            dibuat_pada TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $stmt_notif = $pdo->prepare("INSERT INTO notifikasi (pesanan_id, judul, pesan) VALUES (?, ?, ?)");
        $stmt_notif->execute([
            $order_id,
            'Pesanan Baru #MBM-' . $order_id,
            'Pesanan baru senilai ' . format_rupiah($grand_total) . ' dari ' . e($name) . ' (' . e($payment_method) . ')'
        ]);
    } catch (Exception $e) {
        // Notif table may not exist, silently ignore
    }

    $pdo->commit();

    // Clear Cart
    $_SESSION['cart'] = [];

    // Redirect to payments page with method indicator
    $method_param = ($payment_method === 'COD') ? 'cod' : 'midtrans';
    header("Location: pembayaran.php?order_id=" . $order_id . "&method=" . $method_param);
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    die("Error processing order: " . $e->getMessage());
}
?>
