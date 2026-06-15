<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../includes/functions.php';
require_login();

// Redirect reseller or any other non-admin to customer account dashboard
header("Location: ../account/dashboard.php");
exit;
?>
