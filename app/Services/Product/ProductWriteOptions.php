<?php

namespace App\Services\Product;

class ProductWriteOptions
{
    public function __construct(
        public readonly ?int $vendorId = null,
        public readonly ?bool $forceApproval = null,
        public readonly ?bool $forceStatus = null,
        public readonly bool $appendRandomSuffix = false,
        public readonly bool $clearMissingThumb = false,
    ) {}
}
