const encoder = new TextEncoder();

function bytesToHex(bytes) {
  return Array.from(bytes, (byte) => byte.toString(16).padStart(2, "0")).join("");
}

function hexToBytes(hex) {
  return Uint8Array.from(hex.match(/.{1,2}/g).map((part) => parseInt(part, 16)));
}

export function randomHex(length = 16) {
  const bytes = crypto.getRandomValues(new Uint8Array(length));
  return bytesToHex(bytes);
}

export async function hashPassword(password, salt) {
  const key = await crypto.subtle.importKey(
    "raw",
    encoder.encode(password),
    "PBKDF2",
    false,
    ["deriveBits"]
  );
  const bits = await crypto.subtle.deriveBits(
    {
      name: "PBKDF2",
      salt: hexToBytes(salt),
      iterations: 120000,
      hash: "SHA-256",
    },
    key,
    256
  );
  return bytesToHex(new Uint8Array(bits));
}

export async function verifyPassword(password, salt, expectedHash) {
  const actualHash = await hashPassword(password, salt);
  if (actualHash.length !== expectedHash.length) return false;
  let difference = 0;
  for (let index = 0; index < actualHash.length; index += 1) {
    difference |= actualHash.charCodeAt(index) ^ expectedHash.charCodeAt(index);
  }
  return difference === 0;
}

async function signingKey(secret) {
  return crypto.subtle.importKey(
    "raw",
    encoder.encode(secret),
    { name: "HMAC", hash: "SHA-256" },
    false,
    ["sign"]
  );
}

export async function createSession(username, env) {
  await env.DB.exec(
    "CREATE TABLE IF NOT EXISTS sessions (token TEXT PRIMARY KEY, username TEXT NOT NULL, expires_at TEXT NOT NULL)"
  );
  const token = randomHex(32);
  const expiresAt = new Date(Date.now() + 8 * 60 * 60 * 1000).toISOString();
  await env.DB.prepare(
    "INSERT INTO sessions (token, username, expires_at) VALUES (?, ?, ?)"
  ).bind(token, username, expiresAt).run();
  await env.DB.prepare(
    "DELETE FROM sessions WHERE expires_at < ?"
  ).bind(new Date().toISOString()).run();
  return token;
}

export async function readSession(request, env) {
  await env.DB.exec(
    "CREATE TABLE IF NOT EXISTS sessions (token TEXT PRIMARY KEY, username TEXT NOT NULL, expires_at TEXT NOT NULL)"
  );
  const cookie = request.headers.get("Cookie") || "";
  const match = cookie.match(/(?:^|;\s*)banser_session=([^;]+)/);
  if (!match) return null;
  const token = decodeURIComponent(match[1]);
  const session = await env.DB.prepare(
    "SELECT username, expires_at FROM sessions WHERE token = ?"
  ).bind(token).first();
  if (!session) return null;
  if (session.expires_at < new Date().toISOString()) {
    await env.DB.prepare("DELETE FROM sessions WHERE token = ?").bind(token).run();
    return null;
  }
  return { username: session.username };
}

export function escapeHtml(value) {
  return String(value)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;");
}
