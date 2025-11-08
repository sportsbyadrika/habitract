<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/db.php';

start_session();

$error = null;
$lockoutSeconds = get_login_lockout_seconds();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = attempt_login($mysqli, $identifier, $password);

    if ($result['success']) {
        header('Location: dashboard.php');
        exit;
    }

    $error = $result['message'];
    $lockoutSeconds = get_login_lockout_seconds();
}

$isLocked = $lockoutSeconds > 0;
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
              <label class="form-label" for="username">Username or Email</label>
              <input type="text" class="form-control" id="username" name="username" required <?php echo $isLocked ? 'disabled' : ''; ?>>
            </div>
            <div class="mb-3">
              <label class="form-label" for="password">Password</label>
              <input type="password" class="form-control" id="password" name="password" required <?php echo $isLocked ? 'disabled' : ''; ?>>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-success" <?php echo $isLocked ? 'disabled' : ''; ?>>Sign In</button>
            </div>
          </form>
          <?php if ($isLocked): ?>
            <p class="text-center text-muted small mb-0 mt-3">Too many login attempts. Please wait <?php echo ceil($lockoutSeconds / 60); ?> minute(s) before trying again.</p>
          <?php else: ?>
            <p class="text-center text-muted small mb-0 mt-3">You can sign in using either your username or email address.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
