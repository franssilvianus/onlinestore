<?php
define('DB_HOST','localhost');
define('WHATSAPP_PHONE','6281414002303');

define('DB_NAME','simple_store');
define('DB_USER','root');
define('DB_PASS','');

function getPDO(){
  static $pdo;
  if(!$pdo){
    $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
  }
  return $pdo;
}

// --- Admin login (sederhana) ---
if(!defined('ADMIN_USER')) define('ADMIN_USER','admin');
if(!defined('ADMIN_PASS')) define('ADMIN_PASS','admin123'); // ganti di sini
?>
