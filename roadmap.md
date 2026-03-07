Roadmap 3 спринта (Laravel 12 + Inertia React)
Спринт 1 (2 недели): Checkout, промокоды, базовая операционка
1.
Промокоды v1
DB: coupons, coupon_usages.
Backend: сервис расчёта скидки (CouponService), валидация правил (срок, лимит, min amount, first order, one-time).
UI: поле купона в корзине/checkout + причины отклонения.
Tests: feature-тесты на каждый rule + конкуренция повторного применения.
2.
Checkout hardening
DB: orders (snapshot цены/скидок), order_items (фиксируем цену на момент заказа).
Backend: атомарное создание заказа в транзакции, проверка остатков перед подтверждением.
UI: явные шаги checkout, повторная отправка блокируется processing.
Tests: happy path + out-of-stock + invalid address + duplicate submit.
3.
Базовая надежность
Backend: вынести email/уведомления в queue jobs, retry/backoff.
Infra: логгирование order_id, user_id, request_id.
Tests: job dispatch assertions.
Спринт 2 (2 недели): Каталог, поиск, отзывы, личный кабинет
1.
Поиск и фильтры v1
DB: индексы по products(status,is_approved,category_id,brand_id,price,qty), fulltext(name,short_description) (или эквивалент).
Backend: единый ProductFilter + сортировки.
UI: быстрые фильтры, сохранение query-state.
Tests: feature-тесты на комбинации фильтров/сортировок/пагинации.
2.
Отзывы v1
DB: product_reviews + флаги модерации/verified purchase.
Backend: разрешать отзыв только покупателю товара.
UI: список отзывов, рейтинг, модерация в админке.
Tests: cannot review without purchase, moderation flow.
3.
ЛК клиента
Фичи: история заказов, статусы, повтор заказа.
Tests: доступ только владельцу заказа, корректность статусов.
Спринт 3 (2 недели): Рост и монетизация
1.
Price alerts + wishlist growth
DB: price_alerts.
Backend: nightly job на сравнение цены и отправку уведомлений.
UI: “Сообщить о снижении цены”.
Tests: job + dedup уведомлений.
2.
Рекомендации v1
Backend: “похожие товары” (по категории/бренду/цене), “с этим покупают”.
UI: блоки на PDP/cart.
Tests: корректный отбор без N+1.
3.
Аналитика и админ-метрики
Backend: агрегаты по воронке (view->cart->order), top products, conversion.
UI: карточки KPI на админ-дашборде.
Tests: вычисление метрик на фикстурах.


