</main>
<?php if (!isset($_COOKIE['cookie_consent'])): ?>
<div class="cookie-banner" id="cookie-banner">
    <p class="cookie-banner-text">Мы используем cookie для работы сайта и анализа. Оставаясь, вы соглашаетесь с их использованием.</p>
    <div class="cookie-banner-actions">
        <button type="button" class="btn btn-accent btn-sm" id="cookie-accept">Согласен</button>
        <button type="button" class="btn btn-outline btn-sm" id="cookie-decline">Отклонить</button>
    </div>
</div>
<?php endif; ?>
<footer class="site-footer">
    <div class="container">
        <p>GRAND DETAILING — детейлинг-студия · 2023</p>
    </div>
</footer>
<script src="<?= e(asset('js/main.js')) ?>"></script>
</body>
</html>
