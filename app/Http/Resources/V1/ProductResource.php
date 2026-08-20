<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Http\Resources\ApiResource;
use Illuminate\Http\Request;

/**
 * Optional product transformer for /api/v1.
 * Not wired into ProductController yet — use when serializing a Product model.
 */
class ProductResource extends ApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->product_id,
            'name' => $this->product_name,
            'brand' => $this->brand,
            'category_id' => $this->category_id,
            'status' => $this->status,
            'image_url' => $this->image_url,
            'description' => $this->description,
        ];
    }
}
