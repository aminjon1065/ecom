<?php

namespace App\DTO;

use App\Models\Order;

class CheckoutOrderResult
{
    public function __construct(
        public string $status,
        public ?Order $order = null,
        public ?string $field = null,
        public ?string $message = null,
    ) {}

    public static function cartEmpty(): self
    {
        return new self(status: 'cart_empty');
    }

    public static function invalid(string $field, string $message): self
    {
        return new self(status: 'invalid', field: $field, message: $message);
    }

    public static function success(Order $order): self
    {
        return new self(status: 'success', order: $order);
    }

    public static function idempotent(Order $order): self
    {
        return new self(status: 'idempotent', order: $order);
    }
}
