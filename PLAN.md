# Admin Dashboard Implementation Plan

## Overview
Replace placeholder dashboard with a complete admin dashboard featuring statistics, vendor/product approval workflows, order tracking, and review moderation.

## Files to Create/Modify

### 1. Bug Fixes (prerequisites)
- **`app/Models/Vendor.php`** — Add `shop_name` to `$fillable` array (currently missing, will cause mass assignment errors)
- **`routes/web.php`** — Remove duplicate `require __DIR__ . '/client.php'` on line 20

### 2. Backend

#### Create: `app/Http/Controllers/Admin/DashboardController.php`
- `index()` — Aggregate queries for all dashboard statistics:
  - Total revenue (sum of paid orders)
  - Total/pending orders count
  - Total/pending-approval products
  - Total customers (users with 'user' role)
  - Total/pending vendors
  - Order status breakdown (group by order_status)
  - Latest 5 pending vendor applications (with user relation)
  - Latest 5 products pending approval (with vendor.user, category)
  - Latest 10 recent orders (with user)
  - Latest 5 pending reviews (with product, user)
  - Today's revenue vs yesterday (for trend indicator)
- `approveVendor(Vendor $vendor)` — Set vendor status to true
- `rejectVendor(Vendor $vendor)` — Delete the vendor record
- `approveProduct(Product $product)` — Set is_approved to true
- `approveReview(ProductReview $review)` — Set review status to true
- `deleteReview(ProductReview $review)` — Delete the review

#### Modify: `routes/admin.php`
- Replace inline dashboard closure with `DashboardController@index`
- Add POST routes for approve/reject actions:
  - `POST vendors/{vendor}/approve`
  - `DELETE vendors/{vendor}/reject`
  - `POST products/{product}/approve`
  - `POST reviews/{productReview}/approve`
  - `DELETE reviews/{productReview}`

### 3. Frontend Types

#### Create: `resources/js/types/dashboard.d.ts`
- `DashboardStatistics` — all numeric counters
- `OrderStats` — order_status → count mapping
- `PendingVendor` — vendor with nested user
- `PendingProduct` — product with nested vendor.user & category
- `RecentOrder` — order with nested user
- `PendingReview` — review with nested product & user
- `DashboardProps` — all of above combined

### 4. Frontend Dashboard Page

#### Rewrite: `resources/js/pages/admin/dashboard.tsx`

**Layout (top to bottom):**

1. **Stats Grid** (5 cards in responsive grid)
   - Общая выручка (Total Revenue) — with trend indicator
   - Заказы (Orders) — total + pending badge
   - Товары (Products) — total + pending approval badge
   - Покупатели (Customers) — total count
   - Продавцы (Vendors) — total + pending applications badge

2. **Two-column layout:**
   - Left: Статистика заказов (Order Status Breakdown) — CSS progress bars
   - Right: Quick stats summary

3. **Заявки продавцов** (Pending Vendor Applications) — DataTable with approve/reject buttons, shown only if pending_vendors > 0

4. **Товары на модерации** (Products Awaiting Approval) — DataTable with thumbnail, name, price, vendor, category, approve button, shown only if pending_products > 0

5. **Последние заказы** (Recent Orders) — DataTable with invoice #, customer, amount, payment badge, status badge, date

6. **Отзывы на модерации** (Pending Reviews) — List with star ratings, review text, approve/delete buttons, shown only if pending_reviews > 0

**Patterns to follow:**
- Use `AppLayout` with breadcrumbs
- Use `router.post()`/`router.delete()` from `@inertiajs/react` for actions
- Use existing `DataTable`, `Card`, `Badge`, `Button` components
- All labels in Russian
- Use Lucide React icons for stat cards
- Use `toast` from `sonner` for success feedback

### 5. Route Regeneration
- Run `php artisan wayfinder:generate` after adding routes to regenerate TypeScript route helpers

## Implementation Order
1. Fix bugs (Vendor model, web.php)
2. Create DashboardController
3. Update routes/admin.php
4. Create TypeScript types
5. Rewrite dashboard.tsx
6. Regenerate route helpers
7. Test
