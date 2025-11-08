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
        redirect_with_message('/customers.php', 'danger', 'Customer name and address are required.');
    }

    $supplierId = $currentUser['role'] === 'super_admin' ? (int) ($_POST['supplier_id'] ?? 0) : (int) $currentUser['supplier_id'];

    if ($supplierId <= 0) {
        redirect_with_message('/customers.php', 'danger', 'Please select a supplier.');
    }

    $stmt = $mysqli->prepare('INSERT INTO customers (supplier_id, name, route, address, phone, email, supply_type, frequency) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('isssssss', $supplierId, $name, $route, $address, $phone, $email, $supplyType, $frequency);
    $stmt->execute();

    redirect_with_message('/customers.php', 'success', 'Customer added successfully.');
}

$supplierCondition = '';
if ($currentUser['role'] === 'super_admin' && $selectedSupplierId > 0) {
    $supplierCondition = 'WHERE c.supplier_id = ' . $selectedSupplierId;
} elseif ($currentUser['role'] !== 'super_admin') {
    $supplierCondition = 'WHERE c.supplier_id = ' . (int) $currentUser['supplier_id'];
}

$query = "SELECT c.*, s.name AS supplier_name FROM customers c LEFT JOIN suppliers s ON s.id = c.supplier_id {$supplierCondition} ORDER BY c.created_at DESC";
$customers = $mysqli->query($query)->fetch_all(MYSQLI_ASSOC);

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
            <h2 class="h5">Add Customer</h2>
            <form method="post" novalidate>
              <div class="mb-3">
                <label class="form-label" for="name">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
              </div>
              <div class="mb-3">
                <label class="form-label" for="route">Route</label>
                <input type="text" class="form-control" id="route" name="route">
              </div>
              <div class="mb-3">
                <label class="form-label" for="address">Address</label>
                <textarea class="form-control" id="address" name="address" rows="2" required></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label" for="phone">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone">
              </div>
              <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email">
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
                <label class="form-label" for="frequency">Delivery Frequency</label>
                <select class="form-select" id="frequency" name="frequency">
                  <option value="Daily">Daily</option>
                  <option value="Weekly">Weekly</option>
                  <option value="Monthly">Monthly</option>
                </select>
              </div>
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
              <div class="d-grid">
                <button type="submit" class="btn btn-success">Save Customer</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h2 class="h5">Customer Directory</h2>
            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Route</th>
                    <th>Supply</th>
                    <th>Frequency</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Supplier</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($customers as $customer): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($customer['name']); ?></td>
                      <td><?php echo htmlspecialchars($customer['route']); ?></td>
                      <td><?php echo htmlspecialchars($customer['supply_type']); ?></td>
                      <td><?php echo htmlspecialchars($customer['frequency']); ?></td>
                      <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                      <td><?php echo htmlspecialchars($customer['email']); ?></td>
                      <td><?php echo htmlspecialchars($customer['supplier_name']); ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (empty($customers)): ?>
                    <tr><td colspan="7" class="text-center text-muted">No customers recorded yet.</td></tr>
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
