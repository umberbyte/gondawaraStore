@extends('layouts.app')

@section('title', '【店主専用】店舗管理画面')

@section('content')

<div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--accent-gold); padding-bottom: 8px; margin-bottom: 25px;">
  <h2 style="margin: 0; color: var(--primary-navy);">
    権田原佃煮店 - 店主管理画面
  </h2>
  <span style="font-size: 13px; color: #666;">
    ログイン中: 三代目店主・権田原権三
  </span>
</div>

<div style="background: #fdfaf5; border: 1px solid #ebdccb; padding: 15px; margin-bottom: 30px; border-radius: 4px; font-size: 13px;">
  <strong>【店主の日常運用メモ】</strong><br>
  ・店頭のノート（帳面）を見て、店頭で売り切れた佃煮はここで在庫数を「0」に書き換えて「在庫更新」ボタンを押すこと。<br>
  ・忘れるとネットから無限に注文が入ってしまい、店主が電話で謝ることになります。<br>
  <span style="color: #c00;">（※開発者裏口: <code>/admin?admin_bypass=1</code> で認証なしログイン中）</span>
</div>

<h3 style="color: var(--accent-red); margin-bottom: 15px;">1. 商品在庫の管理・手動同期</h3>

<table class="retro-table">
  <thead>
    <tr>
      <th style="width: 10%;">ID</th>
      <th style="width: 40%;">商品名</th>
      <th style="width: 15%;">価格(税込)</th>
      <th style="width: 20%;">現在庫数</th>
      <th style="width: 15%;">操作</th>
    </tr>
  </thead>
  <tbody>
    @foreach($items as $it)
      <tr>
        <td>{{ $it->id }}</td>
        <td><strong>{{ $it->name }}</strong></td>
        <td>¥{{ number_format($it->price) }}</td>
        <td>
          <form action="{{ route('admin.stock', $it->id) }}" method="POST" style="display: flex; gap: 8px; align-items: center;">
            @csrf
            <input type="number" name="stock" value="{{ $it->stock }}" style="width: 70px; padding: 4px; font-size: 14px;">
            <button type="submit" class="btn btn-secondary" style="padding: 4px 10px; font-size: 12px;">在庫更新</button>
          </form>
        </td>
        <td>
          <a href="{{ route('items.show', $it->id) }}" target="_blank" style="font-size: 12px;">商品ページ &raquo;</a>
        </td>
      </tr>
    @endforeach
  </tbody>
</table>

<h3 style="color: var(--accent-red); margin-top: 40px; margin-bottom: 15px;">
  2. 直近の受注一覧 (最新50件) <span style="font-size: 12px; font-weight: normal; color: #777;">※N+1クエリが発生中</span>
</h3>

<table class="retro-table">
  <thead>
    <tr>
      <th>注文番号</th>
      <th>受注日時</th>
      <th>お客様氏名</th>
      <th>会員情報</th>
      <th>品数</th>
      <th>合計金額</th>
      <th>決済方法</th>
      <th>ステータス</th>
    </tr>
  </thead>
  <tbody>
    @forelse($orders as $ord)
      <tr>
        <td><strong>{{ $ord->order_number }}</strong></td>
        <td style="font-size: 12px;">{{ $ord->created_at }}</td>
        <td>{{ $ord->customer_name }}</td>
        <td style="font-size: 12px;">
          <!-- N+1クエリで取得されたユーザー情報 -->
          @if($ord->user_info)
            会員: {{ $ord->user_info->name }} ({{ $ord->user_info->points }}pt)
          @else
            <span style="color: #999;">ゲスト注文</span>
          @endif
        </td>
        <td>
          <!-- N+1クエリで取得された明細件数 -->
          {{ $ord->items_count }}品
        </td>
        <td style="font-weight: bold; color: {{ $ord->total_price < 0 ? 'red' : '#c00' }};">
          ¥{{ number_format($ord->total_price) }}
        </td>
        <td style="font-size: 12px;">{{ $ord->payment_method }}</td>
        <td>
          <span style="display: inline-block; padding: 2px 6px; font-size: 11px; background: #e2e8f0; border-radius: 3px;">
            {{ $ord->status }}
          </span>
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="8" style="text-align: center; color: #777;">注文データがありません。</td>
      </tr>
    @endforelse
  </tbody>
</table>

@endsection
