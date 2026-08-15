<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Official Default Laravel Welcome View & JSON API Root
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (request()->wantsJson() || request()->is('api*')) {
        return response()->json([
            'success'       => true,
            'name'          => 'CSMS-API (Store Stock & Point-of-Sale System)',
            'version'       => '1.0.0',
            'status'        => 'online',
            'documentation' => 'https://api.kesararamwithdigital.tech/API_DOCS.md',
            'api_base_url'  => 'https://api.kesararamwithdigital.tech/api/v1',
            'database'      => 'Neon Cloud PostgreSQL',
        ]);
    }

    return view('welcome');
});
