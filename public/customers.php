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
    $name = trim($_POST['name'] ?? '');
    $route = trim($_POST['route'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $supplyType = trim($_POST['supply_type'] ?? '');
    $frequency = trim($_POST['frequency'] ?? 'daily');

    if ($name === '' || $address === '') {
        redirect_with_message('customers.php', 'danger', 'Customer name and address are required.');
    }

    $supplierId = $currentUser['role'] === 'super_admin' ? (int) ($_POST['supplier_id'] ?? 0) : (int) $currentUser['supplier_id'];

    if ($supplierId <= 0) {
        redirect_with_message('customers.php', 'danger', 'Please select a supplier.');
    }

    $stmt = $mysqli->prepare('INSERT INTO customers (supplier_id, name, route, address, phone, email, supply_type, frequency) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('isssssss', $supplierId, $name, $route, $address, $phone, $email, $supplyType, $frequency);
    $stmt->execute();

    redirect_with_message('customers.php', 'success', 'Customer added successfully.');
}

$supplierCondition = '';
if ($currentUser['role'] === 'super_admin' && $selectedSupplierId > 0) {
    $supplierCondition = 'WHERE c.supplier_id = ' . $selectedSupplierId;
} elseif ($currentUser['role'] !== 'super_admin') {
    $supplierCondition = 'WHERE c.supplier_id = ' . (int) $currentUser['supplier_id'];
}

$query = "SELECT c.*, s.name AS supplier_name FROM customers c LEFT JOIN suppliers s ON s.id = c.supplier_id {$supplierCondition} ORDER BY c.created_at DESC";
$customers = $mysqli->query($query)->fetch_all(MYSQLI_ASSOC);

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
        <h2 class="text-xl font-semibold text-slate-900">Add Customer</h2>
        <p class="mt-1 text-sm text-slate-500">Capture delivery details for a new household on your route.</p>
        <form method="post" class="mt-6 space-y-4" novalidate>
          <div>
            <label class="<?php echo $labelClass; ?>" for="name">Name</label>
            <input type="text" class="<?php echo $inputClass; ?>" id="name" name="name" required>
          </div>
          <div>
            <label class="<?php echo $labelClass; ?>" for="route">Route</label>
            <input type="text" class="<?php echo $inputClass; ?>" id="route" name="route">
          </div>
          <div>
            <label class="<?php echo $labelClass; ?>" for="address">Address</label>
            <textarea class="<?php echo $inputClass; ?>" id="address" name="address" rows="2" required></textarea>
          </div>
          <div>
            <label class="<?php echo $labelClass; ?>" for="phone">Phone</label>
            <input type="text" class="<?php echo $inputClass; ?>" id="phone" name="phone">
          </div>
          <div>
            <label class="<?php echo $labelClass; ?>" for="email">Email</label>
            <input type="email" class="<?php echo $inputClass; ?>" id="email" name="email">
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
            <label class="<?php echo $labelClass; ?>" for="frequency">Delivery Frequency</label>
            <select class="<?php echo $inputClass; ?>" id="frequency" name="frequency">
              <option value="Daily">Daily</option>
              <option value="Weekly">Weekly</option>
              <option value="Monthly">Monthly</option>
            </select>
          </div>
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
          <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-brand-600/30 transition hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">
            Save Customer
          </button>
        </form>
      </div>
      <div class="lg:col-span-2">
        <div class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-lg shadow-slate-200/40 backdrop-blur">
          <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
            <div>
              <h2 class="text-2xl font-semibold text-slate-900">Customer Directory</h2>
              <p class="text-sm text-slate-500">All registered households and their delivery preferences.</p>
            </div>
            <?php if ($currentUser['role'] === 'super_admin'): ?>
              <div class="w-full sm:w-auto">
                <label class="<?php echo $labelClass; ?>" for="supplier_id_filter">Filter by supplier</label>
                <select id="supplier_id_filter" class="<?php echo $inputClass; ?>">
                  <option value="">All suppliers</option>
                  <?php foreach ($suppliers as $supplier): ?>
                    <option value="<?php echo (int) $supplier['id']; ?>" <?php echo (int) $supplier['id'] === $selectedSupplierId ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($supplier['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            <?php endif; ?>
          </div>
          <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                  <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Route</th>
                    <th class="px-4 py-3">Supply</th>
                    <th class="px-4 py-3">Frequency</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Supplier</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white/50">
                  <?php foreach ($customers as $customer): ?>
                    <tr class="hover:bg-slate-50/80">
                      <td class="px-4 py-3 font-medium text-slate-900"><?php echo htmlspecialchars($customer['name']); ?></td>
                      <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($customer['route']); ?></td>
                      <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($customer['supply_type']); ?></td>
                      <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($customer['frequency']); ?></td>
                      <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($customer['phone']); ?></td>
                      <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($customer['email']); ?></td>
                      <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($customer['supplier_name']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (empty($customers)): ?>
                    <tr>
                      <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">No customers recorded yet.</td>
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
    const supplierSelect = document.getElementById('supplier_id');
    const supplierFilter = document.getElementById('supplier_id_filter');

    function updateSupplierFilter(value) {
      const url = new URL(window.location.href);
      if (value) {
        url.searchParams.set('supplier_id', value);
      } else {
        url.searchParams.delete('supplier_id');
      }
      window.location.href = url.toString();
    }

    supplierSelect?.addEventListener('change', function () {
      updateSupplierFilter(this.value);
    });

    supplierFilter?.addEventListener('change', function () {
      updateSupplierFilter(this.value);
    });
  </script>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
