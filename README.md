# sim-banser-profile
Website profil publik BANSER Jawa Timur
Banser Jawa Timur
==================

Website profil publik dan dashboard administrasi kaderisasi Banser wilayah Jawa Timur.

Menjalankan secara lokal
------------------------

Gunakan PHP 8 dengan SQLite:

```powershell
php -S 127.0.0.1:8080
```

Buka `http://127.0.0.1:8080/index.html` untuk profil publik.

Pada kunjungan pertama ke `http://127.0.0.1:8080/login.php`, buat akun admin melalui halaman setup. Username dan password tidak disimpan di kode sumber. Password disimpan sebagai hash dan sesi dashboard dilindungi dengan cookie HTTP-only.

Deployment Cloudflare
---------------------

Versi online menggunakan Cloudflare Pages Functions dan D1. Versi PHP lokal tetap tersedia sebagai alternatif.

1. Masuk ke Cloudflare Dashboard.
2. Buka **My Profile > API Tokens > Create Token**.
3. Pilih template **Edit Cloudflare Workers** atau buat Custom Token dengan izin:
   - Account: `D1:Edit`
   - Account: `Workers Scripts:Edit`
   - Account: `Pages:Edit`
   - Account Resources: akun yang digunakan
4. Buat token dan simpan nilainya.
5. Di terminal Windows PowerShell jalankan:

```powershell
$env:CLOUDFLARE_API_TOKEN="ISI_TOKEN_DISINI"
npx.cmd --yes wrangler@latest d1 create banser-admin
```

Salin `database_id` dari hasil perintah, lalu gantikan `REPLACE_WITH_D1_DATABASE_ID` pada `wrangler.toml`.
Terapkan skema database dan buat secret sesi:

```powershell
npx.cmd --yes wrangler@latest d1 migrations apply banser-admin
npx.cmd --yes wrangler@latest pages secret put SESSION_SECRET --project-name sim-banser-profile
npx.cmd --yes wrangler@latest pages deploy . --project-name sim-banser-profile
```

Untuk `SESSION_SECRET`, masukkan nilai acak yang panjang dan simpan dengan aman. Setelah deployment, buka URL `*.pages.dev` dan kunjungi `/setup` untuk membuat admin pertama.
