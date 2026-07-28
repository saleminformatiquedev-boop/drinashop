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
            
            // Handle file upload
            if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/public/imagesProduits/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $filename = uniqid('prod_') . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', basename($_FILES['image_file']['name']));
                $targetFile = $uploadDir . $filename;
                
                if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) {
                    $image = '/public/imagesProduits/' . $filename;
                }
            } elseif ($action === 'edit' && empty($image)) {
                $original_id = $_POST['original_id'];
                $stmtImg = $db->prepare("SELECT image FROM products WHERE id = ?");
                $stmtImg->execute([$original_id]);
                $oldImage = $stmtImg->fetchColumn();
                if ($oldImage) {
                    $image = $oldImage;
                }
            }

            // --- EXTRA IMAGES LOGIC ---
            $extra_images = [];
            if ($action === 'edit') {
                $original_id = $_POST['original_id'];
                $stmtImg = $db->prepare("SELECT extra_images FROM products WHERE id = ?");
                $stmtImg->execute([$original_id]);
                $oldExtras = $stmtImg->fetchColumn();
                if ($oldExtras) {
                    $extra_images = json_decode($oldExtras, true) ?: [];
                }
            }
            
            if (isset($_POST['extra_images']) && is_string($_POST['extra_images'])) {
                $parsed = json_decode($_POST['extra_images'], true);
                if (is_array($parsed)) {
                    $extra_images = $parsed; // User manual JSON edit priority
                }
            }
            
            if (isset($_FILES['extra_images_files']) && !empty($_FILES['extra_images_files']['name'][0])) {
                $uploadDir = __DIR__ . '/public/imagesProduits/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                foreach ($_FILES['extra_images_files']['name'] as $key => $name) {
                    if ($_FILES['extra_images_files']['error'][$key] === UPLOAD_ERR_OK) {
                        $filename = uniqid('prod_extra_') . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', basename($name));
                        $targetFile = $uploadDir . $filename;
                        if (move_uploaded_file($_FILES['extra_images_files']['tmp_name'][$key], $targetFile)) {
                            $extra_images[] = '/public/imagesProduits/' . $filename;
                        }
                    }
                }
            }
            $extra_images_json = json_encode($extra_images);
            
            if ($action === 'add') {
                $stmt = $db->prepare("INSERT INTO products (id, title, description, price, promo_price, stock, category, image, title_en, title_ar, description_en, description_ar, extra_images) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$id, $title, $description, $price, $promo_price, $stock, $category, $image, $title_en, $title_ar, $description_en, $description_ar, $extra_images_json]);
                $msg = "Produit ajouté avec succès.";
            } else {
                $original_id = $_POST['original_id'];
                $stmt = $db->prepare("UPDATE products SET id=?, title=?, description=?, price=?, promo_price=?, stock=?, category=?, image=?, title_en=?, title_ar=?, description_en=?, description_ar=?, extra_images=? WHERE id=?");
                $stmt->execute([$id, $title, $description, $price, $promo_price, $stock, $category, $image, $title_en, $title_ar, $description_en, $description_ar, $extra_images_json, $original_id]);

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
            
            <form method="POST" id="productForm" enctype="multipart/form-data">
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
                    
                    <div class="col-span-2 relative">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Description</label>
                        <button type="button" onclick="toggleFullScreen('editor-description-wrapper')" class="absolute top-0 right-0 text-xs bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded">Plein écran</button>
                        <div id="editor-description-wrapper" class="bg-white">
                            <button type="button" onclick="toggleFullScreen('editor-description-wrapper')" class="hidden fs-close absolute top-4 right-4 bg-red-500 text-white px-4 py-2 rounded z-[10001]">Fermer le plein écran</button>
                            <div id="editor-description" class="h-32"></div>
                        </div>
                        <input type="hidden" name="description" id="field_description">
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Image Principale</label>
                        <input type="text" name="image" id="field_image" class="w-full p-2 border rounded focus:border-blue-500 outline-none text-xs mb-2" placeholder="URL ou chemin..." oninput="document.getElementById('modal_image_preview').src = (this.value.startsWith('/') ? '<?= BASE_URL ?>' : '') + this.value; document.getElementById('modal_image_preview').style.display = this.value ? 'block' : 'none';">
                        <div class="text-xs text-gray-500 mb-1">Ou télécharger depuis votre appareil :</div>
                        <input type="file" name="image_file" accept="image/*" class="w-full text-xs border rounded p-1">
                        <img id="modal_image_preview" src="" alt="Aperçu" class="mt-2 w-24 h-24 object-cover rounded shadow" style="display: none;">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Images Supplémentaires</label>
                        <textarea name="extra_images" id="field_extra_images" rows="2" class="w-full p-2 border rounded focus:border-blue-500 outline-none font-mono text-xs mb-2" placeholder='["/public/...", ...]' oninput="updateExtraImagesPreview(this.value)"></textarea>
                        <div id="extra_images_preview" class="flex gap-2 flex-wrap mb-2"></div>
                        <div class="text-xs text-gray-500 mb-1">Télécharger plus d'images :</div>
                        <input type="file" name="extra_images_files[]" multiple accept="image/*" class="w-full text-xs border rounded p-1">
                    </div>

                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Titre (EN)</label>
                        <input type="text" name="title_en" id="field_title_en" class="w-full p-2 border rounded focus:border-blue-500 outline-none">
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Titre (AR)</label>
                        <input type="text" name="title_ar" id="field_title_ar" class="w-full p-2 border rounded focus:border-blue-500 outline-none" dir="rtl">
                    </div>
                    <div class="col-span-2 sm:col-span-1 relative">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Description (EN)</label>
                        <button type="button" onclick="toggleFullScreen('editor-description_en-wrapper')" class="absolute top-0 right-0 text-xs bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded">Plein écran</button>
                        <div id="editor-description_en-wrapper" class="bg-white">
                            <button type="button" onclick="toggleFullScreen('editor-description_en-wrapper')" class="hidden fs-close absolute top-4 right-4 bg-red-500 text-white px-4 py-2 rounded z-[10001]">Fermer le plein écran</button>
                            <div id="editor-description_en" class="h-24"></div>
                        </div>
                        <input type="hidden" name="description_en" id="field_description_en">
                    </div>
                    <div class="col-span-2 sm:col-span-1 relative">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Description (AR)</label>
                        <button type="button" onclick="toggleFullScreen('editor-description_ar-wrapper')" class="absolute top-0 left-0 text-xs bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded">Plein écran</button>
                        <div id="editor-description_ar-wrapper" class="bg-white" dir="rtl">
                            <button type="button" onclick="toggleFullScreen('editor-description_ar-wrapper')" class="hidden fs-close absolute top-4 left-4 bg-red-500 text-white px-4 py-2 rounded z-[10001]">Fermer le plein écran</button>
                            <div id="editor-description_ar" class="h-24"></div>
                        </div>
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
    <style>
        .fs-mode {
            position: fixed !important;
            top: 0; left: 0; right: 0; bottom: 0;
            z-index: 10000;
            background: white;
            padding: 4rem 1rem 1rem 1rem;
        }
        .fs-mode .ql-container {
            height: calc(100vh - 120px) !important;
        }
        .fs-mode .fs-close {
            display: block !important;
        }
    </style>
    <script>
        function updateExtraImagesPreview(jsonStr) {
            const container = document.getElementById('extra_images_preview');
            container.innerHTML = '';
            try {
                const images = JSON.parse(jsonStr);
                if (Array.isArray(images)) {
                    images.forEach(img => {
                        const imgUrl = (img.startsWith('/') ? '<?= BASE_URL ?>' : '') + img;
                        const el = document.createElement('img');
                        el.src = imgUrl;
                        el.className = 'w-16 h-16 object-cover rounded shadow';
                        container.appendChild(el);
                    });
                }
            } catch (e) {
                // Invalid JSON, don't update preview
            }
        }

        function toggleFullScreen(wrapperId) {
            document.getElementById(wrapperId).classList.toggle('fs-mode');
        }
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
            
            updateExtraImagesPreview(product.extra_images || '[]');
            
            quillDesc.root.innerHTML = product.description || '';
        }
    </script>
<?php endif; ?>

</body>
</html>
