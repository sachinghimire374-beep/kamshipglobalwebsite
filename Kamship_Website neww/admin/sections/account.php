<?php
declare(strict_types=1);
if (!defined('KAMSHIP')) { http_response_code(403); exit('Forbidden'); }
$me = auth_require();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'password') {
    $cur = (string) ($_POST['current'] ?? '');
    $new = (string) ($_POST['password'] ?? '');
    $new2 = (string) ($_POST['password2'] ?? '');

    $user = user_find((string) $me['username']);
    if ($user === null || !password_verify($cur, (string) ($user['password'] ?? ''))) {
        $errors[] = 'Your current password is not correct.';
    }
    if ($new !== $new2) {
        $errors[] = 'The two new passwords do not match.';
    } else {
        $problem = password_problem($new);
        if ($problem !== '') { $errors[] = $problem; }
    }
    if (!$errors && $user !== null) {
        $user['password'] = password_hash($new, PASSWORD_DEFAULT);
        $user['password_changed'] = date('c');
        if (user_save($user)) {
            // Force a fresh session after a credential change.
            session_regenerate_id(true);
            flash('Password changed. Use the new one next time you sign in.');
            redirect('index.php?p=account');
        }
        $errors[] = 'Could not save the new password. Check that data/ is writable.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'adduser') {
    $u = mb_substr(trim((string) ($_POST['username'] ?? '')), 0, 60);
    $n = mb_substr(trim((string) ($_POST['name'] ?? '')), 0, 80);
    $pw = (string) ($_POST['newpassword'] ?? '');
    if (!preg_match('/^[a-zA-Z0-9._-]{3,60}$/', $u)) {
        $errors[] = 'Username: 3 to 60 letters, numbers, dots, dashes or underscores.';
    } elseif (user_find($u) !== null) {
        $errors[] = 'There is already an account with that username.';
    }
    if ($n === '') { $errors[] = 'Please give the person a name.'; }
    $problem = password_problem($pw);
    if ($problem !== '') { $errors[] = $problem; }

    if (!$errors) {
        user_save(['username' => $u, 'name' => $n,
                   'password' => password_hash($pw, PASSWORD_DEFAULT), 'created' => date('c')]);
        flash('Added an account for ' . $n . '.');
        redirect('index.php?p=account');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'deluser') {
    $u = (string) ($_POST['username'] ?? '');
    $users = users_all();
    if (count($users) <= 1) {
        flash('You cannot delete the only account.', 'bad');
    } elseif ($u === (string) $me['username']) {
        flash('You cannot delete the account you are signed in with.', 'bad');
    } else {
        $users = array_values(array_filter($users, static fn($x) => ($x['username'] ?? '') !== $u));
        store_write('users', $users);
        flash('Account removed.');
    }
    redirect('index.php?p=account');
}

admin_shell_open('account', 'Your account', 'Change your password, or give someone else access.');
?>
<?php if ($errors): ?>
  <div class="anote anote-bad"><ul><?php foreach ($errors as $x): ?><li><?= e($x) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form method="post" action="index.php?p=account" class="apanel" autocomplete="off">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="password">
  <h2>Change your password</h2>
  <p class="aempty" style="margin-bottom:20px">Signed in as <b><?= e((string) $me['name']) ?></b> (<?= e((string) $me['username']) ?>).</p>
  <?php fld('current', 'Current password', '', 'password', ['wide' => true]); ?>
  <?php fld('password', 'New password', '', 'password', ['wide' => true, 'hint' => 'At least 12 characters']); ?>
  <?php fld('password2', 'New password again', '', 'password', ['wide' => true]); ?>
  <div class="aactions"><button class="btn btn-primary" type="submit">Change password</button></div>
</form>

<section class="apanel">
  <h2>Who can sign in</h2>
  <table class="atable atable-rows">
    <thead><tr><th>Name</th><th>Username</th><th>Added</th><th class="aright"></th></tr></thead>
    <tbody>
      <?php foreach (users_all() as $u): ?>
        <tr>
          <td><b><?= e((string) ($u['name'] ?? '')) ?></b><?= ($u['username'] ?? '') === $me['username'] ? ' <span class="atag">You</span>' : '' ?></td>
          <td><?= e((string) ($u['username'] ?? '')) ?></td>
          <td class="anowrap"><?= e(isset($u['created']) ? date('j M Y', (int) strtotime((string) $u['created'])) : '—') ?></td>
          <td class="aright">
            <?php if (($u['username'] ?? '') !== $me['username']): ?>
              <form method="post" action="index.php?p=account" class="ainline" data-confirm="Remove this person's access?">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="deluser">
                <input type="hidden" name="username" value="<?= e((string) ($u['username'] ?? '')) ?>">
                <button class="btn btn-danger btn-sm" type="submit">Remove</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>

<form method="post" action="index.php?p=account" class="apanel" autocomplete="off">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="adduser">
  <h2>Give someone else access</h2>
  <p class="aempty" style="margin-bottom:20px">Everyone who signs in can edit everything. There are no restricted roles.</p>
  <div class="arow">
    <?php fld('name', 'Their name', '', 'text', ['max' => 80]); ?>
    <?php fld('username', 'Username', '', 'text', ['max' => 60]); ?>
  </div>
  <?php fld('newpassword', 'Password to give them', '', 'password', ['wide' => true, 'hint' => 'At least 12 characters. Ask them to change it once they sign in.']); ?>
  <div class="aactions"><button class="btn btn-primary" type="submit">Add account</button></div>
</form>
<?php admin_shell_close();
