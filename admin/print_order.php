<?php
require_once __DIR__ . '/../includes/db.php';
requireAdmin();

if (!isset($_GET['id'])) {
    die("ID de commande manquant.");
}

$order_id = intval($_GET['id']);

// Fetch order
$stmt = $db->prepare("
    SELECT o.*, u.name as user_name, u.email as user_email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    die("Commande introuvable.");
}

// Fetch items
$stmtItems = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmtItems->execute([$order_id]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture - Commande #<?= $order['id'] ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 2rem;
            background: #fff;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #22c55e;
            padding-bottom: 1rem;
            margin-bottom: 2rem;
        }
        .header h1 {
            color: #22c55e;
            margin: 0;
        }
        .invoice-details {
            text-align: right;
        }
        .invoice-details h2 {
            margin: 0 0 0.5rem 0;
            color: #555;
        }
        .customer-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            background: #f9f9f9;
            padding: 1.5rem;
            border-radius: 8px;
        }
        .customer-info div {
            width: 48%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }
        table th, table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        table th {
            background: #f9f9f9;
            color: #555;
        }
        .total-row td {
            font-weight: bold;
            font-size: 1.2rem;
            background: #f0fdf4;
            color: #166534;
        }
        .comment-box {
            border: 1px solid #e2e8f0;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            background: #f8fafc;
        }
        .footer {
            text-align: center;
            color: #777;
            font-size: 0.9rem;
            border-top: 1px solid #eee;
            padding-top: 1rem;
        }
        
        /* Options d'impression */
        @media print {
            body { padding: 0; }
            @page { margin: 1cm; }
            .no-print { display: none !important; }
        }
        
        .btn-print {
            display: inline-block;
            background: #22c55e;
            color: #fff;
            padding: 0.8rem 1.5rem;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-bottom: 2rem;
            cursor: pointer;
            border: none;
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="btn-print no-print">🖨️ Imprimer la facture</button>

    <div class="header">
        <div>
            <h1>DRINASHOP</h1>
            <p>L'Épicerie fine de référence</p>
        </div>
        <div class="invoice-details">
            <h2>FACTURE</h2>
            <p><strong>N° Commande :</strong> #<?= $order['id'] ?></p>
            <p><strong>Date :</strong> <?= date('d/m/Y à H:i', strtotime($order['created_at'])) ?></p>
            <p><strong>Statut :</strong> <?= htmlspecialchars($order['status']) ?></p>
        </div>
    </div>

    <div class="customer-info">
        <div>
            <h3 style="margin-top: 0;">Informations Client</h3>
            <?php if ($order['user_id']): ?>
                <p><strong>Nom :</strong> <?= htmlspecialchars($order['user_name']) ?></p>
                <p><strong>Email :</strong> <?= htmlspecialchars($order['user_email']) ?></p>
            <?php else: ?>
                <p>Client Invité</p>
            <?php endif; ?>
            <p><strong>Téléphone :</strong> <?= htmlspecialchars($order['phone']) ?></p>
        </div>
        <div>
            <h3 style="margin-top: 0;">Adresse de Livraison</h3>
            <p style="white-space: pre-line;"><?= htmlspecialchars($order['shipping_address']) ?></p>
        </div>
    </div>

    <?php if (!empty($order['customer_comment'])): ?>
    <div class="comment-box">
        <h3 style="margin-top: 0; color: #475569;">Commentaire de la commande :</h3>
        <p style="margin-bottom: 0; white-space: pre-wrap; font-style: italic; color: #334155;"><?= htmlspecialchars($order['customer_comment']) ?></p>
    </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Prix Unitaire</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['product_name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td><?= format_price($item['price']) ?></td>
                <td><?= format_price($item['price'] * $item['quantity']) ?></td>
            </tr>
            <?php endforeach; ?>
            
            <tr class="total-row">
                <td colspan="3" style="text-align: right;">TOTAL À PAYER</td>
                <td><?= format_price($order['total']) ?></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Merci pour votre commande sur Drinashop.</p>
        <p>Pour toute question, veuillez nous contacter via notre site internet.</p>
    </div>

    <script>
        // Lance l'impression automatiquement si l'utilisateur l'autorise
        window.onload = function() {
            // Optionnel : décommenter la ligne suivante pour ouvrir la boîte d'impression auto.
            window.print();
        }
    </script>
</body>
</html>
