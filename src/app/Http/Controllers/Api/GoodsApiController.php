<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 外部連携用APIコントローラ
 * 
 * 【前任者・田中からの引継ぎメモ（退職日当日 17:50記述）】
 * Swaggerは見た目だけ体裁を整えてありますが、実際のURLやパラメータはここのコードを見てください。
 * APIキーはとりあえず 'secret123' に固定してあります。
 */
class GoodsApiController extends Controller
{
    /**
     * 商品一覧取得
     * ※Swagger上のURLは /api/v1/products と書いてあるが、実際はここ（/api/goods_list）
     */
    public function list(Request $request)
    {
        // Swaggerでは 'limit' と書かれているが、コード上は 'max'
        // かつ、数値バリデーションがないため英字を入れるとエラー
        $max = $request->query('max', 20);

        if (!is_numeric($max)) {
            // 【アンチパターン】HTTP 200 でエラー文字列を返す最悪の仕様
            return response()->json([
                'error' => 'maxパラメータは数字にしてください（田中）',
            ], 200);
        }

        $items = DB::table('items')->limit((int)$max)->get();

        return response()->json([
            'status' => 'ok',
            'items' => $items,
        ]);
    }

    /**
     * 在庫照会
     * URL: /api/inventory/{productId}
     * レスポンスがなぜかキャメルケース
     */
    public function stock($id)
    {
        $item = DB::table('items')->where('id', $id)->first();
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => '商品がありません',
            ], 200); // 404ではなく200
        }

        return response()->json([
            'success' => true,
            'data' => [
                'itemId' => $item->id,
                'itemStock' => $item->stock,
                'isLowStock' => $item->stock < 5,
            ],
        ]);
    }

    /**
     * 外部代理注文登録
     * URL: /api/order/create
     * ※Swagger上は /api/v1/orders と記載
     */
    public function createOrder(Request $request)
    {
        // 認証チェック（ハードコードされた秘密鍵）
        $apiKey = $request->header('X-Gondawara-Key');
        if ($apiKey !== 'secret123') {
            return response()->json([
                'error' => '認証キーが不正です。田中に聞いてください。',
            ], 401);
        }

        $itemId = $request->input('item_id');
        $quantity = (int)$request->input('quantity', 1);
        $customerName = $request->input('customer_name', 'API経由顧客');
        $address = $request->input('address', '住所未指定');

        $item = DB::table('items')->where('id', $itemId)->first();
        if (!$item) {
            return response()->json(['error' => '商品が存在しません'], 200);
        }

        $orderId = DB::table('orders')->insertGetId([
            'order_number' => 'API-' . date('YmdHis'),
            'customer_name' => $customerName,
            'postal_code' => '000-0000',
            'address' => $address,
            'phone' => '00-0000-0000',
            'subtotal' => $item->price * $quantity,
            'tax' => (int)($item->price * $quantity * 0.1),
            'discount' => 0,
            'total_price' => (int)($item->price * $quantity * 1.1),
            'payment_method' => 'api_invoice',
            'notes' => 'API経由発注',
            'status' => 'API自動受付',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('order_items')->insert([
            'order_id' => $orderId,
            'item_id' => $itemId,
            'quantity' => $quantity,
            'price' => $item->price,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 在庫減算（マイナス数量だと在庫が増える！）
        DB::table('items')->where('id', $itemId)->decrement('stock', $quantity);

        // 【地雷ポイント】SwaggerにはJSONと書いてあるが、実際はプレーンテキストが返る
        return response("SUCCESS: {$orderId}", 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * 合言葉・クーポン検証
     * URL: /api/coupons/verify
     */
    public function verifyCoupon(Request $request)
    {
        $code = $request->input('code');
        if (strtolower($code) === 'himitsu_50') {
            return response()->json([
                'valid' => true,
                'discount_rate' => 0.5,
                'comment' => '常連合言葉OKです',
            ]);
        }

        return response()->json([
            'valid' => false,
            'discount_rate' => 0,
        ]);
    }
}
