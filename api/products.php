<?php
declare(strict_types=1);

require __DIR__ . '/auth/bootstrap.php';
auth_start_session();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    api_json(['error' => 'Method not allowed.'], 405);
}

$category = trim((string) ($_GET['category'] ?? ''));
if (strlen($category) > 120) {
    api_json(['error' => 'Category is too long.'], 400);
}

try {
    $pdo = api_db();
    $user = auth_current_user($pdo);
    $canViewStock = $user !== null && $user['role'] === 'admin';
    $sql = 'SELECT p.id, p.name, p.category, p.description, p.icon, p.price,
                   p.specs, p.image_paths, v.variant_id, v.label, v.sku, v.stock
            FROM products AS p
            LEFT JOIN product_variants AS v
              ON v.product_id = p.id AND v.is_active = 1
            WHERE p.is_active = 1';
    $params = [];
    if ($category !== '') {
        $sql .= ' AND p.category = :category';
        $params['category'] = $category;
    }
    $sql .= ' ORDER BY p.sort_order, p.id, v.label';

    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    $products = [];

    while ($row = $statement->fetch()) {
        $id = $row['id'];
        if (!isset($products[$id])) {
            $products[$id] = [
                'id' => $id,
                'name' => $row['name'],
                'category' => $row['category'],
                'description' => $row['description'],
                'icon' => $row['icon'],
                'price' => (float) $row['price'],
                'specs' => (object) json_decode($row['specs'], true, 512, JSON_THROW_ON_ERROR),
                'images' => $row['image_paths'] === null
                    ? []
                    : json_decode($row['image_paths'], true, 512, JSON_THROW_ON_ERROR),
                'variants' => [],
            ];
        }

        if ($row['variant_id'] !== null) {
            $variant = [
                'id' => $row['variant_id'],
                'label' => $row['label'],
                'sku' => $row['sku'],
                'available' => (int) $row['stock'] > 0,
            ];
            if ($canViewStock) {
                $variant['stock'] = (int) $row['stock'];
            }
            $products[$id]['variants'][] = $variant;
        }
    }

    if ($canViewStock && $products) {
        $history = $pdo->query(
            'SELECT m.product_id, v.label, m.quantity_delta, m.reason, m.created_at, u.name AS actor
             FROM inventory_movements m
             JOIN product_variants v ON v.product_id=m.product_id AND v.variant_id=m.variant_id
             LEFT JOIN users u ON u.id=m.performed_by
             ORDER BY m.id DESC LIMIT 500'
        );
        while ($movement = $history->fetch()) {
            $productId = $movement['product_id'];
            if (!isset($products[$productId]) || count($products[$productId]['history'] ?? []) >= 50) continue;
            $products[$productId]['history'][] = [
                'ts' => strtotime($movement['created_at']) * 1000,
                'size' => $movement['label'], 'delta' => (int) $movement['quantity_delta'],
                'reason' => $movement['reason'], 'by' => $movement['actor'] ?? 'Administrator',
            ];
        }
    }
    api_json(['products' => array_values($products)]);
} catch (Throwable $error) {
    api_fail($error);
}
