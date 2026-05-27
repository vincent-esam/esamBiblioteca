<?php
declare(strict_types=1);

session_start();

require __DIR__ . '/php/includes/auth.php';

library_logout_user();
session_unset();
session_destroy();

header('Location: login.php');
exit;
