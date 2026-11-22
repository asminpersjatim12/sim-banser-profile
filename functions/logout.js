export async function onRequest(context) {
  const secure = new URL(context.request.url).protocol === "https:";
  return new Response(null, {
    status: 302,
    headers: {
      Location: "/login",
      "Set-Cookie": `banser_session=; Path=/; HttpOnly; SameSite=Lax; Max-Age=0${secure ? "; Secure" : ""}`,
    },
  });
}
