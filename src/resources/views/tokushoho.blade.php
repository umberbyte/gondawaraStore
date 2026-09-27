@extends('layouts.app')

@section('title', '特定商取引法に基づく表記 (テンプレ放置)')

@section('content')

<!-- 【演習用ツッコミポイント】サイト内のどこからもリンクされていない孤立ページ -->
<div style="background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 12px 18px; margin-bottom: 20px; font-size: 13px;">
  <strong>【法令遵守（コンプライアンス）演習の着眼点】</strong><br>
  特定商取引法に基づく表記はECサイト運営上で法律上掲示が義務付けられていますが、本サイトではヘッダー・フッターからリンクが貼られておらず、URLを直接入力しないと閲覧できない「孤立ページ」になっています。さらに内容もテンプレートのまま放置されています。
</div>

<h2 style="border-bottom: 2px solid var(--accent-gold); padding-bottom: 8px; margin-bottom: 20px;">
  特定商取引法に基づく表記
</h2>

<table class="retro-table">
  <tr>
    <th style="width: 25%;">販売業者</th>
    <td>有限会社 権田原水産（※正式商号要確認）</td>
  </tr>
  <tr>
    <th>運営統括責任者</th>
    <td>権田原 権三</td>
  </tr>
  <tr>
    <th>所在地</th>
    <td>東京都中央区築地X-X-X（※番地未記入）</td>
  </tr>
  <tr>
    <th>電話番号</th>
    <td>03-XXXX-XXXX（※店主の携帯番号にするか検討中）</td>
  </tr>
  <tr>
    <th>メールアドレス</th>
    <td>admin@example.com（※仮）</td>
  </tr>
  <tr>
    <th>返品・不良品について</th>
    <td>生モノにつき原則返品不可。ただし店主の機嫌次第で交換に応じる場合あり。</td>
  </tr>
</table>

@endsection
