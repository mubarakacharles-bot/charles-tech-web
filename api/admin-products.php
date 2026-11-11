<?php
declare(strict_types=1);
require __DIR__ . '/auth/bootstrap.php';
auth_start_session();
auth_require_method('POST');
try {
    $pdo = api_db();
    $user = auth_current_user($pdo);
    if (!$user || !$user['isAdmin']) api_json(['error'=>'Administrator access is required.'],403);
    auth_require_csrf();
    $data = auth_read_json();
    $action = $data['action'] ?? '';
    $id = (string) ($data['id'] ?? '');
    if (!in_array($action,['create','update','remove','stock','add-variant','remove-variant'],true)) api_json(['error'=>'Unknown product action.'],400);
    if ($action !== 'create' && !preg_match('/^[a-z0-9_-]{1,32}$/i',$id)) api_json(['error'=>'Invalid product ID.'],400);
    $integer = static function(mixed $value): int {
        if (!is_int($value) || $value < 0 || $value > 2147483647) throw new InvalidArgumentException('Quantity must be a non-negative whole number.');
        return $value;
    };
    $text = static function(mixed $value,int $limit): string {
        if (!is_string($value) || trim($value)==='' || strlen($value)>$limit) throw new InvalidArgumentException('Required product text is missing or too long.');
        return trim($value);
    };
    $pdo->beginTransaction();
    if ($action === 'create') {
        $id='p'.bin2hex(random_bytes(12));
    } else {
        $lock=$pdo->prepare('SELECT id FROM products WHERE id=:id AND is_active=1 FOR UPDATE');
        $lock->execute(['id'=>$id]);
        if (!$lock->fetchColumn()) { $pdo->rollBack(); api_json(['error'=>'Product not found. Refresh the catalogue.'],404); }
    }
    if ($action==='create' || $action==='update') {
        $name=$text($data['name']??null,200); $category=$text($data['category']??null,120);
        $price=$data['price']??null;
        if ((!is_int($price)&&!is_float($price)) || !is_finite((float)$price) || $price<0 || $price>9999999999.99) throw new InvalidArgumentException('Enter a valid non-negative price.');
        if ($action==='create') {
            $stock=$integer($data['stock']??null);
            $description=(string)($data['description']??$name); $icon=(string)($data['icon']??'');
            if (strlen($description)>8000 || strlen($icon)>32) throw new InvalidArgumentException('Description or icon is too long.');
            $q=$pdo->prepare("INSERT INTO products(id,name,category,description,icon,price,specs) VALUES(:id,:name,:category,:description,:icon,:price,'{}')");
            $q->execute(compact('id','name','category','description','icon','price'));
            $variant='std'; $label='Standard';
        } else {
            $q=$pdo->prepare('UPDATE products SET name=:name,category=:category,price=:price WHERE id=:id');
            $q->execute(compact('name','category','price','id'));
        }
    }
    if ($action==='create' || $action==='add-variant') {
        if ($action==='add-variant') { $stock=$integer($data['stock']??null); $label=$text($data['label']??null,100); $variant='v'.bin2hex(random_bytes(12)); }
        $q=$pdo->prepare('INSERT INTO product_variants(product_id,variant_id,label,sku,stock) VALUES(:id,:variant,:label,:sku,:stock)');
        $q->execute(['id'=>$id,'variant'=>$variant,'label'=>$label,'sku'=>strtoupper($id.'-'.$variant),'stock'=>$stock]);
        $delta=$stock; $reason='Opening balance';
    }
    if ($action==='stock' || $action==='remove-variant') {
        $variant=$text($data['variantId']??null,40);
        $q=$pdo->prepare('SELECT stock FROM product_variants WHERE product_id=:id AND variant_id=:variant AND is_active=1 FOR UPDATE');
        $q->execute(compact('id','variant')); $old=$q->fetchColumn();
        if ($old===false) throw new InvalidArgumentException('Variant not found.');
        if ($action==='stock') {
            $stock=$integer($data['stock']??null); $expected=$integer($data['expectedStock']??null);
            if ((int)$old!==$expected) { $pdo->rollBack(); api_json(['error'=>'Stock changed in another session. Refresh before saving.'],409); }
            $q=$pdo->prepare('UPDATE product_variants SET stock=:stock WHERE product_id=:id AND variant_id=:variant');
            $q->execute(compact('stock','id','variant')); $delta=$stock-(int)$old; $reason='Administrator stock adjustment';
        } else {
            $q=$pdo->prepare('SELECT COUNT(*) FROM product_variants WHERE product_id=:id AND is_active=1'); $q->execute(['id'=>$id]);
            if ((int)$q->fetchColumn()<=1) throw new InvalidArgumentException('A product must keep at least one variant.');
            $q=$pdo->prepare('UPDATE product_variants SET is_active=0 WHERE product_id=:id AND variant_id=:variant'); $q->execute(compact('id','variant'));
            $delta=0; $reason='Variant removed';
        }
    }
    if (isset($delta) && $delta!==0) {
        $q=$pdo->prepare('INSERT INTO inventory_movements(product_id,variant_id,quantity_delta,reason,performed_by) VALUES(:id,:variant,:delta,:reason,:admin)');
        $q->execute(['id'=>$id,'variant'=>$variant,'delta'=>$delta,'reason'=>$reason,'admin'=>$user['id']]);
    }
    if ($action==='remove') {
        $q=$pdo->prepare('UPDATE products SET is_active=0 WHERE id=:id'); $q->execute(['id'=>$id]);
    }
    $pdo->commit(); api_json(['saved'=>true,'id'=>$id],$action==='create'?201:200);
} catch (Throwable $error) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    if ($error instanceof InvalidArgumentException) api_json(['error'=>$error->getMessage()],400);
    api_fail($error);
}
