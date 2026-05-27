<?php
declare(strict_types=1);

session_start();

require __DIR__ . '/php/includes/auth.php';

$currentUser = library_current_user();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESAM Biblioteca | Landing</title>
    <link rel="stylesheet" href="assets/php-app.css">
</head>
<body class="app-body">
    <header class="site-header">
        <div class="shell nav-bar">
            <a href="#inicio" class="brand">
                <span class="brand__mark">E</span>
                <span>
                    <strong>ESAM Biblioteca</strong>
                    <small>Landing principal</small>
                </span>
            </a>

            <nav class="nav-links" aria-label="Principal">
                <a href="#inicio">Inicio</a>
                <a href="#modulos">Modulos</a>
                <a href="#panel">Panel</a>
                <a href="#contacto">Contacto</a>
            </nav>

            <div class="nav-actions">
                <?php if ($currentUser !== null): ?>
                    <a href="panel.php" class="button button-secondary">Ir al panel</a>
                    <a href="logout.php" class="button button-ghost">Cerrar sesion</a>
                <?php else: ?>
                    <a href="login.php" class="button button-secondary">Iniciar sesion</a>
                    <a href="login.php" class="button button-primary">Acceso demo</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        <section class="hero" id="inicio">
            <img
                class="hero__image"
                src="https://images.unsplash.com/photo-1507842217343-583bb7270b66?auto=format&amp;fit=crop&amp;w=2200&amp;q=90"
                alt="Biblioteca virtual ESAM"
            >
            <div class="hero__overlay"></div>
            <div class="shell hero__grid">
                <div class="hero__copy">
                    <span class="pill">Biblioteca virtual ESAM</span>
                    <h1>Una portada limpia y un acceso separado para el sistema.</h1>
                    <p>
                        Ahora el login vive en una vista aparte para que la landing siga siendo institucional y el
                        acceso al CMS tenga su propia entrada en <code>/login.php</code>.
                    </p>

                    <div class="hero__actions">
                        <a href="login.php" class="button button-primary">Ir al login</a>
                        <a href="panel.php" class="button button-secondary">Ver dashboard</a>
                    </div>

                    <div class="hero__stats">
                        <article class="mini-card">
                            <span>Ruta</span>
                            <strong>/login.php</strong>
                        </article>
                        <article class="mini-card">
                            <span>Panel</span>
                            <strong>Sidebar + topbar + main</strong>
                        </article>
                        <article class="mini-card">
                            <span>Estado</span>
                            <strong>Listo para crecer</strong>
                        </article>
                    </div>
                </div>

                <aside class="hero__panel">
                    <div class="hero__panel-card">
                        <span class="panel-label">Nueva estructura</span>
                        <h2>Landing y acceso ya no comparten la misma vista</h2>
                        <ul class="check-list">
                            <li>Portada enfocada en presentacion institucional.</li>
                            <li>Login separado para experiencia mas ordenada.</li>
                            <li>Dashboard protegido por sesion mock.</li>
                            <li>Base visual para modulos administrativos.</li>
                        </ul>
                    </div>

                    <div class="hero__panel-card hero__panel-card--accent">
                        <span class="panel-label">Siguiente paso</span>
                        <p class="status-copy">
                            Ya podemos seguir creando vistas reales por modulo sin mezclar el acceso con la portada.
                        </p>
                    </div>
                </aside>
            </div>
        </section>

        <section class="section" id="modulos">
            <div class="shell">
                <div class="section-heading">
                    <span class="pill">Modulos base</span>
                    <h2>El dashboard conserva una estructura de CMS pensada para biblioteca virtual.</h2>
                    <p>
                        La portada ahora solo presenta el proyecto y deriva al login o al panel segun el estado de sesion.
                    </p>
                </div>

                <div class="feature-grid">
                    <article class="feature-card">
                        <span class="feature-card__eyebrow">Catalogo</span>
                        <h3>Gestion centralizada</h3>
                        <p>Espacio para colecciones, filtros, fichas bibliograficas y estados de inventario.</p>
                    </article>
                    <article class="feature-card">
                        <span class="feature-card__eyebrow">Prestamos</span>
                        <h3>Operacion diaria</h3>
                        <p>Flujo listo para resumenes, vencimientos, devoluciones y seguimiento de reservas.</p>
                    </article>
                    <article class="feature-card">
                        <span class="feature-card__eyebrow">Usuarios</span>
                        <h3>Control de acceso</h3>
                        <p>Base util para roles, perfiles y permisos cuando conecten datos reales.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="section" id="panel">
            <div class="shell">
                <div class="cta-card">
                    <div>
                        <span class="pill">Panel principal</span>
                        <h2>Dashboard separado del landing y listo para evolucionar como CMS.</h2>
                        <p>
                            El acceso inicia desde una ruta propia y el panel mantiene su estructura de administracion con nave lateral y contenido principal.
                        </p>
                    </div>
                    <div class="cta-card__actions">
                        <a href="login.php" class="button button-primary">Abrir login</a>
                        <a href="panel.php" class="button button-ghost">Abrir dashboard</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer" id="contacto">
        <div class="shell footer-bar">
            <div>
                <strong>ESAM Biblioteca</strong>
                <p>Landing institucional con acceso independiente al sistema.</p>
            </div>
            <p>Campus ESAM · biblioteca@esam.edu.bo</p>
        </div>
    </footer>
</body>
</html>
