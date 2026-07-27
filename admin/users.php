<?php
require_once __DIR__ . '/admin_header.php';

$stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<main style="padding: 3rem 2rem; min-height: 80vh; max-width: 1200px; margin: 0 auto;">
    <h1 style="margin-bottom: 2rem; color: #fbbf24;">Utilisateurs inscrits</h1>

    <div class="card" style="padding: 0; overflow: hidden;">
      <div style="overflow-x: auto; width: 100%;">
        <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 600px;">
            <thead>
                <tr style="background: var(--bg-cream); border-bottom: 1px solid var(--border-color);">
                    <th style="padding: 1rem; color: var(--secondary-color);">ID</th>
                    <th style="padding: 1rem; color: var(--secondary-color);">Nom</th>
                    <th style="padding: 1rem; color: var(--secondary-color);">Email</th>
                    <th style="padding: 1rem; color: var(--secondary-color);">Rôle</th>
                    <th style="padding: 1rem; color: var(--secondary-color);">Inscription</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <td style="padding: 1rem; color: #94a3b8;">#<?= $user['id'] ?></td>
                    <td style="padding: 1rem; font-weight: bold;"><?= htmlspecialchars($user['name']) ?></td>
                    <td style="padding: 1rem; color: #94a3b8;"><?= htmlspecialchars($user['email']) ?></td>
                    <td style="padding: 1rem;">
                        <?php if ($user['role'] === 'admin'): ?>
                            <span style="background: rgba(251, 191, 36, 0.2); color: #fbbf24; padding: 0.3rem 0.8rem; border-radius: 9999px; font-size: 0.9rem;">Admin</span>
                        <?php else: ?>
                            <span style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; padding: 0.3rem 0.8rem; border-radius: 9999px; font-size: 0.9rem;">Client</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 1rem; color: #94a3b8;"><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
      </div>
    </div>
</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
