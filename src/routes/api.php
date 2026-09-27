<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GoodsApiController;

/**
 * 外部連携APIルーティング
 * 
 * ※Swaggerには /api/v1/products と書かれていますが、実際のルートは以下です。
 */

// 商品一覧API（Swaggerでは /v1/products）
Route::get('/goods_list', [GoodsApiController::class, 'list']);

// 在庫照会API
Route::get('/inventory/{productId}', [GoodsApiController::class, 'stock']);

// 外部注文API（Swaggerでは /v1/orders）
Route::post('/order/create', [GoodsApiController::class, 'createOrder']);

// 合言葉検証API
Route::post('/coupons/verify', [GoodsApiController::class, 'verifyCoupon']);
