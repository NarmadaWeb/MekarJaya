<?php
require_once 'includes/functions.php';
require_once 'config/db.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    
    // Verify order ownership
    $stmt = $pdo->prepare("SELECT * FROM pesanan WHERE pesanan_id = ? AND pengguna_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        die("Order tidak ditemukan.");
    }
    
    if (isset($_FILES['bukti_transfer']) && $_FILES['bukti_transfer']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['bukti_transfer']['tmp_name'];
        $file_name = $_FILES['bukti_transfer']['name'];
        $file_size = $_FILES['bukti_transfer']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_exts = ['jpg', 'jpeg', 'png'];
        if (!in_array($file_ext, $allowed_exts)) {
            die("Format file tidak didukung. Harap upload gambar JPG atau PNG.");
        }
        
        // 2MB max
        if ($file_size > 2 * 1024 * 1024) {
            die("Ukuran file terlalu besar. Maksimal 2MB.");
        }
        
        $upload_dir = __DIR__ . '/assets/uploads/bukti/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $new_file_name = 'bukti_' . $order_id . '_' . time() . '.' . $file_ext;
        $dest_path = $upload_dir . $new_file_name;
        
        if (move_uploaded_file($file_tmp, $dest_path)) {
            // Update order payment_proof and status
            $stmt = $pdo->prepare("UPDATE pesanan SET bukti_pembayaran = ?, status = 'Processed' WHERE pesanan_id = ?");
            $stmt->execute(['assets/uploads/bukti/' . $new_file_name, $order_id]);
            
            header("Location: pembayaran.php?order_id=" . $order_id . "&success=1");
            exit;
        } else {
            die("Gagal mengunggah file.");
        }
    } else {
        die("Harap pilih file bukti transfer.");
    }
} else {
    header('Location: account/orders.php');
    exit;
}
?>
