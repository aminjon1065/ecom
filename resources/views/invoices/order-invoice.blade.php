<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            line-height: 1.5;
        }
        .container {
            padding: 30px 40px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }
        .header-left {
            float: left;
        }
        .header-right {
            float: right;
            text-align: right;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }
        .invoice-title {
            font-size: 20px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 5px;
        }
        .invoice-number {
            font-size: 14px;
            color: #6b7280;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .info-section {
            margin-bottom: 25px;
        }
        .info-grid {
            width: 100%;
        }
        .info-grid td {
            padding: 4px 0;
            vertical-align: top;
        }
        .info-label {
            color: #6b7280;
            width: 160px;
        }
        .info-value {
            font-weight: 600;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #374151;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .products-table th {
            background-color: #f3f4f6;
            padding: 8px 12px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #d1d5db;
            font-size: 11px;
            text-transform: uppercase;
        }
        .products-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .products-table .text-right {
            text-align: right;
        }
        .products-table .text-center {
            text-align: center;
        }
        .totals-section {
            float: right;
            width: 250px;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 6px 0;
        }
        .totals-table .total-row td {
            padding-top: 10px;
            border-top: 2px solid #1a1a1a;
            font-size: 14px;
            font-weight: bold;
        }
        .totals-table .text-right {
            text-align: right;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-processing {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-shipped {
            background-color: #e0e7ff;
            color: #3730a3;
        }
        .status-delivered {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #9ca3af;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header clearfix">
            <div class="header-left">
                <div class="company-name">Ecom</div>
                <div style="color: #6b7280; margin-top: 4px;">info@ecom.tj</div>
                <div style="color: #6b7280;">+992 (00) 000-00-00</div>
            </div>
            <div class="header-right">
                <div class="invoice-title">{{ $title }}</div>
                <div class="invoice-number">#{{ $order->invoice_id }}</div>
                <div style="color: #6b7280; margin-top: 4px;">
                    {{ $order->created_at->format('d.m.Y') }}
                </div>
            </div>
        </div>

        {{-- Order Info --}}
        <div class="info-section">
            <div class="section-title">Информация о заказе</div>
            <table class="info-grid">
                <tr>
                    <td class="info-label">Номер заказа:</td>
                    <td class="info-value">#{{ $order->invoice_id }}</td>
                </tr>
                <tr>
                    <td class="info-label">Транзакция:</td>
                    <td class="info-value">{{ $order->transaction_id }}</td>
                </tr>
                <tr>
                    <td class="info-label">Дата:</td>
                    <td class="info-value">{{ $order->created_at->translatedFormat('d F Y, H:i') }}</td>
                </tr>
                <tr>
                    <td class="info-label">Клиент:</td>
                    <td class="info-value">{{ $order->user->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Способ оплаты:</td>
                    <td class="info-value">{{ $order->payment_method === 'cash' ? 'Наличными' : 'Картой' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Статус оплаты:</td>
                    <td class="info-value">{{ $order->payment_status ? 'Оплачен' : 'Не оплачен' }}</td>
                </tr>
                <tr>
                    <td class="info-label">Статус заказа:</td>
                    <td>
                        @php
                            $statusLabels = [
                                'pending' => 'В обработке',
                                'processing' => 'Обрабатывается',
                                'shipped' => 'Отправлен',
                                'delivered' => 'Доставлен',
                                'cancelled' => 'Отменен',
                            ];
                            $statusClass = 'status-' . $order->order_status;
                        @endphp
                        <span class="status-badge {{ $statusClass }}">
                            {{ $statusLabels[$order->order_status] ?? $order->order_status }}
                        </span>
                    </td>
                </tr>
                @if($order->coupon)
                <tr>
                    <td class="info-label">Купон:</td>
                    <td class="info-value">{{ $order->coupon }}</td>
                </tr>
                @endif
            </table>
        </div>

        {{-- Products --}}
        <div class="info-section">
            <div class="section-title">Товары</div>
            <table class="products-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Наименование</th>
                        <th class="text-center" style="width: 80px;">Кол-во</th>
                        <th class="text-right" style="width: 120px;">Цена</th>
                        <th class="text-right" style="width: 120px;">Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->products as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->product->name ?? 'Товар удален' }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->unit_price, 2, '.', ' ') }} сом.</td>
                        <td class="text-right">{{ number_format($item->quantity * $item->unit_price, 2, '.', ' ') }} сом.</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totals --}}
        <div class="clearfix">
            <div class="totals-section">
                <table class="totals-table">
                    <tr>
                        <td>Количество товаров:</td>
                        <td class="text-right">{{ $order->product_quantity }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>Итого:</td>
                        <td class="text-right">{{ number_format($order->amount, 2, '.', ' ') }} сом.</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            Ecom &mdash; {{ date('Y') }}. Все права защищены.
        </div>
    </div>
</body>
</html>
