# 「権田原佃煮店」講師用ガイド・バグ修正模範解答集

本資料は、教材パッケージ「権田原佃煮店」に仕込まれた10大バグ・脆弱性の詳細、再現手順、および模範修正コードを解説した講師・メンター向けの手引きです。

---

## 1. 仕込まれたバグ・脆弱性一覧と配点目安

| No | 種別 | 難易度 | バグ・脆弱性の内容 | 影響度 |
|:---|:---|:---|:---|:---|
| **1** | 業務ロジック | 初級 | カート数量のマイナス値入力による無料・返金購入 | 致命的 (金銭被害) |
| **2** | 業務ロジック | 中級 | 画面・DB間での消費税端数処理の不一致 (四捨五入/切り捨て/切り上げ) | 中 (経理不整合) |
| **3** | 業務ロジック | 中級 | 排他制御欠如による在庫マイナス販売 (レースコンディション) | 高 (欠品・出荷不能) |
| **4** | 業務ロジック | 初級 | 大文字小文字判定の甘さによる同一クーポンの無限重複適用 | 高 (利益率悪化) |
| **5** | セキュリティ | 初級 | 商品検索窓におけるSQLインジェクション (生SQL文字列結合) | 致命的 (情報漏洩) |
| **6** | セキュリティ | 初級 | 商品レビュー欄におけるStored XSS (Blade `{!! !!}` 出力) | 致命的 (セッション乗っ取り) |
| **7** | セキュリティ | 中級 | 注文詳細閲覧におけるIDOR / 水平権限昇格 (他人の個人情報漏洩) | 致命的 (プライバシー侵害) |
| **8** | セキュリティ | 初級 | URLパラメータによる管理画面認証バイパス (`?admin_bypass=1`) | 致命的 (不正アクセス) |
| **9** | パフォーマンス | 初級 | 管理画面の受注一覧におけるN+1クエリ問題 | 中 (DB負荷増大) |
| **10** | 設計・運用 | 初級 | 巨大神コントローラ (`GondawaraShopController.php`) & 本番デバッグ露出 | 高 (保守性崩壊・情報漏洩) |

---

## 2. 各バグの再現手順と修正模範解答

### バグ1: カート数量のマイナス値入力
* **該当ファイル**: `app/Http/Controllers/GondawaraShopController.php` (`updateCart` メソッド)
* **再現手順**:
  1. 商品詳細画面またはカート画面で、数量に `-1` を入力して更新。
  2. 商品小計がマイナスになり、合計金額がマイナス（または相殺）された状態で注文完了できる。
* **修正例**:
  ```php
  // 修正前:
  $quantity = (int)$request->input('quantity', 1);

  // 修正後:
  $request->validate([
      'quantity' => 'required|integer|min:0|max:99',
  ]);
  $quantity = (int)$request->input('quantity');
  ```

---

### バグ2: 消費税端数処理の不一致
* **該当箇所**:
  * カート画面 (`viewCart`): `round($subtotal * 0.1)` (四捨五入)
  * 確認画面 (`checkout`): `floor($subtotal * 0.1)` (切り捨て)
  * DB保存時 (`processOrder`): `ceil($subtotal * 0.1)` (切り上げ)
* **問題点**:
  単価に端数が出る場合（例: 885円など）、確認画面に表示された金額と、サンクス画面・DBに保存された金額で「1円のズレ」が生じる。
* **修正例**:
  システム全体で「切り捨て（`floor`）」または「四捨五入（`round`）」に統一する定数またはサービスメソッドを用意する。

---

### バグ3: 在庫減算時の排他制御欠如（レースコンディション）
* **該当ファイル**: `app/Http/Controllers/GondawaraShopController.php` (`processOrder` メソッド)
* **再現手順**:
  在庫が残り1個の商品（限定うなぎ佃煮 ID:6）を、別ブラウザで同時に購入確定すると、在庫が `0` を下回り `-1` になる。
* **修正例**:
  ```php
  // 修正後:
  $item = DB::table('items')->where('id', $itemId)->lockForUpdate()->first();
  if ($item->stock < $qty) {
      throw new \Exception("申し訳ありません。「{$item->name}」は在庫不足です。");
  }
  DB::table('items')->where('id', $itemId)->decrement('stock', $qty);
  ```

---

### バグ4: クーポンコードの無限重複適用
* **該当ファイル**: `app/Http/Controllers/GondawaraShopController.php` (`applyCoupon` メソッド)
* **再現手順**:
  カート画面で合言葉に `HIMITSU_50` を適用した後、続けて `himitsu_50` や `Himitsu_50` を入力すると、重複チェックをすり抜けて値引き額が 500円 &rarr; 1000円 &rarr; 1500円 と加算される。
* **修正例**:
  ```php
  // 修正後: 大文字に正規化して判定
  $normalizedCode = strtoupper(trim($request->input('coupon_code', '')));
  if (in_array($normalizedCode, $appliedCoupons)) {
      return redirect()->route('cart.view')->with('error', 'すでに適用済みの合言葉です。');
  }
  ```

---

### バグ5: 検索窓のSQLインジェクション (SQLi)
* **該当ファイル**: `app/Http/Controllers/GondawaraShopController.php` (`items` メソッド)
* **PoC (攻撃実証)**:
  検索窓に `' OR '1'='1` を入力して検索。全商品がヒットする。または `' UNION SELECT ...` 等で他テーブルのデータ抽出が可能。
* **修正例**:
  ```php
  // 修正前:
  $rawSql = "SELECT * FROM items WHERE name LIKE '%" . $keyword . "%' OR description LIKE '%" . $keyword . "%'";
  $items = DB::select($rawSql);

  // 修正後: クエリビルダでプレースホルダを使用
  $items = DB::table('items')
      ->where('name', 'LIKE', "%{$keyword}%")
      ->orWhere('description', 'LIKE', "%{$keyword}%")
      ->get();
  ```

---

### バグ6: レビュー欄のStored XSS
* **該当ファイル**: `resources/views/items/show.blade.php`
* **PoC (攻撃実証)**:
  レビュー投稿フォームのコメント欄に `<script>alert(document.cookie);</script>` を投稿。詳細画面をリロードするとアラートが表示される。
* **修正例**:
  ```html
  <!-- 修正前: -->
  {!! $rev->comment !!}

  <!-- 修正後: Bladeの標準エスケープと改行反映 -->
  {!! nl2br(e($rev->comment)) !!}
  ```

---

### バグ7: 注文詳細のIDOR（水平権限昇格）
* **該当ファイル**: `app/Http/Controllers/GondawaraShopController.php` (`orderDetail` メソッド)
* **再現手順**:
  山田太郎（ユーザーID: 2）でログイン後、マイページのURL `/mypage/orders/1001` から、数字を `/mypage/orders/1002` に変更する。佐藤花子の氏名・配送先住所・電話番号が丸見えになる。
* **修正例**:
  ```php
  // 修正後: ログインユーザーの所有物かチェック
  $order = DB::table('orders')
      ->where('id', $id)
      ->where('user_id', Auth::id())
      ->first();

  if (!$order && !Auth::user()->is_admin) {
      abort(403, '他のお客様のご注文明細は閲覧できません。');
  }
  ```

---

### バグ8: 管理画面の認証バイパス
* **該当ファイル**: `app/Http/Controllers/GondawaraShopController.php` (`adminDashboard` メソッド)
* **再現手順**:
  未ログインの状態で `/admin?admin_bypass=1` に直接アクセスすると、認証をすり抜けて管理画面に入室できる。
* **修正例**:
  クエリパラメータによる裏口コードを全削除し、Laravel標準のミドルウェア（`auth` および認可ゲート）で管理者を厳格に判定する。

---

## 3. 【テスター育成・QA研修用】API仕様書 vs 実装の「間違い探し」チェックリスト

本教材のAPI（`/api/v1/...`）は、仕様書（`docs/api/openapi.yaml`）と「だいたい合っている」ように見えますが、テスターの着眼点（境界値、型チェック、HTTPステータス、エラーハンドリング等）を試す**13箇所の不整合・バグ**が仕込まれています。

| No | 対象エンドポイント | 仕様書の記述（ドキュメント） | 実際のバックエンド挙動（実装） | テスターの着眼点・テスト技法 |
|:---|:---|:---|:---|:---|
| **1** | `GET /v1/items` | `limit: max 100`（最大100件） | `min($limit, 50)` で**最大50件に制限**されている | **境界値分析** (`limit=50`, `limit=51`, `limit=100` の検証) |
| **2** | `GET /v1/items` | `items[].price: integer`（数値型） | なぜか**文字列型 `"880"` (string)** で返却される | **スキーマ・データ型検証** (JSON Schemaバリデーション) |
| **3** | `GET /v1/items` | ページ番号キー名: `current_page` | 実際のレスポンスキー名は **`page`** | **レスポンスキー名の網羅突合** |
| **4** | `GET /v1/items/{id}` | 存在しないIDのエラーキー: `{"message": "..."}` | 実際のキー名は **`{"error": "...", "code": 404}`** | **異常系・エラーレスポンススキーマ検証** |
| **5** | `GET /v1/items/{id}` | 画像URLキー名: `imageUrl` (キャメルケース) | 実際は **`image_url` (スネークケース)** | **命名規則（ケース）の統一性チェック** |
| **6** | `GET /v1/items/{id}/stock` | `stock >= 1` で `in_stock: true` | コードが `> 1` のため、**在庫が「ちょうど1個」のとき false になる** | **境界値テスト** (在庫0, 1, 2の境界判定) |
| **7** | `POST /v1/orders` | 成功時ステータス: `201 Created` | 実際の実装は **`200 OK`** を返している | **HTTPステータスコードの整合性検証** |
| **8** | `POST /v1/orders` | `quantity: min 1` (1以上必須) | サーバー側minチェック漏れで **`0` や `-1` が通る** | **境界値・負数入力テスト** (不正入力遮断) |
| **9** | `POST /v1/orders` | `phone`: 必須 (required) | コード側で検証されておらず、**未指定でも注文が通る** | **必須パラメータの欠落テスト** |
| **10** | `POST /v1/coupons/verify` | 値引き金額キー名: `discount_amount: 500` | 実際は **`discount_rate: 0.5` (率)** が返る | **業務ロジック・契約（Contract）仕様突合** |
| **11** | `POST /v1/coupons/verify` | 無効クーポンのステータス: `200 OK` (`valid: false`) | 実際は **`400 Bad Request`** が返却される | **状態遷移・エラーハンドリング仕様突合** |
| **12** | `GET /v1/reviews` | `created_at`: RFC3339/ISO8601 (`...Z`) | 実際は **`Y-m-d H:i:s` (JST日時文字列)** で返却 | **フォーマット検証** (日付・タイムゾーン) |
| **13** | `POST /v1/reviews` | `rating: 1〜5` (5段階評価) | 範囲バリデーションがなく、**`0` や `99` も保存できる** | **範囲外入力・同値分割テスト** (`rating=-1, 0, 1, 5, 6`) |

---

### 演習の採点・評価基準例
* **初級レベル (1〜4個発見)**:
  * 主に「注文が200で返ってきた」「合言葉でエラーコード400が出た」といった直接的な挙動の違いに気づく。
* **中級レベル (5〜9個発見)**:
  * 「キー名が違う」「priceが文字列型になっている」「日付フォーマットがISO8601ではない」など、データ構造や型の違いに気づく。
* **上級・プロレベル (10個以上発見)**:
  * 在庫1個のときの `in_stock: false`（境界値バグ）や、`limit=100` で50件頭打ちになる現象、負数数量や電話番号未指定が通るバリデーション漏れをテストケース設計で体系的にあぶり出せる。

