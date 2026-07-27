<?php
require_once __DIR__ . '/../includes/db.php';
requireAdmin();

$current_admin_page = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$current_admin_page = trim($current_admin_page, '/');
if ($current_admin_page === 'admin') $current_admin_page = 'admin/index';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="admin-nav" style="background: var(--bg-light); border-bottom: 1px solid var(--border-color); padding: 1rem 2rem; display: flex; gap: 2rem; position: sticky; top: 80px; z-index: 98; overflow-x: auto; white-space: nowrap; -webkit-overflow-scrolling: touch;">
    <a href="<?= BASE_URL ?>/admin/" style="color: <?= $current_admin_page === 'admin/index' ? 'var(--primary-color)' : 'var(--text-color)' ?>; text-decoration: none; font-weight: bold;">Vue d'ensemble</a>
    <a href="<?= BASE_URL ?>/admin/orders" style="color: <?= $current_admin_page === 'admin/orders' ? 'var(--primary-color)' : 'var(--text-color)' ?>; text-decoration: none; font-weight: bold;">Commandes</a>
    <a href="<?= BASE_URL ?>/admin/users" style="color: <?= $current_admin_page === 'admin/users' ? 'var(--primary-color)' : 'var(--text-color)' ?>; text-decoration: none; font-weight: bold;">Utilisateurs</a>
    <a href="<?= BASE_URL ?>/admin/settings" style="color: <?= $current_admin_page === 'admin/settings' ? 'var(--primary-color)' : 'var(--text-color)' ?>; text-decoration: none; font-weight: bold;">Paramètres</a>
    <a href="<?= BASE_URL ?>/admin/slider" style="color: <?= $current_admin_page === 'admin/slider' ? 'var(--primary-color)' : 'var(--text-color)' ?>; text-decoration: none; font-weight: bold;">Slider</a>
    <a href="<?= BASE_URL ?>/admin/products" style="color: <?= $current_admin_page === 'admin/products' ? 'var(--primary-color)' : 'var(--text-color)' ?>; text-decoration: none; font-weight: bold;">Produits</a>
</div>
