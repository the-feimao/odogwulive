<a href="javascript:void(0)" id="admin-theme-toggle" class="dropdown-item has-icon" role="button"
    aria-label="{{ __('Toggle dark mode') }}">
    <i class="fas fa-moon" aria-hidden="true" data-admin-theme-icon="dark"></i>
    <i class="fas fa-sun" aria-hidden="true" data-admin-theme-icon="light"></i>
    <span class="admin-theme-label">{{ __('Dark mode') }}</span>
</a>
<script>
    (function () {
        var toggle = document.getElementById('admin-theme-toggle');
        if (!toggle) return;

        var label = toggle.querySelector('.admin-theme-label');
        var darkIcon = toggle.querySelector('[data-admin-theme-icon="dark"]');
        var lightIcon = toggle.querySelector('[data-admin-theme-icon="light"]');

        var update = function (dark) {
            toggle.setAttribute('aria-pressed', dark ? 'true' : 'false');
            if (label) label.textContent = dark ? @json(__('Light mode')) : @json(__('Dark mode'));
            if (darkIcon) darkIcon.style.display = dark ? 'none' : '';
            if (lightIcon) lightIcon.style.display = dark ? '' : 'none';
        };

        update(document.documentElement.classList.contains('tw-dark'));

        toggle.addEventListener('click', function () {
            var dark = !document.documentElement.classList.contains('tw-dark');
            document.documentElement.classList.toggle('tw-dark', dark);
            localStorage.setItem('ds-admin-theme', dark ? 'dark' : 'light');
            update(dark);
        });
    })();
</script>
