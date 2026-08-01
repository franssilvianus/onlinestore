<?php include 'header.php'; ?>
<h3>Keranjang</h3>
<?php
$cart = $_SESSION['cart'] ?? [];
if(!$cart): ?>
<div class="alert alert-info">Keranjang masih kosong. <a href="index.php">Belanja dulu</a>.</div>
<?php else:
$total = 0; ?>
<form method="post" action="update_cart.php">
  <div class="table-responsive">
  <table class="table align-middle">
    <thead>
      <tr><th>Produk</th><th>Seller</th><th>Ukuran</th><th>Harga</th><th>Qty</th><th>Subtotal</th><th></th></tr>
    </thead>
    <tbody>
    <?php foreach($cart as $k=>$item):
      $subtotal = $item['price'] * $item['qty'];
      $total += $subtotal; ?>
      <tr>
        <td>
          <div class="d-flex align-items-center">
            <?php if($item['image']): ?><img src="<?php echo esc($item['image']); ?>" width="60" class="rounded me-2"><?php endif; ?>
            <div>
              <div><?php echo esc($item['name']); ?></div>
            </div>
          </div>
        </td>
        <td><?php echo !empty($item['seller_name']) ? esc($item['seller_name']) : '-'; ?></td>
        <td><?php echo esc($item['size']); ?></td>
        <td><?php echo formatRupiah($item['price']); ?></td>
        <td style="max-width:120px;">
          <input type="number" class="form-control" min="1" name="qty[<?php echo esc($k); ?>]" value="<?php echo (int)$item['qty']; ?>">
        </td>
        <td><?php echo formatRupiah($subtotal); ?></td>
        <td><a href="update_cart.php?remove=<?php echo urlencode($k); ?>" class="btn btn-sm btn-outline-danger">Hapus</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <div class="d-flex justify-content-between">
    <a class="btn btn-primary" href="index.php">Lanjut Belanja</a>
    <div>
      <span class="me-3 fw-bold fs-5">Total: <?php echo formatRupiah($total); ?></span>
      <button class="btn btn-primary">Update Keranjang</button>
      <?php if(!empty($_SESSION['customer_id'])): ?>
        <a class="btn btn-success" href="checkout.php">Checkout</a>
      <?php else: ?>
        <a class="btn btn-warning" href="customer_login.php?next=checkout.php">Login untuk Checkout</a>
      <?php endif; ?>
    </div>
  </div>
</form>
<?php endif; ?>
<?php include 'footer.php'; ?>
