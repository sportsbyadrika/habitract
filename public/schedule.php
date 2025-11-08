<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

require_login();
require_role(['super_admin', 'supplier_admin', 'supplier_staff']);

$currentUser = current_user();

$suppliers = [];
if ($currentUser['role'] === 'super_admin') {
    $suppliers = $mysqli->query('SELECT id, name FROM suppliers ORDER BY name')->fetch_all(MYSQLI_ASSOC);
} else {
    $suppliers[] = ['id' => $currentUser['supplier_id'], 'name' => $currentUser['supplier_name'] ?? 'My Supplier'];
}

$selectedSupplierId = $currentUser['role'] === 'super_admin'
    ? (int) ($_POST['supplier_id'] ?? ($_GET['supplier_id'] ?? ($suppliers[0]['id'] ?? 0)))
    : (int) $currentUser['supplier_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $supplierId = $currentUser['role'] === 'super_admin' ? (int) ($_POST['supplier_id'] ?? 0) : (int) $currentUser['supplier_id'];
    $customerId = (int) ($_POST['customer_id'] ?? 0);
    $driverId = (int) ($_POST['driver_id'] ?? 0);
    $supplyType = trim($_POST['supply_type'] ?? '');
    $scheduledDate = $_POST['scheduled_date'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if ($supplierId <= 0 || $customerId <= 0 || $driverId <= 0 || $scheduledDate === '') {
        redirect_with_message('/schedule.php', 'danger', 'Supplier, customer, driver, and schedule date are required.');
    }

    $stmt = $mysqli->prepare('INSERT INTO schedules (supplier_id, customer_id, driver_id, supply_type, scheduled_date, notes) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('iiisss', $supplierId, $customerId, $driverId, $supplyType, $scheduledDate, $notes);
    $stmt->execute();

    redirect_with_message('/schedule.php', 'success', 'Delivery scheduled successfully.');
}

$supplierCondition = '';
if ($currentUser['role'] === 'super_admin' && $selectedSupplierId > 0) {
    $supplierCondition = 'WHERE sch.supplier_id = ' . $selectedSupplierId;
} elseif ($currentUser['role'] !== 'super_admin') {
    $supplierCondition = 'WHERE sch.supplier_id = ' . (int) $currentUser['supplier_id'];
}

$schedulesQuery = "SELECT sch.*, c.name AS customer_name, u.name AS driver_name, s.name AS supplier_name
FROM schedules sch
JOIN customers c ON c.id = sch.customer_id
JOIN users u ON u.id = sch.driver_id
JOIN suppliers s ON s.id = sch.supplier_id
{$supplierCondition}
ORDER BY sch.scheduled_date DESC";
$schedules = $mysqli->query($schedulesQuery)->fetch_all(MYSQLI_ASSOC);

$customersStmt = $mysqli->prepare('SELECT id, name FROM customers WHERE supplier_id = ? ORDER BY name');
$customersStmt->bind_param('i', $selectedSupplierId);
$customersStmt->execute();
$customerOptions = $customersStmt->get_result()->fetch_all(MYSQLI_ASSOC);

$driversStmt = $mysqli->prepare("SELECT id, name FROM users WHERE supplier_id = ? AND role = 'supplier_driver' ORDER BY name");
$driversStmt->bind_param('i', $selectedSupplierId);
$driversStmt->execute();
$driverOptions = $driversStmt->get_result()->fetch_all(MYSQLI_ASSOC);

include __DIR__ . '/../includes/header.php';
?>
<?php include __DIR__ . '/../includes/navigation.php'; ?>
<div class="content-wrapper">
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <main class="main-content">
    <?php foreach (get_flash_messages() as $flash): ?>
      <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>">
        <?php echo htmlspecialchars($flash['message']); ?>
      </div>
    <?php endforeach; ?>

    <div class="row g-4">
      <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h2 class="h5">Schedule Delivery</h2>
            <form method="post" novalidate>
              <div class="mb-3">
                <label class="form-label" for="supplier_id">Supplier</label>
                <select class="form-select" id="supplier_id" name="supplier_id" <?php echo $currentUser['role'] !== 'super_admin' ? 'disabled' : ''; ?>>
                  <option value="">Select supplier</option>
                  <?php foreach ($suppliers as $supplier): ?>
                    <option value="<?php echo (int) $supplier['id']; ?>" <?php echo (int) $supplier['id'] === $selectedSupplierId ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($supplier['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <?php if ($currentUser['role'] !== 'super_admin'): ?>
                  <input type="hidden" name="supplier_id" value="<?php echo (int) $currentUser['supplier_id']; ?>">
                <?php endif; ?>
              </div>
              <div class="mb-3">
                <label class="form-label" for="customer_id">Customer</label>
                <select class="form-select" id="customer_id" name="customer_id" required>
                  <option value="">Select customer</option>
                  <?php foreach ($customerOptions as $customer): ?>
                    <option value="<?php echo (int) $customer['id']; ?>"><?php echo htmlspecialchars($customer['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label" for="driver_id">Driver</label>
                <select class="form-select" id="driver_id" name="driver_id" required>
                  <option value="">Select driver</option>
                  <?php foreach ($driverOptions as $driver): ?>
                    <option value="<?php echo (int) $driver['id']; ?>"><?php echo htmlspecialchars($driver['name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label" for="supply_type">Supply Type</label>
                <select class="form-select" id="supply_type" name="supply_type">
                  <option value="Milk">Milk</option>
                  <option value="Food">Food</option>
                  <option value="Newspaper">Newspaper</option>
                  <option value="Mixed">Mixed</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label" for="scheduled_date">Scheduled Date</label>
                <input type="date" class="form-control" id="scheduled_date" name="scheduled_date" required>
              </div>
              <div class="mb-3">
                <label class="form-label" for="notes">Notes</label>
                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
              </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-success">Add Schedule</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h2 class="h5">Upcoming Deliveries</h2>
            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Driver</th>
                    <th>Supply</th>
                    <th>Supplier</th>
                    <th>Notes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($schedules as $schedule): ?>
                    <tr>
                      <td><?php echo htmlspecialchars((new DateTime($schedule['scheduled_date']))->format('d M Y')); ?></td>
                      <td><?php echo htmlspecialchars($schedule['customer_name']); ?></td>
                      <td><?php echo htmlspecialchars($schedule['driver_name']); ?></td>
                      <td><?php echo htmlspecialchars($schedule['supply_type']); ?></td>
                      <td><?php echo htmlspecialchars($schedule['supplier_name']); ?></td>
                      <td><?php echo htmlspecialchars($schedule['notes']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (empty($schedules)): ?>
                    <tr><td colspan="6" class="text-center text-muted">No deliveries scheduled.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
<?php if ($currentUser['role'] === 'super_admin'): ?>
  <script>
    document.getElementById('supplier_id').addEventListener('change', function () {
      const value = this.value;
      const url = new URL(window.location.href);
      if (value) {
        url.searchParams.set('supplier_id', value);
      } else {
        url.searchParams.delete('supplier_id');
      }
      window.location.href = url.toString();
    });
  </script>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
