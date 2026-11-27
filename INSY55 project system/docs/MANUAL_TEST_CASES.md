# Manual Test Cases – Come N' Meatshop Inventory System

Environment: Laragon (Apache + MySQL), project at `http://localhost/clone/INSY55-System/INSY55%20project%20system/`  
Default demo accounts (from sample data): Admin `admin` / `Admin@123`, Staff `staff` / `Staff@123`

## Authentication & access control
| ID | Test case | Steps | Expected result |
|----|-----------|-------|-----------------|
| TC-01 | Admin login | Open login page, enter Admin credentials, submit | Login succeeds; redirected to dashboard; role identified as Admin; Admin navigation visible |
| TC-02 | Staff login | Open login page, enter Staff credentials, submit | Login succeeds; redirected to dashboard; role identified as Staff; Staff navigation visible |
| TC-03 | Invalid login | Enter wrong username/password | Login rejected with user-friendly error; no session created |
| TC-04 | Staff trying to access Admin page | Login as Staff; directly browse to `modules/analytics/index.php`, `modules/users/index.php`, `modules/reports/sales.php`, or `modules/backup_restore/index.php` | Access denied/redirected; Staff cannot view or perform Admin-only actions |

## Inventory
| ID | Test case | Steps | Expected result |
|----|-----------|-------|-----------------|
| TC-05 | Add product | Login (Admin or authorized user), Inventory → Add Product, fill required fields, save | Product created with correct details/defaults (min stock editable, status active) and appears in list |
| TC-06 | Edit product | Open existing product, modify details (price/stock/min/expiry etc.), save | Changes persisted and reflected in inventory views |
| TC-07 | Add stock | Select product → Add Stock, enter quantity/movement details, submit | Stock quantity increases; stock movement recorded |
| TC-08 | Search product | Use inventory search/filter | Matching products shown; non-matching hidden |
| TC-09 | Low-stock alert | Set product stock at/below min level (or add stock down via sale) | Low-stock indicator/notification generated for relevant roles; visible in dashboard/notifications |
| TC-10 | Expired-product alert | Set product expiration date in the past (active product) | Expired/near-expiry alert shown appropriately (admin-focused for expired where configured) |

## Sales
| ID | Test case | Steps | Expected result |
|----|-----------|-------|-----------------|
| TC-11 | Record sale | Sales → Record Sale, add items/qty, enter discount/VAT/payment/amount paid | Totals computed correctly (subtotal, discount, VAT, total, change) |
| TC-12 | Review sale before saving | Proceed to review step; use Make Changes and then Confirm & Save | Review screen shows details; sale is not official until Confirm & Save; changes possible before save |
| TC-13 | Stock deduction after sale | Complete a sale for a product with known stock | Product stock reduced by sold quantity; sale/stock movement/receipt recorded |
| TC-14 | Edit sale | Open completed sale (Staff: own sale; Admin: permitted), edit items/values, save | Sale updated; previous stock restored and new deduction applied correctly; edit logged/notified |
| TC-15 | Void sale | Admin voids a completed/edited sale with reason | Sale status becomes Voided; stock restored; payment/status updated; activity/notification recorded |
| TC-16 | Delete sale | Admin deletes a completed/edited sale | Sale marked Deleted (or removed per implementation); stock restored; activity/notification recorded |
| TC-17 | Stock restoration after void/delete | Compare stock before and after void/delete | Stock levels return to correct values based on reversed sale quantities |
| TC-18 | Insufficient stock prevention | Attempt to sell more than available stock | System blocks/alerts; sale not saved with invalid quantities |

## Reports (Admin)
| ID | Test case | Steps | Expected result |
|----|-----------|-------|-----------------|
| TC-19 | Generate sales report | Admin → Reports → Sales, select date range/grouping, generate | On-screen summary, by product/staff, trends shown; print/Save-as-PDF works; CSV/Excel downloads generated (no payment-method filter) |
| TC-20 | Generate inventory report | Admin → Reports → Inventory, generate/download | Current inventory, low/out/near-expiry/expired, movements, categories, archived items shown; print/Save-as-PDF and CSV/Excel downloads work |

## Users (Admin)
| ID | Test case | Steps | Expected result |
|----|-----------|-------|-----------------|
| TC-21 | Create Staff account | Admin → Users → Add, enter staff details, save | Staff account created (no phpMyAdmin needed); can log in with assigned credentials |
| TC-22 | Edit Staff account | Admin edits staff profile/status/details | Updates saved and reflected; notifications/logs as applicable |
| TC-23 | Delete Staff account | Admin deletes/removes a staff account (with confirmation) | Account removed/deactivated per implementation; cannot access system as that user |

## Notifications
| ID | Test case | Steps | Expected result |
|----|-----------|-------|-----------------|
| TC-24 | Receive notification | Trigger events (low stock, sale recorded, backup, etc.) | In-app notification appears for appropriate role/user |
| TC-25 | Mark notification as read | Open notifications, mark one/all as read | Read state updates; unread count/badges update |
| TC-26 | Clear notification | Clear a read/unread notification or clear read notifications | Cleared items removed from active list/history view as designed |

## Backup & Restore (Admin)
| ID | Test case | Steps | Expected result |
|----|-----------|-------|-----------------|
| TC-27 | Backup database | Admin → Backup & Restore → Create backup | SQL backup file generated/downloaded (mysqldump or PHP fallback); history recorded; notification/log created |
| TC-28 | Restore full database | Upload valid full SQL backup, confirm warnings, restore | Validation passes; safety backup attempted (if enabled); full restore completes; data reflects backup state |
| TC-29 | Restore selected table | Upload valid backup, choose selected tables mode, pick tables, restore | Only selected table statements executed; other data unchanged as expected |

## Settings & session
| ID | Test case | Steps | Expected result |
|----|-----------|-------|-----------------|
| TC-30 | Edit profile | User → Settings/Profile, update allowed fields/password, save | Profile updated successfully; changes persist |
| TC-31 | Logout | Click Logout | Session ended; redirected to login; protected pages inaccessible without login |

## Additional checks (recommended)
- **Language switch:** toggle English/Filipino and verify labels/text update where translated.
- **Receipt printing:** open a receipt and use browser Print → Save as PDF; placeholder branding visible.
- **Role-specific notifications:** confirm Staff and Admin receive only relevant alerts.
- **CSRF/validation:** submit forms with missing/invalid data; expect validation errors and no unsafe state changes.
- **Python scripts:** run `backup_validator.py` on a known-good and a malformed file; generate sample/sales/inventory reports with valid DB credentials.
