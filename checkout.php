<?php
include 'header.php';
$cart = $_SESSION['cart'] ?? [];
if(!$cart){
  echo '<div class="alert alert-info">Keranjang kosong. <a href="index.php">Belanja dulu</a>.</div>';
  include 'footer.php'; exit;
}
$subtotal = 0;
foreach($cart as $item){ $subtotal += $item['price'] * $item['qty']; }
?>
<div class="row g-3 align-items-start">
  <div class="col-lg-7">
    <div class="card p-3">
      <h4 class="mb-2">Ringkasan Pesanan</h4>
      <div class="table-responsive">
        <table class="table table-sm align-middle mb-2" style="table-layout: fixed; width: 100%;">
          <colgroup>
            <col style="width: 34%;">
            <col style="width: 18%;">
            <col style="width: 12%;">
            <col style="width: 12%;">
            <col style="width: 8%;">
            <col style="width: 16%;">
          </colgroup>
          <thead><tr><th>Produk</th><th>Seller</th><th>Ukuran</th><th class="text-end">Harga</th><th class="text-center">Qty</th><th class="text-end">Subtotal</th></tr></thead>
          <tbody>
          <?php foreach($cart as $item): $st = $item['price'] * $item['qty']; ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <?php if($item['image']): ?><img src="<?php echo esc($item['image']); ?>" width="44" class="rounded" style="object-fit:cover; height:44px; flex-shrink:0;"><?php endif; ?>
                  <div class="small fw-semibold" style="word-break: break-word;"><?php echo esc($item['name']); ?></div>
                </div>
              </td>
              <td class="small"><?php echo !empty($item['seller_name']) ? esc($item['seller_name']) : '-'; ?></td>
              <td class="small"><?php echo esc($item['size']); ?></td>
              <td class="text-end small"><?php echo formatRupiah($item['price']); ?></td>
              <td class="text-center small"><?php echo (int)$item['qty']; ?></td>
              <td class="text-end small"><?php echo formatRupiah($st); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="5" class="text-end">Subtotal</th>
              <th class="text-end"><?php echo formatRupiah($subtotal); ?></th>
            </tr>
          </tfoot>
        </table>
      </div>
      <a href="cart.php" class="btn btn-primary btn-sm">Kembali ke Keranjang</a>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card p-3">
      <h4 class="mb-2">Alamat Pengiriman</h4>
      <form method="post" action="place_order.php" novalidate>
        <div class="mb-2">
          <label class="form-label">Nama Lengkap</label>
          <input type="text" class="form-control" name="full_name" required>
        </div>
        <div class="mb-2">
          <label class="form-label">No. HP / WA</label>
          <input type="tel" class="form-control" name="phone" required>
        </div>
        <div class="mb-2">
          <label class="form-label">Alamat Lengkap</label>
          <textarea class="form-control" name="address" rows="3" required></textarea>
        </div>
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">Kota / Kabupaten</label>
            <input type="text" class="form-control" name="city" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Provinsi</label>
            <input type="text" class="form-control" name="province" required>
          </div>
        </div>
        <div class="row g-2 mt-1">
          <div class="col-md-6">
            <label class="form-label">Kode Pos</label>
            <input type="text" class="form-control" name="postal_code" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Catatan (opsional)</label>
            <input type="text" class="form-control" name="notes">
          </div>
        </div>

        <div class="row g-2 mt-2">
          <div class="col-md-6">
            <label class="form-label">Kurir</label>
            <select class="form-select" name="courier" id="courier" required>
              <option value="JNE">JNE</option>
              <option value="SiCepat">SiCepat</option>
              <option value="AnterAja">AnterAja</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Layanan</label>
            <select class="form-select" name="service" id="service" required></select>
          </div>
        </div>
        <input type="hidden" name="shipping_cost" id="shipping_cost" value="0">

        <div class="border-top mt-2 pt-2">
          <div class="d-flex justify-content-between"><span>Subtotal</span><strong id="subtotal_text"><?php echo formatRupiah($subtotal); ?></strong></div>
          <div class="d-flex justify-content-between"><span>Ongkir</span><strong id="shipping_text">Rp 0</strong></div>
          <hr>
          <div class="d-flex justify-content-between fs-5"><span>Total</span><strong id="total_text"><?php echo formatRupiah($subtotal); ?></strong></div>
          <div class="mt-2 d-flex justify-content-end">
            <button class="btn btn-success btn-sm">Buat Pesanan</button>
          </div>
        </div>
      </form>
      <div class="small text-muted mt-2">* Ongkir dihitung sederhana (flat-rate).</div>
    </div>
  </div>
</div>

<script>
(function(){
  const subtotal = <?php echo (int)$subtotal; ?>;
  const courierEl = document.getElementById('courier');
  const serviceEl = document.getElementById('service');
  const shipHidden = document.getElementById('shipping_cost');
  const shipText = document.getElementById('shipping_text');
  const totalText = document.getElementById('total_text');
  const provinceEl = document.querySelector('input[name="province"]');
  const RATES = {
    'JNE': {'REG':20000,'YES':35000},
    'SiCepat': {'REG':18000,'BEST':28000},
    'AnterAja': {'REG':17000,'NDS':27000}
  };
  function fillServices(){
    const c = courierEl.value;
    const services = RATES[c];
    serviceEl.innerHTML = '';
    Object.keys(services).forEach(s=>{
      const opt = document.createElement('option'); opt.value = s; opt.textContent = s; serviceEl.appendChild(opt);
    });
  }
  function calcShipping(){
    const c = courierEl.value, s = serviceEl.value;
    let cost = (RATES[c] && RATES[c][s])? RATES[c][s] : 0;
    const prov = (provinceEl && provinceEl.value || '').toLowerCase();
    if(prov.includes('jakarta') || prov.includes('dki')) cost = Math.max(10000, Math.round(cost*0.8));
    return cost;
  }
  function formatRupiah(n){ return 'Rp ' + (n||0).toLocaleString('id-ID'); }
  function updateTotals(){
    const ship = calcShipping();
    shipHidden.value = ship;
    shipText.textContent = formatRupiah(ship);
    totalText.textContent = formatRupiah(subtotal + ship);
  }
  courierEl.addEventListener('change', ()=>{ fillServices(); updateTotals(); });
  serviceEl.addEventListener('change', updateTotals);
  if(provinceEl){ provinceEl.addEventListener('input', updateTotals); }
  fillServices(); updateTotals();
})();
</script>
<?php include 'footer.php'; ?>
