@extends('layouts.app')

@section('title', 'ご注文手続き')

@section('content')

<h2 style="border-bottom: 2px solid var(--accent-gold); padding-bottom: 8px; margin-bottom: 20px;">ご注文手続き</h2>

<div style="display: flex; gap: 30px;">
  <!-- お届け先・決済情報入力フォーム -->
  <div style="flex: 1.5; background: #fff; border: 1px solid var(--border-color); padding: 25px; border-radius: 4px;">
    <h3 style="margin-top: 0; color: var(--primary-navy); font-size: 18px; border-bottom: 1px solid #eee; padding-bottom: 8px;">
      お届け先情報
    </h3>

    <form action="{{ route('order.process') }}" method="POST">
      @csrf

      <div style="margin-bottom: 14px;">
        <label for="customer_name" style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 4px;">お名前 <span style="color:red;">*</span>:</label>
        <input type="text" name="customer_name" id="customer_name" required value="{{ $user->name ?? '' }}" placeholder="例: 権田原 太郎" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 3px;">
      </div>

      <div style="margin-bottom: 14px;">
        <label for="customer_email" style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 4px;">メールアドレス:</label>
        <input type="email" name="customer_email" id="customer_email" value="{{ $user->email ?? '' }}" placeholder="例: customer@example.com" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 3px;">
      </div>

      <div style="margin-bottom: 14px;">
        <label for="postal_code" style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 4px;">郵便番号 <span style="color:red;">*</span>:</label>
        <input type="text" name="postal_code" id="postal_code" required value="{{ $user->postal_code ?? '' }}" placeholder="例: 104-0045" style="width: 180px; padding: 8px; border: 1px solid #ccc; border-radius: 3px;">
        <p style="font-size: 11px; color: #888; margin: 4px 0 0 0;">※北海道・沖縄への配送は店主から「足が早いから無理」とお断り電話がいく場合があります。</p>
      </div>

      <div style="margin-bottom: 14px;">
        <label for="address" style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 4px;">ご住所 <span style="color:red;">*</span>:</label>
        <input type="text" name="address" id="address" required value="{{ $user->address ?? '' }}" placeholder="例: 東京都中央区築地4-12-1 権田原ビル101" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 3px;">
      </div>

      <div style="margin-bottom: 14px;">
        <label for="phone" style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 4px;">お電話番号 <span style="color:red;">*</span>:</label>
        <input type="tel" name="phone" id="phone" required value="{{ $user->phone ?? '' }}" placeholder="例: 090-1234-5678" style="width: 220px; padding: 8px; border: 1px solid #ccc; border-radius: 3px;">
      </div>

      <h3 style="color: var(--primary-navy); font-size: 18px; border-bottom: 1px solid #eee; padding-bottom: 8px; margin-top: 30px;">
        お支払い方法
      </h3>

      <div style="margin-bottom: 15px;">
        <label style="display: block; margin-bottom: 8px; cursor: pointer;">
          <input type="radio" name="payment_method" value="cod" checked> 代金引換（手数料一律330円を店主がサービス）
        </label>
        <label style="display: block; margin-bottom: 8px; cursor: pointer;">
          <input type="radio" name="payment_method" value="bank"> 銀行振込（みずほ銀行 築地支店・前払い）
        </label>
        <label style="display: block; margin-bottom: 8px; color: #888;">
          <input type="radio" name="payment_method" value="paypay" disabled> PayPay / クレジットカード （※店主が『手数料が高い』と渋ったため準備中）
        </label>
      </div>

      <h3 style="color: var(--primary-navy); font-size: 18px; border-bottom: 1px solid #eee; padding-bottom: 8px; margin-top: 30px;">
        備考・手書き熨斗のご要望
      </h3>

      <div style="margin-bottom: 25px;">
        <label for="notes" style="display: block; font-size: 12px; color: #555; margin-bottom: 4px;">
          ※熨斗（のし）や配送へのご要望がある場合はここに自由にご記入ください。店主が手作業で毛筆対応します。
        </label>
        <textarea name="notes" id="notes" rows="4" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 3px;" placeholder="例: お中元のし希望。表書き『御中元』、名入れ『権田原』"></textarea>
      </div>

      <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 18px; padding: 14px 20px;">
        この内容で注文を確定する
      </button>
    </form>
  </div>

  <!-- 注文内容サマリー -->
  <div style="flex: 1;">
    <div style="background: #fff; border: 1px solid var(--border-color); padding: 20px; border-radius: 4px;">
      <h3 style="margin-top: 0; font-size: 16px; border-bottom: 1px solid #eee; padding-bottom: 8px;">ご注文商品</h3>
      
      @foreach($items as $it)
        <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 8px;">
          <span>{{ $it->name }} × {{ $it->cart_quantity }}</span>
          <span>¥{{ number_format($it->line_total) }}</span>
        </div>
      @endforeach

      <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">

      <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px;">
        <span>商品小計:</span>
        <span>¥{{ number_format($subtotal) }}</span>
      </div>
      <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px; color: #555;">
        <span>消費税 (10% / 【確認画面: 切り捨て】):</span>
        <span>¥{{ number_format($tax) }}</span>
      </div>
      @if($discount > 0)
        <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 6px; color: #c00;">
          <span>合言葉値引き:</span>
          <span>-¥{{ number_format($discount) }}</span>
        </div>
      @endif

      <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: bold; color: #c00; margin-top: 12px; border-top: 1px solid #eee; padding-top: 10px;">
        <span>お支払い合計:</span>
        <span>¥{{ number_format($total) }}</span>
      </div>
      <p style="font-size: 11px; color: #888; margin-top: 8px;">
        ※DB保存時には「切り上げ」されるため、領収書と1円ズレる場合があります。
      </p>
    </div>
  </div>
</div>

@endsection
