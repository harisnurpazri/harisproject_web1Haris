<?php
session_start();
require '../config/koneksi.php';

// Auth Guard
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Get Order ID dari URL
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($orderId <= 0) {
    header('Location: dashboard.php');
    exit;
}

// Ambil data order
$stmt = $koneksi->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->bind_param("ii", $orderId, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header('Location: dashboard.php');
    exit;
}

// Parse items dari JSON
$items = json_decode($order['items'], true);

// Get user info
$userStmt = $koneksi->prepare("SELECT nama, email FROM users WHERE id = ? LIMIT 1");
$userStmt->bind_param("i", $_SESSION['user_id']);
$userStmt->execute();
$user = $userStmt->get_result()->fetch_assoc();

function esc($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Invoice #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?> - Meubeul Dua Putra</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
body {
  background: linear-gradient(135deg, #FAF8F3 0%, #E8DCC4 100%);
  padding: 2rem 0;
}

.invoice-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem 1rem;
}

.invoice-container {
  max-width: 900px;
  width: 100%;
  background: white;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(92, 64, 51, 0.15);
  overflow: hidden;
}

.invoice-header {
  background: linear-gradient(135deg, var(--primary-walnut) 0%, #3E2723 100%);
  color: white;
  padding: 3rem 2.5rem;
  position: relative;
  overflow: hidden;
}

.invoice-header::before {
  content: '';
  position: absolute;
  top: -50%;
  right: -10%;
  width: 400px;
  height: 400px;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 50%;
}

.company-logo {
  width: 70px;
  height: 70px;
  background: linear-gradient(135deg, var(--accent-gold), var(--accent-bronze));
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  margin-bottom: 1rem;
  box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

.invoice-title {
  font-size: 2.5rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.invoice-body {
  padding: 2.5rem;
}

.info-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
  margin-bottom: 3rem;
}

.info-card {
  background: linear-gradient(135deg, rgba(139, 111, 71, 0.05), rgba(205, 127, 50, 0.05));
  padding: 1.5rem;
  border-radius: 12px;
  border-left: 4px solid var(--accent-bronze);
}

.info-label {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #6B5D52;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.info-value {
  color: var(--primary-walnut);
  font-weight: 600;
  font-size: 1rem;
}

.items-table {
  margin-bottom: 2rem;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}

.items-table thead {
  background: linear-gradient(135deg, var(--primary-walnut), #3E2723);
  color: white;
}

.items-table th {
  padding: 1rem;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.85rem;
  letter-spacing: 0.5px;
  border: none;
}

.items-table td {
  padding: 1rem;
  vertical-align: middle;
  border-bottom: 1px solid #E0D5C7;
}

.items-table tbody tr:hover {
  background: var(--warm-white);
  transition: background 0.3s ease;
}

.total-section {
  background: linear-gradient(135deg, rgba(205, 127, 50, 0.1), rgba(212, 175, 55, 0.05));
  padding: 2rem;
  border-radius: 12px;
  margin-bottom: 2rem;
}

.total-row {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 0;
  border-bottom: 1px dashed #E0D5C7;
}

.total-row:last-child {
  border-bottom: none;
  padding-top: 1rem;
  border-top: 2px solid var(--accent-bronze);
  margin-top: 0.5rem;
}

.grand-total {
  font-size: 1.5rem;
  color: var(--accent-bronze);
  font-weight: 700;
}

.payment-status-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: linear-gradient(135deg, #10B981, #059669);
  color: white;
  padding: 0.5rem 1.5rem;
  border-radius: 50px;
  font-weight: 600;
  font-size: 0.9rem;
  box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.footer-note {
  text-align: center;
  padding: 2rem;
  background: var(--warm-white);
  border-radius: 12px;
  margin-bottom: 2rem;
}

.action-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-custom {
  padding: 0.75rem 2rem;
  border-radius: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: all 0.3s ease;
  border: none;
  font-size: 0.9rem;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-print {
  background: linear-gradient(135deg, var(--accent-bronze), var(--primary-teak));
  color: white;
  box-shadow: 0 4px 15px rgba(205, 127, 50, 0.3);
}

.btn-print:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(205, 127, 50, 0.4);
  color: white;
}

.btn-download {
  background: linear-gradient(135deg, var(--accent-gold), var(--accent-bronze));
  color: white;
  box-shadow: 0 4px 15px rgba(212, 175, 55, 0.3);
}

.btn-download:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(212, 175, 55, 0.4);
  color: white;
}

.btn-back {
  background: white;
  color: var(--primary-walnut);
  border: 2px solid var(--primary-walnut);
}

.btn-back:hover {
  background: var(--primary-walnut);
  color: white;
}

@media print {
  body {
    background: white;
    padding: 0;
  }
  .invoice-wrapper {
    padding: 0;
  }
  .invoice-container {
    box-shadow: none;
    border-radius: 0;
  }
  .action-buttons,
  .no-print {
    display: none !important;
  }
}

@media (max-width: 768px) {
  .info-section {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  .invoice-header {
    padding: 2rem 1.5rem;
  }
  .invoice-title {
    font-size: 2rem;
  }
  .invoice-body {
    padding: 1.5rem;
  }
  .action-buttons {
    flex-direction: column;
  }
  .btn-custom {
    width: 100%;
    justify-content: center;
  }
}
</style>
</head>
<body>

<div class="invoice-wrapper">
<div class="invoice-container">
  
  <!-- Invoice Header -->
  <div class="invoice-header">
    <div class="d-flex justify-content-between align-items-start position-relative">
      <div>
        <div class="company-logo">
          <i class="fa-solid fa-couch"></i>
        </div>
        <h1 class="invoice-title">INVOICE</h1>
        <p style="font-size: 1.1rem; opacity: 0.9; margin: 0; letter-spacing: 1px;">
          #INV-<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?>
        </p>
      </div>
      <div class="text-end">
        <h4 style="font-weight: 700; margin-bottom: 0.5rem;">Meubeul Dua Putra</h4>
        <p style="font-size: 0.9rem; opacity: 0.9; margin: 0.25rem 0;">
          <i class="fa-solid fa-location-dot me-2"></i>Pangandaran, Jawa Barat
        </p>
        <p style="font-size: 0.9rem; opacity: 0.9; margin: 0.25rem 0;">
          <i class="fa-solid fa-phone me-2"></i>+62 812-3456-7890
        </p>
        <p style="font-size: 0.9rem; opacity: 0.9; margin: 0;">
          <i class="fa-solid fa-envelope me-2"></i>info@meubeul.com
        </p>
      </div>
    </div>
  </div>

  <!-- Invoice Body -->
  <div class="invoice-body">
    
    <!-- Info Section -->
    <div class="info-section">
      <div class="info-card">
        <div class="info-label">
          <i class="fa-solid fa-user me-1"></i>PELANGGAN
        </div>
        <div class="info-value mb-2"><?= esc($user['nama']) ?></div>
        <div style="font-size: 0.9rem; color: #6B5D52;">
          <div class="mb-1">
            <i class="fa-solid fa-envelope me-2"></i><?= esc($user['email']) ?>
          </div>
        </div>
      </div>

      <div class="info-card">
        <div class="info-label">
          <i class="fa-solid fa-calendar me-1"></i>DETAIL TRANSAKSI
        </div>
        <div class="d-flex justify-content-between mb-2">
          <span style="color: #6B5D52; font-size: 0.9rem;">Tanggal:</span>
          <span class="info-value">
            <?= date('d M Y', strtotime($order['created_at'])) ?>
          </span>
        </div>
        <div class="d-flex justify-content-between">
          <span style="color: #6B5D52; font-size: 0.9rem;">Waktu:</span>
          <span class="info-value">
            <?= date('H:i', strtotime($order['created_at'])) ?> WIB
          </span>
        </div>
      </div>
    </div>

    <!-- Items Table -->
    <table class="table items-table mb-0">
      <thead>
        <tr>
          <th style="width: 50px;">#</th>
          <th>Produk</th>
          <th style="width: 100px;" class="text-center">Qty</th>
          <th style="width: 150px;" class="text-end">Harga</th>
          <th style="width: 150px;" class="text-end">Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $no = 1; 
        foreach ($items as $item): 
        ?>
        <tr>
          <td><?= $no++ ?></td>
          <td>
            <div style="font-weight: 600; color: var(--primary-walnut);">
              <?= esc($item['nama_produk']) ?>
            </div>
          </td>
          <td class="text-center">
            <span class="badge bg-secondary" style="font-size: 0.9rem; padding: 0.4rem 0.8rem;">
              <?= $item['qty'] ?>
            </span>
          </td>
          <td class="text-end">
            Rp <?= number_format($item['harga'], 0, ',', '.') ?>
          </td>
          <td class="text-end" style="color: var(--accent-bronze); font-weight: 600;">
            Rp <?= number_format($item['subtotal'], 0, ',', '.') ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Total Section -->
    <div class="total-section">
      <div class="total-row">
        <span style="font-weight: 600; color: #6B5D52;">Subtotal</span>
        <span style="font-weight: 600; color: var(--primary-walnut);">
          Rp <?= number_format($order['total'], 0, ',', '.') ?>
        </span>
      </div>
      <div class="total-row">
        <span style="font-weight: 600; color: #6B5D52;">
          <i class="fa-solid fa-truck me-2"></i>Ongkir
        </span>
        <span style="font-weight: 600; color: var(--primary-walnut);">Rp 0</span>
      </div>
      <div class="total-row">
        <span style="font-weight: 600; color: #6B5D52;">
          <i class="fa-solid fa-tags me-2"></i>Diskon
        </span>
        <span style="font-weight: 600; color: #10B981;">- Rp 0</span>
      </div>
      <div class="total-row">
        <span style="font-weight: 600; font-size: 1.2rem;">TOTAL PEMBAYARAN</span>
        <span class="grand-total">Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
      </div>
    </div>

    <!-- Payment Info -->
    <div style="background: white; border: 2px dashed var(--accent-bronze); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
          <div style="font-size: 0.85rem; color: #6B5D52; font-weight: 600; margin-bottom: 0.5rem;">
            STATUS PEMBAYARAN
          </div>
          <span class="payment-status-badge">
            <i class="fa-solid fa-circle-check"></i>
            <span>LUNAS</span>
          </span>
        </div>
        <div class="text-end">
          <div style="font-size: 0.85rem; color: #6B5D52; font-weight: 600; margin-bottom: 0.5rem;">
            METODE PEMBAYARAN
          </div>
          <div style="color: var(--primary-walnut); font-weight: 600;">
            <i class="fa-solid fa-credit-card me-2"></i>Simulasi Checkout
          </div>
        </div>
      </div>
      <div style="font-size: 0.85rem; color: #6B5D52; font-style: italic; margin-top: 1rem;">
        <i class="fa-solid fa-circle-info me-2"></i>
        Pembayaran telah dikonfirmasi dan produk siap dikirim
      </div>
    </div>

    <!-- Footer Note -->
    <div class="footer-note">
      <div style="margin-bottom: 1rem;">
        <i class="fa-solid fa-heart" style="color: var(--accent-bronze); font-size: 1.5rem;"></i>
      </div>
      <h6 style="color: var(--primary-walnut); font-weight: 700; margin-bottom: 0.5rem;">
        Terima kasih atas pembelian Anda!
      </h6>
      <p style="color: #6B5D52; font-size: 0.9rem; margin-bottom: 0;">
        Jika ada pertanyaan, silakan hubungi customer service kami
      </p>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons no-print">
      <button class="btn btn-custom btn-print" onclick="window.print()">
        <i class="fa-solid fa-print"></i>
        <span>Cetak Invoice</span>
      </button>
      <a href="dashboard.php" class="btn btn-custom btn-back">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Kembali ke Dashboard</span>
      </a>
    </div>

  </div>

</div>
</div>

<?php include '../components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>