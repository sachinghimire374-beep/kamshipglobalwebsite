<?php
/**
 * CSRF protection. Every state-changing POST in the admin panel and every
 * public form carries a token tied to the visitor's session.
 */
declare(strict_types=1);

if (!defined('KAMSHIP')) {
    http_response_code(403);
    exit('Forbidden');
}

function csrf_token(): string
{
    ks_session_start();
    if (empty($_SESSION['csrf']) || !is_string($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    ks_session_start();
    $sent = $_POST['_token'] ?? '';
    $have = $_SESSION['csrf'] ?? '';
    return is_string($sent)
        && is_string($have)
        && $have !== ''
        && hash_equals($have, $sent);
}

/** Abort the request unless this POST carries a valid token. */
function csrf_require(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    if (!csrf_valid()) {
        http_response_code(419);
        header('Content-Type: text/html; charset=utf-8');
        exit('<!doctype html><meta charset="utf-8"><title>Session expired</title>'
            . '<p style="font:16px system-ui;padding:40px">Your session expired before that '
            . 'was submitted. Go back, reload the page and try again.</p>');
    }
}
