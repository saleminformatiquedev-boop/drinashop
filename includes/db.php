<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// includes/db.php
$is_local = (in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']) || php_sapi_name() === 'cli-server' || strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false);
define('BASE_URL', $is_local ? '' : '/drinashop');
$db_file = __DIR__ . '/../database.sqlite';
$needs_init = !file_exists($db_file);

try {
    $db = new PDO('sqlite:' . $db_file);
    // Activer les exceptions pour PDO
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($needs_init) {
        // Initialisation des tables
        $db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                password TEXT NOT NULL,
                role TEXT DEFAULT 'client',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // Table des commandes
        $db->exec("
            CREATE TABLE orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NULL,
                total REAL NOT NULL,
                status TEXT DEFAULT 'En attente',
                shipping_address TEXT NOT NULL,
                phone TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            );
        ");

        // Table des articles de commande
        $db->exec("
            CREATE TABLE order_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                order_id INTEGER,
                product_id INTEGER,
                product_name TEXT NOT NULL,
                quantity INTEGER NOT NULL,
                price REAL NOT NULL,
                FOREIGN KEY (order_id) REFERENCES orders(id)
            );
        ");

        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Administrateur', 'admin@drinashop.com', $admin_password, 'admin']);
    }

    // Assurer l'existence de la table settings
    $db->exec("CREATE TABLE IF NOT EXISTS settings (key TEXT PRIMARY KEY, value TEXT NOT NULL)");
    $stmt = $db->query("SELECT count(*) FROM settings WHERE key = 'currency'");
    if ($stmt->fetchColumn() == 0) {
        $db->exec("INSERT INTO settings (key, value) VALUES ('currency', '€')");
    }

    // Assurer l'existence de la table products
    $db->exec("
        CREATE TABLE IF NOT EXISTS products (
            id TEXT PRIMARY KEY,
            title TEXT NOT NULL,
            description TEXT,
            price REAL NOT NULL,
            promo_price REAL DEFAULT NULL,
            image TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");
    
    // Add promo_price column if it doesn't exist (for existing DBs)
    try {
        $db->exec("ALTER TABLE products ADD COLUMN promo_price REAL DEFAULT NULL");
    } catch (PDOException $e) {
        // Ignore error if column already exists
    }

    // Table slider
    $db->exec("
        CREATE TABLE IF NOT EXISTS slider (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            image_url TEXT NOT NULL,
            title TEXT,
            subtitle TEXT,
            link_url TEXT,
            link_text TEXT,
            slide_order INTEGER DEFAULT 0
        );
    ");

    $stmt = $db->query("SELECT count(*) FROM slider");
    if ($stmt->fetchColumn() == 0) {
        $db->exec("INSERT INTO slider (image_url, title, subtitle, link_url, link_text, slide_order) VALUES 
        ('https://images.unsplash.com/photo-1618220179428-22790b46a013?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80', 'Nouvelle Collection', 'Découvrez nos nouveautés exclusives.', '/boutique', 'Voir les produits', 1),
        ('https://images.unsplash.com/photo-1556905055-8f358a7a47b2?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80', 'Qualité Premium', 'Des articles soigneusement sélectionnés.', '/about', 'En savoir plus', 2),
        ('https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=2000&q=80', 'Promotions', 'Profitez de nos offres exceptionnelles.', '/boutique', 'Profiter des offres', 3)");
    }

} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

function get_currency() {
    global $db;
    try {
        $stmt = $db->query("SELECT value FROM settings WHERE key = 'currency'");
        $result = $stmt->fetch();
        return $result ? $result['value'] : 'DT';
    } catch (Exception $e) {
        return 'DT';
    }
}

function get_product_by_id($id) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . BASE_URL . '/');
        exit;
    }
}
?>
