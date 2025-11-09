<?php require_once __DIR__ . '/functions.php'; $user = current_user(); ?>
<header class="border-b border-slate-200 bg-white/80 backdrop-blur">
  <div class="mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-4">
    <div>
      <p class="text-sm font-medium text-slate-500">Welcome back</p>
      <h1 class="text-xl font-semibold text-slate-900"><?php echo htmlspecialchars($user['name'] ?? 'Guest'); ?></h1>
    </div>
    <div class="flex items-center gap-4">
      <div class="text-right">
        <p class="text-sm font-semibold text-slate-900"><?php echo htmlspecialchars($user['name'] ?? ''); ?></p>
        <p class="text-xs uppercase tracking-wide text-slate-500"><?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $user['role'] ?? ''))); ?></p>
      </div>
      <img src="<?php echo htmlspecialchars(base_url('assets/avatar.svg')); ?>" alt="Profile" class="h-10 w-10 rounded-full border border-slate-200 bg-slate-100 p-1">
      <a href="<?php echo htmlspecialchars(base_url('logout.php')); ?>" class="inline-flex items-center rounded-md bg-red-600 px-3 py-1 text-sm font-semibold text-white shadow-sm transition hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Logout</a>
    </div>
  </div>
</header>
