<?php
/**
 * Admin authentication.
 *
 * Passwords are stored as bcrypt/argon hashes, never in plain text. Failed
 * logins are rate-limited per username and per IP so the panel cannot be
 * brute-forced from the open internet.
 */
declare(strict_types=1);

if (!defined('KAMSHIP')) {
    http_response_code(403);
    exit('Forbidden');
}

const AUTH_MAX_ATTEMPTS  = 6;      // failures before a lockout
const AUTH_LOCKOUT_SECS  = 900;    // 15 minutes
const AUTH_IDLE_SECS     = 7200;   // sign out after 2 hours idle
const AUTH_ABSOLUTE_SECS = 43200;  // and after 12 hours regardless

function users_all(): array
{
    return store_read('users', []);
}

function user_find(string $username): ?array
{
    foreach (users_all() as $u) {
        if (isset($u['username']) && hash_equals((string) $u['username'], $username)) {
            return $u;
        }
    }
    return null;
}

function user_save(array $user): bool
{
    $users = users_all();
    $found = false;
    foreach ($users as $i => $u) {
        if (($u['username'] ?? '') === ($user['username'] ?? '')) {
            $users[$i] = $user;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $users[] = $user;
    }
    return store_write('users', $users);
}

/** True when no admin account exists yet — triggers first-run setup. */
function auth_needs_setup(): bool
{
    return users_all() === [];
}

// ------------------------------------------------------- rate limiting ----
function auth_log_read(): array
{
    return store_read('loginlog', []);
}

function auth_locked(string $username): int
{
    $log = auth_log_read();
    $key = 'u:' . strtolower($username);
    $ipk = 'i:' . client_ip();
    $now = time();
    $until = 0;
    foreach ([$key, $ipk] as $k) {
        $rec = $log[$k] ?? null;
        if (is_array($rec)
            && ($rec['count'] ?? 0) >= AUTH_MAX_ATTEMPTS
            && ($rec['last'] ?? 0) + AUTH_LOCKOUT_SECS > $now) {
            $until = max($until, (int) $rec['last'] + AUTH_LOCKOUT_SECS);
        }
    }
    return $until > $now ? $until - $now : 0;
}

function auth_record_failure(string $username): void
{
    $log = auth_log_read();
    $now = time();

    // Drop entries that have aged out, so the file stays small.
    foreach ($log as $k => $rec) {
        if (!is_array($rec) || ($rec['last'] ?? 0) + AUTH_LOCKOUT_SECS * 4 < $now) {
            unset($log[$k]);
        }
    }
    foreach (['u:' . strtolower($username), 'i:' . client_ip()] as $k) {
        $rec = $log[$k] ?? ['count' => 0, 'last' => 0];
        // Reset the counter if the last failure was long ago.
        if (($rec['last'] ?? 0) + AUTH_LOCKOUT_SECS < $now) {
            $rec['count'] = 0;
        }
        $rec['count'] = (int) $rec['count'] + 1;
        $rec['last'] = $now;
        $log[$k] = $rec;
    }
    store_write('loginlog', $log);
}

function auth_clear_failures(string $username): void
{
    $log = auth_log_read();
    unset($log['u:' . strtolower($username)], $log['i:' . client_ip()]);
    store_write('loginlog', $log);
}

// -------------------------------------------------------------- login ----
/** @return array{0:bool,1:string} [ok, message] */
function auth_login(string $username, string $password): array
{
    ks_session_start();

    $wait = auth_locked($username);
    if ($wait > 0) {
        return [false, 'Too many failed attempts. Try again in ' . ceil($wait / 60) . ' minutes.'];
    }

    $user = user_find($username);

    // Always run a hash comparison so a wrong username and a wrong password
    // take the same amount of time.
    $hash = $user['password'] ?? '$2y$12$usercdoesnotexistusercdoesnotexistusercdoesnotexistuserc';
    $ok = password_verify($password, $hash) && $user !== null;

    if (!$ok) {
        auth_record_failure($username);
        return [false, 'That username and password do not match.'];
    }

    // Rehash if PHP's default cost has moved on since the password was set.
    if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
        $user['password'] = password_hash($password, PASSWORD_DEFAULT);
        user_save($user);
    }

    auth_clear_failures($username);

    // New session ID on privilege change, to prevent session fixation.
    session_regenerate_id(true);
    $_SESSION['user']     = $user['username'];
    $_SESSION['name']     = $user['name'] ?? $user['username'];
    $_SESSION['login_at'] = time();
    $_SESSION['seen_at']  = time();
    $_SESSION['fp']       = auth_fingerprint();

    return [true, ''];
}

function auth_fingerprint(): string
{
    return hash('sha256', ($_SERVER['HTTP_USER_AGENT'] ?? '') . '|' . ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
}

function auth_logout(): void
{
    ks_session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function auth_user(): ?array
{
    ks_session_start();
    if (empty($_SESSION['user'])) {
        return null;
    }
    $now = time();
    if (($_SESSION['seen_at'] ?? 0) + AUTH_IDLE_SECS < $now
        || ($_SESSION['login_at'] ?? 0) + AUTH_ABSOLUTE_SECS < $now
        || !hash_equals((string) ($_SESSION['fp'] ?? ''), auth_fingerprint())) {
        auth_logout();
        return null;
    }
    $_SESSION['seen_at'] = $now;
    return ['username' => $_SESSION['user'], 'name' => $_SESSION['name'] ?? $_SESSION['user']];
}

/** Guard for every admin page. */
function auth_require(): array
{
    $u = auth_user();
    if ($u === null) {
        $to = $_SERVER['REQUEST_URI'] ?? '/admin/';
        redirect('index.php?next=' . urlencode($to));
    }
    return $u;
}

// ------------------------------------------------------ password rules ----
/** @return string '' when acceptable, otherwise the reason. */
function password_problem(string $pw): string
{
    if (mb_strlen($pw) < 12) {
        return 'Use at least 12 characters. A short phrase you can remember works well.';
    }
    if (mb_strlen($pw) > 200) {
        return 'That is longer than 200 characters.';
    }
    if (preg_match('/^(.)\1+$/u', $pw)) {
        return 'That is the same character repeated. Use something less guessable.';
    }
    $common = ['password', 'kamship', '123456', 'qwerty', 'admin', 'letmein', 'welcome'];
    $low = strtolower($pw);
    foreach ($common as $bad) {
        if ($low === $bad || $low === $bad . '123' || $low === $bad . '1234') {
            return 'That password is too easy to guess.';
        }
    }
    return '';
}
