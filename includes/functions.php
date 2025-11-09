<?php
function app_config(): array
{
    static $config;

    if ($config === null) {
        $config = require __DIR__ . '/../config/config.php';
    }

    return $config;
}

function normalize_base_path(string $base): string
{
    $base = trim($base);

    if ($base === '' || $base === '/') {
        return '';
    }

    if ($base[0] !== '/') {
        $base = '/' . $base;
    }

    return rtrim($base, '/');
}

function base_url(string $path = ''): string
{
    $config = app_config();
    $base = normalize_base_path((string) ($config['base_url'] ?? ''));

    $path = trim($path);
    if ($path === '') {
        return $base === '' ? '/' : $base;
    }

    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }

    $path = '/' . ltrim($path, '/');

    return ($base === '' ? '' : $base) . $path;
}

function start_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function regenerate_session(): void
{
    start_session();
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

function current_user(): ?array
{
    start_session();
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: ' . base_url('index.php'));
        exit;
    }
}

function require_role(array $roles): void
{
    $user = current_user();
    if (!$user || !in_array($user['role'], $roles, true)) {
        header('HTTP/1.1 403 Forbidden');
        echo '<h1 class="text-center mt-5">403 - Access denied</h1>';
        exit;
    }
}

function has_role(string $role): bool
{
    $user = current_user();
    return $user && $user['role'] === $role;
}

function can_manage_role(string $role): bool
{
    $user = current_user();
    if (!$user) {
        return false;
    }

    return match ($user['role']) {
        'super_admin' => in_array($role, ['super_admin', 'supplier_admin', 'supplier_staff', 'supplier_driver'], true),
        'supplier_admin' => in_array($role, ['supplier_staff', 'supplier_driver'], true),
        default => false,
    };
}

function redirect_with_message(string $path, string $type, string $message): void
{
    start_session();
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    header('Location: ' . base_url($path));
    exit;
}

function get_flash_messages(): array
{
    start_session();
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function fetch_user_by_identifier(\mysqli $mysqli, string $identifier): ?array
{
    $normalizedInput = trim($identifier);
    if ($normalizedInput === '') {
        return null;
    }

    $normalized = function_exists('mb_strtolower')
        ? mb_strtolower($normalizedInput, 'UTF-8')
        : strtolower($normalizedInput);

    $sql = 'SELECT u.id, u.supplier_id, u.name, u.mobile, u.address, u.email, u.username, u.password_hash, u.role, u.created_at, s.name AS supplier_name '
        . 'FROM users u '
        . 'LEFT JOIN suppliers s ON s.id = u.supplier_id '
        . 'WHERE LOWER(u.username) = ? OR (u.email IS NOT NULL AND LOWER(u.email) = ?) '
        . 'LIMIT 1';

    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('ss', $normalized, $normalized);
    $stmt->execute();

    $user = null;
    if (method_exists($stmt, 'get_result')) {
        $result = $stmt->get_result();
        $user = $result->fetch_assoc() ?: null;
    } else {
        $stmt->bind_result(
            $id,
            $supplierId,
            $name,
            $mobile,
            $address,
            $email,
            $username,
            $passwordHash,
            $role,
            $createdAt,
            $supplierName
        );

        if ($stmt->fetch()) {
            $user = [
                'id' => $id,
                'supplier_id' => $supplierId,
                'name' => $name,
                'mobile' => $mobile,
                'address' => $address,
                'email' => $email,
                'username' => $username,
                'password_hash' => $passwordHash,
                'role' => $role,
                'created_at' => $createdAt,
                'supplier_name' => $supplierName,
            ];
        }
    }

    $stmt->close();

    return $user;
}

function clear_login_attempts(): void
{
    start_session();
    unset($_SESSION['login_attempts']);
}

function record_failed_login_attempt(): void
{
    start_session();

    $state = $_SESSION['login_attempts'] ?? ['count' => 0, 'lock_until' => 0];

    $now = time();
    if (($state['lock_until'] ?? 0) > $now) {
        $_SESSION['login_attempts'] = $state;
        return;
    }

    $state['lock_until'] = 0;
    $state['count'] = ($state['count'] ?? 0) + 1;

    if ($state['count'] >= 5) {
        $state['lock_until'] = $now + 300;
        $state['count'] = 0;
    }

    $_SESSION['login_attempts'] = $state;
}

function get_login_lockout_seconds(): int
{
    start_session();

    $state = $_SESSION['login_attempts'] ?? null;
    if (!$state) {
        return 0;
    }

    $lockUntil = (int) ($state['lock_until'] ?? 0);
    if ($lockUntil <= 0) {
        return 0;
    }

    $remaining = $lockUntil - time();

    if ($remaining <= 0) {
        unset($_SESSION['login_attempts']);
        return 0;
    }

    return $remaining;
}

function attempt_login(\mysqli $mysqli, string $identifier, string $password): array
{
    $identifier = trim($identifier);
    $password = (string) $password;

    if ($identifier === '' || $password === '') {
        return [
            'success' => false,
            'message' => 'Please provide both a username or email and password.',
        ];
    }

    $lockoutSeconds = get_login_lockout_seconds();
    if ($lockoutSeconds > 0) {
        $minutes = max(1, (int) ceil($lockoutSeconds / 60));
        return [
            'success' => false,
            'message' => "Too many failed login attempts. Please try again in {$minutes} minute(s).",
        ];
    }

    $user = fetch_user_by_identifier($mysqli, $identifier);

    if (!$user || !password_verify($password, $user['password_hash'] ?? '')) {
        record_failed_login_attempt();
        $lockoutSeconds = get_login_lockout_seconds();

        if ($lockoutSeconds > 0) {
            $minutes = max(1, (int) ceil($lockoutSeconds / 60));

            return [
                'success' => false,
                'message' => "Too many failed login attempts. Please try again in {$minutes} minute(s).",
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid credentials. Please check your details and try again.',
        ];
    }

    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $mysqli->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $updateStmt->bind_param('si', $newHash, $user['id']);
        $updateStmt->execute();
        $updateStmt->close();
        $user['password_hash'] = $newHash;
    }

    clear_login_attempts();
    regenerate_session();

    unset($user['password_hash']);

    start_session();
    $_SESSION['user'] = $user;

    return [
        'success' => true,
        'user' => $user,
    ];
}
