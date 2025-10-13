<?php
if (session_status()===PHP_SESSION_NONE) session_start();
require_once __DIR__.'/functions.php';
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Rebelstuff Store</title>
<meta name="description" content="Toko online sederhana dengan PHP dan MySQL">
<meta name="author" content="Your Name">
<link rel="icon" type="image/png" href="favicon.ico">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
:root{
  --bg:#0b0b0e; --card:#13131a; --text:#e6e6ea; --muted:#9aa0a6;
  --accent:#39ff14; --accent-2:#00e5ff;
}
*{font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,'Apple Color Emoji','Segoe UI Emoji'}
h1,h2,h3,.navbar-brand{font-family:'Bebas Neue','Inter',sans-serif; letter-spacing:.5px}
body{background:var(--bg); color:var(--text)}
a{color:var(--accent)} a:hover{color:var(--accent-2)}
.navbar-brand{font-weight:700}
.brand-neon{color:var(--accent)}
.card{background:var(--card); border:none; box-shadow:0 8px 24px rgba(0,0,0,.35)}
.text-muted{color:var(--muted)!important}
.badge-size{cursor:pointer; border:1px solid rgba(255,255,255,.08)!important; background:#1b1b24!important; color:var(--text)!important}
.badge-size.bg-dark{background:#000!important}
.btn-primary{background:var(--accent); border-color:var(--accent); color:#111}
.btn-primary:hover{background:#2be10c; border-color:#2be10c; color:#111}
.btn-outline-secondary{color:var(--text); border-color:rgba(255,255,255,.2)}
.btn-outline-secondary:hover{background:rgba(255,255,255,.08)}
.table{color:var(--text)}
.table thead th{border-color:rgba(255,255,255,.1)}
.table td,.table th{border-color:rgba(255,255,255,.06)}
.form-control,.form-select{background:#1a1a22; color:var(--text); border-color:rgba(255,255,255,.12)}
.form-control:focus,.form-select:focus{border-color:var(--accent); box-shadow:0 0 0 .25rem rgba(57,255,20,.15)}
.divider{height:3px;width:90px;background:linear-gradient(90deg,var(--accent),var(--accent-2));border-radius:8px;margin:8px 0 18px 0}
.hero-gradient{
  background:
    radial-gradient(1200px 600px at -10% -10%, rgba(57,255,20,.16), rgba(57,255,20,0) 65%),
    radial-gradient(800px 400px at 110% 10%, rgba(0,229,255,.16), rgba(0,229,255,0) 60%),
    linear-gradient(135deg, #0a0a0d, #12121a);
  color:#fff;
}
.glass-card{background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.12); backdrop-filter: blur(6px);}
.grid-icons .icon{width:42px; height:42px; display:inline-flex; align-items:center; justify-content:center; border-radius:12px; background:rgba(57,255,20,.12)}
.timeline{position:relative; padding-left:2rem}
.timeline:before{content:''; position:absolute; left:14px; top:0; bottom:0; width:2px; background:#242433}
.timeline .t-item{position:relative; margin-bottom:1rem; padding-left:2rem}
.timeline .t-item:before{content:''; position:absolute; left:6px; top:.4rem; width:10px; height:10px; background:var(--accent); border-radius:50%; box-shadow:0 0 0 3px rgba(57,255,20,.15)}
.carousel-caption{backdrop-filter: blur(2px)}

/* THEME RESET LIGHT */
html, body{ background:#ffffff !important; color:#212529 !important; }
.navbar{ background:#ffffff !important; }
.card{ background:#ffffff; }
.text-light{ color:#212529 !important; } /* prevent accidental light text */
a.nav-link{ color:#212529 !important; }
.carousel-caption{ color:#212529; }

/* Admin Products — equal height & tidy */
.admin-products-row { align-items: stretch; }
.admin-card { height: 100%; }
.admin-card .item { display:flex; gap:16px; align-items:flex-start; }
.product-thumb { width:96px; height:96px; object-fit:cover; border-radius:12px; display:block; }
.admin-card .actions { margin-top:auto; }


/* Admin Products — stable spacing & clean look */
.admin-products-row { align-items: stretch; }
.admin-card {
  height: 100%;
  border:1px solid #e9ecef;
  border-radius:14px;
  box-shadow:0 6px 16px rgba(0,0,0,.06);
  overflow:hidden;
  transition: box-shadow .2s ease;
}
.admin-card:hover{ box-shadow:0 12px 28px rgba(0,0,0,.10); }

.admin-card .item { display:flex; gap:16px; align-items:flex-start; }
.thumb-box{
  padding:6px; border-radius:12px;
  background:#f6f9ff;
  border:1px solid #e7effc;
  box-shadow:0 2px 8px rgba(13,110,253,.04);
  flex:0 0 auto;
}
.product-thumb{ width:110px; height:110px; border-radius:10px; object-fit:cover; display:block; }
@media (min-width: 992px){ .product-thumb{ width:120px; height:120px; } }
.admin-card .actions { margin-top:auto; }

/* Admin Products — meta flex fix */
.admin-card .meta{ flex:1 1 auto; min-width:0; display:flex; flex-direction:column; }
.admin-card .item{ width:100%; }
</style>
</head>
<?php $BASE_PATH = (strpos($_SERVER['PHP_SELF'],'/admin/')!==false)? '../' : ''; ?>
<body>
<nav class="navbar bg-white navbar-expand-lg shadow-sm" style="background:#0b0b0e;">
  <div class="container">
    <a class="navbar-brand brand-neon" href="<?php echo $BASE_PATH; ?>index.php">Rebelstuff</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div id="nav" class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="<?php echo $BASE_PATH; ?>index.php">Produk</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo $BASE_PATH; ?>cart.php"><i class="fa fa-shopping-cart"></i> Keranjang (<?php echo isset($_SESSION['cart'])? array_sum(array_column($_SESSION['cart'],'qty')):0; ?>)</a></li>
        <li class="nav-item"><a class="nav-link" href="<?php echo $BASE_PATH; ?>admin/products.php">Admin</a></li>
        <?php if(!empty($_SESSION['is_admin'])): ?>
        <li class="nav-item"><a class="nav-link" href="<?php echo $BASE_PATH; ?>admin/logout.php">Logout</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container my-4">
