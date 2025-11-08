<?php require_once __DIR__ . '/functions.php'; ?>
<nav class="sidebar bg-dark text-white p-3">
  <div class="d-flex align-items-center mb-4">
    <img src="/assets/logo.svg" alt="HabitRact Supply" class="img-fluid sidebar-logo">
  </div>
  <ul class="nav nav-pills flex-column">
    <li class="nav-item mb-1"><a class="nav-link text-white" href="/dashboard.php">Dashboard</a></li>
    <?php if (has_role('super_admin')): ?>
      <li class="nav-item mb-1"><a class="nav-link text-white" href="/suppliers.php">Suppliers</a></li>
      <li class="nav-item mb-1"><a class="nav-link text-white" href="/users.php?role=supplier_admin">Supplier Admins</a></li>
    <?php endif; ?>
    <?php if (has_role('super_admin') || has_role('supplier_admin')): ?>
      <li class="nav-item mb-1"><a class="nav-link text-white" href="/users.php?role=supplier_staff">Supplier Staff</a></li>
      <li class="nav-item mb-1"><a class="nav-link text-white" href="/users.php?role=supplier_driver">Supplier Drivers</a></li>
    <?php endif; ?>
    <li class="nav-item mb-1"><a class="nav-link text-white" href="/schedule.php">Delivery Schedule</a></li>
    <li class="nav-item mb-1"><a class="nav-link text-white" href="/customers.php">Customers</a></li>
  </ul>
</nav>
