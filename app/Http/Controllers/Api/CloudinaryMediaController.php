<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Cloudinary\Api\Admin\AdminApi;
use Cloudinary\Api\Search\SearchApi;
use Cloudinary\Configuration\Configuration;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CloudinaryMediaController extends Controller
{
    public function __construct()
    {
        $cloudinaryUrl = config('cloudinary.cloud_url') ?? env('CLOUDINARY_URL');

        if ($cloudinaryUrl && str_starts_with($cloudinaryUrl, 'cloudinary://')) {
            Configuration::instance($cloudinaryUrl);
        } else {
            Configuration::instance([
                'cloud' => [
                    'cloud_name' => config('cloudinary.cloud_name') ?? env('CLOUDINARY_CLOUD_NAME', 'od8t271n'),
                    'api_key' => config('cloudinary.api_key') ?? env('CLOUDINARY_API_KEY'),
                    'api_secret' => config('cloudinary.api_secret') ?? env('CLOUDINARY_API_SECRET'),
                ],
                'url' => ['secure' => true],
            ]);
        }
    }

    /**
     * GET /api/v1/cloudinary/folders
     * Lists all 24 root and sub-folders in Cloudinary
     */
    public function getFolders(Request $request): JsonResponse
    {
        try {
            $adminApi = new AdminApi;
            $response = $adminApi->rootFolders();
            $folders = $response['folders'] ?? [];

            $result = array_map(function ($f) {
                return [
                    'name' => $f['name'],
                    'path' => $f['path'],
                ];
            }, $folders);

            return response()->json([
                'success' => true,
                'data' => $result,
                'total' => count($result),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch folders: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/v1/cloudinary/assets
     * Query all 1,843 assets with folder filter, search query, and cursor pagination
     *
     * Query Params:
     * - folder (string, optional): e.g. "jackets", "jerseys", "Nike", "Market"
     * - search (string, optional): e.g. "jordan", "hoodie", "eagle"
     * - max_results (int, optional, default: 60)
     * - next_cursor (string, optional): for fetching next page
     */
    public function getAssets(Request $request): JsonResponse
    {
        try {
            $folder = trim((string) $request->query('folder', ''));
            $isAll = empty($folder) || strtoupper($folder) === 'ALL';
            $defaultMax = $isAll ? 100 : 60;
            $maxResults = max(1, min(500, (int) $request->query('max_results', $defaultMax)));
            $query = trim((string) $request->query('search', ''));
            $nextCursor = $request->query('next_cursor');

            $expression = 'resource_type:image';

            if (! $isAll) {
                // Escape special characters in folder query
                $cleanFolder = preg_replace('/[^\w\-\/\s]/', '', $folder);
                $expression .= ' AND (folder:'.$cleanFolder.' OR folder:'.$cleanFolder.'*)';
            }

            if (! empty($query)) {
                // Strip Lucene special syntax characters to prevent query parsing errors
                $cleanQuery = trim(preg_replace('/[^\w\-\s]/', '', $query));
                if (! empty($cleanQuery)) {
                    $expression .= ' AND (public_id:'.$cleanQuery.'* OR filename:'.$cleanQuery.'* OR tags:'.$cleanQuery.' OR '.$cleanQuery.')';
                }
            }

            $searchApi = (new SearchApi)
                ->expression($expression)
                ->sortBy('created_at', 'desc')
                ->maxResults($maxResults);

            if (! empty($nextCursor)) {
                $searchApi->nextCursor($nextCursor);
            }

            $searchResponse = $searchApi->execute();

            $assets = [];
            foreach ($searchResponse['resources'] ?? [] as $res) {
                $assetFolder = $res['asset_folder'] ?? ($res['folder'] ?? null);
                if (! $assetFolder) {
                    $dir = dirname($res['public_id'] ?? '');
                    $assetFolder = ($dir !== '.' && $dir !== '') ? $dir : ($isAll ? 'root' : $folder);
                }

                $assets[] = [
                    'public_id' => $res['public_id'],
                    'name' => $res['filename'] ?? ($res['display_name'] ?? basename($res['public_id'] ?? '')),
                    'folder' => $assetFolder,
                    'url' => $res['secure_url'] ?? ($res['url'] ?? ''),
                    'format' => $res['format'] ?? 'webp',
                    'width' => $res['width'] ?? 0,
                    'height' => $res['height'] ?? 0,
                    'bytes' => $res['bytes'] ?? 0,
                    'created_at' => $res['created_at'] ?? null,
                ];
            }

            return response()->json([
                'success' => true,
                'total_count' => $searchResponse['total_count'] ?? count($assets),
                'data' => $assets,
                'next_cursor' => $searchResponse['next_cursor'] ?? null,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to query Cloudinary assets: '.$e->getMessage(),
            ], 500);
        }
    }
}
