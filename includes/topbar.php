<?php require_once __DIR__ . '/functions.php'; $user = current_user(); ?>
<header class="topbar d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
  <h1 class="h5 mb-0">Welcome, <?php echo htmlspecialchars($user['name'] ?? 'Guest'); ?></h1>
  <div class="d-flex align-items-center gap-3">
    <div class="text-end">
      <div class="fw-semibold"><?php echo htmlspecialchars($user['name'] ?? ''); ?></div>
      <div class="small text-muted text-capitalize"><?php echo htmlspecialchars(str_replace('_', ' ', $user['role'] ?? '')); ?></div>
    </div>
    <img src="/assets/avatar.svg" alt="Profile" class="avatar-thumb">
    <a href="/logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
  </div>
</header>
