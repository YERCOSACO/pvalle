
import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

const updateNetworkStatus = (online) => {
    document.querySelectorAll('[data-network-status]').forEach((status) => {
        const indicator = status.querySelector('[data-network-indicator]');
        const label = status.querySelector('[data-network-label]');

        label.textContent = online ? 'Internet disponible' : 'Sin internet';
        status.className = `fixed bottom-4 right-4 z-50 flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold shadow-lg ${
            online ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'
        }`;
        indicator.className = `h-2.5 w-2.5 rounded-full ${
            online ? 'bg-emerald-500' : 'bg-rose-500'
        }`;
        status.title = 'Se comprueba el acceso a internet. No identifica si la conexión es Wi-Fi o cable.';
    });
};

const checkInternetConnection = async () => {
    if (!navigator.onLine) {
        updateNetworkStatus(false);
        return;
    }

    // navigator.onLine solo detecta la red local; esta petición comprueba salida a internet.
    const controller = new AbortController();
    const timeout = window.setTimeout(() => controller.abort(), 5000);

    try {
        await fetch('https://connectivitycheck.gstatic.com/generate_204', {
            method: 'GET',
            mode: 'no-cors',
            cache: 'no-store',
            signal: controller.signal,
        });
        updateNetworkStatus(true);
    } catch {
        updateNetworkStatus(false);
    } finally {
        window.clearTimeout(timeout);
    }
};

window.addEventListener('online', checkInternetConnection);
window.addEventListener('offline', () => updateNetworkStatus(false));
document.addEventListener('DOMContentLoaded', checkInternetConnection);
document.addEventListener('visibilitychange', () => {
    if (!document.hidden) checkInternetConnection();
});
window.setInterval(checkInternetConnection, 20000);

checkInternetConnection();

Alpine.start();
