<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryObserver
{
    public function saved(Category $category): void
    {
        Cache::forget('categories:all');
        Cache::forget("category:{$category->category_id}");
    }

    public function deleted(Category $category): void
    {
        Cache::forget('categories:all');
        Cache::forget("category:{$category->category_id}");
    }
}
