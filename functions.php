<?php
require_once __DIR__.'/config.php';

function esc($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function ensureOrderSchema(){
  $pdo = getPDO();
  $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_no VARCHAR(50) NOT NULL UNIQUE,
    customer_id INT NULL,
    customer_name VARCHAR(120) NOT NULL,
    customer_phone VARCHAR(50) NOT NULL,
    customer_address TEXT NOT NULL,
    customer_city VARCHAR(100) NOT NULL,
    customer_province VARCHAR(100) NOT NULL,
    customer_postal_code VARCHAR(20) NOT NULL,
    customer_notes TEXT NULL,
    courier VARCHAR(50) NULL,
    service VARCHAR(50) NULL,
    subtotal INT NOT NULL DEFAULT 0,
    shipping_cost INT NOT NULL DEFAULT 0,
    total INT NOT NULL DEFAULT 0,
    points_earned INT NOT NULL DEFAULT 0,
    voucher_awarded INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_orders_customer_id (customer_id)
  ) ENGINE=InnoDB");

  $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NULL,
    product_name VARCHAR(255) NOT NULL,
    size VARCHAR(50) NULL,
    price INT NOT NULL DEFAULT 0,
    qty INT NOT NULL DEFAULT 1,
    seller_id INT NULL DEFAULT NULL,
    seller_name VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_order_items_order_id (order_id),
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
  ) ENGINE=InnoDB");

  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN customer_name VARCHAR(120) NOT NULL DEFAULT ''"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN customer_phone VARCHAR(50) NOT NULL DEFAULT ''"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN customer_address TEXT NOT NULL DEFAULT ''"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN customer_city VARCHAR(100) NOT NULL DEFAULT ''"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN customer_province VARCHAR(100) NOT NULL DEFAULT ''"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN customer_postal_code VARCHAR(20) NOT NULL DEFAULT ''"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN customer_notes TEXT NULL"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN courier VARCHAR(50) NULL"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN service VARCHAR(50) NULL"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN subtotal INT NOT NULL DEFAULT 0"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN shipping_cost INT NOT NULL DEFAULT 0"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN total INT NOT NULL DEFAULT 0"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN customer_id INT NULL"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN points_earned INT NOT NULL DEFAULT 0"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE orders ADD COLUMN voucher_awarded INT NOT NULL DEFAULT 0"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE order_items ADD COLUMN seller_id INT NULL DEFAULT NULL"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE order_items ADD COLUMN seller_name VARCHAR(255) NULL"); }catch(Throwable $e){}
}

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
    seller_id INT NULL DEFAULT NULL,
    is_best_seller TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB");
  // add columns if they don't exist (ignore errors)
  try{ $pdo->exec("ALTER TABLE products ADD COLUMN seller_id INT NULL DEFAULT NULL"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE products ADD COLUMN is_best_seller TINYINT(1) NOT NULL DEFAULT 0"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE products ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP"); }catch(Throwable $e){}
}

function ensureSellerColumns(){
  $pdo = getPDO();
  $columns = [];
  foreach($pdo->query("SHOW COLUMNS FROM sellers")->fetchAll() as $col){
    $columns[$col['Field']] = true;
  }
  $alterParts = [];
  if(!isset($columns['username'])){ $alterParts[] = "ADD COLUMN username VARCHAR(100) NULL"; }
  if(!isset($columns['password_hash'])){ $alterParts[] = "ADD COLUMN password_hash VARCHAR(255) NULL"; }
  if(!isset($columns['is_active'])){ $alterParts[] = "ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1"; }
  if($alterParts){
    $pdo->exec("ALTER TABLE sellers " . implode(', ', $alterParts));
  }
}

function ensureSellersSchema(){
  $pdo = getPDO();
  $pdo->exec("CREATE TABLE IF NOT EXISTS sellers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    owner_name VARCHAR(120) NULL,
    phone VARCHAR(50) NULL,
    email VARCHAR(120) NULL,
    address TEXT NULL,
    username VARCHAR(100) NULL,
    password_hash VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB");
  ensureSellerColumns();
  try{ $pdo->exec("ALTER TABLE products ADD COLUMN seller_id INT NULL DEFAULT NULL"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE products ADD INDEX idx_products_seller_id (seller_id)"); }catch(Throwable $e){}
  try{ $pdo->exec("ALTER TABLE products ADD CONSTRAINT fk_products_seller FOREIGN KEY (seller_id) REFERENCES sellers(id) ON DELETE SET NULL"); }catch(Throwable $e){}
  ensureDemoSeller();
}

function ensureCustomersSchema(){
  $pdo = getPDO();
  $pdo->exec("CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    phone VARCHAR(50) NOT NULL UNIQUE,
    address TEXT NULL,
    city VARCHAR(100) NULL,
    province VARCHAR(100) NULL,
    postal_code VARCHAR(20) NULL,
    points_balance INT NOT NULL DEFAULT 0,
    voucher_count INT NOT NULL DEFAULT 0,
    last_order_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB");
}

function getCustomerByPhone($phone){
  ensureCustomersSchema();
  $pdo = getPDO();
  $st = $pdo->prepare("SELECT * FROM customers WHERE phone = ? LIMIT 1");
  $st->execute([trim($phone)]);
  return $st->fetch();
}

function getCustomerById($id){
  ensureCustomersSchema();
  $pdo = getPDO();
  $st = $pdo->prepare("SELECT * FROM customers WHERE id = ? LIMIT 1");
  $st->execute([(int)$id]);
  return $st->fetch();
}

function updateCustomerById($id, $data){
  ensureCustomersSchema();
  $pdo = getPDO();
  $st = $pdo->prepare("UPDATE customers SET name=?, address=?, city=?, province=?, postal_code=? WHERE id=?");
  $st->execute([
    trim($data['name'] ?? ''),
    trim($data['address'] ?? ''),
    trim($data['city'] ?? ''),
    trim($data['province'] ?? ''),
    trim($data['postal_code'] ?? ''),
    (int)$id,
  ]);
  return getCustomerById($id);
}

function upsertCustomerPoints($data, $pointsEarned, $customerId = null){
  ensureCustomersSchema();
  $pdo = getPDO();
  $phone = trim($data['phone'] ?? '');
  if($phone === ''){ throw new RuntimeException('Nomor telepon customer wajib diisi untuk sistem poin.'); }
  if($customerId === null && !empty($_SESSION['customer_id'])){
    $customerId = (int)$_SESSION['customer_id'];
  }
  $customer = null;
  if($customerId){
    $customer = getCustomerById($customerId);
    if(!$customer){
      $customer = getCustomerByPhone($phone);
    }
  } else {
    $customer = getCustomerByPhone($phone);
  }
  $currentPoints = (int)($customer['points_balance'] ?? 0);
  $currentVouchers = (int)($customer['voucher_count'] ?? 0);
  $pointsEarned = max(0, (int)$pointsEarned);
  $newPointsTotal = $currentPoints + $pointsEarned;
  $earnedVouchers = intdiv($newPointsTotal, 100);
  $updatedPoints = $newPointsTotal % 100;
  $updatedVouchers = $currentVouchers + $earnedVouchers;
  $now = date('Y-m-d H:i:s');

  if($customer){
    $st = $pdo->prepare("UPDATE customers SET name=?, phone=?, address=?, city=?, province=?, postal_code=?, points_balance=?, voucher_count=?, last_order_at=? WHERE id=?");
    $st->execute([
      trim($data['name'] ?? ''),
      $phone,
      trim($data['address'] ?? ''),
      trim($data['city'] ?? ''),
      trim($data['province'] ?? ''),
      trim($data['postal_code'] ?? ''),
      $updatedPoints,
      $updatedVouchers,
      $now,
      $customer['id'],
    ]);
    $customer = getCustomerById($customer['id']);
    $customer['points_earned'] = $pointsEarned;
    $customer['voucher_awarded'] = $earnedVouchers;
    return $customer;
  }

  $st = $pdo->prepare("INSERT INTO customers (name, phone, address, city, province, postal_code, points_balance, voucher_count, last_order_at) VALUES (?,?,?,?,?,?,?,?,?)");
  $st->execute([
    trim($data['name'] ?? ''),
    $phone,
    trim($data['address'] ?? ''),
    trim($data['city'] ?? ''),
    trim($data['province'] ?? ''),
    trim($data['postal_code'] ?? ''),
    $updatedPoints,
    $updatedVouchers,
    $now,
  ]);
  $customer = getCustomerById((int)$pdo->lastInsertId());
  $customer['points_earned'] = $pointsEarned;
  $customer['voucher_awarded'] = $earnedVouchers;
  return $customer;
}

function ensureDemoSeller(){
  $pdo = getPDO();
  $existing = $pdo->prepare("SELECT id, password_hash FROM sellers WHERE username = ? LIMIT 1");
  $existing->execute(['sellerdemo']);
  $row = $existing->fetch();

  if($row){
    if(empty($row['password_hash'])){
      $pdo->prepare("UPDATE sellers SET password_hash = ? WHERE id = ?")
        ->execute([password_hash('seller123', PASSWORD_DEFAULT), (int)$row['id']]);
    }
    return;
  }

  $username = 'sellerdemo';
  $password = 'seller123';
  $pdo->prepare("INSERT INTO sellers (name, owner_name, phone, email, address, username, password_hash, is_active) VALUES (?,?,?,?,?,?,?,?)")
    ->execute([
      'Seller Demo',
      'Demo Owner',
      '081234567890',
      'sellerdemo@example.com',
      'Cibubur',
      $username,
      password_hash($password, PASSWORD_DEFAULT),
      1,
    ]);
}

function getProducts($sellerId = null){
  ensureProductsSchema();
  ensureSellersSchema();
  $pdo = getPDO();
  if($sellerId !== null && $sellerId !== ''){
    $st = $pdo->prepare("SELECT p.*, s.name AS seller_name FROM products p LEFT JOIN sellers s ON s.id = p.seller_id WHERE p.seller_id = ? ORDER BY p.id DESC");
    $st->execute([(int)$sellerId]);
    return $st->fetchAll();
  }
  return $pdo->query("SELECT p.*, s.name AS seller_name FROM products p LEFT JOIN sellers s ON s.id = p.seller_id ORDER BY p.id DESC")->fetchAll();
}
function getProduct($id){
  ensureProductsSchema();
  ensureSellersSchema();
  $pdo = getPDO();
  $st = $pdo->prepare("SELECT p.*, s.name AS seller_name FROM products p LEFT JOIN sellers s ON s.id = p.seller_id WHERE p.id=?");
  $st->execute([$id]);
  return $st->fetch();
}
function getSellers(){
  ensureSellersSchema();
  $pdo = getPDO();
  return $pdo->query("SELECT * FROM sellers ORDER BY name ASC")->fetchAll();
}
function getSeller($id){
  ensureSellersSchema();
  $pdo = getPDO();
  $st = $pdo->prepare("SELECT * FROM sellers WHERE id=?");
  $st->execute([$id]);
  return $st->fetch();
}
function getSellerByUsername($username){
  ensureSellersSchema();
  $pdo = getPDO();
  $st = $pdo->prepare("SELECT * FROM sellers WHERE username=? LIMIT 1");
  $st->execute([trim($username)]);
  return $st->fetch();
}
function addSeller($data){
  ensureSellersSchema();
  $pdo = getPDO();
  $username = trim($data['username'] ?? '');
  $password = trim($data['password'] ?? '');
  if($username === ''){ throw new RuntimeException('Username seller wajib diisi.'); }
  if($password === ''){ throw new RuntimeException('Password seller wajib diisi.'); }
  $passwordHash = password_hash($password, PASSWORD_DEFAULT);
  $st = $pdo->prepare("INSERT INTO sellers (name, owner_name, phone, email, address, username, password_hash, is_active) VALUES (?,?,?,?,?,?,?,?)");
  try{
    $st->execute([
      trim($data['name'] ?? ''),
      trim($data['owner_name'] ?? ''),
      trim($data['phone'] ?? ''),
      trim($data['email'] ?? ''),
      trim($data['address'] ?? ''),
      $username,
      $passwordHash,
      !empty($data['is_active']) ? 1 : 0,
    ]);
  } catch(Throwable $e){
    if(stripos($e->getMessage(), 'column') !== false || stripos($e->getMessage(), 'doesn\'t exist') !== false){
      ensureSellerColumns();
      $st = $pdo->prepare("INSERT INTO sellers (name, owner_name, phone, email, address, username, password_hash, is_active) VALUES (?,?,?,?,?,?,?,?)");
      $st->execute([
        trim($data['name'] ?? ''),
        trim($data['owner_name'] ?? ''),
        trim($data['phone'] ?? ''),
        trim($data['email'] ?? ''),
        trim($data['address'] ?? ''),
        $username,
        $passwordHash,
        !empty($data['is_active']) ? 1 : 0,
      ]);
    } else {
      throw $e;
    }
  }
  return $pdo->lastInsertId();
}
function updateSeller($id, $data){
  ensureSellersSchema();
  $pdo = getPDO();
  $username = trim($data['username'] ?? '');
  if($username === ''){ throw new RuntimeException('Username seller wajib diisi.'); }
  $password = trim($data['password'] ?? '');
  if($password !== ''){
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $st = $pdo->prepare("UPDATE sellers SET name=?, owner_name=?, phone=?, email=?, address=?, username=?, password_hash=?, is_active=? WHERE id=?");
    $st->execute([
      trim($data['name'] ?? ''),
      trim($data['owner_name'] ?? ''),
      trim($data['phone'] ?? ''),
      trim($data['email'] ?? ''),
      trim($data['address'] ?? ''),
      $username,
      $passwordHash,
      !empty($data['is_active']) ? 1 : 0,
      $id,
    ]);
  }else{
    $st = $pdo->prepare("UPDATE sellers SET name=?, owner_name=?, phone=?, email=?, address=?, username=?, is_active=? WHERE id=?");
    $st->execute([
      trim($data['name'] ?? ''),
      trim($data['owner_name'] ?? ''),
      trim($data['phone'] ?? ''),
      trim($data['email'] ?? ''),
      trim($data['address'] ?? ''),
      $username,
      !empty($data['is_active']) ? 1 : 0,
      $id,
    ]);
  }
}
function deleteSeller($id){
  ensureSellersSchema();
  $pdo = getPDO();
  $pdo->prepare("UPDATE products SET seller_id = NULL WHERE seller_id=?")->execute([$id]);
  $pdo->prepare("DELETE FROM sellers WHERE id=?")->execute([$id]);
}
function calculatePointsFromSubtotal($subtotal){
  $subtotal = max(0, (int)$subtotal);
  return (int) floor($subtotal / 10000);
}

function createOrder($orderData, $items){
  ensureOrderSchema();
  $pdo = getPDO();
  $st = $pdo->prepare("INSERT INTO orders (order_no, customer_id, customer_name, customer_phone, customer_address, customer_city, customer_province, customer_postal_code, customer_notes, courier, service, subtotal, shipping_cost, total, points_earned, voucher_awarded) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
  $st->execute([
    $orderData['order_no'],
    $orderData['customer_id'] ?? null,
    $orderData['customer_name'],
    $orderData['customer_phone'],
    $orderData['customer_address'],
    $orderData['customer_city'],
    $orderData['customer_province'],
    $orderData['customer_postal_code'],
    $orderData['customer_notes'] ?? '',
    $orderData['courier'] ?? null,
    $orderData['service'] ?? null,
    (int)$orderData['subtotal'],
    (int)$orderData['shipping_cost'],
    (int)$orderData['total'],
    (int)$orderData['points_earned'],
    (int)$orderData['voucher_awarded'],
  ]);
  $orderId = (int)$pdo->lastInsertId();

  $itemSt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, size, price, qty, seller_id, seller_name) VALUES (?,?,?,?,?,?,?,?)");
  foreach($items as $item){
    $itemSt->execute([
      $orderId,
      (int)($item['id'] ?? 0),
      $item['name'] ?? '',
      $item['size'] ?? '',
      (int)($item['price'] ?? 0),
      (int)($item['qty'] ?? 1),
      isset($item['seller_id']) ? (int)$item['seller_id'] : null,
      $item['seller_name'] ?? null,
    ]);
  }

  return $orderId;
}

function addProduct($data, $file){
  ensureProductsSchema();
  ensureSellersSchema();
  $pdo = getPDO();
  $imagePath = null;
  if(isset($file['image']) && $file['image']['error']===UPLOAD_ERR_OK){
    $imagePath = handleUpload($file['image']);
  }
  $sizes = isset($data['sizes']) ? implode(',', $data['sizes']) : 'All Size,S,M,L,XL';
  $sellerIdRaw = $data['seller_id'] ?? '';
  $sellerId = ($sellerIdRaw !== '' && $sellerIdRaw !== null) ? (int)$sellerIdRaw : null;
  $st = $pdo->prepare("INSERT INTO products (name, description, price, image_path, sizes, seller_id, is_best_seller) VALUES (?,?,?,?,?,?,?)");
  $st->execute([$data['name'], $data['description'], $data['price'], $imagePath, $sizes, $sellerId, !empty($data['is_best_seller'])?1:0]);
}
function updateProduct($id, $data, $file){
  ensureProductsSchema();
  ensureSellersSchema();
  $pdo = getPDO();
  $product = getProduct($id);
  if(!$product){ throw new RuntimeException('Produk tidak ditemukan'); }
  $imagePath = $product['image_path'];
  if(isset($file['image']) && $file['image']['error']===UPLOAD_ERR_OK){
    $imagePath = handleUpload($file['image']);
  }
  $sizes = isset($data['sizes']) ? implode(',', $data['sizes']) : 'All Size,S,M,L,XL';
  $sellerIdRaw = $data['seller_id'] ?? '';
  $sellerId = ($sellerIdRaw !== '' && $sellerIdRaw !== null) ? (int)$sellerIdRaw : null;
  $st = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, image_path=?, sizes=?, seller_id=?, is_best_seller=? WHERE id=?");
  $st->execute([$data['name'], $data['description'], $data['price'], $imagePath, $sizes, $sellerId, !empty($data['is_best_seller'])?1:0, $id]);
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
