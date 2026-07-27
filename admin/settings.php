<?php
require_once __DIR__ . '/admin_header.php';

$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['currency'])) {
    $new_currency = trim($_POST['currency']);
    if (in_array($new_currency, ['€', '$', 'DT'])) {
        $stmt = $db->prepare("UPDATE settings SET value = ? WHERE key = 'currency'");
        $stmt->execute([$new_currency]);
        $success = "La devise a été mise à jour avec succès.";
    }
}

$current_currency = get_currency();
?>

<main style="padding: 3rem 2rem; min-height: 80vh; max-width: 800px; margin: 0 auto;">
    <h1 style="margin-bottom: 2rem; color: #fbbf24;">Paramètres de la boutique</h1>

    <?php if ($success): ?>
        <div style="background: rgba(34, 197, 94, 0.2); color: #22c55e; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2 style="margin-bottom: 1.5rem;">Devise</h2>
        <form method="POST" action="settings.php" style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div class="form-group">
                <label>Devise d'affichage</label>
                <select name="currency" style="width: 100%; padding: 0.8rem; border-radius: 4px; background: var(--bg-light); border: 1px solid var(--border-color); color: var(--text-color); font-size: 1.1rem; font-family: inherit;">
                    <option value="€" <?= $current_currency === '€' ? 'selected' : '' ?>>Euro (€)</option>
                    <option value="$" <?= $current_currency === '$' ? 'selected' : '' ?>>Dollar ($)</option>
                    <option value="DT" <?= $current_currency === 'DT' ? 'selected' : '' ?>>Dinar Tunisien (DT)</option>
                </select>
            </div>
            <button type="submit" style="background: var(--primary-color); color: #fff; padding: 1rem; border: none; border-radius: 10px; font-weight: bold; cursor: pointer; transition: background 0.3s ease; width: fit-content;">Enregistrer les paramètres</button>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
