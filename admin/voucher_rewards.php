<?php
require_once __DIR__.'/../functions.php';
if (session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__.'/auth.php';

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = null;
$success = null;

try {
  if($_SERVER['REQUEST_METHOD'] === 'POST'){
    if(!csrf_verify($_POST['csrf'] ?? '')) throw new Exception('Token CSRF tidak valid.');

    if($action === 'create'){
      addVoucherReward($_POST);
      header('Location: voucher_rewards.php?msg=created'); exit;
    }

    if($action === 'update' && $id){
      updateVoucherReward($id, $_POST);
      header('Location: voucher_rewards.php?msg=updated'); exit;
    }
  }

  if($action === 'delete' && $id){
    deleteVoucherReward($id);
    header('Location: voucher_rewards.php?msg=deleted'); exit;
  }
} catch(Throwable $e){
  $error = $e->getMessage();
}

include '../header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h3>Reward Voucher</h3>
    <p class="small text-muted">Tambah, ubah, atau nonaktifkan hadiah yang bisa ditukar pelanggan.</p>
  </div>
  <div class="d-flex gap-2">
    <a href="products.php" class="btn btn-outline-secondary"><i class="fa fa-arrow-left"></i> Kembali</a>
    <a href="voucher_rewards.php?action=new" class="btn btn-primary"><i class="fa fa-plus"></i> Reward Baru</a>
  </div>
</div>
<?php if($error): ?><div class="alert alert-danger"><?php echo esc($error); ?></div><?php endif; ?>
<?php if(isset($_GET['msg'])): ?><div class="alert alert-success">Sukses <?php echo esc($_GET['msg']); ?>.</div><?php endif; ?>

<?php if($action === 'new' || ($action === 'edit' && $id)):
  $reward = ['reward_key'=>'','name'=>'','description'=>'','voucher_cost'=>1,'is_active'=>1];
  if($action === 'edit'){
    $row = getVoucherRewardById($id);
    if($row) $reward = $row;
  }
?>
<div class="card p-3 mb-4">
  <form method="post" action="voucher_rewards.php?action=<?php echo $action === 'new' ? 'create' : 'update&id='.(int)$id; ?>">
    <input type="hidden" name="csrf" value="<?php echo esc(csrf_token()); ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Kode Reward</label>
        <input type="text" name="reward_key" class="form-control" required value="<?php echo esc($reward['reward_key']); ?>" <?php echo $action === 'edit' ? 'readonly' : ''; ?> placeholder="contoh: eco_tote">
      </div>
      <div class="col-md-6">
        <label class="form-label">Nama Reward</label>
        <input type="text" name="name" class="form-control" required value="<?php echo esc($reward['name']); ?>">
      </div>
      <div class="col-md-12">
        <label class="form-label">Deskripsi</label>
        <textarea name="description" class="form-control" rows="3"><?php echo esc($reward['description']); ?></textarea>
      </div>
      <div class="col-md-4">
        <label class="form-label">Biaya Voucher</label>
        <input type="number" name="voucher_cost" class="form-control" min="1" required value="<?php echo esc($reward['voucher_cost']); ?>">
      </div>
      <div class="col-md-4">
        <label class="form-label">Aktif?</label>
        <select name="is_active" class="form-select">
          <option value="1" <?php echo $reward['is_active'] ? 'selected' : ''; ?>>Ya</option>
          <option value="0" <?php echo !$reward['is_active'] ? 'selected' : ''; ?>>Tidak</option>
        </select>
      </div>
      <div class="col-md-12 d-flex gap-2">
        <button class="btn btn-primary"><?php echo $action === 'new' ? 'Simpan' : 'Update'; ?></button>
        <a href="voucher_rewards.php" class="btn btn-outline-secondary">Batal</a>
      </div>
    </div>
  </form>
</div>
<?php else:
  $rewards = getAllVoucherRewards(100);
?>
<div class="table-responsive">
  <table class="table align-middle table-hover">
    <thead>
      <tr>
        <th>ID</th>
        <th>Kode</th>
        <th>Nama</th>
        <th>Biaya</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php if(!$rewards): ?>
        <tr><td colspan="6" class="text-center text-muted">Belum ada reward voucher.</td></tr>
      <?php endif; ?>
      <?php foreach($rewards as $row): ?>
        <tr>
          <td><?php echo (int)$row['id']; ?></td>
          <td><?php echo esc($row['reward_key']); ?></td>
          <td><?php echo esc($row['name']); ?></td>
          <td><?php echo (int)$row['voucher_cost']; ?></td>
          <td><?php echo $row['is_active'] ? 'Aktif' : 'Nonaktif'; ?></td>
          <td>
            <a class="btn btn-sm btn-outline-primary" href="voucher_rewards.php?action=edit&id=<?php echo (int)$row['id']; ?>">Edit</a>
            <a class="btn btn-sm btn-outline-danger" href="voucher_rewards.php?action=delete&id=<?php echo (int)$row['id']; ?>" onclick="return confirm('Hapus reward ini?')">Hapus</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
<?php include '../footer.php'; ?>