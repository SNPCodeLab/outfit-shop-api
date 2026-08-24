<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class CloudinaryMediaTest extends TestCase
{
    public function test_can_fetch_cloudinary_folders(): void
    {
        $response = $this->getJson('/api/v1/cloudinary/folders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['name', 'path'],
                ],
                'total',
            ])
            ->assertJsonPath('success', true);
    }

    public function test_can_fetch_cloudinary_assets(): void
    {
        $response = $this->getJson('/api/v1/cloudinary/assets?folder=ALL&max_results=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'total_count',
                'data' => [
                    '*' => [
                        'public_id',
                        'name',
                        'folder',
                        'url',
                        'format',
                        'width',
                        'height',
                    ],
                ],
            ])
            ->assertJsonPath('success', true);
    }
}
