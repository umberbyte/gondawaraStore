<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GoodsApiController;

/**
 * 権田原佃煮店 外部連携・公開API (v1)
 */

Route::prefix('v1')->group(function () {
    // 1. 商品一覧・検索
    Route::get('/items', [GoodsApiController::class, 'list']);
    // 2. 商品詳細
    Route::get('/items/{id}', [GoodsApiController::class, 'detail']);
    // 3. 在庫照会
    Route::get('/items/{id}/stock', [GoodsApiController::class, 'stock']);
    // 4. 代理注文・外部受注
    Route::post('/orders', [GoodsApiController::class, 'createOrder']);
    // 5. 合言葉・クーポン検証
    Route::post('/coupons/verify', [GoodsApiController::class, 'verifyCoupon']);
    // 6. レビュー一覧
    Route::get('/reviews', [GoodsApiController::class, 'reviews']);
    // 7. レビュー投稿
    Route::post('/reviews', [GoodsApiController::class, 'createReview']);
});

// 後方互換用レガシールート（前任者の残骸）
Route::get('/goods_list', [GoodsApiController::class, 'list']);
Route::get('/inventory/{productId}', [GoodsApiController::class, 'stock']);
Route::post('/order/create', [GoodsApiController::class, 'createOrder']);
