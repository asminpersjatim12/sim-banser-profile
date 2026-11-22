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

export async function createSession(username, secret) {
  const payload = JSON.stringify({
    username,
    expires: Date.now() + 8 * 60 * 60 * 1000,
  });
  const payloadEncoded = bytesToHex(encoder.encode(payload));
  const signature = await crypto.subtle.sign("HMAC", await signingKey(secret), encoder.encode(payloadEncoded));
  return `${payloadEncoded}.${bytesToHex(new Uint8Array(signature))}`;
}

export async function readSession(request, secret) {
  const cookie = request.headers.get("Cookie") || "";
  const match = cookie.match(/(?:^|;\s*)banser_session=([^;]+)/);
  if (!match) return null;

  const [payloadEncoded, signatureHex] = decodeURIComponent(match[1]).split(".");
  if (!payloadEncoded || !signatureHex) return null;

  const expected = await crypto.subtle.sign("HMAC", await signingKey(secret), encoder.encode(payloadEncoded));
  const expectedHex = bytesToHex(new Uint8Array(expected));
  if (signatureHex.length !== expectedHex.length) return null;
  let difference = 0;
  for (let index = 0; index < signatureHex.length; index += 1) {
    difference |= signatureHex.charCodeAt(index) ^ expectedHex.charCodeAt(index);
  }
  if (difference !== 0) return null;

  const bytes = hexToBytes(payloadEncoded);
  const payload = JSON.parse(new TextDecoder().decode(bytes));
  if (!payload.username || payload.expires < Date.now()) return null;
  return payload;
}

export function escapeHtml(value) {
  return String(value)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;");
}
