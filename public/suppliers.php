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
        redirect_with_message('suppliers.php', 'danger', 'Supplier name is required.');
    }

    $stmt = $mysqli->prepare('INSERT INTO suppliers (name, address, phone, email) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $name, $address, $phone, $email);
    $stmt->execute();

    redirect_with_message('suppliers.php', 'success', 'Supplier created successfully.');
}

$suppliers = $mysqli->query('SELECT * FROM suppliers ORDER BY created_at DESC')->fetch_all(MYSQLI_ASSOC);

$inputClass = 'mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm transition focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-200';
$labelClass = 'block text-sm font-semibold text-slate-700';

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

    <div class="grid gap-6 lg:grid-cols-3">
      <div class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-lg shadow-slate-200/40 backdrop-blur">
        <h2 class="text-xl font-semibold text-slate-900">Create Supplier</h2>
        <p class="mt-1 text-sm text-slate-500">Add a new partner organisation to the HabitRact network.</p>
        <form method="post" class="mt-6 space-y-4" novalidate>
          <div>
            <label class="<?php echo $labelClass; ?>" for="name">Supplier Name</label>
            <input type="text" class="<?php echo $inputClass; ?>" id="name" name="name" required>
          </div>
          <div>
            <label class="<?php echo $labelClass; ?>" for="address">Address</label>
            <textarea class="<?php echo $inputClass; ?>" id="address" name="address" rows="2"></textarea>
          </div>
          <div>
            <label class="<?php echo $labelClass; ?>" for="phone">Phone</label>
            <input type="text" class="<?php echo $inputClass; ?>" id="phone" name="phone">
          </div>
          <div>
            <label class="<?php echo $labelClass; ?>" for="email">Email</label>
            <input type="email" class="<?php echo $inputClass; ?>" id="email" name="email">
          </div>
          <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-brand-600/30 transition hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600">
            Create Supplier
          </button>
        </form>
      </div>
      <div class="lg:col-span-2">
        <div class="rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-lg shadow-slate-200/40 backdrop-blur">
          <h2 class="text-2xl font-semibold text-slate-900">Existing Suppliers</h2>
          <p class="text-sm text-slate-500">Review and coordinate the organisations that deliver to your customers.</p>
          <div class="mt-6 overflow-hidden rounded-2xl border border-slate-200">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                  <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Address</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Created</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white/50">
                  <?php foreach ($suppliers as $supplier): ?>
                    <tr class="hover:bg-slate-50/80">
                      <td class="px-4 py-3 font-medium text-slate-900"><?php echo htmlspecialchars($supplier['name']); ?></td>
                      <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($supplier['address']); ?></td>
                      <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($supplier['phone']); ?></td>
                      <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars($supplier['email']); ?></td>
                      <td class="px-4 py-3 text-slate-600"><?php echo htmlspecialchars((new DateTime($supplier['created_at']))->format('d M Y')); ?></td>
                    </tr>
                  <?php endforeach; ?>
                  <?php if (empty($suppliers)): ?>
                    <tr>
                      <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500">No suppliers added yet.</td>
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
