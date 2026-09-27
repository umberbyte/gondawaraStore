@extends('layouts.app')

@section('title', '佃煮商品一覧')

@section('content')

<h2 style="border-bottom: 2px solid var(--accent-gold); padding-bottom: 8px; margin-bottom: 20px;">佃煮商品一覧</h2>

<!-- 検索フォーム（※SQLインジェクション脆弱性あり） -->
<div style="background: #fff; border: 1px solid var(--border-color); padding: 20px; margin-bottom: 30px; border-radius: 4px;">
  <form action="{{ route('items.index') }}" method="GET" style="display: flex; gap: 10px; align-items: center;">
    <label for="keyword" style="font-weight: bold; font-size: 14px;">商品名・説明文検索:</label>
    <input type="text" name="keyword" id="keyword" value="{{ $keyword ?? '' }}" placeholder="例: あさり、昆布、辛口..." style="flex: 1; padding: 8px; font-size: 14px; border: 1px solid #ccc; border-radius: 3px;">
    
    <label for="category" style="font-weight: bold; font-size: 14px; margin-left: 10px;">カテゴリ:</label>
    <select name="category" id="category" style="padding: 8px; border: 1px solid #ccc; border-radius: 3px;">
      <option value="">すべて</option>
      <option value="tsukudani" {{ ($category ?? '') === 'tsukudani' ? 'selected' : '' }}>定番・佃煮</option>
      <option value="spicy" {{ ($category ?? '') === 'spicy' ? 'selected' : '' }}>激辛シリーズ</option>
      <option value="gift" {{ ($category ?? '') === 'gift' ? 'selected' : '' }}>ご贈答用・桐箱</option>
    </select>

    <button type="submit" class="btn btn-primary" style="padding: 8px 24px;">検索</button>
  </form>
  @if(!empty($keyword))
    <p style="font-size: 12px; color: #666; margin: 8px 0 0 0;">
      「<strong>{{ $keyword }}</strong>」の検索結果: {{ count($items) }}件
    </p>
  @endif
</div>

<div class="item-grid">
  @forelse($items as $item)
    <div class="item-card">
      <img src="{{ $item->image_url }}" alt="{{ $item->name }}">
      <div class="item-card-body">
        <h3>{{ $item->name }}</h3>
        <p style="font-size: 13px; color: #666; height: 50px; overflow: hidden;">{{ $item->description }}</p>
        <div class="item-price">¥{{ number_format($item->price) }} <span style="font-size: 11px; color: #555;">(税込)</span></div>
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <span style="font-size: 12px; color: {{ $item->stock > 0 ? '#28a745' : '#dc3545' }}; font-weight: bold;">
            在庫: {{ $item->stock }}個
          </span>
          <a href="{{ route('items.show', $item->id) }}" class="btn btn-primary">詳細を見る</a>
        </div>
      </div>
    </div>
  @empty
    <p style="padding: 30px; color: #777;">該当する商品が見つかりませんでした。</p>
  @endforelse
</div>

@endsection
