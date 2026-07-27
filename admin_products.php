<?php
session_start();

$db_file = __DIR__ . '/database.sqlite';
$db = new PDO('sqlite:' . $db_file);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$ADMIN_PASSWORD = 'drinashop_admin';

// Handle login
if (isset($_POST['login'])) {
    if ($_POST['password'] === $ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $error = "Mot de passe incorrect.";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_products.php");
    exit;
}

$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;

// Handle CRUD operations if logged in
if ($is_logged_in && $_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['login'])) {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add' || $action === 'edit') {
            $id = $_POST['id']; // ID string (SKU)
            $title = $_POST['title'];
            $description = $_POST['description'];
            $price = floatval($_POST['price']);
            $promo_price = !empty($_POST['promo_price']) ? floatval($_POST['promo_price']) : null;
            $stock = intval($_POST['stock']);
            $category = $_POST['category'];
            $image = $_POST['image'];
            
            $title_en = $_POST['title_en'] ?? '';
            $title_ar = $_POST['title_ar'] ?? '';
            $description_en = $_POST['description_en'] ?? '';
            $description_ar = $_POST['description_ar'] ?? '';
            
            if ($action === 'add') {
                $stmt = $db->prepare("INSERT INTO products (id, title, description, price, promo_price, stock, category, image, title_en, title_ar, description_en, description_ar) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id, $title, $description, $price, $promo_price, $stock, $category, $image, $title_en, $title_ar, $description_en, $description_ar]);
                $msg = "Produit ajouté avec succès.";
            } else {
                $original_id = $_POST['original_id'];
                $stmt = $db->prepare("UPDATE products SET id=?, title=?, description=?, price=?, promo_price=?, stock=?, category=?, image=?, title_en=?, title_ar=?, description_en=?, description_ar=? WHERE id=?");
                $stmt->execute([$id, $title, $description, $price, $promo_price, $stock, $category, $image, $title_en, $title_ar, $description_en, $description_ar, $original_id]);
                $msg = "Produit mis à jour avec succès.";
            }
        } elseif ($action === 'delete') {
            $id = $_POST['id'];
            $stmt = $db->prepare("DELETE FROM products WHERE id=?");
            $stmt->execute([$id]);
            $msg = "Produit supprimé avec succès.";
        }
    } catch(Exception $e) {
        $error = "Erreur : " . $e->getMessage();
    }
}

// Fetch all products
if ($is_logged_in) {
    $stmt = $db->query("SELECT * FROM products ORDER BY title ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration Drinashop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
    </style>
</head>
<body class="text-gray-800">

<?php if (!$is_logged_in): ?>
    <div class="min-h-screen flex items-center justify-center bg-gray-100">
        <div class="bg-white p-8 rounded-lg shadow-xl w-96">
            <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Admin Login</h2>
            <?php if (isset($error)): ?>
                <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Mot de passe</label>
                    <input type="password" name="password" class="w-full p-3 border rounded focus:outline-none focus:border-blue-500" required>
                </div>
                <button type="submit" name="login" class="w-full bg-blue-600 text-white font-bold py-3 rounded hover:bg-blue-700 transition">Se connecter</button>
            </form>
        </div>
    </div>
<?php else: ?>

    <!-- Navbar -->
    <nav class="bg-gray-800 text-white p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Gestion des Produits</h1>
            <div class="space-x-4">
                <a href="/" target="_blank" class="text-gray-300 hover:text-white">Voir la boutique</a>
                <a href="?logout=1" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded font-semibold text-sm">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-4 mt-6">
        
        <?php if (isset($msg)): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 shadow-sm">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 shadow-sm">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold">Liste des Produits (<?= count($products) ?>)</h2>
            <button onclick="openModal('add')" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded shadow">
                + Ajouter un Produit
            </button>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Image</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID / Titre</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Prix</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Stock</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Catégorie</th>
                        <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4 border-b border-gray-200 text-sm">
                            <div class="w-12 h-12">
                                <img src="<?= htmlspecialchars($p['image']) ?>" alt="" class="w-full h-full rounded object-cover shadow border">
                            </div>
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 text-sm">
                            <p class="text-gray-900 font-bold"><?= htmlspecialchars($p['title']) ?></p>
                            <p class="text-gray-500 text-xs mt-1">ID: <?= htmlspecialchars($p['id']) ?></p>
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 text-sm">
                            <?php if ($p['promo_price']): ?>
                                <span class="line-through text-gray-400 mr-2"><?= number_format($p['price'], 2) ?></span>
                                <span class="text-red-500 font-bold"><?= number_format($p['promo_price'], 2) ?></span>
                            <?php else: ?>
                                <span class="font-bold text-gray-900"><?= number_format($p['price'], 2) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 text-sm">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $p['stock'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                <?= $p['stock'] ?>
                            </span>
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 text-sm">
                            <span class="text-gray-600"><?= htmlspecialchars($p['category']) ?></span>
                        </td>
                        <td class="px-5 py-4 border-b border-gray-200 text-sm">
                            <button onclick='openEdit(<?= json_encode($p) ?>)' class="text-blue-500 hover:text-blue-700 mr-3 font-semibold">Éditer</button>
                            <form method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700 font-semibold">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form -->
    <div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white w-full max-w-2xl rounded-lg shadow-2xl p-6 mx-4 max-h-[90vh] overflow-y-auto relative">
            <button type="button" onclick="closeModal()" class="absolute top-4 right-4 text-gray-500 hover:text-gray-800 text-2xl font-bold">&times;</button>
            <h2 id="modalTitle" class="text-2xl font-bold mb-6 text-gray-800">Ajouter un Produit</h2>
            
            <form method="POST" id="productForm">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="original_id" id="original_id" value="">
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">ID Produit (SKU)</label>
                        <input type="text" name="id" id="field_id" required class="w-full p-2 border rounded focus:border-blue-500 outline-none">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Titre</label>
                        <input type="text" name="title" id="field_title" required class="w-full p-2 border rounded focus:border-blue-500 outline-none">
                    </div>
                    
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Description</label>
                        <textarea name="description" id="field_description" rows="3" class="w-full p-2 border rounded focus:border-blue-500 outline-none"></textarea>
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Titre (EN)</label>
                        <input type="text" name="title_en" id="field_title_en" class="w-full p-2 border rounded focus:border-blue-500 outline-none">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Titre (AR)</label>
                        <input type="text" name="title_ar" id="field_title_ar" class="w-full p-2 border rounded focus:border-blue-500 outline-none" dir="rtl">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Description (EN)</label>
                        <textarea name="description_en" id="field_description_en" rows="2" class="w-full p-2 border rounded focus:border-blue-500 outline-none"></textarea>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Description (AR)</label>
                        <textarea name="description_ar" id="field_description_ar" rows="2" class="w-full p-2 border rounded focus:border-blue-500 outline-none" dir="rtl"></textarea>
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Prix (Standard)</label>
                        <input type="number" step="0.01" name="price" id="field_price" required class="w-full p-2 border rounded focus:border-blue-500 outline-none">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Prix Promo (Optionnel)</label>
                        <input type="number" step="0.01" name="promo_price" id="field_promo_price" class="w-full p-2 border rounded focus:border-blue-500 outline-none">
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Stock</label>
                        <input type="number" name="stock" id="field_stock" required value="0" class="w-full p-2 border rounded focus:border-blue-500 outline-none">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Catégorie</label>
                        <input type="text" name="category" id="field_category" class="w-full p-2 border rounded focus:border-blue-500 outline-none">
                    </div>

                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">URL de l'image (Ex: /public/imagesProduits/produits/image.jpg)</label>
                        <input type="text" name="image" id="field_image" required class="w-full p-2 border rounded focus:border-blue-500 outline-none">
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="button" onclick="closeModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">Annuler</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(mode) {
            document.getElementById('productModal').classList.remove('hidden');
            document.getElementById('formAction').value = mode;
            if (mode === 'add') {
                document.getElementById('modalTitle').innerText = 'Ajouter un Produit';
                document.getElementById('productForm').reset();
                document.getElementById('original_id').value = '';
            }
        }

        function closeModal() {
            document.getElementById('productModal').classList.add('hidden');
        }

        function openEdit(product) {
            openModal('edit');
            document.getElementById('modalTitle').innerText = 'Modifier le Produit';
            document.getElementById('original_id').value = product.id;
            
            document.getElementById('field_id').value = product.id;
            document.getElementById('field_title').value = product.title || '';
            document.getElementById('field_description').value = product.description || '';
            document.getElementById('field_title_en').value = product.title_en || '';
            document.getElementById('field_title_ar').value = product.title_ar || '';
            document.getElementById('field_description_en').value = product.description_en || '';
            document.getElementById('field_description_ar').value = product.description_ar || '';
            document.getElementById('field_price').value = product.price || '';
            document.getElementById('field_promo_price').value = product.promo_price || '';
            document.getElementById('field_stock').value = product.stock;
            document.getElementById('field_category').value = product.category || '';
            document.getElementById('field_image').value = product.image || '';
        }
    </script>
<?php endif; ?>

</body>
</html>
