<?php

namespace App\Jobs;

use App\Models\ProductImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessImageUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 180;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $productImageId,
        public string $cloudinaryPublicId,
        public string $imageUrl
    ) {}

    /**
     * Execute background image processing & WebP optimization.
     */
    public function handle(): void
    {
        Log::info("Processing background image optimization for ProductImage ID: {$this->productImageId}");

        $image = ProductImage::find($this->productImageId);
        if ($image) {
            // Apply Cloudinary automatic f_auto,q_auto optimization flags
            $optimizedUrl = str_replace('/upload/', '/upload/f_auto,q_auto,w_1200/', $this->imageUrl);
            $image->update([
                'image_url' => $optimizedUrl,
            ]);
            Log::info("ProductImage ID {$this->productImageId} optimized to: {$optimizedUrl}");
        }
    }
}
