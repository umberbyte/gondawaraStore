@extends('layouts.app')

@section('title', '創業明治38年 伝統の味・権田原佃煮店')

@section('content')

<!-- 【店主要望演出】秘伝タレ動画音ドーン（自動再生ポリシーでブロックされる演出） -->
<div style="background: #000; color: #fff; padding: 25px; text-align: center; border-radius: 6px; margin-bottom: 30px;">
  <p style="font-size: 13px; color: #c5a059;">【店主熱望】創業120年の秘伝ダレ 煮込み大迫力ムービー</p>
  <!-- 音付きautoplayをブラウザに拒絶されるビデオモック -->
  <div style="border: 2px dashed #c5a059; padding: 30px; margin: 15px auto; max-width: 700px; background: #111;">
    <p style="font-size: 20px; font-weight: bold; margin-bottom: 10px;">🍲 グツグツグツ…（秘伝ダレ沸騰中）</p>
    <p style="font-size: 12px; color: #aaa;">
      ※店主「音もドーンと鳴らせ！」との指示ですが、Chrome等の自動再生ポリシーにより音声はブロックされています。<br>
      スマホのパケット通信量にご注意ください。
    </p>
  </div>
</div>

<div style="background: #fff; border: 1px solid var(--border-color); padding: 30px; margin-bottom: 35px; border-left: 6px solid var(--accent-red);">
  <h2 style="margin-top: 0; color: var(--accent-red); font-size: 22px;">三代目店主・権田原権三よりご挨拶</h2>
  <p style="line-height: 2.0;">
    いらっしゃい！わしが権田原水産三代目の権三（ごんぞう）じゃ。<br>
    明治三十八年、築地の路地裏で小さな鍋ひとつから始まった当店の佃煮。<br>
    「今の若いもんはネットで物を買うらしい」と聞きつけ、わしも一念発起してこのホームページを立ち上げたんじゃ！<br>
    北海道産の日高昆布、江戸前・築地仕込みのあさり、そしてわしが勢いで作った激辛ハバネロ佃煮。<br>
    どれも熱々の白米に乗せたら何杯でも食える自慢の逸品ばかりじゃ。心ゆくまで味わってくだされ！
  </p>
  <p style="text-align: right; font-weight: bold; margin-bottom: 0;">有限会社 権田原水産　代表取締役　権田原 権三</p>
</div>

<h2 style="border-bottom: 2px solid var(--accent-gold); padding-bottom: 8px; margin-bottom: 20px;">店主おすすめ 自慢の逸品</h2>

<div class="item-grid">
  @foreach($featured_items as $item)
    <div class="item-card">
      <img src="{{ $item->image_url }}" alt="{{ $item->name }}">
      <div class="item-card-body">
        <h3>{{ $item->name }}</h3>
        <p style="font-size: 13px; color: #666; height: 45px; overflow: hidden;">{{ Str::limit($item->description, 50) }}</p>
        <div class="item-price">¥{{ number_format($item->price) }} <span style="font-size: 11px; color: #555;">(税込)</span></div>
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 12px; color: {{ $item->stock > 0 ? '#28a745' : '#dc3545' }}">
            在庫: {{ $item->stock }}個
          </span>
          <a href="{{ route('items.show', $item->id) }}" class="btn btn-primary">詳細を見る</a>
        </div>
      </div>
    </div>
  @endforeach
</div>

<div style="text-align: center; margin-top: 35px;">
  <a href="{{ route('items.index') }}" class="btn btn-gold" style="font-size: 16px; padding: 12px 30px;">すべての商品一覧を見る &raquo;</a>
</div>

@endsection
