<?php
function start_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function is_logged_in(): bool
{
    start_session();
    return isset($_SESSION['user']);
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: index.php');
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
    header('Location: ' . $path);
    exit;
}

function get_flash_messages(): array
{
    start_session();
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}
