<?php
declare(strict_types=1);

function fetch_active_products_for_sale(): array
{
    return db()->query(
        "SELECT id, name, meat_type, price, unit, stock_quantity, min_stock_level
         FROM products
         WHERE status = 'Active' AND stock_quantity > 0
         ORDER BY name"
    )->fetchAll();
}

function get_sale_with_items(int $saleId): ?array
{
    $stmt = db()->prepare(
        'SELECT s.*, u.full_name AS cashier_name
         FROM sales s
         INNER JOIN users u ON u.id = s.cashier_user_id
         WHERE s.id = ? LIMIT 1'
    );
    $stmt->execute([$saleId]);
    $sale = $stmt->fetch();
    if (!$sale) {
        return null;
    }
    $items = db()->prepare('SELECT * FROM sale_items WHERE sale_id = ? ORDER BY id');
    $items->execute([$saleId]);
    $sale['items'] = $items->fetchAll();
    return $sale;
}

function can_edit_sale(array $sale): bool
{
    if (!in_array($sale['status'], ['Completed', 'Edited'], true)) {
        return false;
    }
    if (is_admin()) {
        return true;
    }
    return is_staff() && (int) $sale['cashier_user_id'] === (int) current_user()['id'];
}

function can_void_or_delete_sale(array $sale): bool
{
    return is_admin() && in_array($sale['status'], ['Completed', 'Edited'], true);
}

function adjust_stock_for_items(PDO $pdo, array $items, string $movementType, int $saleId, string $remarks): void
{
    foreach ($items as $item) {
        if (empty($item['product_id'])) {
            continue;
        }
        $qty = (float) $item['quantity'];
        $productId = (int) $item['product_id'];

        $lock = $pdo->prepare('SELECT stock_quantity FROM products WHERE id = ? FOR UPDATE');
        $lock->execute([$productId]);
        $before = (float) $lock->fetchColumn();

        if (in_array($movementType, ['sale_deduction'], true)) {
            $change = -1 * abs($qty);
            if ($before + $change < -0.0001) {
                throw new RuntimeException('Insufficient stock for product ID ' . $productId);
            }
        } else {
            $change = abs($qty);
        }

        $after = $before + $change;
        $pdo->prepare('UPDATE products SET stock_quantity = ?, updated_at = NOW() WHERE id = ?')
            ->execute([$after, $productId]);
        $pdo->prepare(
            'INSERT INTO stock_movements (product_id, movement_type, quantity_change, quantity_before, quantity_after, reference_type, reference_id, remarks, performed_by)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $productId,
            $movementType,
            $change,
            $before,
            $after,
            'sale',
            $saleId,
            $remarks,
            current_user()['id'],
        ]);
    }
}

function parse_sale_items_from_post(): array
{
    $productIds = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    $items = [];

    if (!is_array($productIds) || !is_array($quantities)) {
        return [];
    }

    foreach ($productIds as $i => $productId) {
        $productId = (int) $productId;
        $qty = (float) ($quantities[$i] ?? 0);
        if ($productId <= 0 || $qty <= 0) {
            continue;
        }
        $stmt = db()->prepare("SELECT * FROM products WHERE id = ? AND status = 'Active' LIMIT 1");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        if (!$product) {
            throw new RuntimeException('Invalid product selected.');
        }
        $items[] = [
            'product_id' => $productId,
            'product' => $product,
            'quantity' => $qty,
            'unit_price' => (float) $product['price'],
            'line_subtotal' => round((float) $product['price'] * $qty, 2),
            'product_name_snapshot' => $product['name'],
            'meat_type_snapshot' => $product['meat_type'],
            'unit_snapshot' => $product['unit'],
        ];
    }
    return $items;
}

function save_sale_transaction(array $payload, ?int $existingSaleId = null): int
{
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $items = $payload['items'];
        if (!$items) {
            throw new RuntimeException('Add at least one product.');
        }

        $subtotal = array_sum(array_column($items, 'line_subtotal'));
        $totals = calculate_sale_totals($subtotal, (float) $payload['discount_percent'], (float) $payload['vat_rate_percent']);
        $amountPaid = (float) $payload['amount_paid'];
        if ($amountPaid + 0.001 < $totals['total_amount']) {
            throw new RuntimeException('Amount paid is less than total.');
        }
        $changeDue = round($amountPaid - $totals['total_amount'], 2);

        if ($existingSaleId) {
            $existing = get_sale_with_items($existingSaleId);
            if (!$existing || !can_edit_sale($existing)) {
                throw new RuntimeException('You cannot edit this sale.');
            }
            // Restore previous stock then apply new deductions
            adjust_stock_for_items($pdo, $existing['items'], 'sale_restore', $existingSaleId, 'Restore before sale edit');
            $pdo->prepare('DELETE FROM sale_items WHERE sale_id = ?')->execute([$existingSaleId]);
            $pdo->prepare('DELETE FROM payments WHERE sale_id = ?')->execute([$existingSaleId]);

            $pdo->prepare(
                'UPDATE sales SET customer_name=?, subtotal=?, discount_percent=?, discount_amount=?, vat_rate_percent=?, vat_amount=?, total_amount=?,
                 payment_method=?, amount_paid=?, change_due=?, status=?, review_confirmed_at=NOW(), remarks=?, updated_by=?, updated_at=NOW()
                 WHERE id=?'
            )->execute([
                $payload['customer_name'],
                $totals['subtotal'],
                $totals['discount_percent'],
                $totals['discount_amount'],
                $totals['vat_rate_percent'],
                $totals['vat_amount'],
                $totals['total_amount'],
                $payload['payment_method'],
                $amountPaid,
                $changeDue,
                SALE_EDITED,
                $payload['remarks'],
                current_user()['id'],
                $existingSaleId,
            ]);
            $saleId = $existingSaleId;
        } else {
            $receipt = generate_receipt_number($pdo);
            $pdo->prepare(
                'INSERT INTO sales (receipt_number, sale_datetime, cashier_user_id, customer_name, subtotal, discount_percent, discount_amount,
                 vat_rate_percent, vat_amount, total_amount, payment_method, amount_paid, change_due, status, review_confirmed_at, remarks, created_by, updated_by)
                 VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)'
            )->execute([
                $receipt,
                current_user()['id'],
                $payload['customer_name'],
                $totals['subtotal'],
                $totals['discount_percent'],
                $totals['discount_amount'],
                $totals['vat_rate_percent'],
                $totals['vat_amount'],
                $totals['total_amount'],
                $payload['payment_method'],
                $amountPaid,
                $changeDue,
                SALE_COMPLETED,
                $payload['remarks'],
                current_user()['id'],
                current_user()['id'],
            ]);
            $saleId = (int) $pdo->lastInsertId();
        }

        $itemStmt = $pdo->prepare(
            'INSERT INTO sale_items (sale_id, product_id, product_name_snapshot, meat_type_snapshot, unit_snapshot, unit_price, quantity, line_subtotal)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($items as $item) {
            // Validate stock for deduction
            $lock = $pdo->prepare('SELECT stock_quantity, name FROM products WHERE id = ? FOR UPDATE');
            $lock->execute([$item['product_id']]);
            $row = $lock->fetch();
            if (!$row || (float) $row['stock_quantity'] < (float) $item['quantity']) {
                throw new RuntimeException('Insufficient stock for ' . ($row['name'] ?? 'product') . '.');
            }
            $itemStmt->execute([
                $saleId,
                $item['product_id'],
                $item['product_name_snapshot'],
                $item['meat_type_snapshot'],
                $item['unit_snapshot'],
                $item['unit_price'],
                $item['quantity'],
                $item['line_subtotal'],
            ]);
        }

        adjust_stock_for_items($pdo, $items, 'sale_deduction', $saleId, $existingSaleId ? 'Sale edit deduction' : 'Sale deduction');

        $pdo->prepare(
            'INSERT INTO payments (sale_id, payment_method, amount_tendered, change_amount, payment_status, paid_at)
             VALUES (?, ?, ?, ?, ?, NOW())'
        )->execute([$saleId, $payload['payment_method'], $amountPaid, $changeDue, 'Paid']);

        $pdo->commit();

        if ($existingSaleId) {
            create_notification('sale_edited', 'Sale edited', 'Receipt was edited.', 'info', 'All', 'sales', 'sale', $saleId, 'modules/sales/receipt.php?id=' . $saleId);
            log_activity('sale_edit', 'sales', 'Edited sale', 'sale', $saleId);
        } else {
            create_notification('sale_recorded', 'New sale recorded', 'A sale was recorded.', 'success', 'All', 'sales', 'sale', $saleId, 'modules/sales/receipt.php?id=' . $saleId);
            log_activity('sale_create', 'sales', 'Recorded sale', 'sale', $saleId);
        }

        try {
            check_inventory_alerts();
        } catch (Throwable $e) {
        }

        return $saleId;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function void_or_delete_sale(int $saleId, string $mode, string $reason = ''): void
{
    if (!is_admin()) {
        throw new RuntimeException('Only Admin can void or delete sales.');
    }
    $pdo = db();
    $sale = get_sale_with_items($saleId);
    if (!$sale || !can_void_or_delete_sale($sale)) {
        throw new RuntimeException('Sale cannot be voided/deleted.');
    }

    $pdo->beginTransaction();
    try {
        adjust_stock_for_items($pdo, $sale['items'], $mode === 'void' ? 'void_restore' : 'sale_restore', $saleId, ucfirst($mode) . ' restore');
        $status = $mode === 'void' ? SALE_VOIDED : SALE_DELETED;
        $pdo->prepare(
            'UPDATE sales SET status=?, void_reason=?, voided_by=?, voided_at=NOW(), updated_by=?, updated_at=NOW() WHERE id=?'
        )->execute([$status, $reason ?: null, current_user()['id'], current_user()['id'], $saleId]);
        $pdo->prepare("UPDATE payments SET payment_status='Voided' WHERE sale_id=?")->execute([$saleId]);
        $pdo->commit();

        $type = $mode === 'void' ? 'sale_voided' : 'sale_deleted';
        create_notification($type, 'Sale ' . $mode . 'ed', 'Sale #' . $sale['receipt_number'] . ' was ' . $mode . 'ed.', 'warning', 'All', 'sales', 'sale', $saleId);
        log_activity($type, 'sales', ucfirst($mode) . ' sale', 'sale', $saleId);
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}
