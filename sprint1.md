Спринт 1 Backlog (детально)
1.
Coupon engine v1
•
Цель: единый расчет скидки для cart/checkout.
•
Файлы:
◦
app/Services/Coupon/CouponService.php
◦
app/DTO/CouponResult.php
◦
app/Enums/CouponType.php (fixed, percent)
◦
app/Http/Controllers/Client/CheckoutController.php (use service)
•
Миграции:
◦
coupons: code unique, type, value, starts_at, ends_at, usage_limit, usage_per_user, min_subtotal, is_active, first_order_only
◦
coupon_usages: coupon_id, user_id, order_id, timestamps, unique(coupon_id,user_id,order_id)
•
Тесты:
◦
tests/Feature/Checkout/CouponApplyTest.php
◦
кейсы: expired, inactive, usage limit reached, user limit, min subtotal, first order, success percent/fixed.
2.
Apply/remove coupon endpoints
•
Цель: UX с понятной причиной отказа.
•
Файлы:
◦
routes/client.php (checkout.coupon.apply, checkout.coupon.remove)
◦
app/Http/Requests/Checkout/ApplyCouponRequest.php
◦
app/Http/Controllers/Client/CheckoutCouponController.php
•
UI:
◦
resources/js/pages/client/checkout/index.tsx (поле кода, applied badge, remove)
•
Тесты:
◦
tests/Feature/Checkout/CouponEndpointsTest.php
◦
проверка session/state + messages.
3.
Order pricing snapshot
•
Цель: фиксировать цены и скидки на момент покупки.
•
Файлы:
◦
app/Models/Order.php, app/Models/OrderItem.php (если нет)
◦
app/Services/Checkout/PlaceOrderService.php
•
Миграции:
◦
orders: subtotal, discount_total, shipping_total, grand_total, coupon_code nullable
◦
order_items: unit_price, discount_amount, line_total, snapshot product_name, sku
•
Тесты:
◦
tests/Feature/Checkout/PlaceOrderSnapshotTest.php
◦
проверка корректных totals и snapshot при изменении цены после заказа.
4.
Stock check + transaction safety
•
Цель: исключить oversell.
•
Файлы:
◦
PlaceOrderService с DB::transaction + lockForUpdate по product rows.
•
Тесты:
◦
tests/Feature/Checkout/StockValidationTest.php
◦
qty меньше остатка, ровно остаток, больше остатка, race-like scenario (2 запроса).
5.
Idempotent submit
•
Цель: защита от двойной оплаты/дублей заказа.
•
Файлы:
◦
orders добавить idempotency_key unique nullable
◦
PlaceOrderService использовать ключ из request/session.
•
Тесты:
◦
tests/Feature/Checkout/IdempotencyTest.php
◦
одинаковый key => один order.
6.
Checkout form validation
•
Цель: жёсткая валидация адреса/контактов.
•
Файлы:
◦
app/Http/Requests/Checkout/PlaceOrderRequest.php
◦
использовать FormRequest вместо inline validate.
•
Тесты:
◦
tests/Feature/Checkout/CheckoutValidationTest.php
◦
required fields, phone format, invalid shipping option.
7.
Queue notifications
•
Цель: не блокировать checkout.
•
Файлы:
◦
app/Jobs/SendOrderPlacedNotificationsJob.php
◦
app/Notifications/OrderPlacedNotification.php
•
Тесты:
◦
tests/Feature/Checkout/OrderNotificationDispatchTest.php
◦
Queue::fake() и assert pushed.
8.
Structured logging
•
Цель: наблюдаемость и разбор инцидентов.
•
Файлы:
◦
PlaceOrderService, CheckoutController, ProductImportService (уже есть логирование — привести к единому формату)
•
Формат: event, order_id, user_id, request_id, duration_ms, status.
•
Тесты:
◦
минимум smoke на отсутствие exceptions в critical path.
9.
Admin coupon CRUD (минимальный)
•
Цель: управлять купонами без SQL.
•
Файлы:
◦
app/Http/Controllers/Admin/CouponController.php
◦
app/Http/Requests/Admin/StoreCouponRequest.php, UpdateCouponRequest.php
◦
resources/js/pages/admin/coupon/index.tsx, create.tsx, edit.tsx
◦
routes/admin.php
•
Тесты:
◦
tests/Feature/Admin/CouponCrudTest.php
10.
Smoke acceptance
•
Цель: закрыть спринт интеграционно.
•
Тесты:
◦
tests/Feature/Checkout/CheckoutSmokeTest.php
◦
сценарий: add to cart -> apply coupon -> place order -> stock decreased -> notification job queued.
