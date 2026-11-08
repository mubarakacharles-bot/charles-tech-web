<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

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
                'specs' => json_decode($row['specs'], true, 512, JSON_THROW_ON_ERROR),
                'images' => $row['image_paths'] === null
                    ? []
                    : json_decode($row['image_paths'], true, 512, JSON_THROW_ON_ERROR),
                'variants' => [],
            ];
        }

        if ($row['variant_id'] !== null) {
            $products[$id]['variants'][] = [
                'id' => $row['variant_id'],
                'label' => $row['label'],
                'sku' => $row['sku'],
                'stock' => (int) $row['stock'],
            ];
        }
    }

    api_json(['products' => array_values($products)]);
} catch (Throwable $error) {
    api_fail($error);
}
