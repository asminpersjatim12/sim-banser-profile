import { escapeHtml, randomHex, hashPassword } from "./lib/auth.js";

function page(error = "") {
  return `<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Setup Admin | SIM BANSER JATIM</title><style>*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;font-family:Arial,sans-serif;color:#1d2a38;background:linear-gradient(135deg,#111f35,#9f1d24)}.card{width:min(420px,calc(100% - 2rem));padding:2rem;border-radius:1.2rem;background:#fff}.card>p:first-child{color:#9f1d24;font-size:.75rem;font-weight:900;letter-spacing:.12em;text-transform:uppercase}h1{margin:.2rem 0 .75rem;font-family:Georgia,serif}.muted{color:#667079}label{display:block;margin-top:1rem;font-size:.82rem;font-weight:800}input{width:100%;margin-top:.45rem;padding:.8rem .9rem;border:1px solid #ddd5c5;border-radius:.65rem;font:inherit}button{width:100%;margin-top:1.25rem;padding:.85rem;border:0;border-radius:.65rem;background:#111f35;color:#fff;font-weight:900}.error{padding:.75rem;border-radius:.65rem;background:#faeeee;color:#9f1d24}</style></head><body><main class="card"><p>Konfigurasi pertama kali</p><h1>Buat akun admin</h1><p class="muted">Akun ini menjadi satu-satunya pintu masuk dashboard.</p>${error ? `<p class="error">${escapeHtml(error)}</p>` : ""}<form method="post"><label for="username">Username</label><input id="username" name="username" required minlength="3" autocomplete="username"><label for="password">Password</label><input id="password" name="password" type="password" required minlength="12" autocomplete="new-password"><label for="password_confirmation">Konfirmasi password</label><input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password"><button type="submit">Buat admin dan masuk</button></form></main></body></html>`;
}

export async function onRequest(context) {
  const { request, env } = context;
  if (!env.DB) return new Response("Database D1 belum dikonfigurasi.", { status: 500 });

  const user = await env.DB.prepare("SELECT id FROM users LIMIT 1").first();
  if (user) return new Response("Halaman setup tidak tersedia.", { status: 404 });

  if (request.method === "GET") {
    return new Response(page(), { headers: { "Content-Type": "text/html;charset=UTF-8" } });
  }
  if (request.method !== "POST") return new Response("Metode tidak diizinkan.", { status: 405 });
  if (!env.SESSION_SECRET) return new Response("SESSION_SECRET belum dikonfigurasi.", { status: 500 });

  const form = await request.formData();
  const username = String(form.get("username") || "").trim();
  const password = String(form.get("password") || "");
  const confirmation = String(form.get("password_confirmation") || "");
  if (username.length < 3) return new Response(page("Username minimal 3 karakter."), { status: 400, headers: { "Content-Type": "text/html;charset=UTF-8" } });
  if (password.length < 12) return new Response(page("Password minimal 12 karakter."), { status: 400, headers: { "Content-Type": "text/html;charset=UTF-8" } });
  if (password !== confirmation) return new Response(page("Konfirmasi password tidak cocok."), { status: 400, headers: { "Content-Type": "text/html;charset=UTF-8" } });

  const salt = randomHex(16);
  const passwordHash = await hashPassword(password, salt);
  await env.DB.prepare(
    "INSERT INTO users (username, password_salt, password_hash, created_at) VALUES (?, ?, ?, ?)"
  ).bind(username, salt, passwordHash, new Date().toISOString()).run();

  const { createSession } = await import("./lib/auth.js");
  const session = await createSession(username, env.SESSION_SECRET);
  const secure = new URL(request.url).protocol === "https:";
  return new Response(null, {
    status: 302,
    headers: {
      Location: "/dashboard",
      "Set-Cookie": `banser_session=${encodeURIComponent(session)}; Path=/; HttpOnly; SameSite=Lax; Max-Age=28800${secure ? "; Secure" : ""}`,
    },
  });
}
