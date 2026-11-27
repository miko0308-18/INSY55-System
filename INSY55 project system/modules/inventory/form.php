<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth_check.php';
require_admin();

$id = (int) get('id', 0);
$isEdit = $id > 0;
$pageTitle = page_title($isEdit ? 'Edit Product' : __('Add Product'));
$pdo = db();
$categories = $pdo->query("SELECT id, name FROM product_categories WHERE status='Active' ORDER BY name")->fetchAll();

$product = [
    'category_id' => '',
    'name' => '',
    'meat_type' => '',
    'description' => '',
    'price' => '0.00',
    'unit' => 'kg',
    'stock_quantity' => '0',
    'min_stock_level' => (string) DEFAULT_MIN_STOCK_LEVEL,
    'expiration_date' => '',
    'barcode' => '',
    'supplier' => '',
    'status' => 'Active',
    'image_path' => 'assets/images/placeholders/product-placeholder.svg',
];

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) {
        flash('error', 'Product not found.');
        redirect('modules/inventory/index.php');
    }
    $product = $found;
}

if (request_method() === 'POST') {
    require_csrf();
    $data = [
        'category_id' => (int) post('category_id') ?: null,
        'name' => trim((string) post('name')),
        'meat_type' => trim((string) post('meat_type')),
        'description' => trim((string) post('description')),
        'price' => (float) post('price', 0),
        'unit' => (string) post('unit', 'kg'),
        'stock_quantity' => (float) post('stock_quantity', 0),
        'min_stock_level' => (float) post('min_stock_level', DEFAULT_MIN_STOCK_LEVEL),
        'expiration_date' => trim((string) post('expiration_date')) ?: null,
        'barcode' => trim((string) post('barcode')) ?: null,
        'supplier' => trim((string) post('supplier')) ?: null,
        'status' => (string) post('status', 'Active'),
    ];

    $errors = [];
    if ($data['name'] === '') $errors[] = 'Product name is required.';
    if ($data['price'] < 0) $errors[] = 'Price cannot be negative.';
    if ($data['stock_quantity'] < 0) $errors[] = 'Stock cannot be negative.';
    if (!in_array($data['unit'], PRODUCT_UNITS, true)) $data['unit'] = 'kg';

    $imagePath = $product['image_path'] ?? 'assets/images/placeholders/product-placeholder.svg';
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true)) {
            $errors[] = 'Invalid image type.';
        } else {
            $filename = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destRel = 'uploads/products/' . $filename;
            $destAbs = __DIR__ . '/../../' . $destRel;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destAbs)) {
                $imagePath = $destRel;
            } else {
                $errors[] = 'Failed to upload image.';
            }
        }
    }

    if (!$errors) {
        if ($isEdit) {
            $sql = 'UPDATE products SET category_id=?, name=?, meat_type=?, description=?, price=?, unit=?, min_stock_level=?, expiration_date=?, image_path=?, barcode=?, supplier=?, status=?, updated_by=?, updated_at=NOW() WHERE id=?';
            $pdo->prepare($sql)->execute([
                $data['category_id'], $data['name'], $data['meat_type'] ?: null, $data['description'] ?: null,
                $data['price'], $data['unit'], $data['min_stock_level'], $data['expiration_date'],
                $imagePath, $data['barcode'], $data['supplier'], $data['status'], current_user()['id'], $id,
            ]);
            log_activity('product_update', 'inventory', 'Updated product', 'product', $id, null, $data);
            flash('success', 'Product updated.');
        } else {
            $sql = 'INSERT INTO products (category_id, name, meat_type, description, price, unit, stock_quantity, min_stock_level, expiration_date, image_path, barcode, supplier, status, created_by, updated_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $pdo->prepare($sql)->execute([
                $data['category_id'], $data['name'], $data['meat_type'] ?: null, $data['description'] ?: null,
                $data['price'], $data['unit'], $data['stock_quantity'], $data['min_stock_level'], $data['expiration_date'],
                $imagePath, $data['barcode'], $data['supplier'], $data['status'], current_user()['id'], current_user()['id'],
            ]);
            $newId = (int) $pdo->lastInsertId();
            if ($data['stock_quantity'] > 0) {
                $pdo->prepare(
                    'INSERT INTO stock_movements (product_id, movement_type, quantity_change, quantity_before, quantity_after, reference_type, remarks, performed_by)
                     VALUES (?, ?, ?, 0, ?, ?, ?, ?)'
                )->execute([$newId, 'initial', $data['stock_quantity'], $data['stock_quantity'], 'setup', 'Initial stock', current_user()['id']]);
            }
            log_activity('product_create', 'inventory', 'Created product', 'product', $newId, null, $data);
            flash('success', 'Product created.');
        }
        redirect('modules/inventory/index.php');
    }

    foreach ($errors as $err) flash('error', $err);
    $product = array_merge($product, $data);
    $product['image_path'] = $imagePath;
}

require __DIR__ . '/../../includes/header.php';
?>
<div class="page-header">
    <div>
        <h1><?= e($isEdit ? 'Edit Product' : __('Add Product')) ?></h1>
        <p class="subtitle">Meat product details</p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(base_url('modules/inventory/index.php')) ?>"><?= e(__('Cancel')) ?></a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Product name</label>
                    <input type="text" name="name" class="form-control" required value="<?= e($product['name']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">—</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= (int) $c['id'] ?>" <?= (int) $product['category_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Meat type</label>
                    <input type="text" name="meat_type" class="form-control" value="<?= e((string) $product['meat_type']) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2"><?= e((string) $product['description']) ?></textarea>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Price</label>
                    <input type="number" step="0.01" min="0" name="price" class="form-control" required value="<?= e((string) $product['price']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Unit</label>
                    <select name="unit" class="form-select">
                        <?php foreach (PRODUCT_UNITS as $unit): ?>
                            <option value="<?= $unit ?>" <?= $product['unit'] === $unit ? 'selected' : '' ?>><?= $unit ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (!$isEdit): ?>
                <div class="col-md-3">
                    <label class="form-label">Initial stock</label>
                    <input type="number" step="0.001" min="0" name="stock_quantity" class="form-control" value="<?= e((string) $product['stock_quantity']) ?>">
                </div>
                <?php endif; ?>
                <div class="col-md-3">
                    <label class="form-label">Min stock level</label>
                    <input type="number" step="0.001" min="0" name="min_stock_level" class="form-control" value="<?= e((string) $product['min_stock_level']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Expiration date</label>
                    <input type="date" name="expiration_date" class="form-control" value="<?= e((string) $product['expiration_date']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Barcode (optional)</label>
                    <input type="text" name="barcode" class="form-control" value="<?= e((string) $product['barcode']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Supplier (optional)</label>
                    <input type="text" name="supplier" class="form-control" value="<?= e((string) $product['supplier']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= e(__('Status')) ?></label>
                    <select name="status" class="form-select">
                        <?php foreach (['Active','Inactive','Archived'] as $s): ?>
                            <option value="<?= $s ?>" <?= $product['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Product image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                    <div class="form-text">Uses placeholder if none uploaded.</div>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-primary"><?= e(__('Save')) ?></button>
            </div>
        </form>
    </div>
</div>
<?php require __DIR__ . '/../../includes/footer.php'; ?>
