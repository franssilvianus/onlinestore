<?php
require_once __DIR__.'/functions.php';
if (session_status()===PHP_SESSION_NONE) session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$seller = getSeller($id);
if(!$seller){
  http_response_code(404);
  echo '<!doctype html><html><head><meta charset="utf-8"><title>Seller tidak ditemukan</title></head><body><div style="font-family:Arial,sans-serif;padding:24px">Seller tidak ditemukan.</div></body></html>';
  exit;
}

$products = getProducts($id);
include 'header.php';
?>
<div class="container py-4">
  <div class="card p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
      <div>
        <h2 class="mb-2"><?php echo esc($seller['name']); ?></h2>
        <p class="text-muted mb-1"><i class="fa-solid fa-user me-2"></i><?php echo esc($seller['owner_name'] ?: '-'); ?></p>
        <p class="text-muted mb-1"><i class="fa-solid fa-phone me-2"></i><?php echo esc($seller['phone'] ?: '-'); ?></p>
        <p class="text-muted mb-0"><i class="fa-solid fa-location-dot me-2"></i><?php echo esc($seller['address'] ?: '-'); ?></p>
      </div>
      <a href="index.php#shop" class="btn btn-outline-success">Kembali ke Toko</a>
    </div>
  </div>

  <h4 class="mb-3">Produk dari Seller Ini</h4>
  <?php if(!$products): ?>
    <div class="alert alert-info">Belum ada produk dari seller ini.</div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach($products as $p): ?>
        <div class="col-12 col-sm-6 col-lg-4">
          <div class="card h-100">
            <?php if($p['image_path']): ?><img src="<?php echo esc($p['image_path']); ?>" class="card-img-top" alt="<?php echo esc($p['name']); ?>" style="height:220px; object-fit:cover;"><?php endif; ?>
            <div class="card-body d-flex flex-column">
              <h5 class="card-title"><?php echo esc($p['name']); ?></h5>
              <p class="text-muted small flex-grow-1"><?php echo esc($p['description']); ?></p>
              <div class="fw-semibold mb-2"><?php echo formatRupiah($p['price']); ?></div>
              <a href="index.php#shop" class="btn btn-primary">Lihat di Halaman Utama</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
