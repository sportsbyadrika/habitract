<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

require_login();
require_role(['super_admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name === '') {
        redirect_with_message('/suppliers.php', 'danger', 'Supplier name is required.');
    }

    $stmt = $mysqli->prepare('INSERT INTO suppliers (name, address, phone, email) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $name, $address, $phone, $email);
    $stmt->execute();

    redirect_with_message('/suppliers.php', 'success', 'Supplier created successfully.');
}

$suppliers = $mysqli->query('SELECT * FROM suppliers ORDER BY created_at DESC')->fetch_all(MYSQLI_ASSOC);

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
            <h2 class="h5">Create Supplier</h2>
            <form method="post" novalidate>
              <div class="mb-3">
                <label class="form-label" for="name">Supplier Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
              </div>
              <div class="mb-3">
                <label class="form-label" for="address">Address</label>
                <textarea class="form-control" id="address" name="address" rows="2"></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label" for="phone">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone">
              </div>
              <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email">
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
            <h2 class="h5">Existing Suppliers</h2>
            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Created</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($suppliers as $supplier): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($supplier['name']); ?></td>
                      <td><?php echo htmlspecialchars($supplier['address']); ?></td>
                      <td><?php echo htmlspecialchars($supplier['phone']); ?></td>
                      <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                      <td><?php echo htmlspecialchars((new DateTime($supplier['created_at']))->format('d M Y')); ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (empty($suppliers)): ?>
                    <tr><td colspan="5" class="text-center text-muted">No suppliers added yet.</td></tr>
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
