@extends('layouts.app')

@section('title', $item->name)

@section('content')

<p style="font-size: 13px; color: #666; margin-bottom: 20px;">
  <a href="{{ route('index') }}">TOP</a> &gt; 
  <a href="{{ route('items.index') }}">商品一覧</a> &gt; 
  {{ $item->name }}
</p>

<div style="display: flex; gap: 40px; background: #fff; border: 1px solid var(--border-color); padding: 30px; margin-bottom: 40px;">
  <div style="flex: 1;">
    <img src="{{ $item->image_url }}" alt="{{ $item->name }}" style="width: 100%; border: 1px solid #eee; border-radius: 4px;">
  </div>
  <div style="flex: 1.2;">
    <h2 style="margin-top: 0; color: var(--accent-red); font-size: 24px;">{{ $item->name }}</h2>
    <div style="font-size: 28px; font-weight: bold; color: #c00; margin: 15px 0;">
      ¥{{ number_format($item->price) }} <span style="font-size: 14px; color: #555;">(税込)</span>
    </div>

    <p style="line-height: 1.9; font-size: 15px; margin-bottom: 25px;">
      {{ $item->description }}
    </p>

    <div style="background: #fdfaf5; border: 1px solid #ebdccb; padding: 15px; margin-bottom: 25px; border-radius: 4px;">
      <p style="margin: 0; font-size: 13px;">
        <strong>現在の在庫状況:</strong> 
        <span style="font-size: 16px; font-weight: bold; color: {{ $item->stock > 0 ? '#28a745' : '#dc3545' }};">
          {{ $item->stock }} 個
        </span>
        @if($item->stock <= 0)
          <span style="color: red; margin-left: 10px;">（※在庫切れですが、店主が電話で何とかするかもしれません）</span>
        @endif
      </p>
    </div>

    <!-- カート投入フォーム（※数量にマイナスを入力可能！） -->
    <form action="{{ route('cart.update') }}" method="POST" style="background: #f1ede7; padding: 20px; border-radius: 4px;">
      @csrf
      <input type="hidden" name="item_id" value="{{ $item->id }}">
      <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
        <label for="quantity" style="font-weight: bold; font-size: 14px;">購入数量:</label>
        <!-- 【業務バグ】min="1" 属性やサーバー側での正数バリデーションがない -->
        <input type="number" name="quantity" id="quantity" value="1" style="width: 80px; padding: 6px 10px; font-size: 16px; border: 1px solid #ccc; border-radius: 3px;">
        <span style="font-size: 12px; color: #777;">個</span>
      </div>
      <button type="submit" class="btn btn-primary" style="font-size: 16px; padding: 10px 30px; width: 100%;">買い物かごに入れる</button>
    </form>
  </div>
</div>

<!-- レビュー一覧 & 投稿（※Stored XSS脆弱性あり） -->
<h3 style="border-bottom: 2px solid var(--accent-gold); padding-bottom: 6px; margin-bottom: 20px;">お客様の熱いご感想（レビュー）</h3>

@forelse($reviews as $rev)
  <div class="review-box">
    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
      <span style="font-weight: bold; font-size: 14px;">投稿者: {{ $rev->author_name }} 様</span>
      <span style="color: #f39c12;">
        @for($i = 0; $i < $rev->rating; $i++) ★ @endfor
      </span>
    </div>
    <!-- 【セキュリティ脆弱性: Stored XSS】
         Bladeの {{ }} ではなく {!! !!} で出力しているため、<script>タグがそのままブラウザで実行される！ -->
    <div style="font-size: 14px; line-height: 1.6; color: #333;">
      {!! $rev->comment !!}
    </div>
    <div style="font-size: 11px; color: #999; margin-top: 8px; text-align: right;">
      投稿日時: {{ $rev->created_at }}
    </div>
  </div>
@empty
  <p style="color: #777; margin-bottom: 30px;">まだご感想はありません。最初の投稿者になりませんか？</p>
@endforelse

<!-- レビュー投稿フォーム -->
<div style="background: #fff; border: 1px solid var(--border-color); padding: 25px; margin-top: 30px; border-radius: 4px;">
  <h4 style="margin-top: 0; color: var(--primary-navy);">佃煮の感想を店主に送る</h4>
  <form action="{{ route('items.review', $item->id) }}" method="POST">
    @csrf
    <div style="margin-bottom: 12px;">
      <label for="author_name" style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 4px;">お名前（ニックネーム）:</label>
      <input type="text" name="author_name" id="author_name" value="" placeholder="例: 佃煮好きの江戸っ子" style="width: 300px; padding: 6px; border: 1px solid #ccc; border-radius: 3px;">
    </div>

    <div style="margin-bottom: 12px;">
      <label for="rating" style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 4px;">評価:</label>
      <select name="rating" id="rating" style="padding: 6px; border: 1px solid #ccc; border-radius: 3px;">
        <option value="5">★★★★★ (大変うまい！白米が止まらん)</option>
        <option value="4">★★★★☆ (うまい)</option>
        <option value="3">★★★☆☆ (普通)</option>
        <option value="2">★★☆☆☆ (辛すぎる/味が濃い)</option>
        <option value="1">★☆☆☆☆ (口に合わなかった)</option>
      </select>
    </div>

    <div style="margin-bottom: 15px;">
      <label for="comment" style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 4px;">感想コメント:</label>
      <textarea name="comment" id="comment" rows="4" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 3px;" placeholder="佃煮の味や白米との相性を語ってください"></textarea>
    </div>

    <button type="submit" class="btn btn-secondary">感想を送信する</button>
  </form>
</div>

@endsection
