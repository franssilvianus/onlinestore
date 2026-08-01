<?php
require_once __DIR__.'/functions.php';
if (session_status()===PHP_SESSION_NONE) session_start();
if(empty($_SESSION['customer_id'])){
  header('Location: customer_login.php?next=account.php'); exit;
}
$customer = getCustomerById($_SESSION['customer_id']);
if(!$customer){
  header('Location: customer_logout.php'); exit;
}
if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $customer = updateCustomerById($customer['id'], [
    'name' => $_POST['name'] ?? '',
    'address' => $_POST['address'] ?? '',
    'city' => $_POST['city'] ?? '',
    'province' => $_POST['province'] ?? '',
    'postal_code' => $_POST['postal_code'] ?? '',
  ]);
  $_SESSION['customer_name'] = $customer['name'];
  $_SESSION['customer_address'] = $customer['address'];
  $_SESSION['customer_city'] = $customer['city'];
  $_SESSION['customer_province'] = $customer['province'];
  $_SESSION['customer_postal_code'] = $customer['postal_code'];
  $updated = true;
}
include 'header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card p-4">
      <h3 class="mb-3">Akun Pelanggan</h3>
      <?php if(!empty($updated)): ?><div class="alert alert-success">Profil berhasil diperbarui.</div><?php endif; ?>
      <div class="mb-3">
        <div class="fw-semibold">Nama</div>
        <div><?php echo esc($customer['name']); ?></div>
      </div>
      <div class="row g-3">
        <div class="col-md-6">
          <div class="card p-3 mb-3">
            <div class="fw-semibold">Saldo Poin</div>
            <div class="fs-2 text-success"><?php echo (int)$customer['points_balance']; ?></div>
            <div class="small text-muted">Poin saat ini</div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card p-3 mb-3">
            <div class="fw-semibold">Voucher Terkumpul</div>
            <div class="fs-2 text-success"><?php echo (int)$customer['voucher_count']; ?></div>
            <div class="small text-muted">Voucher eco-friendly yang sudah bisa kamu tukarkan</div>
          </div>
        </div>
      </div>
      <div class="alert alert-info">
        <p class="mb-1"><strong>Aturan Poin:</strong></p>
        <p class="mb-0">Belanja minimal Rp 10.000 = 1 poin. 100 poin otomatis jadi 1 voucher eco-friendly.</p>
      </div>
      <h5 class="mt-4">Ubah Profil</h5>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Nama Lengkap</label>
          <input type="text" name="name" class="form-control" required value="<?php echo esc($customer['name']); ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">No. HP / WA</label>
          <input type="text" class="form-control" value="<?php echo esc($customer['phone']); ?>" readonly>
        </div>
        <div class="mb-3">
          <label class="form-label">Alamat Lengkap</label>
          <textarea name="address" class="form-control" rows="3"><?php echo esc($customer['address']); ?></textarea>
        </div>
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">Kota / Kabupaten</label>
            <input type="text" name="city" class="form-control" value="<?php echo esc($customer['city']); ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Provinsi</label>
            <input type="text" name="province" class="form-control" value="<?php echo esc($customer['province']); ?>">
          </div>
        </div>
        <div class="row g-2 mt-2">
          <div class="col-md-6">
            <label class="form-label">Kode Pos</label>
            <input type="text" name="postal_code" class="form-control" value="<?php echo esc($customer['postal_code']); ?>">
          </div>
          <div class="col-md-6 d-flex align-items-end">
            <button class="btn btn-primary w-100">Simpan Perubahan</button>
          </div>
        </div>
      </form>
      <div class="mt-3">
        <a class="btn btn-outline-secondary" href="checkout.php">Kembali ke Checkout</a>
      </div>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
