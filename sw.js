/* =====================================================================================
   Charles Tech — SERVICE WORKER (makes the website work like an app, also offline)
   • Saves the website and EVERY picture of the images/ folder on the phone / tablet / computer,
     so pictures never disappear even with a weak or no connection.
   • Pictures: shown at once from the saved copy, and quietly refreshed from the server —
     when you replace a picture file (same name), visitors get the new one on their next visit.
   • After you change index.html or add new picture names below: change VERSION (e.g. v2 → v3).
   ===================================================================================== */
const VERSION = 'chts-v10';
const CORE = VERSION + '-core', PICS = VERSION + '-pictures';

const CORE_FILES = [
  './', './index.html', './student-portal.html', './manifest.webmanifest',
  'assets/css/bootstrap.min.css',
  'assets/icons/icon-192.png', 'assets/icons/icon-512.png', 'assets/icons/maskable-512.png',
  'assets/icons/apple-touch-icon.png', 'assets/icons/favicon.png'
];
/* every picture of the website, from Home to the last page (missing ones are simply skipped) */
const PICTURE_FILES = [
    "assets/icons/apple-touch-icon.png",
    "assets/icons/favicon.png",
    "assets/icons/icon-192.png",
    "assets/icons/icon-512.png",
    "assets/icons/maskable-512.png",
    "images/00-logo-and-icons/icon-chat.png",
    "images/00-logo-and-icons/icon-equity.png",
    "images/00-logo-and-icons/icon-map.png",
    "images/00-logo-and-icons/icon-mtn.png",
    "images/00-logo-and-icons/icon-phone.png",
    "images/00-logo-and-icons/icon-whatsapp.png",
    "images/00-logo-and-icons/logo-mark.png",
    "images/00-logo-and-icons/logo-wide.png",
    "images/01-home/slide-1.jpg",
    "images/01-home/slide-2.jpg",
    "images/01-home/slide-3.jpg",
    "images/01-home/slide-4.jpg",
    "images/01-home/slide-5.jpg",
    "images/01-home/slide-6.jpg",
    "images/01-home/workshop.jpg",
    "images/02-shop/slide-1.jpg",
    "images/02-shop/slide-2.jpg",
    "images/02-shop/slide-3.jpg",
    "images/03-learn/gallery-01.jpg",
    "images/03-learn/gallery-02.jpg",
    "images/03-learn/gallery-03.jpg",
    "images/03-learn/gallery-04.jpg",
    "images/03-learn/gallery-05.jpg",
    "images/03-learn/gallery-06.jpg",
    "images/03-learn/gallery-07.jpg",
    "images/03-learn/gallery-08.jpg",
    "images/03-learn/gallery-09.jpg",
    "images/03-learn/gallery-10.jpg",
    "images/03-learn/gallery-11.jpg",
    "images/03-learn/gallery-12.jpg",
    "images/03-learn/gallery-13.jpg",
    "images/03-learn/gallery-14.jpg",
    "images/03-learn/gallery-15.jpg",
    "images/03-learn/gallery-16.jpg",
    "images/03-learn/gallery-17.jpg",
    "images/03-learn/gallery-18.jpg",
    "images/03-learn/slide-1.jpg",
    "images/03-learn/slide-2.jpg",
    "images/03-learn/slide-3.jpg",
    "images/04-hot-deals/slide-1.jpg",
    "images/04-hot-deals/slide-2.jpg",
    "images/04-hot-deals/slide-3.jpg",
    "images/07-other-pages/about-manager.jpg",
    "images/07-other-pages/banner-about.jpg",
    "images/07-other-pages/banner-contact.jpg",
    "images/07-other-pages/banner-docs.jpg",
    "images/07-other-pages/banner-tutorials.jpg",
    "images/05-team/member-1.jpg", "images/05-team/member-2.jpg", "images/05-team/member-3.jpg", "images/05-team/member-4.jpg"
];

async function saveAll(cacheName, list){
  const c = await caches.open(cacheName);
  await Promise.all(list.map(u => fetch(u, { cache: 'no-cache' }).then(r => { if (r.ok) return c.put(u, r); }).catch(() => {})));
}
self.addEventListener('install', e => {
  e.waitUntil(Promise.all([saveAll(CORE, CORE_FILES), saveAll(PICS, PICTURE_FILES)]));
});
self.addEventListener('activate', e => {
  e.waitUntil((async () => {
    const keep = [CORE, PICS];
    for (const k of await caches.keys()) if (!keep.includes(k) && k.startsWith('chts-')) await caches.delete(k);
    if (self.registration.navigationPreload) { try { await self.registration.navigationPreload.enable(); } catch (e) {} }
    await self.clients.claim();
  })());
});
self.addEventListener('message', e => { if (e.data === 'skipWaiting') self.skipWaiting(); });

self.addEventListener('fetch', e => {
  const req = e.request;
  if (req.method !== 'GET') return;
  const url = new URL(req.url);
  if (url.origin !== location.origin) return;                       // other websites are never cached

  // pages: newest from the internet, saved copy when offline
  if (req.mode === 'navigate') {
    e.respondWith((async () => {
      try {
        const r = (await e.preloadResponse) || await fetch(req);
        if (r && r.ok) { const c = await caches.open(CORE); c.put(req, r.clone()); }
        return r;
      } catch (err) {
        return (await caches.match(req)) || (await caches.match('./index.html')) || (await caches.match('./')) || Response.error();
      }
    })());
    return;
  }

  // API data is live and may be private; never store backend responses offline.
  if (url.pathname.includes('/api/')) {
    e.respondWith(fetch(req));
    return;
  }

  // pictures: instant from the saved copy + refresh in the background
  if (url.pathname.includes('/images/') || req.destination === 'image') {
    e.respondWith((async () => {
      const c = await caches.open(PICS);
      const hit = await c.match(req, { ignoreSearch: true });
      const net = fetch(req).then(r => { if (r && r.ok) c.put(req, r.clone()); return r; }).catch(() => null);
      if (hit) { e.waitUntil(net); return hit; }
      return (await net) || new Response('', { status: 404, statusText: 'Picture not found' });
    })());
    return;
  }

  // everything else of this website: saved copy first, then internet
  e.respondWith((async () => {
    const hit = await caches.match(req, { ignoreSearch: true });
    if (hit) return hit;
    try { const r = await fetch(req); if (r.ok) { const c = await caches.open(CORE); c.put(req, r.clone()); } return r; }
    catch (err) { return Response.error(); }
  })());
});
