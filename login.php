<?php
require_once 'includes/db.php';

// Si déjà connecté, rediriger
if (isLoggedIn()) {
    header('Location: ' . BASE_URL . '/');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Login success
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            
            if ($user['role'] === 'admin') {
                header('Location: ' . BASE_URL . '/admin/');
            } else {
                header('Location: ' . BASE_URL . '/profil');
            }
            exit;
        } else {
            $error = 'Email ou mot de passe incorrect.';
        }
    }
}

require_once 'includes/header.php';
?>

<main style="padding-top: 4rem; min-height: 80vh; display: flex; align-items: center; justify-content: center;">
    <div class="auth-container">
        <h1 style="text-align: center; margin-bottom: 2rem;"><?= __('login') ?></h1>
        
        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.2); color: #ef4444; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; text-align: center;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/login" style="display: flex; flex-direction: column; gap: 1.5rem;">
            <div>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label><?= __('password') ?></label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-primary" style="width: 100%; margin-top: 1rem;"><?= __('login') ?></button>
        </form>
        <p style="text-align: center; margin-top: 1.5rem; color: var(--text-muted);"><?= __('no_account') ?> <a href="<?= BASE_URL ?>/register" style="color: var(--primary-color); text-decoration: none; font-weight: bold;"><?= __('register') ?></a></p>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
