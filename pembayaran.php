<?php
require_once 'includes/functions.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config/db.php';
require_login();

$order_id = (int)($_GET['order_id'] ?? 0);
$method = trim($_GET['method'] ?? 'midtrans');
$action = trim($_GET['action'] ?? '');
$success = (int)($_GET['success'] ?? 0);

// Fetch the order
$stmt = $pdo->prepare("SELECT * FROM pesanan WHERE pesanan_id = ? AND pengguna_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: account/orders.php');
    exit;
}

// Process mock Midtrans payment
if ($method === 'midtrans' && $action === 'pay' && $order['status'] === 'Pending') {
    try {
        $update = $pdo->prepare("UPDATE pesanan SET status = 'Processed' WHERE pesanan_id = ? AND pengguna_id = ?");
        $update->execute([$order_id, $_SESSION['user_id']]);
        
        // Also register mock payment record if pembayaran table exists
        try {
            $stmt_pay = $pdo->prepare("INSERT INTO pembayaran (pesanan_id, pengguna_id, transaksi_id) VALUES (?, ?, ?)");
            $mock_tx = 'TX-MIDTRANS-' . strtoupper(bin2hex(random_bytes(4)));
            $stmt_pay->execute([$order_id, $_SESSION['user_id'], $mock_tx]);
        } catch (PDOException $ex) {
            // Ignore if payments table is structured differently or not present
        }

        header("Location: pembayaran.php?order_id=" . $order_id . "&method=midtrans&success=1");
        exit;
    } catch (PDOException $e) {
        die("Gagal memproses pembayaran: " . $e->getMessage());
    }
}

$page_title = 'Pembayaran Pesanan #' . $order_id;
require_once 'includes/header.php';
?>

<main style="padding: 60px 0; background: #f8fafc; min-height: 85vh; font-family: 'Inter', sans-serif;">
    <div class="container" style="max-width: 800px;">
        
        <!-- Back Navigation -->
        <div style="margin-bottom: 24px;">
            <a href="account/orders.php" style="display: inline-flex; align-items: center; gap: 8px; color: var(--primary); font-weight: 600; text-decoration: none; font-size: 15px; transition: color 0.2s;">
                <span class="material-symbols-outlined" style="font-size: 20px;">arrow_back</span> Kembali ke Pesanan Saya
            </a>
        </div>

        <?php if ($method === 'cod'): ?>
            <!-- COD SUCCESS SCREEN -->
            <div class="card" style="padding: 48px 32px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); text-align: center; background: white; border: 1px solid rgba(0,0,0,0.05);">
                <div style="margin: 0 auto 24px auto; width: 80px; height: 80px; background: #ecfdf5; color: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.15);">
                    <span class="material-symbols-outlined" style="font-size: 48px; font-weight: 600;">check</span>
                </div>
                <h1 style="font-size: 32px; font-weight: 800; color: #1e293b; margin-bottom: 12px;">Pesanan COD Berhasil Dibuat!</h1>
                <p style="color: #64748b; font-size: 16px; max-width: 500px; margin: 0 auto 32px auto; line-height: 1.6;">
                    Terima kasih atas kepercayaan Anda. Pesanan Anda telah diterima oleh sistem dan saat ini sedang kami siapkan untuk dikirimkan.
                </p>

                <!-- Order Details Card -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; text-align: left; max-width: 550px; margin: 0 auto 32px auto; display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding-bottom: 12px; margin-bottom: 4px;">
                        <span style="color: #64748b; font-weight: 500;">ID Pesanan:</span>
                        <strong style="color: #0f172a;">#MBM-<?php echo $order_id; ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding-bottom: 12px; margin-bottom: 4px;">
                        <span style="color: #64748b; font-weight: 500;">Metode Pembayaran:</span>
                        <span style="font-weight: 700; color: var(--primary);">COD (Bayar di Tempat)</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding-bottom: 12px; margin-bottom: 4px;">
                        <span style="color: #64748b; font-weight: 500;">Alamat Pengiriman:</span>
                        <span style="font-weight: 600; color: #334155; text-align: right; max-width: 250px;"><?php echo e($order['alamat_pengiriman']); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding-top: 4px;">
                        <span style="color: #0f172a; font-weight: 700; font-size: 18px;">Total Pembayaran:</span>
                        <strong style="color: var(--primary); font-size: 20px; font-weight: 800;"><?php echo format_rupiah($order['total_harga']); ?></strong>
                    </div>
                </div>
 
                <!-- Alert Notice -->
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 16px; max-width: 550px; margin: 0 auto 32px auto; display: flex; gap: 12px; align-items: flex-start; text-align: left;">
                    <span class="material-symbols-outlined" style="color: #d97706; font-size: 24px; flex-shrink: 0;">info</span>
                    <p style="color: #78350f; font-size: 14px; margin: 0; line-height: 1.5;">
                        <strong>PENTING:</strong> Silakan siapkan uang tunai pas sebesar <strong><?php echo format_rupiah($order['total_harga']); ?></strong> untuk diserahkan kepada kurir pada saat paket Anda tiba di lokasi.
                    </p>
                </div>
 
                <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                    <a href="account/orders.php" class="btn btn-primary" style="padding: 14px 28px; font-size: 15px; font-weight: 600;">Lihat Pesanan Saya</a>
                    <a href="katalog.php" class="btn" style="border: 2px solid var(--outline-variant); color: var(--secondary); background: transparent; padding: 12px 28px; font-size: 15px; font-weight: 600; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; transition: all 0.2s;">Lanjut Belanja</a>
                </div>
            </div>
 
        <?php elseif ($method === 'midtrans' && $success === 1): ?>
            <!-- MIDTRANS PAYMENT SUCCESS SCREEN -->
            <div class="card" style="padding: 48px 32px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); text-align: center; background: white; border: 1px solid rgba(0,0,0,0.05);">
                <div style="margin: 0 auto 24px auto; width: 80px; height: 80px; background: #ecfdf5; color: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(16, 185, 129, 0.15);">
                    <span class="material-symbols-outlined" style="font-size: 48px; font-weight: 600;">verified_user</span>
                </div>
                <h1 style="font-size: 32px; font-weight: 800; color: #1e293b; margin-bottom: 12px;">Pembayaran Midtrans Berhasil!</h1>
                <p style="color: #64748b; font-size: 16px; max-width: 500px; margin: 0 auto 32px auto; line-height: 1.6;">
                    Transaksi Anda telah divalidasi secara otomatis melalui Midtrans. Status pesanan Anda sekarang diubah menjadi <strong>Diproses</strong>.
                </p>
 
                <!-- Receipt Detail Card -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; text-align: left; max-width: 550px; margin: 0 auto 32px auto; display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding-bottom: 12px; margin-bottom: 4px;">
                        <span style="color: #64748b; font-weight: 500;">ID Transaksi Midtrans:</span>
                        <strong style="color: #0f172a; font-family: monospace; font-size: 14px;">MID-<?php echo strtoupper(bin2hex(random_bytes(6))); ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding-bottom: 12px; margin-bottom: 4px;">
                        <span style="color: #64748b; font-weight: 500;">ID Pesanan:</span>
                        <strong style="color: #334155;">#MBM-<?php echo $order_id; ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; border-bottom: 1px dashed #e2e8f0; padding-bottom: 12px; margin-bottom: 4px;">
                        <span style="color: #64748b; font-weight: 500;">Metode Pembayaran:</span>
                        <span style="font-weight: 700; color: #0f172a;">Midtrans (E-Wallet/VA)</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding-top: 4px;">
                        <span style="color: #0f172a; font-weight: 700; font-size: 18px;">Total Nominal Lunas:</span>
                        <strong style="color: var(--primary); font-size: 20px; font-weight: 800;"><?php echo format_rupiah($order['total_harga']); ?></strong>
                    </div>
                </div>
 
                <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                    <a href="account/orders.php" class="btn btn-primary" style="padding: 14px 28px; font-size: 15px; font-weight: 600;">Lihat Pesanan Saya</a>
                    <a href="katalog.php" class="btn" style="border: 2px solid var(--outline-variant); color: var(--secondary); background: transparent; padding: 12px 28px; font-size: 15px; font-weight: 600; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; transition: all 0.2s;">Kembali Belanja</a>
                </div>
            </div>
 
        <?php else: ?>
            <!-- MIDTRANS SNAP MOCKUP UI -->
            <div style="display: flex; flex-direction: column; gap: 24px; align-items: center; margin-top: 10px;">
                
                <!-- Notice and Total info -->
                <div class="card" style="width: 100%; max-width: 650px; padding: 24px; border-radius: 16px; display: flex; justify-content: space-between; align-items: center; background: white; border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 4px 20px rgba(0,0,0,0.02);">
                    <div>
                        <span style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Merchant: BatuMekar Honey</span>
                        <h2 style="font-size: 20px; margin: 4px 0 0 0; color: #1e293b; font-weight: 800;">Pesanan #MBM-<?php echo $order_id; ?></h2>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size: 12px; color: #64748b; display: block;">Total Tagihan:</span>
                        <strong style="font-size: 22px; color: var(--primary); font-weight: 800;"><?php echo format_rupiah($order['total_harga']); ?></strong>
                    </div>
                </div>

                <!-- The Mock Midtrans Frame -->
                <div class="card" style="width: 100%; max-width: 650px; padding: 0; border-radius: 16px; overflow: hidden; border: 1px solid #cbd5e1; box-shadow: 0 20px 40px rgba(0,0,0,0.08); background: #ffffff; display: flex; flex-direction: column;">
                    
                    <!-- Midtrans Header -->
                    <div style="background: #1d2b4f; color: white; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <!-- Midtrans Brand Logo Mock -->
                            <span style="background: #ffffff; color: #1d2b4f; font-weight: 900; font-size: 15px; padding: 4px 8px; border-radius: 6px; letter-spacing: 1px;">midtrans</span>
                            <span style="font-size: 13px; color: #94a3b8; font-weight: 500;">Secure Payment Portal</span>
                        </div>
                        <div style="font-size: 12px; font-weight: 700; background: rgba(255,255,255,0.15); padding: 4px 8px; border-radius: 4px; display: flex; align-items: center; gap: 6px;">
                            <span style="display: inline-block; width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></span> Sandbox Mode
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 240px 1fr; min-height: 320px;">
                        <!-- Left Navigation: Categories -->
                        <div style="background: #f8fafc; border-right: 1px solid #e2e8f0; display: flex; flex-direction: column;">
                            <div class="midtrans-tab active" onclick="switchMidtransTab(this, 'tab_qris')" style="padding: 16px 20px; font-size: 14px; font-weight: 700; color: #1e293b; border-bottom: 1px solid #e2e8f0; cursor: pointer; display: flex; align-items: center; gap: 10px; border-left: 4px solid #3b82f6; background: #eff6ff;">
                                <span class="material-symbols-outlined" style="color: #3b82f6; font-size: 20px;">qr_code_2</span> GoPay / QRIS
                            </div>
                            <div class="midtrans-tab" onclick="switchMidtransTab(this, 'tab_va')" style="padding: 16px 20px; font-size: 14px; font-weight: 600; color: #64748b; border-bottom: 1px solid #e2e8f0; cursor: pointer; display: flex; align-items: center; gap: 10px; border-left: 4px solid transparent;">
                                <span class="material-symbols-outlined" style="font-size: 20px;">account_balance</span> Virtual Account
                            </div>
                            <div class="midtrans-tab" onclick="switchMidtransTab(this, 'tab_card')" style="padding: 16px 20px; font-size: 14px; font-weight: 600; color: #64748b; border-bottom: 1px solid #e2e8f0; cursor: pointer; display: flex; align-items: center; gap: 10px; border-left: 4px solid transparent;">
                                <span class="material-symbols-outlined" style="font-size: 20px;">credit_card</span> Kartu Kredit
                            </div>
                        </div>

                        <!-- Right Panel: Details -->
                        <div style="padding: 24px; display: flex; flex-direction: column; justify-content: space-between;">
                            <!-- QRIS / GOPAY -->
                            <div id="tab_qris" class="midtrans-panel-content" style="display: block;">
                                <h4 style="font-size: 16px; margin: 0 0 10px 0; color: #0f172a; font-weight: 700;">Bayar dengan GoPay / QRIS</h4>
                                <p style="color: #64748b; font-size: 13px; margin: 0 0 16px 0; line-height: 1.5;">Scan QR code di bawah ini menggunakan GoPay, DANA, OVO, LinkAja atau mobile banking untuk melunasi.</p>
                                
                                <div style="display: flex; gap: 16px; align-items: center; background: #f8fafc; padding: 12px; border-radius: 10px; border: 1px solid #e2e8f0;">
                                    <!-- Simple vector mock QR code -->
                                    <div style="background: white; padding: 8px; border: 1px solid #cbd5e1; border-radius: 6px; flex-shrink: 0;">
                                        <svg width="100" height="100" viewBox="0 0 100 100">
                                            <rect width="100" height="100" fill="white"/>
                                            <rect x="0" y="0" width="25" height="25" fill="#1d2b4f"/>
                                            <rect x="5" y="5" width="15" height="15" fill="white"/>
                                            <rect x="75" y="0" width="25" height="25" fill="#1d2b4f"/>
                                            <rect x="80" y="5" width="15" height="15" fill="white"/>
                                            <rect x="0" y="75" width="25" height="25" fill="#1d2b4f"/>
                                            <rect x="5" y="80" width="15" height="15" fill="white"/>
                                            <rect x="40" y="40" width="20" height="20" fill="#1d2b4f"/>
                                            <rect x="35" y="10" width="10" height="15" fill="#1d2b4f"/>
                                            <rect x="10" y="35" width="15" height="10" fill="#1d2b4f"/>
                                            <rect x="70" y="70" width="20" height="15" fill="#1d2b4f"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <span style="font-weight: 800; font-size: 14px; color: #1d2b4f; display: block; letter-spacing: 0.5px;">GOPAY / QRIS MOCK</span>
                                        <span style="font-size: 12px; color: #64748b; display: block; margin-top: 4px; line-height: 1.4;">Jumlah yang akan dipotong sebesar total tagihan belanja Anda.</span>
                                    </div>
                                </div>
                            </div>

                            <!-- VIRTUAL ACCOUNT -->
                            <div id="tab_va" class="midtrans-panel-content" style="display: none;">
                                <h4 style="font-size: 16px; margin: 0 0 10px 0; color: #0f172a; font-weight: 700;">Bayar via Virtual Account</h4>
                                <p style="color: #64748b; font-size: 13px; margin: 0 0 16px 0; line-height: 1.5;">Pilih rekening Virtual Account bank tujuan:</p>
                                
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <div style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; cursor: pointer;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <span style="font-size: 12px; font-weight: 800; background: #e2e8f0; color: #475569; padding: 4px 6px; border-radius: 4px;">BCA</span>
                                            <span style="font-size: 13px; font-weight: 600; color: #334155;">BCA Virtual Account</span>
                                        </div>
                                        <input type="radio" name="va_bank" checked style="accent-color: #3b82f6;">
                                    </div>

                                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; background: white; cursor: pointer;">
                                        <div style="display: flex; align-items: center; gap: 10px;">
                                            <span style="font-size: 12px; font-weight: 800; background: #e2e8f0; color: #475569; padding: 4px 6px; border-radius: 4px;">MANDIRI</span>
                                            <span style="font-size: 13px; font-weight: 600; color: #334155;">Mandiri Bill Payment</span>
                                        </div>
                                        <input type="radio" name="va_bank" style="accent-color: #3b82f6;">
                                    </div>
                                </div>
                            </div>

                            <!-- CARD PAYMENT -->
                            <div id="tab_card" class="midtrans-panel-content" style="display: none;">
                                <h4 style="font-size: 16px; margin: 0 0 10px 0; color: #0f172a; font-weight: 700;">Bayar via Kartu Kredit / Debit</h4>
                                <p style="color: #64748b; font-size: 13px; margin: 0 0 16px 0; line-height: 1.5;">Masukkan detail kartu kredit Anda di bawah ini:</p>
                                
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <div class="form-group" style="margin: 0;">
                                        <label style="font-size: 11px; color: #475569; font-weight: 600;">Nomor Kartu</label>
                                        <input type="text" class="form-control" placeholder="4111 2222 3333 4444" style="height: 38px; font-size: 13px;">
                                    </div>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                                        <div class="form-group" style="margin: 0;">
                                            <label style="font-size: 11px; color: #475569; font-weight: 600;">Masa Berlaku (MM/YY)</label>
                                            <input type="text" class="form-control" placeholder="12/29" style="height: 38px; font-size: 13px;">
                                        </div>
                                        <div class="form-group" style="margin: 0;">
                                            <label style="font-size: 11px; color: #475569; font-weight: 600;">CVV</label>
                                            <input type="password" class="form-control" placeholder="•••" style="height: 38px; font-size: 13px;">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pay Button Action -->
                            <div style="margin-top: 24px; border-top: 1px solid #e2e8f0; padding-top: 16px; display: flex; flex-direction: column; align-items: flex-end;">
                                <div id="pay-spinner" style="display: none; align-items: center; gap: 10px; color: #3b82f6; font-size: 14px; font-weight: 600; margin-bottom: 12px;">
                                    <svg class="spinner" viewBox="0 0 50 50" style="width: 20px; height: 20px; animation: rotate 2s linear infinite; stroke: #3b82f6;">
                                        <circle class="path" cx="25" cy="25" r="20" fill="none" stroke-width="5" style="stroke-linecap: round; animation: dash 1.5s ease-in-out infinite;"></circle>
                                    </svg>
                                    Memproses pembayaran aman...
                                </div>

                                <a href="pembayaran.php?order_id=<?php echo $order_id; ?>&method=midtrans&action=pay" id="btn-pay-midtrans" onclick="showPaymentLoader(event, this);" class="btn btn-primary" style="background: #3b82f6; border-color: #3b82f6; width: 100%; text-align: center; justify-content: center; padding: 14px; font-size: 15px; font-weight: 700; border-radius: 8px;">
                                    Bayar Sekarang
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Page Scripts and Custom Styles -->
            <style>
                .midtrans-tab {
                    transition: all 0.2s ease;
                }
                .midtrans-tab:hover {
                    background: #f1f5f9;
                }
                .spinner {
                    animation: rotate 2s linear infinite;
                }
                @keyframes rotate {
                    100% {
                        transform: rotate(360deg);
                    }
                }
                @keyframes dash {
                    0% {
                        stroke-dasharray: 1, 150;
                        stroke-dashoffset: 0;
                    }
                    50% {
                        stroke-dasharray: 90, 150;
                        stroke-dashoffset: -35;
                    }
                    100% {
                        stroke-dasharray: 90, 150;
                        stroke-dashoffset: -124;
                    }
                }
            </style>
            
            <script>
                function switchMidtransTab(element, panelId) {
                    // Reset all tabs
                    document.querySelectorAll('.midtrans-tab').forEach(function(tab) {
                        tab.style.color = '#64748b';
                        tab.style.fontWeight = '600';
                        tab.style.borderLeftColor = 'transparent';
                        tab.style.background = 'transparent';
                        tab.classList.remove('active');
                    });
                    
                    // Set active tab
                    element.style.color = '#1e293b';
                    element.style.fontWeight = '700';
                    element.style.borderLeftColor = '#3b82f6';
                    element.style.background = '#eff6ff';
                    element.classList.add('active');

                    // Hide all panels
                    document.querySelectorAll('.midtrans-panel-content').forEach(function(panel) {
                        panel.style.display = 'none';
                    });

                    // Show selected panel
                    document.getElementById(panelId).style.display = 'block';
                }

                function showPaymentLoader(event, element) {
                    event.preventDefault();
                    document.getElementById('pay-spinner').style.display = 'flex';
                    element.style.opacity = '0.7';
                    element.style.pointerEvents = 'none';
                    element.textContent = "Menghubungkan...";
                    
                    setTimeout(function() {
                        window.location.href = element.getAttribute('href');
                    }, 2000);
                }
            </script>
        <?php endif; ?>

    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
