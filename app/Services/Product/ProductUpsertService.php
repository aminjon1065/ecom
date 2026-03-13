<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductUpsertService
{
    public function __construct(
        private readonly ImageService $imageService,
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes, ProductWriteOptions $options): Product
    {
        return Product::query()->create($this->preparePayload($attributes, $options));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Product $product, array $attributes, ProductWriteOptions $options): Product
    {
        $product->update($this->preparePayload($attributes, $options, $product));

        return $product;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function preparePayload(array $attributes, ProductWriteOptions $options, ?Product $product = null): array
    {
        $payload = $attributes;
        $payload['slug'] = $this->buildSlug((string) $payload['name'], $options);

        if ($options->vendorId !== null) {
            $payload['vendor_id'] = $options->vendorId;
        }

        if ($options->forceApproval !== null) {
            $payload['is_approved'] = $options->forceApproval;
        }

        if ($options->forceStatus !== null) {
            $payload['status'] = $options->forceStatus;
        }

        $payload = $this->prepareThumb($payload, $product, $options);
        $payload = $this->prepareGallery($payload);

        return $payload;
    }

    private function buildSlug(string $name, ProductWriteOptions $options): string
    {
        $slug = Str::slug($name);

        if ($options->appendRandomSuffix) {
            return $slug.'-'.Str::random(5);
        }

        return $slug;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function prepareThumb(array $payload, ?Product $product, ProductWriteOptions $options): array
    {
        $thumb = $payload['thumb_image'] ?? null;

        if ($thumb instanceof UploadedFile) {
            if ($product?->thumb_image) {
                Storage::disk('public')->delete($product->thumb_image);
            }

            $payload['thumb_image'] = $this->imageService->storeThumb($thumb, 'products/thumbs');

            return $payload;
        }

        if ($product !== null && ! $options->clearMissingThumb) {
            unset($payload['thumb_image']);
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function prepareGallery(array $payload): array
    {
        $gallery = $payload['gallery'] ?? null;

        if (! is_array($gallery)) {
            unset($payload['gallery']);

            return $payload;
        }

        $payload['gallery'] = Collection::make($gallery)
            ->filter(fn ($file) => $file instanceof UploadedFile)
            ->map(fn (UploadedFile $file) => $this->imageService->storeOptimized($file, 'products/gallery', 800))
            ->values()
            ->all();

        return $payload;
    }
}
