@extends('layouts.app')

@section('title', 'マイページ')

@section('content')

<h2 style="border-bottom: 2px solid var(--accent-gold); padding-bottom: 8px; margin-bottom: 20px;">
  お得意様マイページ
</h2>

<div style="background: #fff; border: 1px solid var(--border-color); padding: 20px; border-radius: 4px; margin-bottom: 30px;">
  <p style="margin: 0; font-size: 16px;">
    <strong>{{ $user->name }}</strong> 様、いつもご贔屓ありがとうございます！
  </p>
  <p style="margin: 8px 0 0 0; font-size: 13px; color: #666;">
    現在保有ポイント: <span style="font-size: 16px; font-weight: bold; color: var(--accent-red);">{{ $user->points }} pt</span> 
    （※店主「適当にポイントつけといて」の指示により、使い道は未実装です）
  </p>
</div>

<h3 style="color: var(--primary-navy); font-size: 18px; margin-bottom: 15px;">過去のご注文履歴</h3>

@forelse($orders as $ord)
  <div style="background: #fff; border: 1px solid var(--border-color); padding: 18px; margin-bottom: 15px; border-radius: 4px;">
    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 10px;">
      <div>
        <strong>ご注文番号:</strong> {{ $ord->order_number }}
        <span style="font-size: 12px; color: #888; margin-left: 10px;">({{ $ord->created_at }})</span>
      </div>
      <div>
        <span style="display: inline-block; padding: 2px 8px; font-size: 12px; background: #e2e8f0; border-radius: 3px;">
          {{ $ord->status }}
        </span>
      </div>
    </div>
    
    <div style="display: flex; justify-content: space-between; align-items: center;">
      <div>
        <p style="margin: 0; font-size: 14px;">お届け先: {{ $ord->address }}</p>
        <p style="margin: 4px 0 0 0; font-size: 15px; font-weight: bold; color: #c00;">
          お支払い合計: ¥{{ number_format($ord->total_price) }}
        </p>
      </div>
      <div>
        <!-- 【IDOR脆弱性への導線】 -->
        <a href="{{ route('mypage.order', $ord->id) }}" class="btn btn-secondary" style="font-size: 13px;">
          注文明細を確認（ID: {{ $ord->id }}） &raquo;
        </a>
      </div>
    </div>
  </div>
@empty
  <p style="color: #777; padding: 30px 0;">過去のご注文履歴はございません。</p>
@endforelse

@endsection
