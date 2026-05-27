<?php
declare(strict_types=1);

const LIBRARY_USER_SESSION_KEY = 'library_user';
const LIBRARY_FLASH_KEY = 'library_flash';
const LIBRARY_USERS_FILE = __DIR__ . '/../../storage/users.json';

function library_mock_users(): array
{
    return [
        [
            'first_name' => 'Ana',
            'last_name' => 'Martinez',
            'email' => 'admin@esam.edu.bo',
            'password_hash' => password_hash('Admin123', PASSWORD_DEFAULT),
            'role' => 'Administrador',
            'area' => 'Direccion de biblioteca',
            'accepted_terms' => true,
            'created_at' => '2026-01-15T10:00:00+00:00',
        ],
        [
            'first_name' => 'Luis',
            'last_name' => 'Rojas',
            'email' => 'biblioteca@esam.edu.bo',
            'password_hash' => password_hash('Biblio123', PASSWORD_DEFAULT),
            'role' => 'Bibliotecario',
            'area' => 'Catalogo y prestamos',
            'accepted_terms' => true,
            'created_at' => '2026-02-03T14:30:00+00:00',
        ],
    ];
}

function library_ensure_users_file(): void
{
    $directory = dirname(LIBRARY_USERS_FILE);

    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    if (!file_exists(LIBRARY_USERS_FILE)) {
        file_put_contents(
            LIBRARY_USERS_FILE,
            json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );
    }
}

function library_read_users(): array
{
    library_ensure_users_file();

    $contents = file_get_contents(LIBRARY_USERS_FILE);

    if ($contents === false || trim($contents) === '') {
        return [];
    }

    $decoded = json_decode($contents, true);

    return is_array($decoded) ? $decoded : [];
}

function library_write_users(array $users): void
{
    library_ensure_users_file();

    file_put_contents(
        LIBRARY_USERS_FILE,
        json_encode(array_values($users), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX
    );
}

function library_escape(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function library_normalize_email(string $email): string
{
    return strtolower(trim($email));
}

function library_registration_defaults(): array
{
    return [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'password' => '',
        'terms' => false,
    ];
}

function library_login_defaults(): array
{
    return [
        'email' => '',
        'password' => '',
    ];
}

function library_password_status(string $password): array
{
    $checks = [
        'length' => strlen($password) >= 8,
        'uppercase' => preg_match('/[A-Z]/', $password) === 1,
        'number' => preg_match('/\d/', $password) === 1,
        'lowercase' => preg_match('/[a-z]/', $password) === 1,
        'symbol' => preg_match('/[^A-Za-z0-9]/', $password) === 1,
    ];

    $score = 0;

    foreach ($checks as $check) {
        if ($check) {
            $score++;
        }
    }

    if ($score <= 2) {
        $label = 'Baja';
        $tone = 'danger';
    } elseif ($score === 3) {
        $label = 'Media';
        $tone = 'warning';
    } elseif ($score === 4) {
        $label = 'Alta';
        $tone = 'success';
    } else {
        $label = 'Muy alta';
        $tone = 'success';
    }

    return [
        'checks' => $checks,
        'score' => $score,
        'label' => $label,
        'tone' => $tone,
        'percent' => (int) round(($score / 5) * 100),
    ];
}

function library_find_user_by_email(array $users, string $email): ?array
{
    foreach ($users as $user) {
        if (($user['email'] ?? '') === $email) {
            return $user;
        }
    }

    return null;
}

function library_validate_registration(array $source, array $users): array
{
    $data = [
        'first_name' => trim((string) ($source['first_name'] ?? '')),
        'last_name' => trim((string) ($source['last_name'] ?? '')),
        'email' => library_normalize_email((string) ($source['email'] ?? '')),
        'password' => (string) ($source['password'] ?? ''),
        'terms' => isset($source['terms']) && $source['terms'] === '1',
    ];

    $errors = [];

    if ($data['first_name'] === '') {
        $errors['first_name'] = 'Los nombres son obligatorios.';
    }

    if ($data['last_name'] === '') {
        $errors['last_name'] = 'Los apellidos son obligatorios.';
    }

    if ($data['email'] === '') {
        $errors['email'] = 'El correo electrónico es obligatorio.';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Ingresa un correo electrónico válido.';
    } elseif (library_find_user_by_email($users, $data['email']) !== null) {
        $errors['email'] = 'Ese correo ya está registrado.';
    }

    if ($data['password'] === '') {
        $errors['password'] = 'La contraseña es obligatoria.';
    }

    $passwordStatus = library_password_status($data['password']);

    if (
        $data['password'] !== '' &&
        (
            !$passwordStatus['checks']['length'] ||
            !$passwordStatus['checks']['uppercase'] ||
            !$passwordStatus['checks']['number']
        )
    ) {
        $errors['password'] = 'La contraseña debe tener mínimo 8 caracteres, 1 mayúscula y 1 número.';
    }

    if (!$data['terms']) {
        $errors['terms'] = 'Debes aceptar los términos y condiciones.';
    }

    return [$data, $errors, $passwordStatus];
}

function library_validate_login(array $source, ?array $users = null): array
{
    $data = [
        'email' => library_normalize_email((string) ($source['email'] ?? '')),
        'password' => (string) ($source['password'] ?? ''),
    ];

    $errors = [];
    $user = null;

    if ($data['email'] === '') {
        $errors['email'] = 'El correo electrónico es obligatorio.';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Ingresa un correo electrónico válido.';
    }

    if ($data['password'] === '') {
        $errors['password'] = 'La contraseña es obligatoria.';
    }

    if ($errors === []) {
        $availableUsers = $users ?? library_mock_users();
        $user = library_find_user_by_email($availableUsers, $data['email']);

        if (
            $user === null ||
            !password_verify($data['password'], (string) ($user['password_hash'] ?? ''))
        ) {
            $errors['form'] = 'Credenciales no validas. Prueba con una de las cuentas demo del panel.';
        }
    }

    return [$data, $errors, $user];
}

function library_register_user(array $users, array $data): array
{
    $user = [
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        'email' => $data['email'],
        'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
        'accepted_terms' => true,
        'created_at' => gmdate('c'),
    ];

    $users[] = $user;

    library_write_users($users);

    return $user;
}

function library_registration_ready(array $data): bool
{
    $passwordStatus = library_password_status((string) ($data['password'] ?? ''));

    return
        trim((string) ($data['first_name'] ?? '')) !== '' &&
        trim((string) ($data['last_name'] ?? '')) !== '' &&
        filter_var((string) ($data['email'] ?? ''), FILTER_VALIDATE_EMAIL) !== false &&
        $passwordStatus['checks']['length'] &&
        $passwordStatus['checks']['uppercase'] &&
        $passwordStatus['checks']['number'] &&
        (($data['terms'] ?? false) === true);
}

function library_login_ready(array $data): bool
{
    return
        filter_var((string) ($data['email'] ?? ''), FILTER_VALIDATE_EMAIL) !== false &&
        trim((string) ($data['password'] ?? '')) !== '';
}

function library_is_authenticated(): bool
{
    return isset($_SESSION[LIBRARY_USER_SESSION_KEY]) && is_array($_SESSION[LIBRARY_USER_SESSION_KEY]);
}

function library_current_user(): ?array
{
    return library_is_authenticated() ? $_SESSION[LIBRARY_USER_SESSION_KEY] : null;
}

function library_login_user(array $user): void
{
    session_regenerate_id(true);

    $_SESSION[LIBRARY_USER_SESSION_KEY] = [
        'first_name' => (string) ($user['first_name'] ?? ''),
        'last_name' => (string) ($user['last_name'] ?? ''),
        'email' => (string) ($user['email'] ?? ''),
        'role' => (string) ($user['role'] ?? 'Usuario'),
        'area' => (string) ($user['area'] ?? 'Biblioteca virtual'),
        'created_at' => (string) ($user['created_at'] ?? ''),
    ];
}

function library_logout_user(): void
{
    unset($_SESSION[LIBRARY_USER_SESSION_KEY], $_SESSION[LIBRARY_FLASH_KEY]);
}

function library_set_flash(string $type, string $message): void
{
    $_SESSION[LIBRARY_FLASH_KEY] = [
        'type' => $type,
        'message' => $message,
    ];
}

function library_pull_flash(): ?array
{
    if (!isset($_SESSION[LIBRARY_FLASH_KEY]) || !is_array($_SESSION[LIBRARY_FLASH_KEY])) {
        return null;
    }

    $flash = $_SESSION[LIBRARY_FLASH_KEY];
    unset($_SESSION[LIBRARY_FLASH_KEY]);

    return $flash;
}

function library_format_name(array $user): string
{
    return trim(((string) ($user['first_name'] ?? '')) . ' ' . ((string) ($user['last_name'] ?? '')));
}

function library_redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}
