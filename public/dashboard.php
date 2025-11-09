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
<div class="flex flex-1 flex-col">
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8">
    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
      <div class="rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur">
        <h2 class="text-sm font-medium text-slate-500">Suppliers</h2>
        <p class="mt-2 text-4xl font-semibold text-slate-900"><?php echo (int) $stats['suppliers']; ?></p>
      </div>
      <div class="rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur">
        <h2 class="text-sm font-medium text-slate-500">Supplier Admins</h2>
        <p class="mt-2 text-4xl font-semibold text-slate-900"><?php echo (int) $stats['admins']; ?></p>
      </div>
      <div class="rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur">
        <h2 class="text-sm font-medium text-slate-500">Staff Members</h2>
        <p class="mt-2 text-4xl font-semibold text-slate-900"><?php echo (int) $stats['staff']; ?></p>
      </div>
      <div class="rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur">
        <h2 class="text-sm font-medium text-slate-500">Drivers</h2>
        <p class="mt-2 text-4xl font-semibold text-slate-900"><?php echo (int) $stats['drivers']; ?></p>
      </div>
    </div>

    <section class="mt-8 rounded-3xl border border-slate-200 bg-white/90 p-8 shadow-lg shadow-slate-200/40 backdrop-blur">
      <h2 class="text-2xl font-semibold text-slate-900">Route Overview</h2>
      <p class="mt-2 text-sm text-slate-500">Track your deliveries for milk, food, and newspapers across customer routes.</p>
      <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <div class="flex items-center justify-between rounded-2xl bg-slate-100 px-4 py-3">
          <span class="text-sm font-medium text-slate-600">Active Customers</span>
          <span class="rounded-full bg-brand-600 px-3 py-1 text-sm font-semibold text-white"><?php echo (int) $stats['customers']; ?></span>
        </div>
        <div class="rounded-2xl border border-dashed border-slate-200 px-4 py-3 text-sm text-slate-600">
          Use the menu to manage suppliers, staff, drivers, customers, and delivery schedules.
        </div>
      </div>
    </section>
  </main>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
