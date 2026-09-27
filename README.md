# 教育用教材パッケージ：「権田原佃煮店」

創業明治38年の老舗佃煮屋が格安業者に発注して納品された、**「地雷だらけのECサイト」**と**「見栄えだけ一丁前な破綻ドキュメント」**を題材にした実践型Web開発・QA・要件定義研修用教材です。

---

## 📦 パッケージ同梱物一覧

本教材には、実務で遭遇する「アンチパターン」を忠実に再現した以下の3大コンテンツが含まれています：

```text
gondawara-tsukudani/
├── docker-compose.yml       # Docker一発起動設定 (Nginx, PHP8.2, MySQL8.0, Mailpit)
├── docker/                  # Dockerコンテナ定義
├── docs/                    # 【演習用ドキュメント群】
│   ├── planning/            # 【コンテンツ2】見栄えだけ一丁前な企画書 & サイトマップ
│   │   ├── gondawara_dx_proposal.pptx  # 本物のPowerPointファイル (Keynote等で編集可)
│   │   ├── presentation.html           # ブラウザですぐ見られるHTMLスライド版
│   │   └── generate_pptx.py            # PPTX生成スクリプト
│   ├── api/                 # 【コンテンツ3】見た目だけSwagger風API仕様書
│   │   ├── index.html                  # ダブルクリックで開くSwagger UIスタンドアロン
│   │   └── openapi.yaml                # 実装と盛大に乖離したOpenAPI 3.0仕様書
│   ├── EDUCATIONAL_DESIGN.md # ツッコミポイントの教育的意義・背景解説書
│   └── TEACHER_GUIDE.md     # 講師用解答集（バグ・脆弱性の原因と修正コード例）
└── src/                     # 【コンテンツ1】バグ入り通販サイト「権田原佃煮店」本体 (Laravel)
    ├── app/Http/Controllers/
    │   ├── GondawaraShopController.php # 1500行の神コントローラ (バグの温床)
    │   └── Api/GoodsApiController.php  # Swaggerと乖離したAPI実装
    └── ...
```

---

## 🚀 環境構築・起動手順

Docker環境がインストールされたPC（Mac/Windows/Linux）で以下を実行します。

### 1. コンテナのビルドと起動
```bash
cd gondawara-tsukudani
docker compose up -d --build
```

### 2. Laravel初期設定（初回のみ）
```bash
# Composer依存パッケージのインストール
docker compose exec app composer install

# アプリケーションキーの生成（.envにない場合）
docker compose exec app php artisan key:generate

# データベースのマイグレーションとテストデータ投入
docker compose exec app php artisan migrate:fresh --seed
```

### 3. ブラウザでアクセス
* **権田原佃煮店 ECサイト**: [http://localhost:8080](http://localhost:8080)
* **Mailpit (テストメール受信BOX)**: [http://localhost:8025](http://localhost:8025)
* **Swagger風APIドキュメント**: `docs/api/index.html` をブラウザで直接開く
* **HTMLスライド版 企画書**: `docs/planning/presentation.html` をブラウザで直接開く
* **PowerPoint版 企画書**: `docs/planning/gondawara_dx_proposal.pptx` をPowerPointまたはKeynoteで開く

---

## 🎯 推奨演習カリキュラム

### 演習A: 要件定義・サイトマップ突合演習（所要時間: 90分）
1. `docs/planning/gondawara_dx_proposal.pptx` を受講生に配布。
2. 「店主の居酒屋トークをそのまま実装した場合の業務・法的リスク」をチームで洗い出す。
3. 破綻したサイトマップから「孤立ページ」「法的表記の欠落」を指摘し、あるべきサイトマップを再設計する。

### 演習B: API仕様突合・デバッグ演習（所要時間: 60分）
1. `docs/api/index.html`（Swagger UI）を配布。
2. 実際にAPIクライアント（curlやPostman）でリクエストを送信させる。
3. なぜ動かないのか、実装コード（`src/app/Http/Controllers/Api/GoodsApiController.php`）を調査させ、ドキュメントと実装の「嘘」をレポートさせる。

### 演習C: バグバウンティ & セキュリティ診断演習（所要時間: 120分）
1. サイト（`http://localhost:8080`）を操作し、脆弱性・業務不具合を発見させる。
   * 「数量にマイナスを入力してみる」
   * 「検索窓に `' OR '1'='1` を入れてみる」
   * 「レビュー欄に `<script>alert(1)</script>` を投稿してみる」
   * 「マイページの注文詳細URL（`/mypage/orders/1002`）の数値を変更してみる」
2. 発見した不具合の再現手順、影響度、修正方針をまとめたバグリポートを起票させる。

### 演習D: リファクタリング演習（所要時間: 半日〜1日）
1. `GondawaraShopController.php`（神コントローラ）を解体し、サービスクラスやFormRequestに責務を分離する。
2. 排他制御（`lockForUpdate`）を導入して在庫競合バグを解消する。
3. 管理画面のN+1クエリをEager Loading（`with()`）で解消する。

---

## 📚 講師用ガイド & 解答集
バグの場所、再現手順、修正コード、およびツッコミポイントの詳細な解説は [docs/TEACHER_GUIDE.md](docs/TEACHER_GUIDE.md) および [docs/EDUCATIONAL_DESIGN.md](docs/EDUCATIONAL_DESIGN.md) をご参照ください。
