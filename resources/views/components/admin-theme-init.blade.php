<script>
    (function () {
        // The admin theme is deliberately independent from the public site's
        // preference; staff can use a light admin on a dark public site (and
        // vice versa) without either choice leaking into the other.
        var stored = localStorage.getItem('ds-admin-theme');
        var dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.classList.toggle('tw-dark', dark);
    })();
</script>
