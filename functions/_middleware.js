export async function onRequest(context) {
  const response = await context.next();
  response.headers.set("X-Content-Type-Options", "nosniff");
  response.headers.set("X-Frame-Options", "DENY");
  response.headers.set("Referrer-Policy", "strict-origin-when-cross-origin");
  if (/^\/(setup|login|dashboard|logout)(?:\/|$)/.test(new URL(context.request.url).pathname)) {
    response.headers.set("Cache-Control", "no-store, no-cache, must-revalidate");
  }
  return response;
}
