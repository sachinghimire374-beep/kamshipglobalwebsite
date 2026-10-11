<?php
/**
 * Admin panel front controller.
 *
 * Everything runs through here: one place that checks authentication, one
 * place that validates CSRF, then a section file is included.
 */
declare(strict_types=1);

require dirname(__DIR__) . '/inc/bootstrap.php';
require dirname(__DIR__) . '/inc/auth.php';
require __DIR__ . '/inc/shell.php';

ks_session_start();

$page = (string) ($_GET['p'] ?? 'dashboard');

// -------------------------------------------------------------- logout ----
if ($page === 'logout') {
    auth_logout();
    redirect('index.php');
}

// ---------------------------------------------------------- first run ----
if (auth_needs_setup()) {
    require __DIR__ . '/sections/setup.php';
    exit;
}

// ----------------------------------------------------------- sign in ----
if (auth_user() === null) {
    require __DIR__ . '/sections/login.php';
    exit;
}

// Every POST past this point changes data, so it must carry a valid token.
csrf_require();

$valid = array_column(ADMIN_SECTIONS, 0);
if (!in_array($page, $valid, true)) {
    $page = 'dashboard';
}

// $page comes from a fixed whitelist, never straight into the path.
$file = __DIR__ . '/sections/' . $page . '.php';
if (!is_file($file)) {
    $file = __DIR__ . '/sections/dashboard.php';
    $page = 'dashboard';
}
require $file;
