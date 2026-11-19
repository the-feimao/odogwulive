import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';

const TOKEN_KEY = 'odogwu.scanner.token';
const root = document.getElementById('scanner-app');

if (root) {
    const byId = (id) => document.getElementById(id);
    const authPanel = byId('scanner-auth');
    const workspace = byId('scanner-workspace');
    const loginForm = byId('scanner-login-form');
    const loginButton = byId('scanner-login-button');
    const authError = byId('scanner-auth-error');
    const logoutButton = byId('scanner-logout');
    const refreshButton = byId('scanner-refresh-events');
    const eventsList = byId('scanner-events');
    const eventsEmpty = byId('scanner-events-empty');
    const scanSection = byId('scanner-scan-section');
    const selectedEventName = byId('scanner-selected-event');
    const resultBox = byId('scanner-result');
    const manualForm = byId('scanner-manual-form');
    const manualCode = byId('scanner-manual-code');
    const stopButton = byId('scanner-stop');

    let token = localStorage.getItem(TOKEN_KEY);
    let events = [];
    let selectedEvent = null;
    let scanner = null;
    let lastScanAt = 0;

    const show = (element) => element.classList.remove('tw-hidden');
    const hide = (element) => element.classList.add('tw-hidden');

    function setAuthError(message) {
        if (!message) {
            authError.textContent = '';
            hide(authError);
            return;
        }
        authError.textContent = message;
        show(authError);
    }

    function setResult(message, ok) {
        resultBox.textContent = message;
        resultBox.className = ok
            ? 'tw-mt-4 tw-rounded-stub tw-bg-success-50 tw-px-4 tw-py-3 tw-text-sm tw-font-semibold tw-text-success-700 dark:tw-bg-success-900/30 dark:tw-text-success-100'
            : 'tw-mt-4 tw-rounded-stub tw-bg-danger-50 tw-px-4 tw-py-3 tw-text-sm tw-font-semibold tw-text-danger-700 dark:tw-bg-danger-900/30 dark:tw-text-danger-100';
        show(resultBox);
    }

    async function api(path, options = {}) {
        const headers = {
            Accept: 'application/json',
            ...(options.headers || {}),
        };

        if (options.body) {
            headers['Content-Type'] = 'application/json';
        }
        if (token) {
            headers.Authorization = `Bearer ${token}`;
        }

        const response = await fetch(path, { ...options, headers });
        let payload = {};

        try {
            payload = await response.json();
        } catch (error) {
            payload = {};
        }

        if (response.status === 401) {
            signOut();
            throw new Error('Your session expired. Please sign in again.');
        }

        if (!response.ok) {
            throw new Error(payload.msg || 'The request could not be completed.');
        }

        return payload;
    }

    function renderEvents() {
        eventsList.replaceChildren();

        if (!events.length) {
            show(eventsEmpty);
            return;
        }

        hide(eventsEmpty);

        events.forEach((event) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.dataset.eventId = String(event.id);
            button.className =
                'tw-flex tw-w-full tw-items-center tw-justify-between tw-gap-3 tw-rounded-stub tw-border tw-border-neutral-200 tw-bg-neutral-50 tw-px-4 tw-py-3 tw-text-left tw-transition hover:tw-border-accent-500 hover:tw-bg-accent-50 dark:tw-border-neutral-700 dark:tw-bg-ink-950 dark:hover:tw-border-accent-400 dark:hover:tw-bg-accent-900/20';

            const name = document.createElement('span');
            name.className = 'tw-text-sm tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50';
            name.textContent = event.name || `Event ${event.id}`;

            const count = document.createElement('span');
            count.className = 'tw-shrink-0 tw-text-xs tw-font-semibold tw-text-neutral-500 dark:tw-text-neutral-400';
            count.textContent = `Scanned: ${Number(event.scanTicket || 0)}`;

            button.append(name, count);
            button.addEventListener('click', () => selectEvent(event));
            eventsList.appendChild(button);
        });
    }

    async function loadEvents() {
        refreshButton.disabled = true;
        eventsList.textContent = 'Loading events…';
        hide(eventsEmpty);

        try {
            const payload = await api('/api/scanner/events');
            events = payload.success ? payload.data || [] : [];
            renderEvents();
        } catch (error) {
            eventsList.textContent = '';
            show(eventsEmpty);
            eventsEmpty.textContent = error.message;
        } finally {
            refreshButton.disabled = false;
        }
    }

    async function stopScanner() {
        if (!scanner) {
            return;
        }

        const activeScanner = scanner;
        scanner = null;

        try {
            await activeScanner.stop();
            activeScanner.clear();
        } catch (error) {
            // The camera may already have been released by the browser.
        }

        hide(stopButton);
    }

    async function validateTicket(rawCode) {
        const code = String(rawCode || '').trim();

        if (!code || !selectedEvent) {
            setResult('Select an event and enter or scan a ticket code.', false);
            return;
        }

        try {
            const payload = await api(
                `/api/scanner/scan-ticket/${encodeURIComponent(code)}/${selectedEvent.id}`
            );
            setResult(payload.msg || 'Ticket processed.', Boolean(payload.success));

            if (payload.success) {
                await loadEvents();
            }
        } catch (error) {
            setResult(error.message, false);
        }
    }

    async function onDecoded(decodedText) {
        const now = Date.now();
        if (now - lastScanAt < 1500) {
            return;
        }
        lastScanAt = now;
        await validateTicket(decodedText);
    }

    async function startScanner() {
        await stopScanner();

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            setResult('Camera access is unavailable in this browser. Use the manual code field instead.', false);
            return;
        }

        try {
            scanner = new Html5Qrcode('scanner-reader', {
                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                verbose: false,
            });

            await scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 240, height: 240 } },
                onDecoded,
                () => {}
            );
            show(stopButton);
        } catch (error) {
            scanner = null;
            setResult('The camera could not be started. Use the manual code field instead.', false);
        }
    }

    function selectEvent(event) {
        selectedEvent = event;
        selectedEventName.textContent = event.name || `Event ${event.id}`;
        resultBox.textContent = '';
        hide(resultBox);
        show(scanSection);
        startScanner();
    }

    function showWorkspace() {
        hide(authPanel);
        show(workspace);
        show(logoutButton);
    }

    function signOut() {
        token = null;
        selectedEvent = null;
        events = [];
        localStorage.removeItem(TOKEN_KEY);
        stopScanner();
        hide(workspace);
        hide(scanSection);
        hide(stopButton);
        show(authPanel);
        hide(logoutButton);
        setAuthError('');
        resultBox.textContent = '';
        hide(resultBox);
    }

    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        setAuthError('');
        loginButton.disabled = true;

        try {
            const payload = await api('/api/scanner/login', {
                method: 'POST',
                body: JSON.stringify({
                    email: byId('scanner-email').value.trim(),
                    password: byId('scanner-password').value,
                }),
            });

            if (!payload.success || !payload.data || !payload.data.token) {
                throw new Error(payload.msg || 'Scanner sign in failed.');
            }

            token = payload.data.token;
            localStorage.setItem(TOKEN_KEY, token);
            showWorkspace();
            await loadEvents();
        } catch (error) {
            setAuthError(error.message);
        } finally {
            loginButton.disabled = false;
        }
    });

    logoutButton.addEventListener('click', signOut);
    refreshButton.addEventListener('click', loadEvents);
    stopButton.addEventListener('click', stopScanner);

    manualForm.addEventListener('submit', (event) => {
        event.preventDefault();
        validateTicket(manualCode.value);
        manualCode.value = '';
    });

    if (token) {
        showWorkspace();
        loadEvents();
    }

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/scanner-sw.js').catch(() => {});
        });
    }
}
