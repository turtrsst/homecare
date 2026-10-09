import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import focus from '@alpinejs/focus';
import sora from './sora';

Alpine.plugin(collapse);
Alpine.plugin(focus);

Alpine.data('sora', sora);

window.Alpine = Alpine;
Alpine.start();

// Daftarkan service worker (PWA). Diabaikan bila browser tidak mendukung.
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
}

// Tombol "Pasang aplikasi" (PWA install prompt).
window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    window.deferredInstallPrompt = event;
    document.querySelectorAll('[data-install-app]').forEach((el) => el.classList.remove('hidden'));
});
