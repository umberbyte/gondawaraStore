@extends('layouts.app')

@section('title', 'ご注文明細 - ' . $order->order_number)

@section('content')

<p style="font-size: 13px; color: #666; margin-bottom: 20px;">
  <a href="{{ route('index') }}">TOP</a> &gt; 
  <a href="{{ route('mypage.index') }}">マイページ</a> &gt; 
  注文明細 (ID: {{ $order->id }})
</p>

<!-- IDOR脆弱性に関する演習用ガイダンス -->
<div style="background: #fff3cd; border: 1px solid #ffeeba; color: #856404; padding: 12px 18px; border-radius: 4px; margin-bottom: 25px; font-size: 13px;">
  <strong>【セキュリティ演習の着眼点 (IDOR / 水平権限昇格)】</strong><br>
  現在表示中の注文IDは <code>{{ $order->id }}</code> です。<br>
  ブラウザのアドレスバーのURL（例: <code>/mypage/orders/1002</code>）の数字を変更すると、他人の注文履歴や配送先個人情報が認証チェックなしで丸見えになります。
</div>

<div style="background: #fff; border: 1px solid var(--border-color); padding: 30px; border-radius: 4px;">
  <div style="display: flex; justify-content: space-between; border-bottom: 2px solid var(--accent-gold); padding-bottom: 10px; margin-bottom: 20px;">
    <h2 style="margin: 0; color: var(--primary-navy); font-size: 20px;">
      ご注文明細 ({{ $order->order_number }})
    </h2>
    <span style="font-size: 14px; font-weight: bold; background: #e2e8f0; padding: 4px 12px; border-radius: 3px;">
      ステータス: {{ $order->status }}
    </span>
  </div>

  <div style="display: flex; gap: 30px; margin-bottom: 25px;">
    <div style="flex: 1;">
      <h4 style="margin: 0 0 10px 0; color: var(--accent-red);">お届け先・お客様個人情報</h4>
      <p style="margin: 0 0 6px 0;"><strong>お名前:</strong> {{ $order->customer_name }} 様</p>
      <p style="margin: 0 0 6px 0;"><strong>郵便番号:</strong> 〒{{ $order->postal_code }}</p>
      <p style="margin: 0 0 6px 0;"><strong>ご住所:</strong> {{ $order->address }}</p>
      <p style="margin: 0 0 6px 0;"><strong>お電話番号:</strong> {{ $order->phone }}</p>
      <p style="margin: 0;"><strong>メールアドレス:</strong> {{ $order->customer_email ?? '未登録' }}</p>
    </div>

    <div style="flex: 1;">
      <h4 style="margin: 0 0 10px 0; color: var(--accent-red);">ご注文・お支払い情報</h4>
      <p style="margin: 0 0 6px 0;"><strong>注文日時:</strong> {{ $order->created_at }}</p>
      <p style="margin: 0 0 6px 0;"><strong>決済方法:</strong> {{ $order->payment_method === 'cod' ? '代金引換' : ($order->payment_method === 'bank' ? '銀行振込' : $order->payment_method) }}</p>
      <p style="margin: 0 0 6px 0;"><strong>適用合言葉:</strong> {{ $order->coupon_code ?: 'なし' }}</p>
      @if($order->notes)
        <div style="background: #fafafa; border: 1px solid #eee; padding: 10px; margin-top: 10px; border-radius: 3px;">
          <strong style="font-size: 12px; color: #555;">お客様備考（手書き熨斗など）:</strong><br>
          <span style="font-size: 13px; color: #333;">{{ $order->notes }}</span>
        </div>
      @endif
    </div>
  </div>

  <h4 style="color: var(--primary-navy); margin-bottom: 12px;">ご注文商品一覧</h4>
  <table class="retro-table">
    <thead>
      <tr>
        <th>商品名</th>
        <th style="width: 20%;">単価</th>
        <th style="width: 15%;">数量</th>
        <th style="width: 25%;">小計</th>
      </tr>
    </thead>
    <tbody>
      @foreach($order_items as $item)
        <tr>
          <td><strong>{{ $item->item_name }}</strong></td>
          <td>¥{{ number_format($item->price) }}</td>
          <td>{{ $item->quantity }}</td>
          <td style="font-weight: bold;">¥{{ number_format($item->price * $item->quantity) }}</td>
        </tr>
      @endforeach
      <tr>
        <td colspan="3" style="text-align: right; font-weight: bold;">商品小計:</td>
        <td style="font-weight: bold;">¥{{ number_format($order->subtotal) }}</td>
      </tr>
      <tr>
        <td colspan="3" style="text-align: right; color: #555;">消費税 (DB保存時切り上げ):</td>
        <td>¥{{ number_format($order->tax) }}</td>
      </tr>
      @if($order->discount > 0)
        <tr>
          <td colspan="3" style="text-align: right; color: #c00;">合言葉値引き:</td>
          <td style="color: #c00;">-¥{{ number_format($order->discount) }}</td>
        </tr>
      @endif
      <tr style="background: #fdfaf5; font-size: 16px;">
        <td colspan="3" style="text-align: right; font-weight: bold; color: #c00;">合計ご請求金額:</td>
        <td style="font-weight: bold; color: #c00;">¥{{ number_format($order->total_price) }}</td>
      </tr>
    </tbody>
  </table>

  <div style="margin-top: 30px;">
    <a href="{{ route('mypage.index') }}" class="btn btn-secondary">&laquo; マイページ一覧へ戻る</a>
  </div>
</div>

@endsection
