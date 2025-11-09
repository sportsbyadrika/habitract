<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

require_login();
require_role(['super_admin', 'supplier_admin']);

$role = $_GET['role'] ?? 'supplier_staff';
$manageableRoles = ['supplier_admin', 'supplier_staff', 'supplier_driver'];

if (!in_array($role, $manageableRoles, true) || !can_manage_role($role)) {
    redirect_with_message('dashboard.php', 'danger', 'You are not allowed to manage that user type.');
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
        redirect_with_message("users.php?role={$role}", 'danger', 'Name, username, and password are required.');
    }

    if ($role === 'supplier_admin' || has_role('super_admin')) {
        $supplierId = (int) ($_POST['supplier_id'] ?? 0);
        if ($supplierId <= 0) {
            redirect_with_message("users.php?role={$role}", 'danger', 'Please select a supplier.');
        }
    } else {
        $supplierId = (int) current_user()['supplier_id'];
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $mysqli->prepare('INSERT INTO users (name, mobile, address, email, username, password_hash, role, supplier_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('sssssssi', $name, $mobile, $address, $email, $username, $passwordHash, $role, $supplierId);

    try {
        $stmt->execute();
        redirect_with_message("users.php?role={$role}", 'success', ucfirst(str_replace('_', ' ', $role)) . ' created successfully.');
    } catch (mysqli_sql_exception $e) {
        redirect_with_message("users.php?role={$role}", 'danger', 'Failed to create user: ' . $e->getMessage());
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

$inputClass = 'mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400';
$labelClass = 'block text-sm font-semibold text-slate-700';
$tabBase = 'inline-flex items-center rounded-full border px-4 py-2 text-sm font-semibold transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600';
$tabActive = 'border-brand-600 bg-brand-600 text-white shadow-sm shadow-brand-600/40';
$tabInactive = 'border-slate-300 text-slate-600 hover:border-brand-500 hover:text-brand-600';

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

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-3xl font-semibold capitalize text-slate-900"><?php echo htmlspecialchars(str_replace('_', ' ', $role)); ?></h2>
        <p class="text-sm text-slate-500">Create and manage <?php echo htmlspecialchars(str_replace('_', ' ', $role)); ?> accounts.</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <?php if (can_manage_role('supplier_admin')): ?>
          <a class="<?php echo $tabBase . ' ' . ($role === 'supplier_admin' ? $tabActive : $tabInactive); ?>" href="<?php echo htmlspecialchars(base_url('users.php?role=supplier_admin')); ?>">Supplier Admins</a>
        <?php endif; ?>
        <?php if (can_manage_role('supplier_staff')): ?>
          <a class="<?php echo $tabBase . ' ' . ($role === 'supplier_staff' ? $tabActive : $tabInactive); ?>" href="<?php echo htmlspecialchars(base_url('users.php?role=supplier_staff')); ?>">Staff</a>
        <?php endif; ?>
        <?php if (can_manage_role('supplier_driver')): ?>
          <a class="<?php echo $tabBase . ' ' . ($role === 'supplier_driver' ? $tabActive : $tabInactive); ?>" href="<?php echo htmlspecialchars(base_url('users.php?role=supplier_driver')); ?>">Drivers</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
      <div class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-lg shadow-slate-200/40 backdrop-blur">
        <h3 class="text-xl font-semibold text-slate-900">Create <?php echo htmlspecialchars(str_replace('_', ' ', $role)); ?></h3>
        <p class="text-sm text-slate-500">Invite new members to coordinate your operations.</p>
        <form method="post" class="mt-6 space-y-4" novalidate>
          <div>
            <label class="<?php echo $labelClass; ?>" for="name">Full Name</label>
            <input type="text" class="<?php echo $inputClass; ?>" id="name" name="name" required>
          </div>
          <div>
            <label class="<?php echo $labelClass; ?>" for="mobile">Mobile Number</label>
            <input type="text" class="<?php echo $inputClass; ?>" id="mobile" name="mobile">
          </div>
          <div>
            <label class="<?php echo $labelClass; ?>" for="address">Address</label>
            <textarea class="<?php echo $inputClass; ?>" id="address" name="address" rows="2"></textarea>
          </div>
          <div>
            <label class="<?php echo $labelClass; ?>" for="email">Email</label>
            <input type="email" class="<?php echo $inputClass; ?>" id="email" name="email">
          </div>
          <div>
            <label class="<?php echo $labelClass; ?>" for="username">Username</label>
            <input type="text" class="<?php echo $inputClass; ?>" id="username" name="username" required>
          </div>
          <div>
            <label class="<?php echo $labelClass; ?>" for="password">Password</label>
            <input type="password" class="<?php echo $inputClass; ?>" id="password" name="password" required>
          </div>
          <div>
            <label class="<?php echo $labelClass; ?>" for="supplier_id">Supplier</label>
            <select class="<?php echo $inputClass; ?>" id="supplier_id" name="supplier_id" <?php echo $currentUser['role'] !== 'super_admin' ? 'disabled' : ''; ?>>
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
          <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-brand-600/30 transition hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">
            Create
          </button>
        </form>
      </div>
      <div class="lg:col-span-2">
        <div class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-lg shadow-slate-200/40 backdrop-blur">
          <h3 class="text-2xl font-semibold text-slate-900">Existing <?php echo htmlspecialchars(str_replace('_', ' ', $role)); ?></h3>
          <p class="text-sm text-slate-500">All team members currently authorised for this role.</p>
          <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                  <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Mobile</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Username</th>
                    <th class="px-4 py-3">Supplier</th>
                    <th class="px-4 py-3">Created</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white/50">
                  <?php foreach ($users as $item): ?>
                    <tr class="hover:bg-slate-50/80">
                      <td class="px-4 py-3 font-medium text-slate-900"><?php echo htmlspecialchars($item['name']); ?></td>
                      <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($item['mobile']); ?></td>
                      <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($item['email']); ?></td>
                      <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($item['username']); ?></td>
                      <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($item['supplier_name']); ?></td>
                      <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars((new DateTime($item['created_at']))->format('d M Y')); ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (empty($users)): ?>
                    <tr>
                      <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">No users found.</td>
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
<?php include __DIR__ . '/../includes/footer.php'; ?>
