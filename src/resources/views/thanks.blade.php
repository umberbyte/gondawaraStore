@extends('layouts.app')

@section('title', 'ご注文完了')

@section('content')

<div style="background: #fff; border: 1px solid var(--border-color); padding: 50px 30px; text-align: center; border-radius: 4px; max-width: 700px; margin: 0 auto;">
  <h2 style="color: var(--accent-red); margin-top: 0;">毎度あり！ご注文ありがとうございました</h2>
  <p style="font-size: 16px; margin: 20px 0;">
    三代目店主・権田原権三が心を込めて箱詰めし、速やかに発送いたします！
  </p>

  @if($order)
    <div style="background: #fbf9f5; border: 1px solid #e5dec9; padding: 20px; margin: 30px auto; text-align: left; max-width: 500px; border-radius: 4px;">
      <p style="margin: 0 0 8px 0;"><strong>ご注文番号:</strong> {{ $order->order_number }}</p>
      <p style="margin: 0 0 8px 0;"><strong>お届け先お名前:</strong> {{ $order->customer_name }} 様</p>
      <p style="margin: 0 0 8px 0;"><strong>お届け先ご住所:</strong> {{ $order->address }}</p>
      <p style="margin: 0 0 8px 0;"><strong>お支払い合計金額:</strong> <span style="font-size: 18px; font-weight: bold; color: #c00;">¥{{ number_format($order->total_price) }}</span></p>
      <p style="margin: 0; font-size: 12px; color: #777;">（※DB保存時切り上げ税額: ¥{{ number_format($order->tax) }}）</p>
    </div>
  @endif

  <div style="margin-top: 40px; display: flex; justify-content: center; gap: 20px;">
    <a href="{{ route('index') }}" class="btn btn-secondary">トップページへ戻る</a>
    @if(Auth::check())
      <a href="{{ route('mypage.index') }}" class="btn btn-primary">マイページで注文履歴を確認</a>
    @endif
  </div>
</div>

@endsection
