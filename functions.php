<?php
require_once __DIR__.'/config.php';

function esc($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function ensureProductsSchema(){
  $pdo = getPDO();
  // create products table if not exists
  $pdo->exec("CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    price INT NOT NULL DEFAULT 0,
    image_path VARCHAR(255) NULL,
    sizes VARCHAR(255) NULL,
    is_best_seller TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB");
  // add columns if they don't exist (ignore errors)
  try{ $pdo->exec("ALTER TABLE products ADD COLUMN is_best_seller TINYINT(1) NOT NULL DEFAULT 0"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE products ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"); }catch(Throwable $e){}
}

function getProducts(){
  ensureProductsSchema();
  $pdo = getPDO();
  return $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
}
function getProduct($id){
  ensureProductsSchema();
  $pdo = getPDO();
  $st = $pdo->prepare("SELECT * FROM products WHERE id=?");
  $st->execute([$id]);
  return $st->fetch();
}
function addProduct($data, $file){
  ensureProductsSchema();
  $pdo = getPDO();
  $imagePath = null;
  if(isset($file['image']) && $file['image']['error']===UPLOAD_ERR_OK){
    $imagePath = handleUpload($file['image']);
  }
  $sizes = isset($data['sizes']) ? implode(',', $data['sizes']) : 'All Size,S,M,L,XL';
  $st = $pdo->prepare("INSERT INTO products (name, description, price, image_path, sizes, is_best_seller) VALUES (?,?,?,?,?,?)");
  $st->execute([$data['name'], $data['description'], $data['price'], $imagePath, $sizes, !empty($data['is_best_seller'])?1:0]);
}
function updateProduct($id, $data, $file){
  ensureProductsSchema();
  $pdo = getPDO();
  $product = getProduct($id);
  if(!$product){ throw new RuntimeException('Produk tidak ditemukan'); }
  $imagePath = $product['image_path'];
  if(isset($file['image']) && $file['image']['error']===UPLOAD_ERR_OK){
    $imagePath = handleUpload($file['image']);
  }
  $sizes = isset($data['sizes']) ? implode(',', $data['sizes']) : 'All Size,S,M,L,XL';
  $st = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, image_path=?, sizes=?, is_best_seller=? WHERE id=?");
  $st->execute([$data['name'], $data['description'], $data['price'], $imagePath, $sizes, !empty($data['is_best_seller'])?1:0, $id]);
}
function deleteProduct($id){
  $pdo = getPDO();
  $st = $pdo->prepare("DELETE FROM products WHERE id=?");
  $st->execute([$id]);
}
function handleUpload($f){
  $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
  if(!isset($allowed[$f['type']])){ throw new RuntimeException('Format gambar harus JPG/PNG/WebP'); }
  if($f['size'] > 2*1024*1024){ throw new RuntimeException('Ukuran gambar maks 2MB'); }
  if(!is_dir(__DIR__.'/uploads')){ mkdir(__DIR__.'/uploads', 0777, true); }
  $ext = $allowed[$f['type']];
  $name = uniqid('img_', true).'.'.$ext;
  $dest = __DIR__.'/uploads/'.$name;
  if(!move_uploaded_file($f['tmp_name'], $dest)){ throw new RuntimeException('Gagal upload'); }
  return 'uploads/'.$name;
}
function formatRupiah($n){ return 'Rp '.number_format((int)$n,0,',','.'); }

function calcShipping($courier, $service, $province=null){
  $rates = [
    'JNE' => ['REG'=>20000, 'YES'=>35000],
    'SiCepat' => ['REG'=>18000, 'BEST'=>28000],
    'AnterAja' => ['REG'=>17000, 'NDS'=>27000],
  ];
  $courier = $courier ?: 'JNE';
  if(!isset($rates[$courier])) $courier = 'JNE';
  $service = $service ?: array_key_first($rates[$courier]);
  if(!isset($rates[$courier][$service])) $service = array_key_first($rates[$courier]);
  $base = $rates[$courier][$service];

  if($province){
    $prov = mb_strtolower(trim($province));
    if(strpos($prov, 'jakarta') !== false || strpos($prov, 'dki') !== false){
      $base = max(10000, (int)round($base * 0.8));
    }
  }
  return (int)$base;
}
?>
