<?php
/** Sign-in screen. */
declare(strict_types=1);
if (!defined('KAMSHIP')) { http_response_code(403); exit('Forbidden'); }

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    $username = mb_substr(trim((string) ($_POST['username'] ?? '')), 0, 60);
    $password = (string) ($_POST['password'] ?? '');
    [$ok, $msg] = auth_login($username, $password);
    if ($ok) {
        $next = (string) ($_GET['next'] ?? '');
        // Only ever redirect inside this site.
        if ($next !== '' && str_starts_with($next, '/') && !str_starts_with($next, '//')) {
            redirect($next);
        }
        redirect('index.php?p=dashboard');
    }
    $error = $msg;
}

admin_head('Sign in');
?>
<div class="alogin">
  <div class="alogin-card">
    <img src="../assets/img/kamship-icon-tile-192.png" alt="" width="52" height="52" class="alogo">
    <h1>Kamship admin</h1>
    <p class="asub">Sign in to edit the website.</p>

    <?php if ($error !== ''): ?>
      <div class="anote anote-bad"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="post" action="index.php<?= isset($_GET['next']) ? '?next=' . e(urlencode((string) $_GET['next'])) : '' ?>">
      <?= csrf_field() ?>
      <div class="afield">
        <label for="l-user">Username</label>
        <input id="l-user" name="username" type="text" value="<?= e($username) ?>" maxlength="60" autocomplete="username" autofocus required>
      </div>
      <div class="afield">
        <label for="l-pw">Password</label>
        <input id="l-pw" name="password" type="password" autocomplete="current-password" required>
      </div>
      <button class="btn btn-primary" type="submit" style="width:100%">Sign in</button>
    </form>
    <p class="afine">Forgotten the password? It cannot be recovered, only replaced.
       See "If you are locked out" in the README.</p>
  </div>
</div>
</body></html>
