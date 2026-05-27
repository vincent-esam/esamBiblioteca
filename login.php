<?php
declare(strict_types=1);

session_start();

require __DIR__ . '/php/includes/auth.php';

$loginValues = library_login_defaults();
$loginErrors = [];
$flash = library_pull_flash();
$currentUser = library_current_user();
$mockUsers = library_mock_users();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && (string) ($_POST['action'] ?? '') === 'login') {
    [$loginValues, $loginErrors, $user] = library_validate_login($_POST);

    if ($loginErrors === [] && $user !== null) {
        library_login_user($user);
        library_redirect('panel.php');
    }

    $loginValues['password'] = '';
}

$loginReady = library_login_ready($loginValues);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ESAM Biblioteca</title>
    <link rel="stylesheet" href="assets/php-app.css">
</head>
<body class="app-body">
    <header class="site-header">
        <div class="shell nav-bar">
            <a href="index.php" class="brand">
                <span class="brand__mark">E</span>
                <span>
                    <strong>ESAM Biblioteca</strong>
                    <small>Acceso mock separado</small>
                </span>
            </a>

            <div class="nav-actions">
                <a href="index.php" class="button button-ghost">Volver al inicio</a>
                <?php if ($currentUser !== null): ?>
                    <a href="panel.php" class="button button-secondary">Ir al panel</a>
                    <a href="logout.php" class="button button-primary">Cerrar sesion</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        <section class="section section-auth section-auth--page">
            <div class="shell">
                <div class="section-heading">
                    <span class="pill">Acceso mock</span>
                    <h1 class="login-page-title">Ingresa desde una vista aparte del landing.</h1>
                    <p>
                        Esta pantalla vive por separado en <code>/login.php</code> para que el acceso no comparta
                        espacio con la portada principal del proyecto.
                    </p>
                </div>

                <?php if ($flash !== null): ?>
                    <div class="alert alert--<?= library_escape((string) ($flash['type'] ?? 'success')) ?>">
                        <?= library_escape((string) ($flash['message'] ?? '')) ?>
                    </div>
                <?php endif; ?>

                <div class="auth-layout auth-layout--mock">
                    <article class="auth-card is-active">
                        <div class="auth-card__header">
                            <div>
                                <span class="panel-label">Inicio de sesion</span>
                                <h3>Acceder al panel</h3>
                            </div>
                            <span class="status-badge status-badge--dark">Modo mock</span>
                        </div>

                        <?php if (isset($loginErrors['form'])): ?>
                            <div class="form-alert">
                                <?= library_escape($loginErrors['form']) ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" novalidate data-auth-form="login">
                            <input type="hidden" name="action" value="login">

                            <label class="field <?= isset($loginErrors['email']) ? 'is-invalid' : '' ?>">
                                <span>Correo electronico</span>
                                <input
                                    type="email"
                                    name="email"
                                    value="<?= library_escape($loginValues['email']) ?>"
                                    placeholder="admin@esam.edu.bo"
                                    data-required
                                >
                                <small class="field-error" data-error-for="email"><?= library_escape($loginErrors['email'] ?? '') ?></small>
                            </label>

                            <label class="field <?= isset($loginErrors['password']) ? 'is-invalid' : '' ?>">
                                <span>Contrasena</span>
                                <input
                                    type="password"
                                    name="password"
                                    placeholder="Ingresa tu contrasena"
                                    data-required
                                >
                                <small class="field-error" data-error-for="password"><?= library_escape($loginErrors['password'] ?? '') ?></small>
                            </label>

                            <div class="login-note">
                                Usa una de las cuentas demo para abrir el dashboard sin conectar base de datos.
                            </div>

                            <button
                                type="submit"
                                class="button button-primary button-block"
                                data-submit-button
                                <?= $loginReady ? '' : 'disabled' ?>
                            >
                                Ingresar al panel
                            </button>
                        </form>
                    </article>

                    <article class="auth-card">
                        <div class="auth-card__header">
                            <div>
                                <span class="panel-label">Cuentas demo</span>
                                <h3>Credenciales de prueba</h3>
                            </div>
                            <span class="status-badge">Sin base de datos</span>
                        </div>

                        <div class="demo-credentials">
                            <?php foreach ($mockUsers as $mockUser): ?>
                                <article class="demo-user-card">
                                    <strong><?= library_escape(library_format_name($mockUser)) ?></strong>
                                    <span><?= library_escape((string) $mockUser['role']) ?></span>
                                    <p><?= library_escape((string) $mockUser['email']) ?></p>
                                    <code><?= library_escape($mockUser['email'] === 'admin@esam.edu.bo' ? 'Admin123' : 'Biblio123') ?></code>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </main>

    <script src="assets/php-auth.js"></script>
</body>
</html>
