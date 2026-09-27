@extends('layouts.app')

@section('title', '買い物かご')

@section('content')

<h2 style="border-bottom: 2px solid var(--accent-gold); padding-bottom: 8px; margin-bottom: 20px;">買い物かごの中身</h2>

@if(empty($items))
  <div style="background: #fff; border: 1px solid var(--border-color); padding: 40px; text-align: center;">
    <p style="font-size: 16px; color: #777;">現在、買い物かごには何も入っておりません。</p>
    <a href="{{ route('items.index') }}" class="btn btn-primary" style="margin-top: 15px;">佃煮を探しに行く</a>
  </div>
@else

  <table class="retro-table">
    <thead>
      <tr>
        <th style="width: 45%;">商品名</th>
        <th style="width: 15%;">単価(税込)</th>
        <th style="width: 20%;">数量</th>
        <th style="width: 20%;">小計</th>
      </tr>
    </thead>
    <tbody>
      @foreach($items as $item)
        <tr>
          <td>
            <strong>{{ $item->name }}</strong><br>
            <span style="font-size: 11px; color: #777;">在庫残り: {{ $item->stock }}個</span>
          </td>
          <td>¥{{ number_format($item->price) }}</td>
          <td>
            <!-- 数量変更フォーム（マイナス数量もそのまま通る） -->
            <form action="{{ route('cart.update') }}" method="POST" style="display: flex; gap: 5px; align-items: center;">
              @csrf
              <input type="hidden" name="item_id" value="{{ $item->id }}">
              <input type="number" name="quantity" value="{{ $item->cart_quantity }}" style="width: 60px; padding: 4px; font-size: 14px;">
              <button type="submit" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;">変更</button>
            </form>
          </td>
          <td style="font-weight: bold; color: {{ $item->line_total < 0 ? 'red' : 'inherit' }};">
            ¥{{ number_format($item->line_total) }}
          </td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <div style="display: flex; gap: 30px; margin-bottom: 30px;">
    <!-- 常連合言葉（クーポン）フォーム -->
    <div style="flex: 1; background: #fff; border: 1px solid var(--border-color); padding: 20px; border-radius: 4px;">
      <h4 style="margin-top: 0; color: var(--accent-red);">店主の常連合言葉（割引）</h4>
      <p style="font-size: 12px; color: #666;">店主から聞いた秘密の合言葉を入力してください。（例: HIMITSU_50）</p>
      <form action="{{ route('cart.coupon') }}" method="POST" style="display: flex; gap: 8px;">
        @csrf
        <input type="text" name="coupon_code" placeholder="合言葉を入力" style="flex: 1; padding: 6px; border: 1px solid #ccc; border-radius: 3px;">
        <button type="submit" class="btn btn-secondary">適用</button>
      </form>
      @if(session('applied_coupons'))
        <p style="font-size: 11px; color: #28a745; margin-top: 8px;">
          適用済みコード: {{ implode(', ', session('applied_coupons')) }}
        </p>
      @endif
    </div>

    <!-- お会計内訳 -->
    <div style="flex: 1; background: #fff; border: 1px solid var(--border-color); padding: 20px; border-radius: 4px;">
      <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
        <span>商品小計:</span>
        <span style="font-weight: bold;">¥{{ number_format($subtotal) }}</span>
      </div>
      <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px; color: #555;">
        <span>消費税 (10% / 四捨五入):</span>
        <span>¥{{ number_format($tax) }}</span>
      </div>
      @if($discount > 0)
        <div style="display: flex; justify-content: space-between; margin-bottom: 8px; color: #c00;">
          <span>合言葉値引き:</span>
          <span>-¥{{ number_format($discount) }}</span>
        </div>
      @endif
      <hr style="border: 0; border-top: 1px solid #eee; margin: 10px 0;">
      <div style="display: flex; justify-content: space-between; font-size: 20px; font-weight: bold; color: {{ $total < 0 ? 'red' : '#c00' }};">
        <span>合計金額:</span>
        <span>¥{{ number_format($total) }}</span>
      </div>
      @if($total < 0)
        <p style="font-size: 12px; color: red; margin: 5px 0 0 0;">
          ※合計金額がマイナスになっていますが、このまま購入手続きに進めます（返金状態）。
        </p>
      @endif
      
      <div style="text-align: right; margin-top: 20px;">
        <a href="{{ route('order.checkout') }}" class="btn btn-primary" style="font-size: 16px; padding: 10px 30px; display: block;">
          ご注文手続きへ進む &raquo;
        </a>
      </div>
    </div>
  </div>

@endif

@endsection
