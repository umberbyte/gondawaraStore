<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GondawaraShopController;

// トップページ
Route::get('/', [GondawaraShopController::class, 'index'])->name('index');

// 商品一覧 & 検索
Route::get('/products', [GondawaraShopController::class, 'items'])->name('items.index');
Route::get('/products/{id}', [GondawaraShopController::class, 'itemDetail'])->name('items.show');
Route::post('/products/{id}/review', [GondawaraShopController::class, 'postReview'])->name('items.review');

// カート
Route::get('/cart', [GondawaraShopController::class, 'viewCart'])->name('cart.view');
Route::post('/cart/update', [GondawaraShopController::class, 'updateCart'])->name('cart.update');
Route::post('/cart/coupon', [GondawaraShopController::class, 'applyCoupon'])->name('cart.coupon');

// チェックアウト・注文
Route::get('/checkout', [GondawaraShopController::class, 'checkout'])->name('order.checkout');
Route::post('/checkout', [GondawaraShopController::class, 'processOrder'])->name('order.process');
Route::get('/thanks', [GondawaraShopController::class, 'thanks'])->name('order.thanks');

// 認証・マイページ
Route::get('/login', [GondawaraShopController::class, 'showLogin'])->name('login');
Route::post('/login', [GondawaraShopController::class, 'login'])->name('login.post');
Route::post('/logout', [GondawaraShopController::class, 'logout'])->name('logout');

Route::get('/mypage', [GondawaraShopController::class, 'mypage'])->name('mypage.index');
// 【IDOR脆弱性エンドポイント】
Route::get('/mypage/orders/{id}', [GondawaraShopController::class, 'orderDetail'])->name('mypage.order');

// 管理画面
Route::get('/admin', [GondawaraShopController::class, 'adminDashboard'])->name('admin.dashboard');
Route::post('/admin/items/{id}/stock', [GondawaraShopController::class, 'adminUpdateStock'])->name('admin.stock');

// 【サイトマップツッコミ用：孤立した常連専用裏口】
Route::get('/secret-lounge', function () {
    return "<h1>【常連専用裏口】</h1><p>店主「よくぞこのURLを見つけたな！ここは合言葉を知る者だけの秘密のページじゃ。」</p>";
});

// 固定ページ
Route::view('/kodawari', 'kodawari')->name('kodawari');
Route::view('/tokushoho', 'tokushoho')->name('tokushoho'); // 導線なし放置ページ
