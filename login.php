<?php

declare(strict_types=1);

require __DIR__ . '/config.php';
require __DIR__ . '/auth.php';

$hasUser = $pdo->query('SELECT id FROM users LIMIT 1')->fetch();
if (!$hasUser) {
    header('Location: setup.php');
    exit;
}

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $statement = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = :username');
    $statement->execute(['username' => $username]);
    $admin = $statement->fetch();

    if (!$admin || !password_verify($password, $admin['password_hash'])) {
        $error = 'Username atau password tidak valid.';
    } else {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
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
  <title>Login Admin | SIM BANSER JATIM</title>
  <style>
    :root { --navy:#111f35; --red:#9f1d24; --gold:#dda91f; --muted:#667079; }
    * { box-sizing:border-box; }
    body { margin:0; min-height:100vh; display:grid; grid-template-columns:minmax(280px,1fr) minmax(360px,520px); font-family:Arial,sans-serif; color:#1d2a38; }
    .side { padding:4rem; display:flex; flex-direction:column; justify-content:center; background:linear-gradient(135deg,var(--navy),var(--red)); color:#fff; }
    .side p { max-width:520px; color:#d4e2da; }
    .side h1 { margin:.25rem 0 1rem; font-family:Georgia,"Times New Roman",serif; font-size:clamp(2.2rem,5vw,4.2rem); line-height:1.05; }
    .eyebrow { color:var(--gold); font-size:.75rem; font-weight:900; letter-spacing:.14em; text-transform:uppercase; }
    main { display:grid; place-items:center; padding:2rem; background:#fcfaf4; }
    .card { width:min(390px,100%); }
    h2 { margin:.25rem 0 .7rem; font-family:Georgia,"Times New Roman",serif; font-size:2.1rem; }
    label { display:block; margin-top:1rem; font-size:.82rem; font-weight:800; }
    input { width:100%; margin-top:.45rem; padding:.85rem .95rem; border:1px solid #ddd5c5; border-radius:.65rem; font:inherit; }
    button { width:100%; margin-top:1.25rem; padding:.9rem 1rem; border:0; border-radius:.65rem; background:var(--navy); color:#fff; font-weight:900; cursor:pointer; }
    .back { display:inline-block; margin-top:1rem; color:var(--navy); text-decoration:none; font-size:.88rem; font-weight:800; }
    .error { margin:.8rem 0 0; padding:.75rem; border-radius:.65rem; background:#faeeee; color:#9f1d24; }
    @media(max-width:760px){ body{grid-template-columns:1fr;} .side{min-height:230px;padding:2rem;} main{min-height:calc(100vh - 230px);} }
  </style>
</head>
<body>
  <aside class="side">
    <p class="eyebrow">SATKORWIL BANSER JATIM</p>
    <h1>Area Administrasi</h1>
    <p>Kelola informasi kaderisasi wilayah secara terpisah dari halaman profil publik.</p>
  </aside>
  <main>
    <div class="card">
      <p class="eyebrow" style="color:#9f1d24">Akses terbatas</p>
      <h2>Masuk Dashboard</h2>
      <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
      <?php endif; ?>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
        <label for="username">Username</label>
        <input id="username" name="username" required autocomplete="username" autofocus>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required autocomplete="current-password">
        <button type="submit">Masuk</button>
      </form>
      <a class="back" href="index.html">Kembali ke profil publik</a>
    </div>
  </main>
</body>
</html>
