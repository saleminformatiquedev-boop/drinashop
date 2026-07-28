<?php
session_start();

$db_file = __DIR__ . '/database.sqlite';
$db = new PDO('sqlite:' . $db_file);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$is_local = (in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']) || php_sapi_name() === 'cli-server' || strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false);
if (!defined('BASE_URL')) {
    define('BASE_URL', $is_local ? '' : '/drinashop');
}

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

// Fetch all products with pagination
if ($is_logged_in) {
    $page = max(1, intval($_GET['page'] ?? 1));
    $perPage = 10;
    $offset = ($page - 1) * $perPage;

    $totalStmt = $db->query("SELECT COUNT(*) FROM products");
    $totalProducts = $totalStmt->fetchColumn();
    $totalPages = ceil($totalProducts / $perPage);

    $stmt = $db->prepare("SELECT * FROM products ORDER BY title ASC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
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
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .ql-container { font-family: 'Inter', sans-serif; font-size: 1rem; }
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
            <h2 class="text-2xl font-semibold">Liste des Produits (<?= $totalProducts ?>)</h2>
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
                                <img src="<?= BASE_URL . htmlspecialchars($p['image']) ?>" alt="" class="w-full h-full rounded object-cover shadow border">
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
                            <button onclick="openEdit('<?= htmlspecialchars(rawurlencode(json_encode($p))) ?>')" class="text-blue-500 hover:text-blue-700 mr-3 font-semibold">Éditer</button>
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
        
        <?php if ($totalPages > 1): ?>
        <div class="flex justify-center mt-6 gap-2">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100">&laquo;</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?>" class="px-3 py-1 <?= $i === $page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-100' ?> rounded">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100">&raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
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
                        <div id="editor-description" class="h-32 bg-white"></div>
                        <input type="hidden" name="description" id="field_description">
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Image Principale (URL ou chemin)</label>
                        <input type="text" name="image" id="field_image" class="w-full p-2 border rounded focus:border-blue-500 outline-none" oninput="document.getElementById('modal_image_preview').src = (this.value.startsWith('/') ? '<?= BASE_URL ?>' : '') + this.value; document.getElementById('modal_image_preview').style.display = this.value ? 'block' : 'none';">
                        <img id="modal_image_preview" src="" alt="Aperçu" class="mt-2 w-24 h-24 object-cover rounded shadow" style="display: none;">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Images Supplémentaires (JSON)</label>
                        <textarea name="extra_images" id="field_extra_images" rows="2" class="w-full p-2 border rounded focus:border-blue-500 outline-none font-mono text-xs"></textarea>
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
                        <div id="editor-description_en" class="h-24 bg-white"></div>
                        <input type="hidden" name="description_en" id="field_description_en">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Description (AR)</label>
                        <div id="editor-description_ar" class="h-24 bg-white" dir="rtl"></div>
                        <input type="hidden" name="description_ar" id="field_description_ar">
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

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script>
        // Init Quill Editors
        const quillConfig = {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        };
        const quillDesc = new Quill('#editor-description', quillConfig);
        const quillDescEn = new Quill('#editor-description_en', quillConfig);
        const quillDescAr = new Quill('#editor-description_ar', {
            theme: 'snow',
            modules: { toolbar: [['bold', 'italic', 'underline', 'strike'], [{'list': 'ordered'}, {'list': 'bullet'}], ['clean']] }
        });
        quillDescAr.format('direction', 'rtl');
        quillDescAr.format('align', 'right');

        // Form Submit
        document.getElementById('productForm').onsubmit = function() {
            document.getElementById('field_description').value = quillDesc.root.innerHTML;
            document.getElementById('field_description_en').value = quillDescEn.root.innerHTML;
            document.getElementById('field_description_ar').value = quillDescAr.root.innerHTML;
            return true;
        };

        function openModal(action) {
            document.getElementById('productModal').classList.remove('hidden');
            document.getElementById('formAction').value = action;
            if (action === 'add') {
                document.getElementById('modalTitle').innerText = 'Ajouter un Produit';
                document.getElementById('productForm').reset();
                document.getElementById('field_id').readOnly = false;
                quillDesc.root.innerHTML = '';
                quillDescEn.root.innerHTML = '';
                quillDescAr.root.innerHTML = '';
            }
        }

        function closeModal() {
            document.getElementById('productModal').classList.add('hidden');
        }

        function openEdit(productStr) {
            let product;
            if (typeof productStr === 'string') {
                product = JSON.parse(decodeURIComponent(productStr));
            } else {
                product = productStr;
            }
            openModal('edit');
            document.getElementById('modalTitle').innerText = 'Modifier le Produit';
            document.getElementById('original_id').value = product.id;
            document.getElementById('field_id').readOnly = true;
            
            document.getElementById('field_id').value = product.id;
            document.getElementById('field_title').value = product.title || '';
            document.getElementById('field_title_en').value = product.title_en || '';
            document.getElementById('field_title_ar').value = product.title_ar || '';
            quillDescEn.root.innerHTML = product.description_en || '';
            quillDescAr.root.innerHTML = product.description_ar || '';
            
            document.getElementById('field_price').value = product.price;
            document.getElementById('field_promo_price').value = product.promo_price || '';
            document.getElementById('field_stock').value = product.stock || 0;
            document.getElementById('field_category').value = product.category || '';
            document.getElementById('field_image').value = product.image || '';
            document.getElementById('field_extra_images').value = product.extra_images || '[]';
            
            if (product.image) {
                document.getElementById('modal_image_preview').src = (product.image.startsWith('/') ? '<?= BASE_URL ?>' : '') + product.image;
                document.getElementById('modal_image_preview').style.display = 'block';
            } else {
                document.getElementById('modal_image_preview').style.display = 'none';
            }
            
            quillDesc.root.innerHTML = product.description || '';
        }
    </script>
<?php endif; ?>

</body>
</html>
