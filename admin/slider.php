<?php
require_once __DIR__ . '/../includes/db.php';
requireAdmin();

$current_admin_page = 'admin/slider';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_slide') {
        $id = $_POST['id'] ?? null;
        $image_url = $_POST['image_url'] ?? '';
        $title = $_POST['title'] ?? '';
        $subtitle = $_POST['subtitle'] ?? '';
        $link_url = $_POST['link_url'] ?? '';
        $link_text = $_POST['link_text'] ?? '';
        
        if ($id) {
            $stmt = $db->prepare("UPDATE slider SET image_url = ?, title = ?, subtitle = ?, link_url = ?, link_text = ? WHERE id = ?");
            $stmt->execute([$image_url, $title, $subtitle, $link_url, $link_text, $id]);
        }
        header('Location: ' . BASE_URL . '/admin/slider');
        exit;
    }
}

$stmt = $db->query("SELECT * FROM slider ORDER BY slide_order ASC");
$slides = $stmt->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/admin_header.php';
?>

<main style="padding: 3rem 2rem; min-height: 80vh; max-width: 1200px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="color: var(--secondary-color);">Gestion du Slider Principal</h1>
    </div>

    <div class="card" style="padding: 0; overflow: hidden; margin-bottom: 2rem;">
      <div style="overflow-x: auto; width: 100%;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 600px;">
            <thead>
                <tr style="background: var(--bg-cream); border-bottom: 1px solid var(--border-color);">
                    <th style="padding: 1rem; color: var(--secondary-color);">Ordre</th>
                    <th style="padding: 1rem; color: var(--secondary-color);">Aperçu</th>
                    <th style="padding: 1rem; color: var(--secondary-color);">Titre / Sous-titre</th>
                    <th style="padding: 1rem; color: var(--secondary-color);">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($slides as $slide): ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 1rem; color: var(--text-color);">Slide <?= $slide['slide_order'] ?></td>
                    <td style="padding: 1rem;">
                        <img src="<?= htmlspecialchars($slide['image_url']) ?>" alt="Slide <?= $slide['id'] ?>" style="width: 150px; height: 80px; object-fit: cover; border-radius: 5px; border: 1px solid var(--border-color);">
                    </td>
                    <td style="padding: 1rem;">
                        <strong><?= htmlspecialchars($slide['title']) ?></strong><br>
                        <span style="color: var(--text-muted); font-size: 0.9rem;"><?= htmlspecialchars($slide['subtitle']) ?></span>
                    </td>
                    <td style="padding: 1rem;">
                        <button onclick='editSlide(<?= json_encode($slide) ?>)' class="btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Modifier</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
      </div>
    </div>
</main>

<!-- Modal Édition Slide -->
<div id="slide-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div class="card" style="width: 90%; max-width: 500px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 id="modal-title" style="color: var(--secondary-color);">Modifier le Slide</h2>
            <button onclick="document.getElementById('slide-modal').style.display='none';" style="background: none; border: none; color: var(--text-muted); font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        
        <form method="POST" action="<?= BASE_URL ?>/admin/slider" style="display: flex; flex-direction: column; gap: 1rem;">
            <input type="hidden" name="action" value="save_slide">
            <input type="hidden" name="id" id="modal-id">
            
            <div class="form-group">
                <label>URL de l'image</label>
                <input type="url" name="image_url" id="modal-image" required>
            </div>
            <div class="form-group">
                <label>Titre principal</label>
                <input type="text" name="title" id="modal-title-input">
            </div>
            <div class="form-group">
                <label>Sous-titre</label>
                <input type="text" name="subtitle" id="modal-subtitle">
            </div>
            <div class="form-group">
                <label>Texte du bouton</label>
                <input type="text" name="link_text" id="modal-link-text">
            </div>
            <div class="form-group">
                <label>Lien du bouton</label>
                <input type="text" name="link_url" id="modal-link-url">
            </div>
            
            <button type="submit" class="btn-primary" style="width: 100%; margin-top: 1rem;">Enregistrer</button>
        </form>
    </div>
</div>

<script>
function editSlide(slide) {
    document.getElementById('modal-id').value = slide.id;
    document.getElementById('modal-image').value = slide.image_url;
    document.getElementById('modal-title-input').value = slide.title || '';
    document.getElementById('modal-subtitle').value = slide.subtitle || '';
    document.getElementById('modal-link-text').value = slide.link_text || '';
    document.getElementById('modal-link-url').value = slide.link_url || '';
    
    document.getElementById('slide-modal').style.display = 'flex';
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
