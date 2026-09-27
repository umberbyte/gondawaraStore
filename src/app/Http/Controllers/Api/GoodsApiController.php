<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * 権田原佃煮店 外部連携APIコントローラ (v1)
 * 
 * 【テスター育成・QA研修用コード】
 * 本APIはドキュメント（docs/api/openapi.yaml）と「だいたい合っている」ように見えますが、
 * 境界値、バリデーション、HTTPステータス、レスポンスの型やキー名に意図的な不整合が散りばめられています。
 */
class GoodsApiController extends Controller
{
    /**
     * 1. 商品一覧取得 & 検索
     * GET /api/v1/items
     */
    public function list(Request $request)
    {
        $keyword = $request->query('keyword');
        $category = $request->query('category');
        $page = (int)$request->query('page', 1);
        $limit = (int)$request->query('limit', 20);

        // 【テスター向け不整合 1】
        // ドキュメントには「limit: max 100」とあるが、コード側で勝手に 50 件に制限されている
        $actualLimit = min($limit, 50);

        $query = DB::table('items');

        if (!empty($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%{$keyword}%")
                  ->orWhere('description', 'LIKE', "%{$keyword}%");
            });
        }

        if (!empty($category)) {
            $query->where('category', $category);
        }

        $total = $query->count();
        $offset = ($page - 1) * $actualLimit;
        $items = $query->offset($offset)->limit($actualLimit)->get();

        // 【テスター向け不整合 2】
        // ドキュメントには「price: integer」とあるが、なぜか文字列型で返却される
        $formattedItems = $items->map(function ($it) {
            return [
                'id' => (int)$it->id,
                'name' => $it->name,
                'price' => (string)$it->price, // ※stringになっている！
                'stock' => (int)$it->stock,
                'category' => $it->category,
                'description' => $it->description,
                'image_url' => $it->image_url,
            ];
        });

        // 【テスター向け不整合 3】
        // ドキュメントには「current_page」とあるが、実際のキーは「page」
        return response()->json([
            'status' => 'success',
            'total' => $total,
            'page' => $page,
            'limit' => $actualLimit,
            'items' => $formattedItems,
        ], 200);
    }

    /**
     * 2. 商品詳細取得
     * GET /api/v1/items/{id}
     */
    public function detail($id)
    {
        $item = DB::table('items')->where('id', $id)->first();

        if (!$item) {
            // 【テスター向け不整合 4】
            // ドキュメントには {"message": "..."} とあるが、実装はキー名が "error" になっている
            return response()->json([
                'error' => '指定された商品が見つかりません',
                'code' => 404,
            ], 404);
        }

        return response()->json([
            'id' => (int)$item->id,
            'name' => $item->name,
            'price' => (int)$item->price,
            'stock' => (int)$item->stock,
            'category' => $item->category,
            'description' => $item->description,
            // 【テスター向け不整合 5】
            // ドキュメントには imageUrl（キャメルケース）とあるが、実際は image_url（スネークケース）
            'image_url' => $item->image_url,
        ], 200);
    }

    /**
     * 3. 商品在庫照会
     * GET /api/v1/items/{id}/stock
     */
    public function stock($id)
    {
        $item = DB::table('items')->where('id', $id)->first();

        if (!$item) {
            return response()->json([
                'error' => '商品が存在しません',
            ], 404);
        }

        // 【テスター向け不整合 6: 境界値の不具合】
        // ドキュメントには「stock >= 1 で in_stock: true」とあるが、
        // 開発者が誤って > 1 で判定しているため、在庫がちょうど「1個」のとき false になってしまう！
        $inStock = $item->stock > 1;

        return response()->json([
            'item_id' => (int)$item->id,
            'stock' => (int)$item->stock,
            'in_stock' => $inStock,
        ], 200);
    }

    /**
     * 4. 代理注文・外部受注登録
     * POST /api/v1/orders
     */
    public function createOrder(Request $request)
    {
        // 認証ヘッダーチェック
        $apiKey = $request->header('X-Gondawara-Api-Key');
        if ($apiKey !== 'gondawara-secret-api-key-2026') {
            return response()->json([
                'message' => 'APIキーが無効または未指定です。',
            ], 401);
        }

        // 【テスター向け不整合 7 & 8: バリデーションの嘘】
        // ドキュメントには「quantity: min 1」とあるが、minチェックがなくマイナス値が通る
        // ドキュメントには「phone: required」とあるが、コード側で検証されておらず未指定でも通る
        $itemId = $request->input('item_id');
        $quantity = (int)$request->input('quantity', 1);
        $customerName = $request->input('customer_name');
        $postalCode = $request->input('postal_code');
        $address = $request->input('address');
        $phone = $request->input('phone'); // 未指定でもスルーされる
        $notes = $request->input('notes');

        if (!$itemId || !$customerName || !$postalCode || !$address) {
            return response()->json([
                'message' => '必須パラメータが不足しています。',
            ], 422);
        }

        $item = DB::table('items')->where('id', $itemId)->first();
        if (!$item) {
            return response()->json(['message' => '指定された商品が存在しません。'], 404);
        }

        $subtotal = $item->price * $quantity;
        $tax = (int)ceil($subtotal * 0.1);
        $totalPrice = $subtotal + $tax;
        $orderNumber = 'API-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        $orderId = DB::table('orders')->insertGetId([
            'order_number' => $orderNumber,
            'customer_name' => $customerName,
            'customer_email' => 'api-order@example.com',
            'postal_code' => $postalCode,
            'address' => $address,
            'phone' => $phone ?: '00-0000-0000',
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => 0,
            'total_price' => $totalPrice,
            'payment_method' => 'api_invoice',
            'notes' => $notes,
            'status' => 'API受付完了',
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

        DB::table('items')->where('id', $itemId)->decrement('stock', $quantity);

        // 【テスター向け不整合 9: HTTPステータスコード】
        // ドキュメントには「201 Created」と書かれているが、実装は 200 OK を返している
        return response()->json([
            'order_id' => (int)$orderId,
            'order_number' => $orderNumber,
            'total_price' => $totalPrice,
            'status' => 'API受付完了',
        ], 200);
    }

    /**
     * 5. 合言葉・クーポン検証
     * POST /api/v1/coupons/verify
     */
    public function verifyCoupon(Request $request)
    {
        $code = $request->input('code');

        if (empty($code)) {
            return response()->json(['message' => '合言葉コードは必須です。'], 422);
        }

        if (strtoupper(trim($code)) === 'HIMITSU_50') {
            // 【テスター向け不整合 10: レスポンススキーマの相違】
            // ドキュメントには「discount_amount: integer (500)」とあるが、
            // 実装はなぜか「discount_rate: number (0.5)」が返ってくる
            return response()->json([
                'valid' => true,
                'discount_rate' => 0.5,
                'message' => '常連合言葉が正常に認証されました（半額適用）',
            ], 200);
        }

        // 【テスター向け不整合 11: エラー時のステータスコード】
        // ドキュメントには「無効な場合 200 OK で valid: false が返る」とあるが、
        // 実際は 400 Bad Request が返却される
        return response()->json([
            'valid' => false,
            'error' => '無効な合言葉コードです。',
        ], 400);
    }

    /**
     * 6. レビュー一覧取得
     * GET /api/v1/reviews
     */
    public function reviews(Request $request)
    {
        $itemId = $request->query('item_id');

        if (!$itemId) {
            return response()->json(['message' => 'item_id パラメータは必須です。'], 422);
        }

        $reviews = DB::table('reviews')
            ->where('item_id', $itemId)
            ->orderBy('id', 'desc')
            ->get();

        // 【テスター向け不整合 12: 日時フォーマット】
        // ドキュメントには「created_at は RFC3339/ISO8601 (YYYY-MM-DDTHH:MM:SSZ)」とあるが、
        // 実際はDB文字列そのままの「Y-m-d H:i:s」形式で返される
        $formatted = $reviews->map(function ($r) {
            return [
                'id' => (int)$r->id,
                'item_id' => (int)$r->item_id,
                'author_name' => $r->author_name,
                'rating' => (int)$r->rating,
                'comment' => $r->comment,
                'created_at' => (string)$r->created_at, // 例: 2026-09-25 12:00:00
            ];
        });

        return response()->json([
            'item_id' => (int)$itemId,
            'total' => count($formatted),
            'reviews' => $formatted,
        ], 200);
    }

    /**
     * 7. レビュー投稿
     * POST /api/v1/reviews
     */
    public function createReview(Request $request)
    {
        $itemId = $request->input('item_id');
        $authorName = $request->input('author_name', '名無しさん');
        $rating = $request->input('rating');
        $comment = $request->input('comment');

        // 【テスター向け不整合 13: 境界値バリデーション漏れ】
        // ドキュメントには「rating: 1〜5」と書かれているが、
        // コード側で 0 や 6 以上の数値チェックが抜けており、範囲外の値もそのまま保存される
        if (!$itemId || $rating === null || empty($comment)) {
            return response()->json(['message' => '必須パラメータが不足しています。'], 422);
        }

        $reviewId = DB::table('reviews')->insertGetId([
            'item_id' => $itemId,
            'author_name' => $authorName,
            'rating' => (int)$rating,
            'comment' => $comment,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'id' => (int)$reviewId,
            'item_id' => (int)$itemId,
            'author_name' => $authorName,
            'rating' => (int)$rating,
            'comment' => $comment,
            'created_at' => (string)now(),
        ], 201);
    }
}
