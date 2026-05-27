<?php
declare(strict_types=1);

session_start();

require __DIR__ . '/php/includes/auth.php';

$currentUser = library_current_user();

if ($currentUser === null) {
    library_set_flash('warning', 'Primero inicia sesion con una cuenta demo para entrar al panel.');
    library_redirect('login.php');
}

$displayName = library_format_name($currentUser);
$currentView = (string) ($_GET['view'] ?? 'overview');
$views = [
    'overview' => 'Resumen',
    'catalogo' => 'Catalogo',
    'prestamos' => 'Prestamos',
    'usuarios' => 'Usuarios',
    'reportes' => 'Reportes',
    'configuracion' => 'Configuracion',
];

if (!isset($views[$currentView])) {
    $currentView = 'overview';
}

$stats = [
    ['label' => 'Libros activos', 'value' => '1,248', 'trend' => '+12 este mes'],
    ['label' => 'Prestamos vigentes', 'value' => '86', 'trend' => '14 vencen hoy'],
    ['label' => 'Usuarios nuevos', 'value' => '34', 'trend' => '+8% semanal'],
    ['label' => 'Reservas pendientes', 'value' => '19', 'trend' => '5 prioritarias'],
];

$recentBooks = [
    ['title' => 'Metodologia de la investigacion', 'code' => 'LB-104', 'status' => 'Disponible'],
    ['title' => 'Administracion financiera', 'code' => 'LB-219', 'status' => 'Prestado'],
    ['title' => 'Marketing estrategico', 'code' => 'LB-342', 'status' => 'Revision'],
    ['title' => 'Derecho empresarial', 'code' => 'LB-411', 'status' => 'Disponible'],
];

$activity = [
    ['time' => '09:15', 'title' => 'Nuevo prestamo registrado', 'meta' => 'Ana Perez retiro "Contabilidad I".'],
    ['time' => '10:40', 'title' => 'Reserva aprobada', 'meta' => 'Se habilito una reserva para sala de lectura.'],
    ['time' => '11:25', 'title' => 'Catalogo actualizado', 'meta' => 'Se agregaron 12 ejemplares academicos.'],
];

$alerts = [
    ['type' => 'warning', 'title' => 'Prestamos por vencer', 'copy' => '14 prestamos deben notificarse antes de las 18:00.'],
    ['type' => 'success', 'title' => 'Indice de devolucion', 'copy' => 'La tasa de devolucion subio al 92% esta semana.'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel | ESAM Biblioteca</title>
    <link rel="stylesheet" href="assets/php-app.css">
</head>
<body class="app-body app-body--dashboard">
    <main class="dashboard-shell">
        <aside class="dashboard-sidebar">
            <div class="dashboard-sidebar__brand">
                <a href="index.php" class="brand">
                    <span class="brand__mark">E</span>
                    <span>
                        <strong>ESAM Biblioteca</strong>
                        <small>Panel CMS</small>
                    </span>
                </a>
            </div>

            <div class="dashboard-user">
                <div class="dashboard-user__avatar">
                    <?= library_escape(strtoupper(substr($currentUser['first_name'] ?: 'U', 0, 1))) ?>
                </div>
                <div>
                    <strong><?= library_escape($displayName !== '' ? $displayName : $currentUser['email']) ?></strong>
                    <span><?= library_escape((string) ($currentUser['role'] ?? 'Usuario')) ?></span>
                </div>
            </div>

            <nav class="dashboard-nav" aria-label="Panel principal">
                <?php foreach ($views as $viewKey => $viewLabel): ?>
                    <a href="panel.php?view=<?= library_escape($viewKey) ?>" class="dashboard-nav__link <?= $currentView === $viewKey ? 'is-active' : '' ?>">
                        <span><?= library_escape($viewLabel) ?></span>
                        <small><?= $currentView === $viewKey ? 'Actual' : 'Modulo' ?></small>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="dashboard-sidebar__footer">
                <div class="dashboard-help-card">
                    <span class="panel-label">Modo demo</span>
                    <p>La navegacion ya esta lista para conectar datos reales sin cambiar la interfaz principal.</p>
                </div>
                <a href="logout.php" class="button button-primary button-block">Cerrar sesion</a>
            </div>
        </aside>

        <section class="dashboard-main">
            <header class="dashboard-topbar">
                <div>
                    <span class="pill">Vista <?= library_escape($views[$currentView]) ?></span>
                    <h1><?= library_escape($views[$currentView]) ?> de biblioteca virtual</h1>
                    <p>Panel base con estructura de sidebar, topbar y contenido principal editable por modulo.</p>
                </div>

                <div class="dashboard-topbar__actions">
                    <label class="dashboard-search">
                        <span>Buscar</span>
                        <input type="search" placeholder="Libros, usuarios, prestamos...">
                    </label>
                    <div class="dashboard-icon-group">
                        <span class="dashboard-icon-chip">3</span>
                        <span class="dashboard-icon-chip">7</span>
                    </div>
                </div>
            </header>

            <section class="dashboard-overview">
                <div class="dashboard-kpis">
                    <?php foreach ($stats as $stat): ?>
                        <article class="dashboard-card dashboard-card--stat">
                            <span class="panel-label"><?= library_escape($stat['label']) ?></span>
                            <strong><?= library_escape($stat['value']) ?></strong>
                            <p><?= library_escape($stat['trend']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="dashboard-main-grid">
                    <article class="dashboard-card dashboard-card--hero">
                        <div class="dashboard-card__header">
                            <div>
                                <span class="panel-label">Centro operativo</span>
                                <h2>Espacio principal del modulo actual</h2>
                            </div>
                            <a href="index.php" class="button button-ghost">Volver al inicio</a>
                        </div>

                        <p>
                            Este bloque puede cambiar segun el tab activo. Por ahora sirve como maqueta para resumen,
                            catalogo, prestamos, usuarios, reportes y configuracion del sistema.
                        </p>

                        <div class="dashboard-shortcuts">
                            <div class="shortcut-card">
                                <strong>Nuevo libro</strong>
                                <span>Registrar ejemplar</span>
                            </div>
                            <div class="shortcut-card">
                                <strong>Nuevo prestamo</strong>
                                <span>Salida rapida</span>
                            </div>
                            <div class="shortcut-card">
                                <strong>Nuevo usuario</strong>
                                <span>Alta institucional</span>
                            </div>
                        </div>
                    </article>

                    <article class="dashboard-card">
                        <div class="dashboard-card__header">
                            <div>
                                <span class="panel-label">Alertas</span>
                                <h2>Seguimiento diario</h2>
                            </div>
                        </div>

                        <div class="dashboard-alerts">
                            <?php foreach ($alerts as $alert): ?>
                                <div class="dashboard-alert dashboard-alert--<?= library_escape($alert['type']) ?>">
                                    <strong><?= library_escape($alert['title']) ?></strong>
                                    <p><?= library_escape($alert['copy']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </article>

                    <article class="dashboard-card dashboard-card--table">
                        <div class="dashboard-card__header">
                            <div>
                                <span class="panel-label">Catalogo reciente</span>
                                <h2>Libros destacados</h2>
                            </div>
                        </div>

                        <div class="dashboard-table">
                            <div class="dashboard-table__row dashboard-table__row--head">
                                <span>Titulo</span>
                                <span>Codigo</span>
                                <span>Estado</span>
                            </div>
                            <?php foreach ($recentBooks as $book): ?>
                                <div class="dashboard-table__row">
                                    <span><?= library_escape($book['title']) ?></span>
                                    <span><?= library_escape($book['code']) ?></span>
                                    <span class="status-pill"><?= library_escape($book['status']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </article>

                    <article class="dashboard-card">
                        <div class="dashboard-card__header">
                            <div>
                                <span class="panel-label">Actividad</span>
                                <h2>Ultimos movimientos</h2>
                            </div>
                        </div>

                        <div class="timeline-list">
                            <?php foreach ($activity as $item): ?>
                                <article class="timeline-item">
                                    <span class="timeline-item__time"><?= library_escape($item['time']) ?></span>
                                    <div>
                                        <strong><?= library_escape($item['title']) ?></strong>
                                        <p><?= library_escape($item['meta']) ?></p>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </article>
                </div>
            </section>
        </section>
    </main>
</body>
</html>
