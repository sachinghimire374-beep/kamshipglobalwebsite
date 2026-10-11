<?php
/** First run: create the first administrator account. */
declare(strict_types=1);
if (!defined('KAMSHIP')) { http_response_code(403); exit('Forbidden'); }

$errors = [];
$old = ['username' => '', 'name' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    // Re-check: if someone else completed setup while this form was open, stop.
    if (!auth_needs_setup()) {
        redirect('index.php');
    }
    $old['username'] = mb_substr(trim((string) ($_POST['username'] ?? '')), 0, 60);
    $old['name'] = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 80);
    $pw = (string) ($_POST['password'] ?? '');
    $pw2 = (string) ($_POST['password2'] ?? '');

    if (!preg_match('/^[a-zA-Z0-9._-]{3,60}$/', $old['username'])) {
        $errors[] = 'Username: use 3 to 60 letters, numbers, dots, dashes or underscores.';
    }
    if ($old['name'] === '') {
        $errors[] = 'Please give your name, so the panel can greet you properly.';
    }
    if ($pw !== $pw2) {
        $errors[] = 'The two passwords do not match.';
    } else {
        $problem = password_problem($pw);
        if ($problem !== '') { $errors[] = $problem; }
    }

    if (!$errors) {
        $ok = user_save([
            'username' => $old['username'],
            'name' => $old['name'],
            'password' => password_hash($pw, PASSWORD_DEFAULT),
            'created' => date('c'),
        ]);
        if ($ok) {
            [$loggedIn] = auth_login($old['username'], $pw);
            if ($loggedIn) {
                flash('Welcome. Your administrator account is ready.');
                redirect('index.php?p=dashboard');
            }
            redirect('index.php');
        }
        $errors[] = 'Could not save the account. Check that the data folder is writable by the web server.';
    }
}

admin_head('Set up the admin panel');
?>
<div class="alogin">
  <div class="alogin-card">
    <img src="../assets/img/kamship-icon-tile-192.png" alt="" width="52" height="52" class="alogo">
    <h1>Set up your admin account</h1>
    <p class="asub">This is the first time the panel has been opened, so there is no account yet.
       Create one now. Nobody can reach the panel until you do.</p>

    <?php if ($errors): ?>
      <div class="anote anote-bad"><ul><?php foreach ($errors as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul></div>
    <?php endif; ?>

    <form method="post" action="index.php" autocomplete="off">
      <?= csrf_field() ?>
      <div class="afield">
        <label for="s-name">Your name</label>
        <input id="s-name" name="name" type="text" value="<?= e($old['name']) ?>" maxlength="80" required>
      </div>
      <div class="afield">
        <label for="s-user">Username <span class="ahint">letters, numbers, dots and dashes</span></label>
        <input id="s-user" name="username" type="text" value="<?= e($old['username']) ?>" maxlength="60" autocomplete="username" required>
      </div>
      <div class="afield">
        <label for="s-pw">Password <span class="ahint">at least 12 characters</span></label>
        <input id="s-pw" name="password" type="password" autocomplete="new-password" required>
      </div>
      <div class="afield">
        <label for="s-pw2">Password again</label>
        <input id="s-pw2" name="password2" type="password" autocomplete="new-password" required>
      </div>
      <button class="btn btn-primary" type="submit" style="width:100%">Create account and sign in</button>
    </form>
    <p class="afine">Tip: a short phrase of three or four unrelated words is both easier to remember
       and harder to guess than a short password with symbols in it.</p>
  </div>
</div>
</body></html>
