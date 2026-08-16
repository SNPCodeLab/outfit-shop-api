<?php

namespace App\Services;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudinaryService
{
    protected string $cloudName;
    protected string $apiKey;
    protected string $apiSecret;
    protected string $defaultFolder;

    public function __construct()
    {
        $cloudUrl = config('cloudinary.cloud_url') ?? env('CLOUDINARY_URL');

        if ($cloudUrl && str_starts_with($cloudUrl, 'cloudinary://')) {
            $parsed = parse_url($cloudUrl);
            $this->apiKey = $parsed['user'] ?? '';
            $this->apiSecret = $parsed['pass'] ?? '';
            $this->cloudName = $parsed['host'] ?? '';
        } else {
            $this->cloudName = config('cloudinary.cloud_name') ?? env('CLOUDINARY_CLOUD_NAME', 'od8t271n');
            $this->apiKey = config('cloudinary.api_key') ?? env('CLOUDINARY_API_KEY', '292517627621863');
            $this->apiSecret = config('cloudinary.api_secret') ?? env('CLOUDINARY_API_SECRET', 'CZhMlOoVVxAQBS_Vc_OrnPtqr4g');
        }

        $this->defaultFolder = config('cloudinary.folder', 'khmeriel/products');
    }

    /**
     * Upload an image file (UploadedFile or local file path) to Cloudinary.
     *
     * @param UploadedFile|string $file
     * @param string|null $folder
     * @param string|null $customPublicId
     * @return array
     * @throws Exception
     */
    public function upload(UploadedFile|string $file, ?string $folder = null, ?string $customPublicId = null): array
    {
        $folderName = $folder ?? $this->defaultFolder;
        $timestamp = time();

        $paramsToSign = [
            'folder' => $folderName,
            'timestamp' => $timestamp,
        ];

        if ($customPublicId) {
            $paramsToSign['public_id'] = $customPublicId;
        }

        $signature = $this->generateSignature($paramsToSign);

        $endpoint = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";

        $postData = [
            'api_key'   => $this->apiKey,
            'timestamp' => (string) $timestamp,
            'folder'    => $folderName,
            'signature' => $signature,
        ];

        if ($customPublicId) {
            $postData['public_id'] = $customPublicId;
        }

        if ($file instanceof UploadedFile) {
            $response = Http::timeout(30)
                ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
                ->post($endpoint, $postData);
        } elseif (filter_var($file, FILTER_VALIDATE_URL)) {
            $postData['file'] = $file;
            $response = Http::timeout(30)->asForm()->post($endpoint, $postData);
        } else {
            // Local file path
            if (!file_exists($file)) {
                throw new Exception("File not found at path: {$file}");
            }
            $response = Http::timeout(30)
                ->attach('file', file_get_contents($file), basename($file))
                ->post($endpoint, $postData);
        }

        if ($response->failed()) {
            $errorMsg = $response->json('error.message') ?? $response->body();
            Log::error("Cloudinary Upload Error: {$errorMsg}", ['status' => $response->status()]);
            throw new Exception("Failed to upload image to Cloudinary: {$errorMsg}");
        }

        $result = $response->json();

        return [
            'secure_url' => $result['secure_url'] ?? $result['url'],
            'url'        => $result['url'],
            'public_id'  => $result['public_id'],
            'format'     => $result['format'] ?? null,
            'width'      => $result['width'] ?? null,
            'height'     => $result['height'] ?? null,
            'bytes'      => $result['bytes'] ?? null,
            'created_at' => $result['created_at'] ?? now()->toIso8601String(),
        ];
    }

    /**
     * Delete an image from Cloudinary by public ID or full URL.
     *
     * @param string $publicIdOrUrl
     * @return bool
     * @throws Exception
     */
    public function delete(string $publicIdOrUrl): bool
    {
        $publicId = $this->extractPublicId($publicIdOrUrl);
        $timestamp = time();

        $paramsToSign = [
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ];

        $signature = $this->generateSignature($paramsToSign);

        $endpoint = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/destroy";

        $response = Http::timeout(20)->asForm()->post($endpoint, [
            'public_id' => $publicId,
            'api_key'   => $this->apiKey,
            'timestamp' => (string) $timestamp,
            'signature' => $signature,
        ]);

        if ($response->failed()) {
            $errorMsg = $response->json('error.message') ?? $response->body();
            Log::warning("Cloudinary Delete Warning: {$errorMsg}", ['public_id' => $publicId]);
            return false;
        }

        $result = $response->json();
        return ($result['result'] ?? '') === 'ok';
    }

    /**
     * Generate Cloudinary SHA-1 authentication signature.
     */
    protected function generateSignature(array $params): string
    {
        ksort($params);

        $queryString = '';
        foreach ($params as $key => $value) {
            $queryString .= "{$key}={$value}&";
        }
        $queryString = rtrim($queryString, '&');

        return sha1($queryString . $this->apiSecret);
    }

    /**
     * Extract public_id from a Cloudinary URL if a full URL is provided.
     */
    public function extractPublicId(string $publicIdOrUrl): string
    {
        if (!str_contains($publicIdOrUrl, 'cloudinary.com')) {
            return $publicIdOrUrl;
        }

        // Example: https://res.cloudinary.com/od8t271n/image/upload/v1234567/khmeriel/products/abc123.jpg
        $path = parse_url($publicIdOrUrl, PHP_URL_PATH);
        if (!$path) {
            return $publicIdOrUrl;
        }

        // Remove /image/upload/v\d+/
        $pattern = '#^/?[^/]+/image/upload/(?:v\d+/)?(.*?)(\.[a-zA-Z0-9]+)?$#';
        if (preg_match($pattern, $path, $matches)) {
            return $matches[1];
        }

        return $publicIdOrUrl;
    }
}
