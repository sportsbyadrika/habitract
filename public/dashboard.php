<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

require_login();

$user = current_user();
$role = $user['role'];

// Summary counts
$stats = [
    'suppliers' => 0,
    'admins' => 0,
    'staff' => 0,
    'drivers' => 0,
    'customers' => 0,
];

if ($role === 'super_admin') {
    $stats['suppliers'] = $mysqli->query('SELECT COUNT(*) AS total FROM suppliers')->fetch_assoc()['total'];
    $stats['admins'] = $mysqli->query("SELECT COUNT(*) AS total FROM users WHERE role = 'supplier_admin'")->fetch_assoc()['total'];
    $stats['staff'] = $mysqli->query("SELECT COUNT(*) AS total FROM users WHERE role = 'supplier_staff'")->fetch_assoc()['total'];
    $stats['drivers'] = $mysqli->query("SELECT COUNT(*) AS total FROM users WHERE role = 'supplier_driver'")->fetch_assoc()['total'];
} else {
    $supplierId = (int) $user['supplier_id'];
    $stats['admins'] = $mysqli->query("SELECT COUNT(*) AS total FROM users WHERE role = 'supplier_admin' AND supplier_id = {$supplierId}")->fetch_assoc()['total'];
    $stats['staff'] = $mysqli->query("SELECT COUNT(*) AS total FROM users WHERE role = 'supplier_staff' AND supplier_id = {$supplierId}")->fetch_assoc()['total'];
    $stats['drivers'] = $mysqli->query("SELECT COUNT(*) AS total FROM users WHERE role = 'supplier_driver' AND supplier_id = {$supplierId}")->fetch_assoc()['total'];
}

$stats['customers'] = $mysqli->query('SELECT COUNT(*) AS total FROM customers')->fetch_assoc()['total'];

include __DIR__ . '/../includes/header.php';
?>
<?php include __DIR__ . '/../includes/navigation.php'; ?>
<div class="content-wrapper">
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <main class="main-content">
    <div class="row g-3 mb-4">
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h2 class="h6 text-muted">Suppliers</h2>
            <p class="display-6 mb-0"><?php echo (int) $stats['suppliers']; ?></p>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h2 class="h6 text-muted">Supplier Admins</h2>
            <p class="display-6 mb-0"><?php echo (int) $stats['admins']; ?></p>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h2 class="h6 text-muted">Staff Members</h2>
            <p class="display-6 mb-0"><?php echo (int) $stats['staff']; ?></p>
          </div>
        </div>
      </div>
      <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h2 class="h6 text-muted">Drivers</h2>
            <p class="display-6 mb-0"><?php echo (int) $stats['drivers']; ?></p>
          </div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h2 class="h5">Route Overview</h2>
        <p class="text-muted">Track your deliveries for milk, food, and newspapers across customer routes.</p>
        <ul class="list-group list-group-flush">
          <li class="list-group-item d-flex justify-content-between align-items-center">
            Active Customers
            <span class="badge bg-primary rounded-pill"><?php echo (int) $stats['customers']; ?></span>
          </li>
          <li class="list-group-item">Use the menu to manage suppliers, staff, drivers, customers, and delivery schedules.</li>
        </ul>
      </div>
    </div>
  </main>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
