/**
 * Anteprima riservata: chi non ha la password vede solo la pagina di accesso.
 * La password è nella variabile d'ambiente PREVIEW_PASSWORD del progetto
 * Vercel, non in questo file.
 */
export const config = { matcher: '/:path*' };

const COOKIE = 'zdm_anteprima';

async function token(pw) {
  const data = new TextEncoder().encode('zdm-anteprima|' + pw);
  const hash = await crypto.subtle.digest('SHA-256', data);
  return [...new Uint8Array(hash)].map((b) => b.toString(16).padStart(2, '0')).join('');
}

function page(error, next) {
  const esc = (s) => String(s).replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
  return `<!doctype html><html lang="it"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Anteprima riservata</title>
<style>*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:#0e261d;font:16px/1.5 -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;color:#212529;padding:20px}
.box{width:100%;max-width:400px;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 30px 60px rgba(0,0,0,.35)}
.h{background:#225e48;color:#fff;padding:24px 26px}.h strong{display:block;font-size:1.25rem}.h span{color:#cfe0d8;font-size:.9rem}
form{padding:24px 26px 26px;display:grid;gap:12px}label{font-size:.85rem;font-weight:600}
input{width:100%;padding:12px;border:1px solid #c5ccc9;border-radius:6px;font-size:1rem}input:focus{outline:0;border-color:#225e48;box-shadow:0 0 0 3px rgba(34,94,72,.16)}
button{padding:13px;border:0;border-radius:6px;background:#a0300e;color:#fff;font-size:1rem;font-weight:700;cursor:pointer}
.e{margin:0;color:#b42318;font-size:.9rem}.n{margin:0;font-size:.8rem;color:#5f676f}</style></head>
<body><div class="box"><div class="h"><strong>Anteprima del nuovo sito</strong><span>ZDM Orientamento · accesso riservato</span></div>
<form method="post" action="/__accesso"><input type="hidden" name="next" value="${esc(next)}">
<label for="pw">Password</label><input id="pw" name="password" type="password" autocomplete="current-password" required autofocus>
${error ? '<p class="e">Password non corretta.</p>' : ''}<button>Entra</button>
<p class="n">Copia di sola visione: i moduli di contatto non inviano dati.</p></form></div></body></html>`;
}

export default async function middleware(request) {
  const url = new URL(request.url);
  const pw = process.env.PREVIEW_PASSWORD || '';
  const html = { 'content-type': 'text/html; charset=utf-8', 'cache-control': 'no-store', 'x-robots-tag': 'noindex, nofollow' };
  if (!pw) {
    return new Response('Anteprima non configurata.', { status: 503, headers: html });
  }
  const good = await token(pw);

  if (url.pathname === '/__accesso' && request.method === 'POST') {
    const form = await request.formData();
    let next = String(form.get('next') || '/');
    if (!next.startsWith('/') || next.startsWith('//')) next = '/';
    if (String(form.get('password') || '') === pw) {
      return new Response(null, {
        status: 303,
        headers: { location: next, 'set-cookie': `${COOKIE}=${good}; Path=/; Max-Age=2592000; HttpOnly; Secure; SameSite=Lax` },
      });
    }
    return new Response(page(true, next), { status: 401, headers: html });
  }

  const cookies = request.headers.get('cookie') || '';
  if (cookies.split(/;\s*/).includes(`${COOKIE}=${good}`)) {
    return new Response(null, { headers: { 'x-middleware-next': '1' } });
  }
  return new Response(page(false, url.pathname + url.search), { status: 401, headers: html });
}
