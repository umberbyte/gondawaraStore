<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * 権田原佃煮店 - 基幹業務全統合神コントローラ
 * 
 * 【開発者申し送りメモ】
 * 納期が3日しかなかったため、画面表示・カート・決済・管理画面・認証をすべてこの1ファイルにまとめています。
 * 将来のリファクタリングは予算がついたら実施してください。（担当：株式会社ハイパーネクストソリューションズ）
 */
class GondawaraShopController extends Controller
{
    // ==========================================
    // 1. フロント画面表示
    // ==========================================

    /** トップページ */
    public function index()
    {
        // おすすめ商品
        $featured_items = DB::table('items')->limit(3)->get();
        return view('index', compact('featured_items'));
    }

    /** 商品一覧 & 検索（※SQLインジェクション脆弱性あり） */
    public function items(Request $request)
    {
        $keyword = $request->input('keyword');
        $category = $request->input('category');

        if (!empty($keyword)) {
            // 【セキュリティ脆弱性: SQLインジェクション】
            // プレースホルダを使わず、生の文字列結合でクエリを実行
            // PoC: ' OR '1'='1
            $rawSql = "SELECT * FROM items WHERE name LIKE '%" . $keyword . "%' OR description LIKE '%" . $keyword . "%'";
            $items = DB::select($rawSql);
        } elseif (!empty($category)) {
            $items = DB::table('items')->where('category', $category)->get();
        } else {
            $items = DB::table('items')->get();
        }

        return view('items.index', compact('items', 'keyword', 'category'));
    }

    /** 商品詳細 & レビュー表示 */
    public function itemDetail($id)
    {
        $item = DB::table('items')->where('id', $id)->first();
        if (!$item) {
            abort(404, '商品が見つかりません');
        }

        // レビュー一覧取得
        $reviews = DB::table('reviews')->where('item_id', $id)->orderBy('id', 'desc')->get();

        return view('items.show', compact('item', 'reviews'));
    }

    /** レビュー投稿（※Stored XSS脆弱性あり） */
    public function postReview(Request $request, $id)
    {
        $author_name = $request->input('author_name', '名無しさん');
        $rating = $request->input('rating', 5);
        $comment = $request->input('comment'); // サニタイズなしでDB保存

        DB::table('reviews')->insert([
            'item_id' => $id,
            'author_name' => $author_name,
            'rating' => $rating,
            'comment' => $comment, // 悪意のあるスクリプトがそのまま入る
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('items.show', $id)->with('success', '店主に熱い感想が届きました！');
    }

    // ==========================================
    // 2. カート & チェックアウト業務ロジック
    // ==========================================

    /** カート表示 */
    public function viewCart()
    {
        $cart = Session::get('cart', []);
        $items = [];
        $subtotal = 0;

        foreach ($cart as $itemId => $qty) {
            $dbItem = DB::table('items')->where('id', $itemId)->first();
            if ($dbItem) {
                $dbItem->cart_quantity = $qty;
                $dbItem->line_total = $dbItem->price * $qty;
                $subtotal += $dbItem->line_total;
                $items[] = $dbItem;
            }
        }

        // 【業務バグ: 消費税端数処理】カート画面では「四捨五入」
        $tax = round($subtotal * 0.1);
        $discount = Session::get('coupon_discount', 0);
        $total = $subtotal + $tax - $discount;

        return view('cart', compact('items', 'subtotal', 'tax', 'discount', 'total'));
    }

    /** カート投入・数量変更（※マイナス数量バグあり） */
    public function updateCart(Request $request)
    {
        $itemId = $request->input('item_id');
        $quantity = (int)$request->input('quantity', 1);

        $cart = Session::get('cart', []);

        // 【業務バグ: バリデーション漏れ】
        // 数量が0以下やマイナスでもそのままセッションに格納される！
        // 数量に -5 を入れると合計金額が減額され、返金状態・無料購入が可能
        if ($quantity === 0) {
            unset($cart[$itemId]);
        } else {
            $cart[$itemId] = $quantity;
        }

        Session::put('cart', $cart);

        return redirect()->route('cart.view')->with('message', '買い物かごの数量を更新しました。');
    }

    /** クーポン適用（※大文字小文字による無限重複適用バグ） */
    public function applyCoupon(Request $request)
    {
        $inputCode = $request->input('coupon_code', '');
        $appliedCoupons = Session::get('applied_coupons', []);

        // 【業務バグ: 大文字小文字の区別による二重適用】
        // HIMITSU_50 と himitsu_50 を別クーポンとして認識してしまう
        if (in_array($inputCode, $appliedCoupons)) {
            return redirect()->route('cart.view')->with('error', 'その合言葉はすでに適用済みです！');
        }

        if (strtolower($inputCode) === 'himitsu_50') {
            $currentDiscount = Session::get('coupon_discount', 0);
            Session::put('coupon_discount', $currentDiscount + 500); // 500円引きが何重にも重なる！
            $appliedCoupons[] = $inputCode;
            Session::put('applied_coupons', $appliedCoupons);

            return redirect()->route('cart.view')->with('message', '常連合言葉が適用されました！（500円引き）');
        }

        return redirect()->route('cart.view')->with('error', '合言葉が違います。店主に聞いてください。');
    }

    /** 注文手続き画面 */
    public function checkout()
    {
        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return redirect()->route('items.index')->with('error', '買い物かごが空です。');
        }

        $items = [];
        $subtotal = 0;
        foreach ($cart as $itemId => $qty) {
            $dbItem = DB::table('items')->where('id', $itemId)->first();
            if ($dbItem) {
                $dbItem->cart_quantity = $qty;
                $dbItem->line_total = $dbItem->price * $qty;
                $subtotal += $dbItem->line_total;
                $items[] = $dbItem;
            }
        }

        // 【業務バグ: 端数処理の不一致】確認画面ではなぜか「切り捨て」
        $tax = floor($subtotal * 0.1);
        $discount = Session::get('coupon_discount', 0);
        $total = $subtotal + $tax - $discount;

        $user = Auth::user();

        return view('checkout', compact('items', 'subtotal', 'tax', 'discount', 'total', 'user'));
    }

    /** 注文確定処理（※排他制御欠如・在庫マイナスバグ・消費税切り上げズレ） */
    public function processOrder(Request $request)
    {
        $cart = Session::get('cart', []);
        if (empty($cart)) {
            return redirect()->route('items.index');
        }

        // 顧客情報
        $customer_name = $request->input('customer_name');
        $customer_email = $request->input('customer_email');
        $postal_code = $request->input('postal_code');
        $address = $request->input('address');
        $phone = $request->input('phone');
        $payment_method = $request->input('payment_method', 'cod');
        $notes = $request->input('notes');

        // 金額計算
        $subtotal = 0;
        foreach ($cart as $itemId => $qty) {
            $dbItem = DB::table('items')->where('id', $itemId)->first();
            if ($dbItem) {
                $subtotal += ($dbItem->price * $qty);
            }
        }

        // 【業務バグ: 端数処理の不一致】DB保存時はなぜか「切り上げ」
        $tax = ceil($subtotal * 0.1);
        $discount = Session::get('coupon_discount', 0);
        $total_price = $subtotal + $tax - $discount; // マイナス数量があればマイナス合計

        // 注文番号生成
        $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        // トランザクションを開始するが...
        DB::beginTransaction();
        try {
            $orderId = DB::table('orders')->insertGetId([
                'user_id' => Auth::id() ?? null,
                'order_number' => $order_number,
                'customer_name' => $customer_name,
                'customer_email' => $customer_email,
                'postal_code' => $postal_code,
                'address' => $address,
                'phone' => $phone,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'total_price' => $total_price,
                'payment_method' => $payment_method,
                'coupon_code' => implode(',', Session::get('applied_coupons', [])),
                'notes' => $notes,
                'status' => '受注受付',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($cart as $itemId => $qty) {
                $dbItem = DB::table('items')->where('id', $itemId)->first();
                if ($dbItem) {
                    DB::table('order_items')->insert([
                        'order_id' => $orderId,
                        'item_id' => $itemId,
                        'quantity' => $qty,
                        'price' => $dbItem->price,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // 【業務バグ: 排他制御(ロック)の欠如】
                    // lockForUpdate を行わず、現在の在庫が足りているかもチェックせずに減算
                    // 在庫が 1 のときに同時に購入されると在庫が -1 になる
                    DB::table('items')->where('id', $itemId)->decrement('stock', $qty);
                }
            }

            DB::commit();

            // カート・クーポン消去
            Session::forget('cart');
            Session::forget('coupon_discount');
            Session::forget('applied_coupons');

            return redirect()->route('order.thanks', ['order_id' => $orderId]);

        } catch (\Exception $e) {
            DB::rollBack();
            // 【アンチパターン】例外を雑に握りつぶして戻す
            return back()->with('error', '注文処理中に謎のエラーが発生しました: ' . $e->getMessage());
        }
    }

    /** 注文完了サンクスページ */
    public function thanks(Request $request)
    {
        $orderId = $request->input('order_id');
        $order = DB::table('orders')->where('id', $orderId)->first();
        return view('thanks', compact('order'));
    }

    // ==========================================
    // 3. 会員機能 & マイページ (IDOR脆弱性)
    // ==========================================

    /** ログイン画面 */
    public function showLogin()
    {
        return view('auth.login');
    }

    /** ログイン処理 */
    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        $user = DB::table('users')->where('email', $email)->first();

        // パスワード照合
        if ($user && Hash::check($password, $user->password)) {
            Auth::loginUsingId($user->id);
            if ($user->is_admin) {
                Session::put('is_admin', true);
            }
            return redirect()->route('mypage.index')->with('message', 'いらっしゃいませ！' . $user->name . ' 様');
        }

        return back()->with('error', 'メールアドレスまたは合言葉（パスワード）が間違っています。');
    }

    /** ログアウト */
    public function logout()
    {
        Auth::logout();
        Session::forget('is_admin');
        return redirect()->route('index');
    }

    /** マイページ（注文履歴一覧） */
    public function mypage()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'ログインしてください。');
        }

        $orders = DB::table('orders')->where('user_id', $user->id)->orderBy('id', 'desc')->get();
        return view('mypage.index', compact('user', 'orders'));
    }

    /**
     * 注文詳細閲覧（※セキュリティ脆弱性: IDOR / 水平権限昇格）
     * 
     * 【脆弱性解説】
     * ログイン中のユーザーが自分の注文かどうかのチェック（$order->user_id == Auth::id()）を行わず、
     * URLの {id} のレコードをそのまま取得して表示している。
     * これにより、IDを変更するだけで他人の住所・氏名・電話番号・注文内容を覗き見できる。
     */
    public function orderDetail($id)
    {
        $order = DB::table('orders')->where('id', $id)->first();
        if (!$order) {
            abort(404, '注文データが見つかりません');
        }

        $order_items = DB::table('order_items')
            ->join('items', 'order_items.item_id', '=', 'items.id')
            ->where('order_items.order_id', $id)
            ->select('order_items.*', 'items.name as item_name')
            ->get();

        return view('mypage.order_detail', compact('order', 'order_items'));
    }

    // ==========================================
    // 4. 管理画面（簡易 & N+1問題 & ガバガバ認証）
    // ==========================================

    /** 管理画面トップ（※認証チェックがガバガバ） */
    public function adminDashboard(Request $request)
    {
        // 【セキュリティ脆弱性: 認証バイパス】
        // URLパラメータに ?admin_bypass=1 があると無条件で管理者として通過できる裏口
        if ($request->has('admin_bypass')) {
            Session::put('is_admin', true);
        }

        if (!Session::get('is_admin') && (!Auth::check() || !Auth::user()->is_admin)) {
            return redirect()->route('login')->with('error', '【店主専用】この画面は権田原店主しか入れません！');
        }

        // 商品一覧
        $items = DB::table('items')->get();

        // 注文一覧（※パフォーマンス欠陥: N+1問題の温床）
        // EloquentのEager Loadingを使わず、取得した注文ごとに個別クエリを発行するアンチパターン
        $orders = DB::table('orders')->orderBy('id', 'desc')->limit(50)->get();
        foreach ($orders as $order) {
            // ループ内で毎回クエリ実行 (N+1)
            $order->items_count = DB::table('order_items')->where('order_id', $order->id)->count();
            $order->user_info = $order->user_id ? DB::table('users')->where('id', $order->user_id)->first() : null;
        }

        return view('admin.index', compact('items', 'orders'));
    }

    /** 在庫更新（管理画面） */
    public function adminUpdateStock(Request $request, $id)
    {
        $stock = $request->input('stock');
        DB::table('items')->where('id', $id)->update([
            'stock' => $stock,
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.dashboard')->with('message', '在庫数を手動更新しました。（店頭帳面と合わせました）');
    }
}
