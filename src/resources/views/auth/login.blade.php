@extends('layouts.app')

@section('title', 'ログイン')

@section('content')

<div style="background: #fff; border: 1px solid var(--border-color); padding: 35px; max-width: 480px; margin: 40px auto; border-radius: 4px;">
  <h2 style="margin-top: 0; color: var(--primary-navy); border-bottom: 2px solid var(--accent-gold); padding-bottom: 8px;">
    お得意様 ログイン
  </h2>

  <form action="{{ route('login.post') }}" method="POST" style="margin-top: 20px;">
    @csrf

    <div style="margin-bottom: 15px;">
      <label for="email" style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">メールアドレス:</label>
      <input type="email" name="email" id="email" required placeholder="例: yamada@example.com" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 3px;">
    </div>

    <div style="margin-bottom: 20px;">
      <label for="password" style="display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px;">パスワード:</label>
      <input type="password" name="password" id="password" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 3px;">
    </div>

    <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 16px; padding: 10px;">ログイン</button>
  </form>

  <div style="margin-top: 25px; padding-top: 15px; border-top: 1px solid #eee; font-size: 12px; color: #666; background: #fafafa; padding: 12px; border-radius: 4px;">
    <strong>【研修用テストアカウント】</strong><br>
    ・一般客（山田 太郎）: <code>yamada@example.com</code> / <code>password</code><br>
    ・一般客（佐藤 花子）: <code>sato@example.com</code> / <code>password</code><br>
    ・三代目店主（権三）: <code>gonzo@gondawara.example.com</code> / <code>admin123</code>
  </div>
</div>

@endsection
