<?php
require_once __DIR__.'/../config.php';
if (session_status()===PHP_SESSION_NONE) session_start();
$error = null;
$next = isset($_GET['next']) ? basename($_GET['next']) : 'products.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $u = trim($_POST['username'] ?? '');
  $p = trim($_POST['password'] ?? '');
  if($u === ADMIN_USER && $p === ADMIN_PASS){
    $_SESSION['is_admin'] = true;
    header('Location: '.$next);
    exit;
  }else{
    $error = 'Username atau password salah.';
  }
}
include '../header.php';
?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <div class="card p-4">
      <h3 class="mb-3">Login Admin</h3>
      <?php if($error): ?><div class="alert alert-danger"><?php echo esc($error); ?></div><?php endif; ?>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <input type="hidden" name="next" value="<?php echo esc($next); ?>">
        <div class="d-flex justify-content-between align-items-center">
          <a href="../index.php" class="btn btn-primary">Kembali</a>
          <button class="btn btn-primary">Masuk</button>
        </div>
        <div class="small text-muted mt-2">Default: <code><?php echo esc(ADMIN_USER); ?></code> / <code><?php echo esc(ADMIN_PASS); ?></code> — ganti di <code>config.php</code>.</div>
      </form>
    </div>
  </div>
</div>
<?php include '../footer.php'; ?>
