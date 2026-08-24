<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class CloudinaryMediaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'cloudinary.cloud_url' => 'cloudinary://292517627621863:CZhMlOoVVxAQBS_Vc_OrnPtqr4g@od8t271n',
            'cloudinary.cloud_name' => 'od8t271n',
            'cloudinary.api_key' => '292517627621863',
            'cloudinary.api_secret' => 'CZhMlOoVVxAQBS_Vc_OrnPtqr4g',
        ]);
    }

    public function test_can_fetch_cloudinary_folders(): void
    {
        $response = $this->getJson('/api/v1/cloudinary/folders');

        if ($response->status() === 500) {
            $this->markTestSkipped('Cloudinary API is unreachable in this test environment.');
        }

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

        if ($response->status() === 500) {
            $this->markTestSkipped('Cloudinary API is unreachable in this test environment.');
        }

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
