<?php
/** @var array<int, array{label: string, url?: string}> $crumbs */
$crumbs = $crumbs ?? [];

$baseUrl = rtrim((string) config('app.url'), '/');
$currentPath = (string) (parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$items = [
    ['name' => 'Главная', 'item' => $baseUrl . '/'],
];
foreach ($crumbs as $crumb) {
    $items[] = [
        'name' => (string) $crumb['label'],
        'item' => !empty($crumb['url'])
            ? $baseUrl . $crumb['url']
            : $baseUrl . $currentPath,
    ];
}
$breadcrumbSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => array_map(
        static fn(array $item, int $index): array => [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $item['name'],
            'item' => $item['item'],
        ],
        $items,
        array_keys($items)
    ),
];
?>
<nav class="breadcrumbs" aria-label="Хлебные крошки">
    <a href="<?= e(url('/')) ?>">Главная</a>
    <?php foreach ($crumbs as $crumb): ?>
        <span class="sep">→</span>
        <?php if (!empty($crumb['url'])): ?>
            <a href="<?= e($crumb['url']) ?>"><?= e($crumb['label']) ?></a>
        <?php else: ?>
            <span><?= e($crumb['label']) ?></span>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>
<script type="application/ld+json">
<?= json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>

</script>
