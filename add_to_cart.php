<?php
if (session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__.'/functions.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$product_id = (int)($input['product_id'] ?? 0);
$size = trim($input['size'] ?? 'M');
$qty = max(1, (int)($input['qty'] ?? 1));
$p = getProduct($product_id);

if(!$p){
  http_response_code(404);
  echo json_encode(['ok'=>false,'message'=>'Produk tidak ditemukan']);
  exit;
}

$key = $product_id.'_'.$size;
if(!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
if(isset($_SESSION['cart'][$key])){
  $_SESSION['cart'][$key]['qty'] += $qty;
}else{
  $_SESSION['cart'][$key] = [
    'id'=>$product_id,
    'name'=>$p['name'],
    'price'=>$p['price'],
    'size'=>$size,
    'image'=>$p['image_path'],
    'seller_id'=>(int)($p['seller_id'] ?? 0),
    'seller_name'=>$p['seller_name'] ?? '',
    'qty'=>$qty
  ];
}
$total_items = array_sum(array_column($_SESSION['cart'],'qty'));
$message = '<strong>'.esc($p['name']).'</strong> (size '.esc($size).') ditambahkan ke keranjang.';
echo json_encode(['ok'=>true, 'message'=>$message, 'total_items'=>$total_items]);
?>
