<script>
    (function () {
        var stored = localStorage.getItem('ds-theme');
        var dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.classList.toggle('tw-dark', dark);
    })();
</script>
