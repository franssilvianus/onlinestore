<?php
include 'header.php';
$cart = $_SESSION['cart'] ?? [];
if(!$cart){
  echo '<div class="alert alert-info">Keranjang kosong. <a href="index.php">Belanja dulu</a>.</div>';
  include 'footer.php'; exit;
}

$required = ['full_name','phone','address','city','province','postal_code'];
$missing = [];
foreach($required as $r){ if(empty($_POST[$r])) $missing[] = $r; }

if($missing){
  echo '<div class="alert alert-danger">Mohon lengkapi data pengiriman.</div>';
  echo '<a class="btn btn-primary" href="checkout.php">Kembali ke Checkout</a>';
  include 'footer.php'; exit;
}

$subtotal = 0;
foreach($cart as $item){ $subtotal += $item['price'] * $item['qty']; }

$courier = $_POST['courier'] ?? 'JNE';
$service = $_POST['service'] ?? 'REG';
$province = $_POST['province'] ?? null;
$shipping = calcShipping($courier, $service, $province);
$total = $subtotal + $shipping;

$orderNo = 'INV'.date('YmdHis').rand(100,999);

$lastOrder = [
  'order_no' => $orderNo,
  'items' => array_values($cart),
  'subtotal' => $subtotal,
  'courier' => $courier,
  'service' => $service,
  'shipping' => $shipping,
  'total' => $total,
  'customer' => [
    'full_name' => $_POST['full_name'],
    'phone' => $_POST['phone'],
    'address' => $_POST['address'],
    'city' => $_POST['city'],
    'province' => $_POST['province'],
    'postal_code' => $_POST['postal_code'],
    'notes' => $_POST['notes'] ?? ''
  ],
  'created_at' => date('Y-m-d H:i:s')
];
$_SESSION['last_order'] = $lastOrder;

// ===== WhatsApp message =====
require_once __DIR__.'/config.php';
$phoneAdmin = defined('WHATSAPP_PHONE') ? WHATSAPP_PHONE : '+6287874872257';

$lines = [];
$lines[] = '*Pesanan Baru dari Rebelstuff*';
$lines[] = 'Invoice: #' . $orderNo;
$lines[] = 'Nama: ' . $_POST['full_name'];
$lines[] = 'No. WhatsApp: ' . $_POST['phone'];
$lines[] = 'Alamat: ' . trim($_POST['address'].' '.$_POST['city'].' '.$_POST['province'].' '.$_POST['postal_code']);
$lines[] = '';
$lines[] = '*Rincian Pesanan:*';
foreach($cart as $it){
  $lines[] = '- ' . ((int)$it['qty']) . 'x ' . $it['name'] . ' (Ukuran: ' . $it['size'] . ')';
}
$lines[] = '';
$lines[] = 'Subtotal: ' . formatRupiah($subtotal);
$lines[] = 'Ongkir: ' . formatRupiah($shipping) . ' (' . $courier . ' ' . $service . ')';
$lines[] = 'Total: ' . formatRupiah($total);
$lines[] = '';
$lines[] = '*Silakan konfirmasi pesanan ini segera.*';

$waText = implode("\n", $lines);
$waLink = 'https://api.whatsapp.com/send/?phone='.$phoneAdmin.'&text='.rawurlencode($waText).'&type=phone_number&app_absent=0';

?>
<div class="card p-4">
  <h3 class="mb-3">Terima kasih! Pesanan kamu kami terima.</h3>
  <div class="mb-2">No. Pesanan: <strong><?php echo esc($orderNo); ?></strong></div>
  <div class="row g-4 mt-1">
    <div class="col-md-6">
      <h5>Alamat Pengiriman</h5>
      <div><?php echo esc($_POST['full_name']); ?></div>
      <div><?php echo esc($_POST['phone']); ?></div>
      <div><?php echo nl2br(esc($_POST['address'])); ?></div>
      <div><?php echo esc($_POST['city']); ?>, <?php echo esc($_POST['province']); ?> <?php echo esc($_POST['postal_code']); ?></div>
      <?php if(!empty($_POST['notes'])): ?><div>Catatan: <?php echo esc($_POST['notes']); ?></div><?php endif; ?>
    </div>
    <div class="col-md-6">
      <h5>Ringkasan</h5>
      <div class="d-flex justify-content-between"><span>Subtotal</span><strong><?php echo formatRupiah($subtotal); ?></strong></div>
      <div class="d-flex justify-content-between"><span>Kurir</span><span><?php echo esc($courier); ?> - <?php echo esc($service); ?></span></div>
      <div class="d-flex justify-content-between"><span>Ongkir</span><strong><?php echo formatRupiah($shipping); ?></strong></div>
      <hr>
      <div class="d-flex justify-content-between"><span>Total</span><strong><?php echo formatRupiah($total); ?></strong></div>
    </div>
  </div>

  <div class="table-responsive mt-3">
    <table class="table">
      <thead><tr><th>Produk</th><th>Ukuran</th><th class="text-end">Harga</th><th class="text-center">Qty</th><th class="text-end">Subtotal</th></tr></thead>
      <tbody>
      <?php foreach($cart as $item): $st = $item['price'] * $item['qty']; ?>
        <tr>
          <td><?php echo esc($item['name']); ?></td>
          <td><?php echo esc($item['size']); ?></td>
          <td class="text-end"><?php echo formatRupiah($item['price']); ?></td>
          <td class="text-center"><?php echo (int)$item['qty']; ?></td>
          <td class="text-end"><?php echo formatRupiah($st); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="d-flex flex-wrap gap-2">
  <!-- <a id="btnOpenInvoice" class="btn btn-outline-primary" target="_blank" href="invoice.php?no=<?php echo urlencode($orderNo); ?>">
    <i class="fa fa-file-invoice"></i> Buka Invoice
  </a> -->
  <a id="btnOpenWA" class="btn btn-success" target="_blank" href="<?php echo $waLink; ?>">
    <i class="fa-brands fa-whatsapp"></i> Kirim ke WhatsApp
  </a>
  <a class="btn btn-primary" href="index.php">Belanja Lagi</a>
  <div class="btn-group">
    <button class="btn btn-primary dropdown-toggle" id="btnInvoiceDropdown" data-bs-toggle="dropdown" aria-expanded="false">
      <i class="fa fa-file-invoice"></i> Invoice
    </button>
    <ul class="dropdown-menu">
      <li><a class="dropdown-item" target="_blank" href="invoice.php?no=<?php echo urlencode($orderNo); ?>">Buka Invoice</a></li>
      <li><a class="dropdown-item" href="#" onclick="openInvoice('print');return false;">Cetak</a></li>
      <li><a class="dropdown-item" href="#" onclick="openInvoice('pdf');return false;">Simpan ke PDF</a></li>
    </ul>
  </div>
</div>
<script>
function openInvoice(mode){
  const url = 'invoice.php?no=<?php echo urlencode($orderNo); ?>&mode='+mode;
  const w = window.open(url, '_blank');
  if(!w){ alert('Popup diblok. Izinkan popup untuk situs ini.'); return; }
  w.onload = () => { if(mode==='print' || mode==='pdf'){ w.focus(); w.print(); } };
}

// Auto open invoice & WhatsApp (may be blocked by popup policy)
setTimeout(function(){
  try{
    var winInv = window.open('invoice.php?no=<?php echo urlencode($orderNo); ?>','_blank');
    var winWA  = window.open('<?php echo $waLink; ?>','_blank');
    if(!winInv || !winWA){
      console.log('Popup diblokir. Silakan klik tombol Invoice/WhatsApp.');
    }
  }catch(e){ console.log(e); }
}, 300);
</script>
</div>
<?php
$_SESSION['cart'] = [];
include 'footer.php'; ?>
