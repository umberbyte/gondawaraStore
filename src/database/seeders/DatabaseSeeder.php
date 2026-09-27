<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 商品データ
        DB::table('items')->insert([
            [
                'id' => 1,
                'name' => '名物 築地あさり佃煮（100g）',
                'price' => 880,
                'stock' => 50,
                'category' => 'tsukudani',
                'description' => '明治38年の創業以来、継ぎ足し続けた秘伝の醤油ダレでふっくら煮上げた当店一番人気の看板商品。',
                'image_url' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&auto=format&fit=crop&q=60',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => '伝統の味 日高昆布佃煮（120g）',
                'price' => 750,
                'stock' => 30,
                'category' => 'tsukudani',
                'description' => '厳選された北海道産日高昆布を厚切りにし、じっくりと旨味を凝縮させた老舗の定番。',
                'image_url' => 'https://images.unsplash.com/photo-1563245372-f21724e3856d?w=600&auto=format&fit=crop&q=60',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => '店主渾身 激辛ハバネロ佃煮（80g）',
                'price' => 920,
                'stock' => 100, // 店主が張り切って作りすぎて在庫過多
                'category' => 'spicy',
                'description' => '「今の若者は辛いのが好きだろ！」と店主・権三が勢いだけで開発した激辛佃煮。白米が火を吹く辛さ。',
                'image_url' => 'https://images.unsplash.com/photo-1588166524941-3bf61a9c41db?w=600&auto=format&fit=crop&q=60',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => '高級桐箱入り 贈答用佃煮三種盛り合わせ',
                'price' => 3800,
                'stock' => 10,
                'category' => 'gift',
                'description' => 'あさり・昆布・まぐろ角煮を特製桐箱に詰め合わせた御中元・御歳暮用ギフトセット。※熨斗は備考欄へ。',
                'image_url' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&auto=format&fit=crop&q=60',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => '極上 築地仕込み まぐろ角煮（150g）',
                'price' => 1200,
                'stock' => 20,
                'category' => 'tsukudani',
                'description' => '新鮮なまぐろを一口大に切り分け、生姜を効かせた甘辛タレでホロホロになるまで煮込みました。',
                'image_url' => 'https://images.unsplash.com/photo-1544025162-d76694265947?w=600&auto=format&fit=crop&q=60',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 6,
                'name' => '限定1個！幻の浜名湖産 うなぎ山椒佃煮',
                'price' => 4500,
                'stock' => 1, // 排他制御テスト用（同時購入でマイナス在庫にできる）
                'category' => 'gift',
                'description' => '国産うなぎを贅沢に使用。在庫残りわずか！売り切れ御免の限定品です。',
                'image_url' => 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=600&auto=format&fit=crop&q=60',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // ユーザーデータ
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => '権田原 権三 (店主)',
                'email' => 'gonzo@gondawara.example.com',
                'password' => Hash::make('admin123'),
                'postal_code' => '104-0045',
                'address' => '東京都中央区築地4-12-1 権田原ビル1F',
                'phone' => '03-3541-0000',
                'points' => 9999,
                'is_admin' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => '山田 太郎',
                'email' => 'yamada@example.com',
                'password' => Hash::make('password'),
                'postal_code' => '150-0001',
                'address' => '東京都渋谷区神宮前1-2-3 メゾン原宿201',
                'phone' => '090-1234-5678',
                'points' => 120,
                'is_admin' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => '佐藤 花子 (被害者)',
                'email' => 'sato@example.com',
                'password' => Hash::make('password'),
                'postal_code' => '530-0001',
                'address' => '大阪府大阪市北区梅田2-4-9 ブリーゼタワー10F',
                'phone' => '06-6345-6789',
                'points' => 50,
                'is_admin' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 既存注文データ（IDORテスト用）
        DB::table('orders')->insert([
            [
                'id' => 1001,
                'user_id' => 2, // 山田太郎
                'order_number' => 'ORD-20260901-01',
                'customer_name' => '山田 太郎',
                'customer_email' => 'yamada@example.com',
                'postal_code' => '150-0001',
                'address' => '東京都渋谷区神宮前1-2-3 メゾン原宿201',
                'phone' => '090-1234-5678',
                'subtotal' => 1760,
                'tax' => 176,
                'discount' => 0,
                'total_price' => 1936,
                'payment_method' => 'cod',
                'coupon_code' => null,
                'notes' => '不在時は宅配ボックスへお願いします。',
                'status' => '発送準備中',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'id' => 1002,
                'user_id' => 3, // 佐藤花子（IDORで山田から覗き見られる個人情報！）
                'order_number' => 'ORD-20260902-02',
                'customer_name' => '佐藤 花子',
                'customer_email' => 'sato@example.com',
                'postal_code' => '530-0001',
                'address' => '大阪府大阪市北区梅田2-4-9 ブリーゼタワー10F （※機密個人情報）',
                'phone' => '06-6345-6789',
                'subtotal' => 3800,
                'tax' => 380,
                'discount' => 500,
                'total_price' => 3680,
                'payment_method' => 'bank',
                'coupon_code' => 'HIMITSU_50',
                'notes' => '【至急】お中元用の熨斗希望。表書き「御中元」、名入れ「佐藤」。手書きで綺麗にお願いします！',
                'status' => '入金確認済み',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
        ]);

        DB::table('order_items')->insert([
            ['order_id' => 1001, 'item_id' => 1, 'quantity' => 2, 'price' => 880, 'created_at' => now(), 'updated_at' => now()],
            ['order_id' => 1002, 'item_id' => 4, 'quantity' => 1, 'price' => 3800, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // レビューデータ（XSS PoC入り）
        DB::table('reviews')->insert([
            [
                'item_id' => 1,
                'author_name' => '佃煮マニア',
                'rating' => 5,
                'comment' => 'あさりが大粒で味が染みていて本当に美味しいです！ご飯が何杯でも進みます。',
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ],
            [
                'item_id' => 1,
                'author_name' => '謎のセキュリティ診断士',
                'rating' => 4,
                'comment' => '<script>console.log("【演習警告】Stored XSSが成立しました！Cookie:", document.cookie);</script>味は最高ですが、エスケープ処理されていないようです！',
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ],
            [
                'item_id' => 3,
                'author_name' => '辛党サラリーマン',
                'rating' => 2,
                'comment' => '辛すぎて口から煙が出ました…店主さん、いくらなんでもやりすぎです（笑）',
                'created_at' => now()->subDays(3),
                'updated_at' => now()->subDays(3),
            ],
        ]);
    }
}
