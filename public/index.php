<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

start_session();

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $mysqli->prepare('SELECT u.*, s.name AS supplier_name FROM users u LEFT JOIN suppliers s ON s.id = u.supplier_id WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = $user;
        header('Location: /dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-4">
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <div class="text-center mb-4">
            <img src="/assets/logo.svg" alt="HabitRact Supply" class="img-fluid" style="max-height:56px;">
            <h1 class="h4 mt-3">Welcome Back</h1>
            <p class="text-muted small">Sign in to manage your supply routes.</p>
          </div>
          <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>
          <form method="post" novalidate>
            <div class="mb-3">
              <label class="form-label" for="username">Username</label>
              <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
              <label class="form-label" for="password">Password</label>
              <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-success">Sign In</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
