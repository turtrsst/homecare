/* Soeradji Care — service worker ringan: cache shell statis & halaman offline sederhana. */
const CACHE = 'soeradji-care-v2';
const SHELL = ['/images/soeradji-care-logo.svg', '/manifest.webmanifest'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(CACHE).then((cache) => cache.addAll(SHELL)).then(() => self.skipWaiting()));
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((k) => k !== CACHE).map((k) => caches.delete(k))))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const { request } = event;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    // Halaman dinamis & API selalu dari jaringan (data pasien tidak di-cache).
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => offlinePage()));
        return;
    }

    // Aset statis: cache-first.
    if (url.pathname.startsWith('/images/') || url.pathname.startsWith('/build/')) {
        event.respondWith(
            caches.match(request).then((hit) => hit || fetch(request).then((res) => {
                const copy = res.clone();
                caches.open(CACHE).then((cache) => cache.put(request, copy));
                return res;
            })),
        );
    }
});

function offlinePage() {
    const html = `<!doctype html><html lang="id"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Offline — Soeradji Care</title>
<body style="font-family:system-ui;background:#f8fafc;color:#1d2d42;display:grid;place-items:center;min-height:100vh;margin:0;padding:24px;text-align:center">
<div><img src="/images/soeradji-care-logo.svg" width="72" height="72" alt="" style="border-radius:20px">
<h1 style="font-size:1.4rem;margin:16px 0 8px">Anda sedang offline</h1>
<p style="color:#4d688a;max-width:28rem;margin:auto">Periksa koneksi internet Anda, lalu muat ulang halaman. Untuk kondisi darurat, hubungi <b>119</b>.</p>
<button onclick="location.reload()" style="margin-top:20px;background:#285fce;color:#fff;border:0;border-radius:14px;padding:12px 20px;font-weight:700">Muat ulang</button></div></body></html>`;
    return new Response(html, { headers: { 'Content-Type': 'text/html; charset=utf-8' }, status: 503 });
}
