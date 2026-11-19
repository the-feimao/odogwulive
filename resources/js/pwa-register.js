if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/scanner-sw.js').catch(() => {});
    });
}
