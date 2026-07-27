<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        // Vérifier si l'email existe
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Cet email est déjà utilisé.';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$name, $email, $hashed])) {
                $success = 'Inscription réussie ! Vous pouvez maintenant vous connecter.';
            } else {
                $error = 'Une erreur est survenue lors de l\'inscription.';
            }
        }
    }
}
?>

<main style="padding-top: 4rem; min-height: 80vh; display: flex; align-items: center; justify-content: center;">
    <div class="auth-container">
        <h1 style="text-align: center; margin-bottom: 2rem;"><?= __('register') ?></h1>
        
        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.2); color: #ef4444; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; text-align: center;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div style="background: rgba(34, 197, 94, 0.2); color: #22c55e; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; text-align: center;">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/register" style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div class="form-group">
                <label><?= __('name') ?></label>
                <input type="text" name="name" required>
            </div>
            <div class="form-group">
                <label><?= __('email_address') ?></label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label><?= __('password') ?></label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; margin-top: 1rem;"><?= __('register') ?></button>
        </form>
        <p style="text-align: center; margin-top: 1.5rem; color: var(--text-muted);"><?= __('has_account') ?> <a href="<?= BASE_URL ?>/login" style="color: var(--primary-color); text-decoration: none; font-weight: bold;"><?= __('login') ?></a></p>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
