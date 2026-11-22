<?php

declare(strict_types=1);

require __DIR__ . '/config.php';
require __DIR__ . '/auth.php';
requireAdmin();

$adminUsername = (string) ($_SESSION['admin_username'] ?? 'Admin');
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Kaderisasi | SIM BANSER JATIM</title>
  <style>
    :root { --navy:#111f35; --navy2:#1b3557; --red:#9f1d24; --gold:#dda91f; --cream:#f7f4ec; --ink:#1d2a38; --muted:#667079; --line:#e6decf; --green:#16704b; }
    * { box-sizing:border-box; }
    body { margin:0; min-height:100vh; display:grid; grid-template-columns:248px 1fr; font-family:Arial,sans-serif; color:var(--ink); background:var(--cream); }
    aside { position:sticky; top:0; height:100vh; padding:1.5rem 1.25rem; background:var(--navy); color:#fff; }
    .brand { display:flex; align-items:center; gap:.75rem; margin-bottom:2rem; font-weight:900; letter-spacing:.05em; }
    .brand-mark { width:42px; height:42px; display:grid; place-items:center; border:2px solid #f2cd62; border-radius:50%; background:#fff; color:var(--navy); font-family:Georgia,"Times New Roman",serif; }
    nav a { display:block; margin:.35rem 0; padding:.75rem .85rem; border-radius:.65rem; color:#d4e2da; text-decoration:none; font-size:.88rem; font-weight:800; }
    nav a.active { background:rgba(255,255,255,.1); color:#f2cd62; }
    .logout { position:absolute; left:1.25rem; right:1.25rem; bottom:1.25rem; display:block; padding:.75rem; border:1px solid rgba(255,255,255,.18); border-radius:.65rem; color:#fff; text-align:center; text-decoration:none; font-size:.85rem; font-weight:900; }
    main { min-width:0; }
    header { min-height:78px; display:flex; align-items:center; justify-content:space-between; gap:1rem; padding:0 2.25rem; border-bottom:1px solid var(--line); background:#fff; }
    header strong { font-size:.92rem; }
    header span { color:var(--muted); font-size:.82rem; }
    .content { padding:2.25rem; }
    .heading { display:flex; align-items:flex-end; justify-content:space-between; gap:1rem; margin-bottom:1.5rem; }
    .heading p { margin:0 0 .35rem; color:var(--red); font-size:.72rem; font-weight:900; letter-spacing:.12em; text-transform:uppercase; }
    h1 { margin:0; font-family:Georgia,"Times New Roman",serif; font-size:clamp(2rem,4vw,3rem); }
    .status { padding:.55rem .75rem; border-radius:999px; background:#edf5f1; color:var(--green); font-size:.75rem; font-weight:900; }
    .grid { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; }
    .card { min-height:210px; padding:1.35rem; border:1px solid var(--line); border-radius:1rem; background:#fff; box-shadow:0 10px 24px rgba(17,31,53,.055); }
    .card-number { width:42px; height:42px; display:grid; place-items:center; border-radius:50%; background:#f2cd62; color:var(--navy); font-family:Georgia,"Times New Roman",serif; font-weight:900; }
    .card h2 { margin:1.2rem 0 .55rem; font-family:Georgia,"Times New Roman",serif; color:var(--navy); }
    .card p { margin:0; color:var(--muted); font-size:.9rem; }
    .card-meta { display:flex; flex-wrap:wrap; gap:.45rem; margin-top:1.1rem; }
    .tag { padding:.42rem .55rem; border:1px solid var(--line); border-radius:999px; color:var(--navy2); font-size:.68rem; font-weight:900; }
    .wide { grid-column:1/-1; display:flex; align-items:center; justify-content:space-between; gap:1rem; margin-top:1rem; padding:1.25rem; border-left:4px solid var(--gold); background:#fff; }
    .wide p { margin:.25rem 0 0; color:var(--muted); font-size:.88rem; }
    .public-link { flex:0 0 auto; padding:.75rem .95rem; border-radius:.65rem; background:var(--navy); color:#fff; text-decoration:none; font-size:.82rem; font-weight:900; }
    @media(max-width:900px){ body{grid-template-columns:1fr;} aside{position:relative;height:auto;} aside nav{display:flex;gap:.35rem;} aside nav a{margin:0;} .logout{position:static;margin-top:1.25rem;} .grid{grid-template-columns:1fr;} header{padding-inline:1.25rem;} .content{padding:1.25rem;} .wide{align-items:flex-start;flex-direction:column;} }
  </style>
</head>
<body>
  <aside>
    <div class="brand"><span class="brand-mark">B</span><span>ADMIN BANser</span></div>
    <nav aria-label="Menu dashboard">
      <a class="active" href="dashboard.php">Ringkasan Diklat</a>
      <a href="index.html#program">3 Program Wilayah</a>
      <a href="index.html">Profil Publik</a>
    </nav>
    <a class="logout" href="logout.php">Keluar</a>
  </aside>
  <main>
    <header>
      <strong>Dashboard Kaderisasi Wilayah</strong>
      <span>Masuk sebagai <?= htmlspecialchars($adminUsername, ENT_QUOTES, 'UTF-8') ?></span>
    </header>
    <div class="content">
      <div class="heading">
        <div>
          <p>Satkorwil Banser Jawa Timur</p>
          <h1>Fokus Diklat Wilayah</h1>
        </div>
        <span class="status">3 program aktif</span>
      </div>
      <div class="grid">
        <article class="card">
          <span class="card-number">01</span>
          <h2>SUSBALAN</h2>
          <p>Kursus Banser lanjutan sebagai jenjang kaderisasi tingkat wilayah.</p>
          <div class="card-meta">
            <span class="tag">Jenjang lanjutan</span>
            <span class="tag">Wilayah</span>
          </div>
        </article>
        <article class="card">
          <span class="card-number">02</span>
          <h2>SUSPELAT I</h2>
          <p>Pelatihan instruktur tingkat dasar untuk menangani Diklatsar.</p>
          <div class="card-meta">
            <span class="tag">Pelatih tingkat dasar</span>
            <span class="tag">Wilayah</span>
          </div>
        </article>
        <article class="card">
          <span class="card-number">03</span>
          <h2>DIKLATSUS</h2>
          <p>Pendidikan dan latihan khusus sesuai kualifikasi serta keahlian satuan.</p>
          <div class="card-meta">
            <span class="tag">Pendidikan kejuruan</span>
            <span class="tag">Khusus</span>
          </div>
        </article>
      </div>
      <div class="wide">
        <div>
          <strong>Referensi Juklak Kebanseran 2016</strong>
          <p>Dokumen lama ini digunakan sebagai acuan informasi diklat sampai ada juklak terbaru yang beredar.</p>
        </div>
        <a class="public-link" href="index.html">Lihat halaman publik</a>
      </div>
    </div>
  </main>
</body>
</html>
