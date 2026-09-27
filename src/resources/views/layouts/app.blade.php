<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>@yield('title', '創業明治38年 伝統の味') | 権田原佃煮店 公式オンラインショップ</title>
  <link rel="stylesheet" href="/css/style.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body>

  <header>
    <div class="header-container">
      <div class="logo-area">
        <h1><a href="{{ route('index') }}" style="color: #fff; text-decoration: none;">権田原佃煮店</a></h1>
        <p>創業明治三十八年 秘伝継ぎ足しダレの極上佃煮</p>
      </div>
      <nav>
        <ul>
          <li><a href="{{ route('index') }}">TOP</a></li>
          <li><a href="{{ route('items.index') }}">商品一覧</a></li>
          <li><a href="{{ route('kodawari') }}">店主のこだわり</a></li>
          <li><a href="{{ route('cart.view') }}">買い物かご</a></li>
          @if(Auth::check())
            <li><a href="{{ route('mypage.index') }}">マイページ ({{ Auth::user()->name }})</a></li>
            <li>
              <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" style="background:none;border:none;color:#fff;cursor:pointer;font-family:inherit;">ログアウト</button>
              </form>
            </li>
          @else
            <li><a href="{{ route('login') }}">ログイン</a></li>
          @endif
        </ul>
      </nav>
    </div>
  </header>

  <div class="container">
    @if(session('success') || session('message'))
      <div class="alert alert-success">
        {{ session('success') ?? session('message') }}
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger">
        {{ session('error') }}
      </div>
    @endif

    @yield('content')
  </div>

  <footer>
    <div class="container" style="min-height: auto; margin: 0 auto;">
      <p>
        <a href="{{ route('index') }}">TOP</a>
        <a href="{{ route('items.index') }}">商品一覧</a>
        <a href="{{ route('kodawari') }}">店主の熱いこだわり</a>
        <a href="{{ route('admin.dashboard') }}">店主管理画面</a>
        <!-- 【ツッコミポイント】特定商取引法に基づく表記・プライバシーポリシーへのリンクがフッターに存在しない！ -->
      </p>
      <!-- 開発者メモ: 店主ログインが面倒なときは /admin?admin_bypass=1 で入れるようにしてあります（納品後削除予定） -->
      <p style="margin-top: 15px; color: #777;">&copy; 1905-2026 有限会社 権田原水産 All Rights Reserved.</p>
    </div>
  </footer>

</body>
</html>
