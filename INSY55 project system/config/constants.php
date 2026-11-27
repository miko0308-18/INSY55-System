<?php
declare(strict_types=1);

const ROLE_ADMIN = 'Admin';
const ROLE_STAFF = 'Staff';
const ROLE_ADMIN_ID = 1;
const ROLE_STAFF_ID = 2;

const STATUS_ACTIVE = 'Active';
const STATUS_INACTIVE = 'Inactive';
const STATUS_ARCHIVED = 'Archived';
const STATUS_DELETED = 'Deleted';

const SALE_COMPLETED = 'Completed';
const SALE_EDITED = 'Edited';
const SALE_VOIDED = 'Voided';
const SALE_DELETED = 'Deleted';

const PAYMENT_CASH = 'Cash';
const PAYMENT_GCASH = 'GCash';

const DEFAULT_MIN_STOCK_LEVEL = 10;
const DEFAULT_VAT_RATE_PERCENT = 12.00;
const DEFAULT_DISCOUNT_PERCENT = 0.00;

const PRODUCT_UNITS = ['kg', 'piece', 'pack', 'tray'];

const NOTIFICATION_TYPES = [
    'low_stock',
    'out_of_stock',
    'near_expiry',
    'expired_product',
    'sale_recorded',
    'sale_edited',
    'sale_voided',
    'sale_deleted',
    'staff_account_created',
    'staff_account_updated',
    'staff_account_deleted',
    'backup_completed',
    'restore_completed',
    'system_activity',
];
