<footer class="main-footer">
    <div class="footer-left">
      {{ ($sharedData ?? null)?->setting()?->footer_copyright }}  <a href="#">Odogwu Fest All Right Reserved</a>
    </div>
    <div class="footer-right">
      {{ ($sharedData ?? null)?->setting()?->app_version }}
    </div>
</footer>