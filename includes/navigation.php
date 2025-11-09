<?php require_once __DIR__ . '/functions.php'; ?>
<?php
$currentPath = basename($_SERVER['SCRIPT_NAME'] ?? '');

$linkClasses = static function (string $target, bool $isSub = false, ?bool $forceActive = null) use ($currentPath): string {
    $pathPart = parse_url($target, PHP_URL_PATH);
    $pathPart = $pathPart !== null ? ltrim($pathPart, '/') : ltrim($target, '/');
    $isActive = $forceActive ?? ($pathPart !== '' && $currentPath === basename($pathPart));

    if ($isSub) {
        $base = 'block rounded-md px-4 py-2 text-sm font-medium transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-500';

        return $isActive
            ? $base . ' bg-slate-100 text-slate-900'
            : $base . ' text-slate-700 hover:bg-slate-100 hover:text-slate-900';
    }

    $base = 'rounded-md px-3 py-2 text-sm font-medium transition focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white';

    return $isActive
        ? $base . ' bg-white/15 text-white'
        : $base . ' text-slate-100 hover:bg-white/10 hover:text-white';
};

$directoryLinks = [];
if (has_role('super_admin') || has_role('supplier_admin') || has_role('supplier_staff')) {
    $directoryLinks[] = ['label' => 'Customers', 'url' => 'customers.php'];
    $directoryLinks[] = ['label' => 'Delivery Schedule', 'url' => 'schedule.php'];
}

$teamLinks = [];
if (has_role('super_admin')) {
    $teamLinks[] = ['label' => 'Suppliers', 'url' => 'suppliers.php'];
}

if (can_manage_role('supplier_admin')) {
    $teamLinks[] = ['label' => 'Supplier Admins', 'url' => 'users.php?role=supplier_admin'];
}

if (can_manage_role('supplier_staff')) {
    $teamLinks[] = ['label' => 'Supplier Staff', 'url' => 'users.php?role=supplier_staff'];
}

if (can_manage_role('supplier_driver')) {
    $teamLinks[] = ['label' => 'Supplier Drivers', 'url' => 'users.php?role=supplier_driver'];
}

$directoryActive = false;
foreach ($directoryLinks as $link) {
    $linkPath = parse_url($link['url'], PHP_URL_PATH);
    $linkPath = $linkPath !== null ? basename($linkPath) : basename($link['url']);
    if ($linkPath !== '' && $linkPath === $currentPath) {
        $directoryActive = true;
        break;
    }
}

$teamActive = false;
foreach ($teamLinks as $link) {
    $linkPath = parse_url($link['url'], PHP_URL_PATH);
    $linkPath = $linkPath !== null ? basename($linkPath) : basename($link['url']);
    if ($linkPath !== '' && $linkPath === $currentPath) {
        $teamActive = true;
        break;
    }
}
?>
<nav class="bg-slate-900 text-white shadow">
  <div class="mx-auto w-full max-w-7xl px-4">
    <div class="flex flex-wrap items-center justify-between gap-4 py-4">
      <a href="<?php echo htmlspecialchars(base_url('dashboard.php')); ?>" class="flex items-center gap-3">
        <img src="<?php echo htmlspecialchars(base_url('assets/logo.svg')); ?>" alt="HabitRact Supply" class="h-10 w-auto">
        <span class="text-lg font-semibold tracking-tight">HabitRact Supply</span>
      </a>
      <div class="flex flex-wrap items-center gap-4">
        <a href="<?php echo htmlspecialchars(base_url('dashboard.php')); ?>" class="<?php echo $linkClasses('dashboard.php'); ?>">Dashboard</a>

        <?php if (!empty($directoryLinks)): ?>
          <details class="relative group/nav">
            <summary class="nav-summary inline-flex cursor-pointer items-center gap-2 <?php echo $linkClasses('customers.php', false, $directoryActive); ?>">
              <span>Directory</span>
              <svg class="h-3 w-3 text-slate-200 transition group-open/nav:-rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
              </svg>
            </summary>
            <div class="nav-dropdown pointer-events-none absolute right-0 top-full z-20 mt-3 w-56 rounded-lg bg-white p-2 opacity-0 shadow-xl ring-1 ring-black/5 transition duration-150 ease-out group-open/nav:pointer-events-auto group-open/nav:opacity-100">
              <?php foreach ($directoryLinks as $link): ?>
                <a href="<?php echo htmlspecialchars(base_url($link['url'])); ?>" class="<?php echo $linkClasses($link['url'], true); ?>"><?php echo htmlspecialchars($link['label']); ?></a>
              <?php endforeach; ?>
            </div>
          </details>
        <?php endif; ?>

        <?php if (!empty($teamLinks)): ?>
          <details class="relative group/nav">
            <summary class="nav-summary inline-flex cursor-pointer items-center gap-2 <?php echo $linkClasses('users.php', false, $teamActive); ?>">
              <span>Team</span>
              <svg class="h-3 w-3 text-slate-200 transition group-open/nav:-rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.17l3.71-3.94a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
              </svg>
            </summary>
            <div class="nav-dropdown pointer-events-none absolute right-0 top-full z-20 mt-3 w-56 rounded-lg bg-white p-2 opacity-0 shadow-xl ring-1 ring-black/5 transition duration-150 ease-out group-open/nav:pointer-events-auto group-open/nav:opacity-100">
              <?php foreach ($teamLinks as $link): ?>
                <a href="<?php echo htmlspecialchars(base_url($link['url'])); ?>" class="<?php echo $linkClasses($link['url'], true); ?>"><?php echo htmlspecialchars($link['label']); ?></a>
              <?php endforeach; ?>
            </div>
          </details>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
