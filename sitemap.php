<?php
require_once __DIR__ . '/includes/db.php';

header("Content-Type: text/xml;charset=iso-8859-1");

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$base_url = $protocol . $_SERVER['HTTP_HOST'] . BASE_URL;

// Core pages
$pages = [
    '/',
    '/boutique',
    '/about',
    '/contact'
];

foreach ($pages as $page) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($base_url . $page) . "</loc>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>" . ($page === '/' ? '1.0' : '0.8') . "</priority>\n";
    echo "  </url>\n";
}

// Categories
$stmt = $db->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL AND category != ''");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($categories as $category) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($base_url . '/boutique?category=' . urlencode($category)) . "</loc>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>0.7</priority>\n";
    echo "  </url>\n";
}

echo '</urlset>';
?>
