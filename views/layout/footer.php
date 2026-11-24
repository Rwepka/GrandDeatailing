<?php
$footerPhone = (string) setting('phone', '+7 (988) 413-57-77');
$footerHours = (string) setting('work_hours', 'Ежедневно с 10:00 до 19:00');
?>
</main>
<?php if (!isset($_COOKIE['cookie_consent'])): ?>
<div class="cookie-banner" id="cookie-banner" role="region" aria-label="Согласие на использование cookie" aria-live="polite">
    <p class="cookie-banner-text">Мы используем cookie для работы сайта и анализа. Оставаясь, вы соглашаетесь с их использованием.</p>
    <div class="cookie-banner-actions">
        <button type="button" class="btn btn-accent btn-sm" id="cookie-accept">Согласен</button>
        <button type="button" class="btn btn-outline btn-sm" id="cookie-decline">Отклонить</button>
    </div>
</div>
<?php endif; ?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="<?= e(url('/')) ?>" class="logo logo-gd" aria-label="GRAND DETAILING — на главную">GD</a>
                <p>Детейлинг-студия премиального ухода за автомобилем. Полировка, керамика, защитная плёнка, тонировка, химчистка и мойка.</p>
            </div>
            <div>
                <p class="footer-title">Разделы</p>
                <ul class="footer-links">
                    <li><a href="<?= e(url('/services')) ?>">Услуги и цены</a></li>
                    <li><a href="<?= e(url('/reviews')) ?>">Отзывы</a></li>
                    <li><a href="<?= e(url('/news')) ?>">Новости</a></li>
                    <li><a href="<?= e(url('/booking')) ?>">Запись онлайн</a></li>
                </ul>
            </div>
            <div>
                <p class="footer-title">Контакты</p>
                <ul class="footer-links">
                    <li><a href="tel:<?= e((string) preg_replace('/\D+/', '', $footerPhone)) ?>"><?= e($footerPhone) ?></a></li>
                    <li><?= e($footerHours) ?></li>
                    <li><a href="<?= e(url('/contacts')) ?>">Карта и контакты</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© 2023–<?= e(date('Y')) ?> GRAND DETAILING · детейлинг</span>
            <span>Онлайн-запись и контакты</span>
        </div>
    </div>
</footer>
<script src="<?= e(asset('js/main.js')) ?>"></script>
</body>
</html>
