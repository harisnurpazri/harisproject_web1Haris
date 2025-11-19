<?php
session_start();

// --- Auth Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../auth/login.php');
    exit;
}

require '../../config/koneksi.php';

// --- Function Helper ---
function fetchCount(mysqli $db, string $table, string $where = ''): int {
    $query = "SELECT COUNT(*) AS c FROM {$table}";
    if ($where) $query .= " WHERE {$where}";
    $result = mysqli_query($db, $query);
    return (int) mysqli_fetch_assoc($result)['c'];
}

// --- Stats ---
$produkCount = fetchCount($koneksi, 'produk');
$userCount   = fetchCount($koneksi, 'users', "role='user'");
$orderCount  = fetchCount($koneksi, 'orders');
$todayOrders = fetchCount($koneksi, 'orders', "DATE(created_at) = CURDATE()");
$unreadChats = fetchCount($koneksi, 'chat_messages', "sender_role='user' AND is_read=0");

// Total revenue
$revenueQuery = "SELECT SUM(total) as revenue FROM orders WHERE status != 'cancelled'";
$revenueResult = mysqli_query($koneksi, $revenueQuery);
$totalRevenue = mysqli_fetch_assoc($revenueResult)['revenue'] ?? 0;

// --- Chart Data (Last 7 Days Orders) ---
$chartData = [];
$chartQuery = "
    SELECT DATE(created_at) AS dt, COUNT(*) AS cnt, SUM(total) as revenue
    FROM orders
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at) ASC
";

$chartResult = mysqli_query($koneksi, $chartQuery);
while ($row = mysqli_fetch_assoc($chartResult)) {
    $chartData[] = $row;
}

// Get current page
$currentPage = $_GET['page'] ?? 'dashboard';
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Dashboard - Meubeul Dua Putra</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../../assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

<div class="admin-wrapper">
  
  <!-- SIDEBAR -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
      <a href="dashboard_admin.php" class="sidebar-brand">
        <i class="fa-solid fa-couch"></i>
        <span>Admin Panel</span>
      </a>
      <small style="opacity: 0.7; display: block; margin-top: 0.5rem;">
        Meubeul Dua Putra
      </small>
    </div>
    
    <nav class="sidebar-nav">
      <ul class="nav flex-column">
        <li class="nav-item">
          <a href="dashboard_admin.php?page=dashboard" class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
            <i class="fa-solid fa-gauge-high"></i>
            <span>Dashboard</span>
          </a>
        </li>
        
        <li class="nav-item">
          <a href="dashboard_admin.php?page=products" class="nav-link <?= $currentPage === 'products' ? 'active' : '' ?>">
            <i class="fa-solid fa-box-open"></i>
            <span>Kelola Produk</span>
          </a>
        </li>
        
        <li class="nav-item">
          <a href="dashboard_admin.php?page=sales" class="nav-link <?= $currentPage === 'sales' ? 'active' : '' ?>">
            <i class="fa-solid fa-chart-line"></i>
            <span>Riwayat Penjualan</span>
          </a>
        </li>
        
        <li class="nav-item">
          <a href="dashboard_admin.php?page=chats" class="nav-link <?= $currentPage === 'chats' ? 'active' : '' ?>">
            <i class="fa-solid fa-comments"></i>
            <span>Chat Pelanggan</span>
            <?php if ($unreadChats > 0): ?>
            <span class="badge-notify"><?= $unreadChats ?></span>
            <?php endif; ?>
          </a>
        </li>
        
        <li class="nav-item">
          <a href="dashboard_admin.php?page=users" class="nav-link <?= $currentPage === 'users' ? 'active' : '' ?>">
            <i class="fa-solid fa-users"></i>
            <span>Kelola User</span>
          </a>
        </li>
        
        <li class="nav-item">
          <a href="dashboard_admin.php?page=education" class="nav-link <?= $currentPage === 'education' ? 'active' : '' ?>">
            <i class="fa-solid fa-book-open"></i>
            <span>Konten Edukasi</span>
          </a>
        </li>
        
        <li class="nav-item" style="margin-top: 2rem;">
          <a href="../../auth/logout.php" class="nav-link" style="color: #FCA5A5;">
            <i class="fa-solid fa-right-from-bracket"></i>
            <span>Logout</span>
          </a>
        </li>
      </ul>
    </nav>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="admin-content">
    
    <!-- Header -->
    <div class="admin-header">
      <div>
        <h4 class="mb-1 fw-bold">
          <?php
          $pageTitle = [
            'dashboard' => 'Dashboard Overview',
            'products' => 'Kelola Produk',
            'sales' => 'Riwayat Penjualan',
            'chats' => 'Chat dengan Pelanggan',
            'users' => 'Kelola User',
            'education' => 'Konten Edukasi'
          ];
          echo $pageTitle[$currentPage] ?? 'Dashboard';
          ?>
        </h4>
        <p class="text-muted mb-0">Selamat datang, <?= htmlspecialchars($_SESSION['user_name']) ?></p>
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary mobile-menu-btn" onclick="document.getElementById('adminSidebar').classList.toggle('show')">
          <i class="fa-solid fa-bars"></i>
        </button>
        <a href="../../index.php" class="btn btn-outline-primary">
          <i class="fa-solid fa-eye me-2"></i>Lihat Website
        </a>
      </div>
    </div>

    <?php
    // Load page content
    switch($currentPage) {
      case 'products':
        include 'pages/products.php';
        break;
      case 'sales':
        include 'pages/sales.php';
        break;
      case 'chats':
        include 'pages/chats.php';
        break;
      case 'users':
        include 'pages/users.php';
        break;
      case 'education':
        include 'pages/education.php';
        break;
      default:
        // Dashboard content
    ?>
    
    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
      <div class="col-xl-3 col-md-6">
        <div class="stats-card">
          <div class="stats-icon" style="background: linear-gradient(135deg, rgba(139, 111, 71, 0.1), rgba(205, 127, 50, 0.2));">
            <i class="fa-solid fa-box-open" style="color: var(--accent-bronze);"></i>
          </div>
          <h6 class="text-muted mb-1">Total Produk</h6>
          <h2 class="fw-bold mb-0" style="color: var(--primary-walnut);"><?= $produkCount ?></h2>
          <small class="text-muted">Produk aktif</small>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="stats-card">
          <div class="stats-icon" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.2));">
            <i class="fa-solid fa-chart-line" style="color: #10B981;"></i>
          </div>
          <h6 class="text-muted mb-1">Total Penjualan</h6>
          <h2 class="fw-bold mb-0" style="color: #10B981;"><?= $orderCount ?></h2>
          <small class="text-success">+<?= $todayOrders ?> hari ini</small>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="stats-card">
          <div class="stats-icon" style="background: linear-gradient(135deg, rgba(212, 175, 55, 0.1), rgba(205, 127, 50, 0.2));">
            <i class="fa-solid fa-money-bill-wave" style="color: var(--accent-gold);"></i>
          </div>
          <h6 class="text-muted mb-1">Total Revenue</h6>
          <h2 class="fw-bold mb-0" style="color: var(--accent-gold); font-size: 1.5rem;">
            Rp <?= number_format($totalRevenue, 0, ',', '.') ?>
          </h2>
          <small class="text-muted">Dari semua penjualan</small>
        </div>
      </div>

      <div class="col-xl-3 col-md-6">
        <div class="stats-card">
          <div class="stats-icon" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.2));">
            <i class="fa-solid fa-users" style="color: #3B82F6;"></i>
          </div>
          <h6 class="text-muted mb-1">Total Pelanggan</h6>
          <h2 class="fw-bold mb-0" style="color: #3B82F6;"><?= $userCount ?></h2>
          <small class="text-muted">User terdaftar</small>
        </div>
      </div>
    </div>

    <!-- Chart & Notifications -->
    <div class="row g-4 mb-4">
      <div class="col-xl-8">
        <div class="card shadow-sm border-0 p-4" style="border-radius: 16px;">
          <h5 class="fw-bold mb-3">
            <i class="fa-solid fa-chart-area me-2" style="color: var(--accent-bronze);"></i>
            Grafik Penjualan (7 Hari Terakhir)
          </h5>
          <canvas id="salesChart" style="max-height:300px"></canvas>
        </div>
      </div>
      
      <div class="col-xl-4">
        <div class="card shadow-sm border-0 p-4" style="border-radius: 16px; height: 100%;">
          <h5 class="fw-bold mb-3">
            <i class="fa-solid fa-bell me-2" style="color: #F59E0B;"></i>
            Notifikasi
          </h5>
          
          <?php if ($unreadChats > 0): ?>
          <div class="alert alert-warning" style="border-radius: 12px;">
            <i class="fa-solid fa-comment-dots me-2"></i>
            <strong><?= $unreadChats ?></strong> pesan baru dari pelanggan
            <a href="dashboard_admin.php?page=chats" class="alert-link d-block mt-2">
              Lihat Chat <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
          </div>
          <?php endif; ?>
          
          <?php if ($todayOrders > 0): ?>
          <div class="alert alert-success" style="border-radius: 12px;">
            <i class="fa-solid fa-shopping-cart me-2"></i>
            <strong><?= $todayOrders ?></strong> pesanan baru hari ini
            <a href="dashboard_admin.php?page=sales" class="alert-link d-block mt-2">
              Lihat Pesanan <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
          </div>
          <?php endif; ?>
          
          <?php if ($unreadChats == 0 && $todayOrders == 0): ?>
          <div class="text-center py-4">
            <i class="fa-solid fa-check-circle fa-3x text-success mb-3"></i>
            <p class="text-muted">Semua notifikasi sudah dibaca</p>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="card shadow-sm border-0 p-4" style="border-radius: 16px;">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">
          <i class="fa-solid fa-clock-rotate-left me-2" style="color: var(--accent-bronze);"></i>
          Pesanan Terbaru
        </h5>
        <a href="dashboard_admin.php?page=sales" class="btn btn-sm btn-outline-primary">
          Lihat Semua <i class="fa-solid fa-arrow-right ms-1"></i>
        </a>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>ID Order</th>
              <th>Pelanggan</th>
              <th>Total</th>
              <th>Status</th>
              <th>Tanggal</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $recentOrders = mysqli_query($koneksi, "
            SELECT o.*, u.nama 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC 
            LIMIT 5
          ");
          
          while ($order = mysqli_fetch_assoc($recentOrders)):
            $statusBadge = [
              'pending' => 'warning',
              'processing' => 'info',
              'completed' => 'success',
              'cancelled' => 'danger'
            ];
            $statusClass = $statusBadge[$order['status']] ?? 'secondary';
          ?>
            <tr>
              <td><span class="badge bg-secondary">#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></span></td>
              <td><?= htmlspecialchars($order['nama']) ?></td>
              <td><strong>Rp <?= number_format($order['total'], 0, ',', '.') ?></strong></td>
              <td><span class="badge bg-<?= $statusClass ?>"><?= ucfirst($order['status']) ?></span></td>
              <td><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></td>
              <td class="text-center">
                <a href="dashboard_admin.php?page=sales&view=<?= $order['id'] ?>" class="btn btn-sm btn-outline-primary">
                  <i class="fa-solid fa-eye"></i>
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
    
    <?php
        break;
    }
    ?>

  </main>
  
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
<?php if ($currentPage === 'dashboard'): ?>
// Sales Chart
const labels = <?= json_encode(array_column($chartData, 'dt')); ?>;
const salesData = <?= json_encode(array_map('intval', array_column($chartData, 'cnt'))); ?>;
const revenueData = <?= json_encode(array_map('intval', array_column($chartData, 'revenue'))); ?>;

new Chart(document.getElementById('salesChart'), {
  type: 'line',
  data: {
    labels,
    datasets: [{
      label: 'Jumlah Pesanan',
      data: salesData,
      borderColor: '#CD7F32',
      backgroundColor: 'rgba(205, 127, 50, 0.1)',
      borderWidth: 3,
      tension: 0.4,
      fill: true,
      yAxisID: 'y'
    }, {
      label: 'Revenue (Juta Rp)',
      data: revenueData.map(v => v / 1000000),
      borderColor: '#10B981',
      backgroundColor: 'rgba(16, 185, 129, 0.1)',
      borderWidth: 3,
      tension: 0.4,
      fill: true,
      yAxisID: 'y1'
    }]
  },
  options: { 
    responsive: true,
    maintainAspectRatio: true,
    interaction: {
      mode: 'index',
      intersect: false,
    },
    plugins: {
      legend: {
        display: true,
        position: 'top',
        labels: {
          usePointStyle: true,
          padding: 15
        }
      },
      tooltip: {
        backgroundColor: 'rgba(44, 44, 44, 0.9)',
        padding: 12,
        cornerRadius: 8
      }
    },
    scales: {
      y: {
        type: 'linear',
        display: true,
        position: 'left',
        title: {
          display: true,
          text: 'Jumlah Pesanan'
        },
        beginAtZero: true,
        ticks: {
          precision: 0
        }
      },
      y1: {
        type: 'linear',
        display: true,
        position: 'right',
        title: {
          display: true,
          text: 'Revenue (Juta Rp)'
        },
        beginAtZero: true,
        grid: {
          drawOnChartArea: false
        }
      }
    }
  }
});
<?php endif; ?>
</script>

<!-- Sebelum closing </body> -->
<style>
  body .footer,
  body .footer-simple {
    margin-left: 280px !important;
    width: calc(100% - 280px) !important;
    z-index: 50 !important;
  }
  
  @media (max-width: 992px) {
    body .footer,
    body .footer-simple {
      margin-left: 0 !important;
      width: 100% !important;
    }
  }
</style>

<?php include '../../components/footer.php'; ?>

</body>
</html> 