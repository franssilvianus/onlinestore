<?php
if (session_status()===PHP_SESSION_NONE) session_start();
foreach(['customer_id','customer_name','customer_phone','customer_address','customer_city','customer_province','customer_postal_code','customer_points_balance','customer_voucher_count'] as $key){
  unset($_SESSION[$key]);
}
header('Location: index.php'); exit;
