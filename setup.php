<?php

declare(strict_types=1);

require __DIR__ . '/config.php';
require __DIR__ . '/auth.php';

$user = $pdo->query('SELECT id FROM users LIMIT 1')->fetch();

if ($user) {
    http_response_code(404);
    exit('Halaman setup tidak tersedia.');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirmation = (string) ($_POST['password_confirmation'] ?? '');

    if (strlen($username) < 3) {
        $error = 'Username minimal 3 karakter.';
    } elseif (strlen($password) < 12) {
        $error = 'Password minimal 12 karakter.';
    } elseif ($password !== $passwordConfirmation) {
        $error = 'Konfirmasi password tidak cocok.';
    } else {
        $statement = $pdo->prepare(
            'INSERT INTO users (username, password_hash, created_at) VALUES (:username, :password_hash, :created_at)'
        );
        $statement->execute([
            'username' => $username,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'created_at' => gmdate('c'),
        ]);

        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $pdo->lastInsertId();
        $_SESSION['admin_username'] = $username;
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Setup Admin | SIM BANSER JATIM</title>
  <style>
    :root { --navy:#111f35; --gold:#dda91f; --red:#9f1d24; --cream:#fcfaf4; --muted:#667079; }
    * { box-sizing:border-box; }
    body { margin:0; min-height:100vh; display:grid; place-items:center; font-family:Arial,sans-serif; color:#1d2a38; background:linear-gradient(135deg,var(--navy),var(--red)); }
    .card { width:min(420px,calc(100% - 2rem)); padding:2rem; border-radius:1.2rem; background:#fff; box-shadow:0 22px 60px rgba(0,0,0,.18); }
    h1 { margin:.2rem 0 .75rem; font-family:Georgia,"Times New Roman",serif; }
    p { color:var(--muted); }
    label { display:block; margin-top:1rem; font-size:.82rem; font-weight:800; }
    input { width:100%; margin-top:.45rem; padding:.8rem .9rem; border:1px solid #ddd5c5; border-radius:.65rem; font:inherit; }
    button { width:100%; margin-top:1.25rem; padding:.85rem 1rem; border:0; border-radius:.65rem; background:var(--navy); color:#fff; font-weight:900; cursor:pointer; }
    .error { margin:.8rem 0 0; padding:.75rem; border-radius:.65rem; background:#faeeee; color:#9f1d24; }
  </style>
</head>
<body>
  <main class="card">
    <p>Konfigurasi pertama kali</p>
    <h1>Buat akun admin</h1>
    <p>Akun ini akan menjadi satu-satunya pintu masuk dashboard.</p>
    <?php if ($error): ?>
      <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form method="post" autocomplete="off">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
      <label for="username">Username</label>
      <input id="username" name="username" required minlength="3" autocomplete="username">
      <label for="password">Password</label>
      <input id="password" name="password" type="password" required minlength="12" autocomplete="new-password">
      <label for="password_confirmation">Konfirmasi password</label>
      <input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password">
      <button type="submit">Buat admin dan masuk</button>
    </form>
  </main>
</body>
</html>
