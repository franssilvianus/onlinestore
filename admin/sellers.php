<?php
require_once __DIR__.'/../functions.php';
if (session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__.'/auth.php';

$pdo = getPDO();
$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = null;

try{
  if($_SERVER['REQUEST_METHOD']==='POST'){
    if($action==='create'){
      addSeller($_POST);
      header('Location: sellers.php?msg=created'); exit;
    } elseif($action==='update' && $id){
      updateSeller($id, $_POST);
      header('Location: sellers.php?msg=updated'); exit;
    }
  } elseif($action==='delete' && $id){
    deleteSeller($id);
    header('Location: sellers.php?msg=deleted'); exit;
  }
}catch(Throwable $e){
  $error = $e->getMessage();
}

include '../header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h3>Kelola Seller</h3>
  <a href="sellers.php?action=new" class="btn btn-primary"><i class="fa fa-plus"></i> Seller Baru</a>
</div>
<?php if(isset($_GET['msg'])): ?><div class="alert alert-success">Sukses <?php echo esc($_GET['msg']); ?>.</div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><?php echo esc($error); ?></div><?php endif; ?>

<?php if($action==='new' || ($action==='edit' && $id)):
  $row = ['name'=>'','owner_name'=>'','phone'=>'','email'=>'','address'=>'','username'=>'','is_active'=>1];
  if($action==='edit'){ $row = getSeller($id) ?: $row; }
?>
<div class="card p-3">
  <form method="post" action="sellers.php?action=<?php echo $action==='new'?'create':'update&id='.(int)$id; ?>">
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Nama Seller</label>
        <input class="form-control" name="name" required value="<?php echo esc($row['name'] ?? ''); ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Nama Pemilik</label>
        <input class="form-control" name="owner_name" value="<?php echo esc($row['owner_name'] ?? ''); ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Username Login</label>
        <input class="form-control" name="username" required value="<?php echo esc($row['username'] ?? ''); ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Password Login</label>
        <input class="form-control" name="password" type="password" <?php echo $action==='new' ? 'required' : ''; ?>>
      </div>
      <div class="col-md-6">
        <label class="form-label">Telepon</label>
        <input class="form-control" name="phone" value="<?php echo esc($row['phone'] ?? ''); ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label">Email</label>
        <input class="form-control" name="email" value="<?php echo esc($row['email'] ?? ''); ?>">
      </div>
      <div class="col-12">
        <label class="form-label">Alamat</label>
        <textarea class="form-control" name="address" rows="3"><?php echo esc($row['address'] ?? ''); ?></textarea>
      </div>
      <div class="col-12">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="is_active" value="1" <?php echo !empty($row['is_active']) ? 'checked' : ''; ?>>
          <label class="form-check-label">Aktif</label>
        </div>
      </div>
      <div class="col-12">
        <button class="btn btn-primary">Simpan</button>
        <a href="sellers.php" class="btn btn-outline-secondary">Batal</a>
      </div>
    </div>
  </form>
</div>
<?php else:
  $rows = getSellers(); ?>
<div class="table-responsive">
  <table class="table align-middle">
    <thead><tr><th>ID</th><th>Nama Seller</th><th>Pemilik</th><th>Kontak</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
    <?php foreach($rows as $r): ?>
      <tr>
        <td><?php echo (int)$r['id']; ?></td>
        <td><?php echo esc($r['name']); ?></td>
        <td><?php echo esc($r['owner_name']); ?></td>
        <td><?php echo esc($r['phone'] ?: $r['email']); ?></td>
        <td><?php echo !empty($r['is_active']) ? 'Aktif' : 'User Nonaktif'; ?></td>
        <td>
          <a class="btn btn-sm btn-outline-primary" href="sellers.php?action=edit&id=<?php echo (int)$r['id']; ?>"><i class="fa fa-pen"></i> Edit</a>
          <a class="btn btn-sm btn-outline-danger" href="sellers.php?action=delete&id=<?php echo (int)$r['id']; ?>" onclick="return confirm('Hapus seller ini?')"><i class="fa fa-trash"></i> Hapus</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php include '../footer.php'; ?>
