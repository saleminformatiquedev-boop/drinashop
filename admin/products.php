<?php
require_once __DIR__ . '/admin_header.php';

$success = '';
$error = '';

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add' || $action === 'edit') {
        $id = trim($_POST['id'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $promo_price = $_POST['promo_price'] !== '' ? floatval($_POST['promo_price']) : null;
        $description = trim($_POST['description'] ?? '');
        $image = trim($_POST['image'] ?? '');
        $stock = intval($_POST['stock'] ?? 0);
        
        // Handle file upload
        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../public/imagesProduits/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            // Sanitize filename to avoid issues
            $filename = uniqid('prod_') . '_' . preg_replace('/[^a-zA-Z0-9.-]/', '_', basename($_FILES['image_file']['name']));
            $targetFile = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) {
                $image = '/public/imagesProduits/' . $filename;
            }
        } elseif ($action === 'edit' && empty($image)) {
            // Keep old image if no new one provided
            $stmtImg = $db->prepare("SELECT image FROM products WHERE id = ?");
            $stmtImg->execute([$id]);
            $oldImage = $stmtImg->fetchColumn();
            if ($oldImage) {
                $image = $oldImage;
            }
        }
        
        // --- EXTRA IMAGES LOGIC ---
        $extra_images = [];
        if ($action === 'edit') {
            $stmtImg = $db->prepare("SELECT extra_images FROM products WHERE id = ?");
            $stmtImg->execute([$id]);
            $oldExtras = $stmtImg->fetchColumn();
            if ($oldExtras) {
                $extra_images = json_decode($oldExtras, true) ?: [];
            }
        }
        
        // Delete requested extra images
        if (isset($_POST['delete_extra_images']) && is_array($_POST['delete_extra_images'])) {
            foreach ($_POST['delete_extra_images'] as $imgToDelete) {
                $extra_images = array_filter($extra_images, function($img) use ($imgToDelete) {
                    return $img !== $imgToDelete;
                });
            }
            $extra_images = array_values($extra_images);
        }

        // Upload new extra images
        if (isset($_FILES['extra_images_files']) && !empty($_FILES['extra_images_files']['name'][0])) {
            $uploadDir = __DIR__ . '/../public/imagesProduits/';
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
        // --- END EXTRA IMAGES LOGIC ---
        
        if (empty($id) || empty($title) || $price <= 0) {
            $error = "ID, Titre et Prix valides sont obligatoires.";
        } else {
            if ($action === 'add') {
                $stmt = $db->prepare("SELECT count(*) FROM products WHERE id = ?");
                $stmt->execute([$id]);
                if ($stmt->fetchColumn() > 0) {
                    $error = "Ce produit (ID) existe déjà.";
                } else {
                    $stmt = $db->prepare("INSERT INTO products (id, title, category, description, price, promo_price, image, stock, extra_images) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    if ($stmt->execute([$id, $title, $category, $description, $price, $promo_price, $image, $stock, $extra_images_json])) {
                        $success = "Produit ajouté avec succès.";
                    }
                }
            } else {
                $stmt = $db->prepare("UPDATE products SET title = ?, category = ?, description = ?, price = ?, promo_price = ?, image = ?, stock = ?, extra_images = ? WHERE id = ?");
                if ($stmt->execute([$title, $category, $description, $price, $promo_price, $image, $stock, $extra_images_json, $id])) {
                    $success = "Produit mis à jour avec succès.";
                }
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if ($id) {
            $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
            if ($stmt->execute([$id])) {
                $success = "Produit supprimé.";
            }
        }
    }
}

// Fetch all products with pagination and search
$search = trim($_GET['search'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

if (!empty($search)) {
    $countStmt = $db->prepare("SELECT COUNT(*) FROM products WHERE title LIKE :search OR id LIKE :search");
    $countStmt->execute([':search' => '%' . $search . '%']);
} else {
    $countStmt = $db->query("SELECT COUNT(*) FROM products");
}
$totalProducts = $countStmt->fetchColumn();
$totalPages = ceil($totalProducts / $perPage);

if (!empty($search)) {
    $stmt = $db->prepare("SELECT * FROM products WHERE title LIKE :search OR id LIKE :search ORDER BY title ASC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
} else {
    $stmt = $db->prepare("SELECT * FROM products ORDER BY title ASC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
}

$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main style="padding: 2rem; max-width: 1200px; margin: 0 auto; min-height: 80vh;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <h1>Gestion des Produits</h1>
        <form method="GET" style="display: flex; gap: 0.5rem; flex: 1; max-width: 400px;">
            <input type="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher par ID ou Titre..." class="search-bar" style="flex: 1;">
            <button type="submit" class="btn-primary" style="padding: 0.5rem 1rem;">🔍</button>
        </form>
        <button onclick="document.getElementById('product-modal').style.display='flex'; clearModal();" style="background: var(--primary-color); color: #fff; border: none; padding: 0.8rem 1.5rem; border-radius: 10px; font-weight: bold; cursor: pointer; white-space: nowrap;">+ Ajouter un produit</button>
    </div>

    <?php if ($success): ?>
        <div style="background: rgba(34, 197, 94, 0.2); color: #22c55e; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div style="background: rgba(239, 68, 68, 0.2); color: #ef4444; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <div class="card" style="padding: 0; overflow: hidden;">
      <div style="overflow-x: auto; width: 100%; margin-top: 1rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 800px;">
            <thead>
                <tr style="background: var(--bg-cream); border-bottom: 1px solid var(--border-color);">
                    <th style="padding: 1rem; color: var(--secondary-color);">Image</th>
                    <th style="padding: 1rem; color: var(--secondary-color);">ID</th>
                    <th style="padding: 1rem; color: var(--secondary-color);">Titre</th>
                    <th style="padding: 1rem; color: var(--secondary-color);">Catégorie</th>
                    <th style="padding: 1rem; color: var(--secondary-color);">Prix</th>
                    <th style="padding: 1rem; color: var(--secondary-color);">Stock</th>
                    <th style="padding: 1rem; color: var(--secondary-color);">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 1rem;">
                        <img src="<?= BASE_URL ?><?= htmlspecialchars($p['image']) ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px; border: 1px solid rgba(255,255,255,0.1);">
                        <?php 
                        $extras = json_decode($p['extra_images'] ?? '[]', true) ?: [];
                        if (count($extras) > 0): ?>
                            <div style="font-size: 0.7rem; color: #94a3b8; margin-top: 0.2rem;">+<?= count($extras) ?> img</div>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 1rem; color: var(--text-color);"><?= htmlspecialchars($p['id']) ?></td>
                    <td style="padding: 1rem; font-weight: bold;"><?= htmlspecialchars($p['title']) ?></td>
                    <td style="padding: 1rem;"><?= htmlspecialchars($p['category'] ?? '-') ?></td>
                    <td style="padding: 1rem; color: var(--primary-color);">
                        <?php if ($p['promo_price']): ?>
                            <del style="color: var(--text-muted); font-size: 0.9em;"><?= number_format($p['price'], 2) ?> <?= htmlspecialchars(get_currency()) ?></del><br>
                            <span style="color: #ef4444; font-weight: bold;"><?= number_format($p['promo_price'], 2) ?> <?= htmlspecialchars(get_currency()) ?></span>
                        <?php else: ?>
                            <?= number_format($p['price'], 2) ?> <?= htmlspecialchars(get_currency()) ?>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 1rem;">
                        <?php if (isset($p['stock']) && $p['stock'] <= 0): ?>
                            <span style="color: #ef4444; font-weight: bold;">Rupture (0)</span>
                        <?php else: ?>
                            <span style="color: #22c55e; font-weight: bold;"><?= intval($p['stock']) ?> en stock</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 1rem; display: flex; gap: 0.5rem; align-items: center; height: 50px;">
                        <button type="button" onclick="editProduct('<?= htmlspecialchars(rawurlencode(json_encode($p))) ?>')" style="background: #3b82f6; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 5px; cursor: pointer;">Éditer</button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ce produit ?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($p['id']) ?>">
                            <button type="submit" style="background: #ef4444; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 5px; cursor: pointer;">Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination" style="margin-top: 2rem; display: flex; justify-content: center; gap: 0.5rem;">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="page-item">&laquo;</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="page-item <?= $i === $page ? 'active' : '' ?>">
                <?= $i ?>
            </a>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" class="page-item">&raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</main>

<!-- Modal pour Ajouter / Éditer -->
<div id="product-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div class="card" style="width: 90%; max-width: 800px; max-height: 90vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 id="modal-title" style="color: var(--secondary-color);">Ajouter un produit</h2>
            <button onclick="document.getElementById('product-modal').style.display='none';" style="background: none; border: none; color: var(--text-muted); font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        
        <form method="POST" action="<?= BASE_URL ?>/admin/products" enctype="multipart/form-data" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <input type="hidden" name="action" id="modal-action" value="add">
            
            <div class="form-group">
                <label>ID du produit (unique)</label>
                <input type="text" name="id" id="modal-id" required>
            </div>
            <div class="form-group">
                <label>Titre</label>
                <input type="text" name="title" id="modal-title-input" required>
            </div>
            <div class="form-group">
                <label>Catégorie</label>
                <select name="category" id="modal-category" required style="padding: 0.5rem; border-radius: 5px; border: 1px solid var(--border-color); background: var(--bg-cream); font-family: inherit;">
                    <option value="">-- Sélectionner --</option>
                    <option value="Abat-jours">Abat-jours</option>
                    <option value="Couffins">Couffins</option>
                    <option value="Lampadaire">Lampadaire</option>
                    <option value="Lanternes">Lanternes</option>
                    <option value="Miroirs">Miroirs</option>
                    <option value="Suspensions Luminaires">Suspensions Luminaires</option>
                </select>
            </div>
            <div class="form-group">
                <label>Prix</label>
                <input type="number" step="0.01" name="price" id="modal-price" required>
            </div>
            <div class="form-group">
                <label>Prix de promotion (optionnel)</label>
                <input type="number" step="0.01" name="promo_price" id="modal-promo-price">
            </div>
            <div class="form-group">
                <label>Stock (quantité)</label>
                <input type="number" name="stock" id="modal-stock" value="0" required>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Image du produit (URL ou Fichier)</label>
                <div style="display: flex; gap: 0.5rem; align-items: center; margin-bottom: 0.5rem;">
                    <input type="url" name="image" id="modal-image" placeholder="URL externe (ex: https://...)" style="flex: 1;" oninput="document.getElementById('main-image-preview').src = this.value; document.getElementById('main-image-preview').style.display = this.value ? 'block' : 'none';">
                </div>
                <img id="main-image-preview" src="" style="width:80px; height:80px; object-fit:cover; border-radius:5px; margin-bottom: 0.5rem; display: none; border: 1px solid var(--border-color);">
                <div style="font-size: 0.9rem; margin-bottom: 0.5rem; color: var(--text-muted);">Ou télécharger depuis votre ordinateur :</div>
                <input type="file" name="image_file" accept="image/*" style="border: 1px dashed var(--border-color); padding: 1rem; border-radius: 8px; width: 100%;">
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Images supplémentaires</label>
                <div id="existing-extra-images" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem; flex-wrap: wrap;"></div>
                <input type="file" name="extra_images_files[]" multiple accept="image/*" style="border: 1px dashed var(--border-color); padding: 1rem; border-radius: 8px; width: 100%;">
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
                <label>Description</label>
                <textarea name="description" id="modal-desc" rows="3"></textarea>
            </div>
            
            <button type="submit" style="grid-column: 1 / -1; background: var(--primary-color); color: #fff; padding: 1rem; border: none; border-radius: 10px; font-weight: bold; cursor: pointer; margin-top: 1rem;">Sauvegarder</button>
        </form>
    </div>
</div>

<script>
function clearModal() {
    document.getElementById('modal-title').innerText = "Ajouter un produit";
    document.getElementById('modal-action').value = "add";
    document.getElementById('modal-id').value = "";
    document.getElementById('modal-id').readOnly = false;
    document.getElementById('modal-title-input').value = "";
    document.getElementById('modal-category').value = "";
    document.getElementById('modal-price').value = "";
    document.getElementById('modal-promo-price').value = "";
    document.getElementById('modal-stock').value = "0";
    document.getElementById('modal-image').value = "";
    document.getElementById('modal-desc').value = "";
    document.getElementById('existing-extra-images').innerHTML = "";
    document.getElementById('main-image-preview').src = "";
    document.getElementById('main-image-preview').style.display = "none";
}

function editProduct(productStr) {
    let product;
    if (typeof productStr === 'string') {
        product = JSON.parse(decodeURIComponent(productStr));
    } else {
        product = productStr;
    }
    document.getElementById('modal-title').innerText = "Éditer un produit";
    document.getElementById('modal-action').value = "edit";
    document.getElementById('modal-id').value = product.id;
    document.getElementById('modal-id').readOnly = true; // Empêche de changer l'ID
    document.getElementById('modal-title-input').value = product.title;
    document.getElementById('modal-category').value = product.category || "";
    document.getElementById('modal-price').value = product.price;
    document.getElementById('modal-promo-price').value = product.promo_price || "";
    document.getElementById('modal-stock').value = product.stock || "0";
    document.getElementById('modal-image').value = product.image;
    document.getElementById('modal-desc').value = product.description;
    
    if (product.image) {
        document.getElementById('main-image-preview').src = `${window.BASE_URL || ''}${product.image}`;
        document.getElementById('main-image-preview').style.display = 'block';
    } else {
        document.getElementById('main-image-preview').style.display = 'none';
    }
    
    const extrasContainer = document.getElementById('existing-extra-images');
    extrasContainer.innerHTML = "";
    
    let extraImages = [];
    try {
        if (product.extra_images) extraImages = JSON.parse(product.extra_images);
    } catch(e) {}
    
    if (extraImages.length > 0) {
        extraImages.forEach(img => {
            const div = document.createElement('div');
            div.style.position = 'relative';
            div.innerHTML = `
                <img src="${window.BASE_URL || ''}${img}" style="width:60px; height:60px; object-fit:cover; border-radius:5px; border:1px solid #ccc;">
                <label style="display:block; font-size:0.8rem; text-align:center; color:red; cursor:pointer;">
                    <input type="checkbox" name="delete_extra_images[]" value="${img}"> Supprimer
                </label>
            `;
            extrasContainer.appendChild(div);
        });
    }
    
    document.getElementById('product-modal').style.display = 'flex';
}

// Fermer le modal en cliquant en dehors
window.onclick = function(event) {
    const modal = document.getElementById('product-modal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
