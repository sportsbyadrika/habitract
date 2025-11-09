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
        header('Location: ' . base_url('dashboard.php'));
        exit;
    }

    $error = $result['message'];
    $lockoutSeconds = get_login_lockout_seconds();
}

$isLocked = $lockoutSeconds > 0;
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<main class="flex flex-1 items-center justify-center px-4 py-16">
  <div class="w-full max-w-md">
    <div class="rounded-3xl border border-slate-200 bg-white/80 p-8 shadow-2xl backdrop-blur">
      <div class="text-center">
        <img src="<?php echo htmlspecialchars(base_url('assets/logo.svg')); ?>" alt="HabitRact Supply" class="mx-auto h-12 w-auto">
        <h1 class="mt-6 text-3xl font-semibold text-slate-900">Welcome Back</h1>
        <p class="mt-2 text-sm text-slate-500">Sign in to manage your supply routes.</p>
      </div>
      <?php if ($error): ?>
        <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>
      <form method="post" class="mt-6 space-y-4" novalidate>
        <div>
          <label class="block text-sm font-semibold text-slate-700" for="username">Username or Email</label>
          <input type="text" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400" id="username" name="username" required <?php echo $isLocked ? 'disabled' : ''; ?>>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700" for="password">Password</label>
          <input type="password" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-slate-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400" id="password" name="password" required <?php echo $isLocked ? 'disabled' : ''; ?>>
        </div>
        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-brand-600/30 transition hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 disabled:cursor-not-allowed disabled:bg-slate-300" <?php echo $isLocked ? 'disabled' : ''; ?>>
          Sign In
        </button>
      </form>
      <?php if ($isLocked): ?>
        <p class="mt-6 text-center text-sm text-slate-500">Too many login attempts. Please wait <?php echo ceil($lockoutSeconds / 60); ?> minute(s) before trying again.</p>
      <?php else: ?>
        <p class="mt-6 text-center text-sm text-slate-500">You can sign in using either your username or email address.</p>
      <?php endif; ?>
    </div>
  </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
