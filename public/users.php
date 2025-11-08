<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

require_login();
require_role(['super_admin', 'supplier_admin']);

$role = $_GET['role'] ?? 'supplier_staff';
$manageableRoles = ['supplier_admin', 'supplier_staff', 'supplier_driver'];

if (!in_array($role, $manageableRoles, true) || !can_manage_role($role)) {
    redirect_with_message('/dashboard.php', 'danger', 'You are not allowed to manage that user type.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $supplierId = null;

    if ($name === '' || $username === '' || $password === '') {
        redirect_with_message("/users.php?role={$role}", 'danger', 'Name, username, and password are required.');
    }

    if ($role === 'supplier_admin' || has_role('super_admin')) {
        $supplierId = (int) ($_POST['supplier_id'] ?? 0);
        if ($supplierId <= 0) {
            redirect_with_message("/users.php?role={$role}", 'danger', 'Please select a supplier.');
        }
    } else {
        $supplierId = (int) current_user()['supplier_id'];
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $mysqli->prepare('INSERT INTO users (name, mobile, address, email, username, password_hash, role, supplier_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssssssi', $name, $mobile, $address, $email, $username, $passwordHash, $role, $supplierId);

    try {
        $stmt->execute();
        redirect_with_message("/users.php?role={$role}", 'success', ucfirst(str_replace('_', ' ', $role)) . ' created successfully.');
    } catch (mysqli_sql_exception $e) {
        redirect_with_message("/users.php?role={$role}", 'danger', 'Failed to create user: ' . $e->getMessage());
    }
}

$currentUser = current_user();
$supplierFilter = '';

if ($currentUser['role'] !== 'super_admin') {
    $supplierId = (int) $currentUser['supplier_id'];
    $supplierFilter = ' AND supplier_id = ' . $supplierId;
}

$stmt = $mysqli->prepare("SELECT u.*, s.name AS supplier_name FROM users u LEFT JOIN suppliers s ON s.id = u.supplier_id WHERE u.role = ?{$supplierFilter} ORDER BY u.created_at DESC");
$stmt->bind_param('s', $role);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$suppliers = [];
if ($currentUser['role'] === 'super_admin') {
    $suppliers = $mysqli->query('SELECT id, name FROM suppliers ORDER BY name')->fetch_all(MYSQLI_ASSOC);
} else {
    $suppliers[] = ['id' => $currentUser['supplier_id'], 'name' => $currentUser['supplier_name'] ?? 'My Supplier'];
}

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

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 gap-3">
      <div>
        <h2 class="h4 text-capitalize"><?php echo htmlspecialchars(str_replace('_', ' ', $role)); ?></h2>
        <p class="text-muted mb-0">Create and manage <?php echo htmlspecialchars(str_replace('_', ' ', $role)); ?> accounts.</p>
      </div>
      <div class="btn-group">
        <?php if (can_manage_role('supplier_admin')): ?>
          <a class="btn btn-outline-secondary <?php echo $role === 'supplier_admin' ? 'active' : ''; ?>" href="/users.php?role=supplier_admin">Supplier Admins</a>
        <?php endif; ?>
        <?php if (can_manage_role('supplier_staff')): ?>
          <a class="btn btn-outline-secondary <?php echo $role === 'supplier_staff' ? 'active' : ''; ?>" href="/users.php?role=supplier_staff">Staff</a>
        <?php endif; ?>
        <?php if (can_manage_role('supplier_driver')): ?>
          <a class="btn btn-outline-secondary <?php echo $role === 'supplier_driver' ? 'active' : ''; ?>" href="/users.php?role=supplier_driver">Drivers</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h3 class="h5">Create <?php echo htmlspecialchars(str_replace('_', ' ', $role)); ?></h3>
            <form method="post" novalidate>
              <div class="mb-3">
                <label class="form-label" for="name">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
              </div>
              <div class="mb-3">
                <label class="form-label" for="mobile">Mobile Number</label>
                <input type="text" class="form-control" id="mobile" name="mobile">
              </div>
              <div class="mb-3">
                <label class="form-label" for="address">Address</label>
                <textarea class="form-control" id="address" name="address" rows="2"></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email">
              </div>
              <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
              </div>
              <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
              </div>
              <div class="mb-3">
                <label class="form-label" for="supplier_id">Supplier</label>
                <select class="form-select" id="supplier_id" name="supplier_id" <?php echo $currentUser['role'] !== 'super_admin' ? 'disabled' : ''; ?>>
                  <option value="">Select supplier</option>
                  <?php foreach ($suppliers as $supplier): ?>
                    <option value="<?php echo (int) $supplier['id']; ?>" <?php echo $currentUser['role'] !== 'super_admin' && (int) $supplier['id'] === (int) $currentUser['supplier_id'] ? 'selected' : ''; ?>>
                      <?php echo htmlspecialchars($supplier['name']); ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <?php if ($currentUser['role'] !== 'super_admin'): ?>
                  <input type="hidden" name="supplier_id" value="<?php echo (int) $currentUser['supplier_id']; ?>">
                <?php endif; ?>
              </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-success">Create</button>
              </div>
            </form>
          </div>
        </div>
      </div>
      <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h3 class="h5 mb-3">Existing <?php echo htmlspecialchars(str_replace('_', ' ', $role)); ?></h3>
            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Supplier</th>
                    <th>Created</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($users as $item): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($item['name']); ?></td>
                      <td><?php echo htmlspecialchars($item['mobile']); ?></td>
                      <td><?php echo htmlspecialchars($item['email']); ?></td>
                      <td><?php echo htmlspecialchars($item['username']); ?></td>
                      <td><?php echo htmlspecialchars($item['supplier_name']); ?></td>
                      <td><?php echo htmlspecialchars((new DateTime($item['created_at']))->format('d M Y')); ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (empty($users)): ?>
                    <tr><td colspan="6" class="text-center text-muted">No users found.</td></tr>
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
<?php include __DIR__ . '/../includes/footer.php'; ?>
