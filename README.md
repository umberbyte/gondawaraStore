# 教育用教材パッケージ：「権田原佃煮店」

創業明治38年の老舗佃煮屋が格安業者に発注して納品された、**「地雷だらけのECサイト」**と**「見栄えだけ一丁前な顧客提案書 & 赤裸々な社内メモ」**を題材にした実践型Web開発・QA・要件定義研修用教材です。

---

## 📦 パッケージ同梱物一覧

本教材は、実務の現場で遭遇する**「建前（顧客向け提案書）」「本音（社内ヒアリング議事録）」「現実（地雷だらけの実装コード）」の三層構造**を忠実に再現しています。

```text
gondawara-tsukudani/
├── docker-compose.yml       # Docker一発起動設定 (Nginx, PHP8.2, MySQL8.0, Mailpit)
├── docker/                  # Dockerコンテナ定義
├── docs/                    # 【演習用ドキュメント群】
│   ├── planning/            # 【コンテンツ2】企画書 & サイトマップ
│   │   ├── gondawara_dx_proposal.pptx       # ★【顧客提示用】見栄え完璧・拡大解釈・まともな設計が書かれた提案書
│   │   ├── presentation.html                # ★【顧客提示用】HTMLスライド版
│   │   ├── gondawara_dx_internal_memo.pptx  # 🔒【社内検討用】店主の居酒屋生声・手抜き方針の赤裸々メモ
│   │   ├── internal_presentation.html       # 🔒【社内検討用】HTMLスライド版
│   │   ├── generate_pptx.py                 # 顧客提示用PPTX生成スクリプト
│   │   └── generate_internal_pptx.py        # 社内検討用PPTX生成スクリプト
│   ├── api/                 # 【コンテンツ3】見た目だけSwagger風API仕様書
│   │   ├── index.html                       # ダブルクリックで開くSwagger UIスタンドアロン
│   │   └── openapi.yaml                     # 実装と盛大に乖離したOpenAPI 3.0仕様書
│   ├── EDUCATIONAL_DESIGN.md # 三層構造（建前・本音・現実）の教育的意義解説書
│   └── TEACHER_GUIDE.md     # 講師用解答集（バグ・脆弱性の原因と修正コード例）
└── src/                     # 【コンテンツ1】バグ入り通販サイト「権田原佃煮店」本体 (Laravel)
    ├── app/Http/Controllers/
    │   ├── GondawaraShopController.php # 1500行の神コントローラ (提案書と真逆の実装)
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
* **権田原佃煮店 ECサイト**: [http://localhost:9080](http://localhost:9080)
* **Mailpit (テストメール受信BOX)**: [http://localhost:9025](http://localhost:9025)
* **Swagger風APIドキュメント**: `docs/api/index.html` をブラウザで直接開く
* **【顧客提示用】HTMLスライド**: `docs/planning/presentation.html` をブラウザで開く
* **【社内検討用】HTMLスライド**: `docs/planning/internal_presentation.html` をブラウザで開く
* **PowerPoint版**: `docs/planning/` 配下の `.pptx` をPowerPointまたはKeynoteで開く

---

## 🎯 推奨演習カリキュラム

### 演習A: 「建前」vs「本音」要件分析ディスカッション（所要時間: 90分）
1. 受講生に「顧客提示用提案書（`gondawara_dx_proposal.pptx`）」と「社内メモ（`gondawara_dx_internal_memo.pptx`）」を配布。
2. 「店主の生の要望が、企画側でどのように拡大解釈され、美化されているか」を対比させる。
3. 顧客提示用のスケジュール（Week 1〜4）を精読させ、「なぜこのスケジュールは守れそうで絶対に守れないのか（リスクヘッジの罠）」を特定・ディスカッションさせる。

### 演習B: 「仕様」vs「実装コード」突合演習（所要時間: 90分）
1. 顧客提示用のスライド4ページ目「システム要件定義（排他制御・正数バリデーション・インボイス対応・OWASP準拠）」を受講生に見せる。
2. 実際のコード（`GondawaraShopController.php`）を検証させ、「仕様書に書いてある設計が、現場のコードでどれだけ無視されているか」を洗い出させる。

### 演習C: バグバウンティ & セキュリティ診断演習（所要時間: 120分）
1. サイト（`http://localhost:9080`）を操作し、脆弱性・業務不具合を発見・レポートさせる。
   * 数量マイナス入力による返金購入
   * 生SQL結合によるSQLインジェクション
   * レビュー欄のStored XSS
   * 注文詳細のIDOR（他人の個人情報漏洩）
   * 管理画面の認証バイパス

---

## 📚 講師用ガイド & 解答集
バグの場所、再現手順、修正コード、およびツッコミポイントの詳細な解説は [docs/TEACHER_GUIDE.md](docs/TEACHER_GUIDE.md) および [docs/EDUCATIONAL_DESIGN.md](docs/EDUCATIONAL_DESIGN.md) をご参照ください。
