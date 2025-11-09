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
        redirect_with_message('schedule.php', 'danger', 'Supplier, customer, driver, and schedule date are required.');
    }

    $stmt = $mysqli->prepare('INSERT INTO schedules (supplier_id, customer_id, driver_id, supply_type, scheduled_date, notes) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('iiisss', $supplierId, $customerId, $driverId, $supplyType, $scheduledDate, $notes);
    $stmt->execute();

    redirect_with_message('schedule.php', 'success', 'Delivery scheduled successfully.');
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

$inputClass = 'mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400';
$labelClass = 'block text-sm font-semibold text-slate-700';

include __DIR__ . '/../includes/header.php';
?>
<?php include __DIR__ . '/../includes/navigation.php'; ?>
<div class="flex flex-1 flex-col">
  <?php include __DIR__ . '/../includes/topbar.php'; ?>
  <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8">
    <?php foreach (get_flash_messages() as $flash): ?>
      <?php
        $alertPalettes = [
            'success' => 'border-brand-500 bg-brand-50 text-brand-700',
            'danger' => 'border-red-500 bg-red-50 text-red-700',
            'warning' => 'border-amber-500 bg-amber-50 text-amber-700',
            'info' => 'border-slate-500 bg-slate-50 text-slate-700',
        ];
        $alertClass = $alertPalettes[$flash['type']] ?? $alertPalettes['info'];
      ?>
      <div class="mb-6 flex items-start gap-3 rounded-xl border-l-4 px-4 py-3 text-sm shadow-sm <?php echo $alertClass; ?>">
        <span class="font-semibold capitalize"><?php echo htmlspecialchars($flash['type']); ?>:</span>
        <span><?php echo htmlspecialchars($flash['message']); ?></span>
      </div>
    <?php endforeach; ?>

    <div class="grid gap-6 lg:grid-cols-3">
      <div class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-lg shadow-slate-200/40 backdrop-blur">
        <h2 class="text-xl font-semibold text-slate-900">Schedule Delivery</h2>
        <p class="mt-1 text-sm text-slate-500">Assign a driver and plan the next drop-off.</p>
        <form method="post" class="mt-6 space-y-4" novalidate>
          <div>
            <label class="<?php echo $labelClass; ?>" for="supplier_id">Supplier</label>
            <select class="<?php echo $inputClass; ?>" id="supplier_id" name="supplier_id" <?php echo $currentUser['role'] !== 'super_admin' ? 'disabled' : ''; ?>>
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
          <div>
            <label class="<?php echo $labelClass; ?>" for="customer_id">Customer</label>
            <select class="<?php echo $inputClass; ?>" id="customer_id" name="customer_id" required>
              <option value="">Select customer</option>
              <?php foreach ($customerOptions as $customer): ?>
                <option value="<?php echo (int) $customer['id']; ?>"><?php echo htmlspecialchars($customer['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="<?php echo $labelClass; ?>" for="driver_id">Driver</label>
            <select class="<?php echo $inputClass; ?>" id="driver_id" name="driver_id" required>
              <option value="">Select driver</option>
              <?php foreach ($driverOptions as $driver): ?>
                <option value="<?php echo (int) $driver['id']; ?>"><?php echo htmlspecialchars($driver['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="<?php echo $labelClass; ?>" for="supply_type">Supply Type</label>
            <select class="<?php echo $inputClass; ?>" id="supply_type" name="supply_type">
              <option value="Milk">Milk</option>
              <option value="Food">Food</option>
              <option value="Newspaper">Newspaper</option>
              <option value="Mixed">Mixed</option>
            </select>
          </div>
          <div>
            <label class="<?php echo $labelClass; ?>" for="scheduled_date">Scheduled Date</label>
            <input type="date" class="<?php echo $inputClass; ?>" id="scheduled_date" name="scheduled_date" required>
          </div>
          <div>
            <label class="<?php echo $labelClass; ?>" for="notes">Notes</label>
            <textarea class="<?php echo $inputClass; ?>" id="notes" name="notes" rows="3"></textarea>
          </div>
          <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-brand-600/30 transition hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">
            Add Schedule
          </button>
        </form>
      </div>
      <div class="lg:col-span-2">
        <div class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-lg shadow-slate-200/40 backdrop-blur">
          <h2 class="text-2xl font-semibold text-slate-900">Upcoming Deliveries</h2>
          <p class="text-sm text-slate-500">Monitor who is delivering what, and when.</p>
          <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                  <tr>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Driver</th>
                    <th class="px-4 py-3">Supply</th>
                    <th class="px-4 py-3">Supplier</th>
                    <th class="px-4 py-3">Notes</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white/50">
                  <?php foreach ($schedules as $schedule): ?>
                    <tr class="hover:bg-slate-50/80">
                      <td class="px-4 py-3 font-medium text-slate-900"><?php echo htmlspecialchars((new DateTime($schedule['scheduled_date']))->format('d M Y')); ?></td>
                      <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($schedule['customer_name']); ?></td>
                      <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($schedule['driver_name']); ?></td>
                      <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($schedule['supply_type']); ?></td>
                      <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($schedule['supplier_name']); ?></td>
                      <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($schedule['notes']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (empty($schedules)): ?>
                    <tr>
                      <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">No deliveries scheduled.</td>
                    </tr>
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
