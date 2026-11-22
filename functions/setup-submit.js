import { randomHex, hashPassword, createSession } from "./lib/auth.js";

function errorPage(error) {
  return `<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Setup Admin | SIM BANSER JATIM</title><meta http-equiv="refresh" content="4;url=/setup"></head><body style="margin:0;min-height:100vh;display:grid;place-items:center;font-family:Arial,sans-serif;background:linear-gradient(135deg,#111f35,#9f1d24);color:#fff"><main><strong>${error}</strong><p><a style="color:#fff" href="/setup">Kembali ke setup</a></p></main></body></html>`;
}

export async function onRequest(context) {
  const { request, env } = context;
  if (request.method !== "POST") return Response.redirect(new URL("/setup", request.url), 302);
  if (!env.DB) return new Response("Database D1 belum dikonfigurasi.", { status: 500 });

  const user = await env.DB.prepare("SELECT id FROM users LIMIT 1").first();
  if (user) return new Response("Akun admin sudah dibuat. Gunakan halaman login.", { status: 409 });

  const form = await request.formData();
  const username = String(form.get("username") || "").trim();
  const password = String(form.get("password") || "");
  const confirmation = String(form.get("password_confirmation") || "");
  if (username.length < 3) return new Response(errorPage("Username minimal 3 karakter."), { status: 400, headers: { "Content-Type": "text/html;charset=UTF-8" } });
  if (password.length < 12) return new Response(errorPage("Password minimal 12 karakter."), { status: 400, headers: { "Content-Type": "text/html;charset=UTF-8" } });
  if (password !== confirmation) return new Response(errorPage("Konfirmasi password tidak cocok."), { status: 400, headers: { "Content-Type": "text/html;charset=UTF-8" } });

  const salt = randomHex(16);
  const passwordHash = await hashPassword(password, salt);
  await env.DB.prepare(
    "INSERT INTO users (username, password_salt, password_hash, created_at) VALUES (?, ?, ?, ?)"
  ).bind(username, salt, passwordHash, new Date().toISOString()).run();
  const session = await createSession(username, env);
  const secure = new URL(request.url).protocol === "https:";
  return new Response(null, {
    status: 302,
    headers: {
      Location: "/dashboard",
      "Set-Cookie": `banser_session=${encodeURIComponent(session)}; Path=/; HttpOnly; SameSite=Lax; Max-Age=28800${secure ? "; Secure" : ""}`,
    },
  });
}
