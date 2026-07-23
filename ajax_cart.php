<?php
session_start();
header('Content-Type: application/json');

require_once 'includes/csv_parser.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_GET['action'] ?? '';

if ($action === 'add') {
    $id = $_POST['id'] ?? '';
    $product = get_product_by_id($id);
    
    if ($product) {
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['quantity']++;
        } else {
            $_SESSION['cart'][$id] = [
                'id' => $product['id'],
                'title' => $product['title'],
                'price' => $product['price'],
                'image' => $product['image'],
                'quantity' => 1
            ];
        }
        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['success' => false, 'error' => 'Product not found']);
    exit;
}

if ($action === 'remove') {
    $id = $_POST['id'] ?? '';
    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'update') {
    $id = $_POST['id'] ?? '';
    $qty = (int)($_POST['quantity'] ?? 1);
    
    if (isset($_SESSION['cart'][$id])) {
        if ($qty > 0) {
            $_SESSION['cart'][$id]['quantity'] = $qty;
        } else {
            unset($_SESSION['cart'][$id]);
        }
    }
    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'get') {
    $total = 0;
    $count = 0;
    $items = array_values($_SESSION['cart']);
    foreach ($items as $item) {
        $total += $item['price'] * $item['quantity'];
        $count += $item['quantity'];
    }
    echo json_encode([
        'items' => $items,
        'total' => $total,
        'count' => $count
    ]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Invalid action']);
