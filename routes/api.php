<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\GalleryController;
use App\Http\Controllers\CommonController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('api.token')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/{category_url}', [CategoryController::class, 'categoryData']);

Route::prefix('common')->controller(CommonController::class)->group(function () {
    Route::get('definitions', 'definitions');
    Route::get('categories', 'categories');
    Route::get('doctors', 'doctors');
});


Route::middleware('check.api')->group(function () {
    Route::get('galleries', [GalleryController::class, 'galleries']);
});