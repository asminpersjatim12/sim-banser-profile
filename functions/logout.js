export async function onRequest(context) {
  const cookie = context.request.headers.get("Cookie") || "";
  const match = cookie.match(/(?:^|;\s*)banser_session=([^;]+)/);
  if (match && context.env.DB) {
    await context.env.DB.prepare(
      "DELETE FROM sessions WHERE token = ?"
    ).bind(decodeURIComponent(match[1])).run();
  }
  const secure = new URL(context.request.url).protocol === "https:";
  return new Response(null, {
    status: 302,
    headers: {
      Location: "/login",
      "Set-Cookie": `banser_session=; Path=/; HttpOnly; SameSite=Lax; Max-Age=0${secure ? "; Secure" : ""}`,
    },
  });
}
