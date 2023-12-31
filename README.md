# ショートログイン (Short Login)

このファイルは WordPress のプラグインです。ショートコードを使用して、ログイン、パスワードを忘れた方、アカウント編集ページを生成します。また、siteguard wp plugin の画像キャプチャに対応しています。  
※siteguard wp plugin を使用していない場合は、直接コードを修正して該当箇所を削除してご利用ください。また、このプラグインに関心がありましたら、ISSUE やその他の方法でご連絡ください。需要に合わせて開発を行います。

## ショートコード一覧

- `[sl_login]`: ログインフォームを表示
- `[sl_lost_password login_form_url="/login"]`: パスワードリセットフォームを表示。ログインフォームへの URL を login_form_url に入力してください。
- `[sl_login_redirect url="/mypage"]`: ログインしているユーザー向けのリダイレクト。リダイレクト先 URL を url に入力してください。最初の行に配置してください。※使用するには、ショートコードの実行タイミングを get_header より前に変更する必要があります。
- `[sl_nologin_redirect url="/login"]`: ログインしていないユーザー向けのリダイレクト。リダイレクト先 URL を url に入力してください。最初の行に配置してください。※使用するには、ショートコードの実行タイミングを get_header より前に変更する必要があります。
- `[sl_logout_button label="ログアウト"]`: ログアウトボタンを表示
- `[sl_account]`: アカウント編集フォームを表示（パスワードのみ変更可能）

## 補足

- このコードにはスタイル関連の設定は含まれていません。デザインを適用する際には、クラス名からスタイルを適用してください。
- ショートコードを使用してログインできるユーザーの権限は「購読者」に限定されます。セキュリティ上の理由から、購読者以外のユーザーはログインできません。
- リダイレクト関連のショートコードは get_header より前に実行する必要があります。テーマを作成している方は、以下のコードを参考にしてください。

```php
ob_start();
while (have_posts()) {
  the_post();
  the_content();
}
$result = ob_get_contents();
ob_end_clean();

get_header();
echo $result;
get_footer();
```

## サンプルコード

### ログインページ

```php
[sl_login_redirect url="/mypage"]
<h1>ログインフォーム↓</h1>
[sl_login]
<p><a href="/lost-password">パスワードを忘れた方</a></p>
```

### パスワード忘れた方ページ

```php
[sl_login_redirect url="/mypage"]
[sl_lost_password login_form_url="/login"]
<p><a href="/login">ログインフォームへ</a></p>
```

### マイページ

```php
[sl_nologin_redirect url="/login"]
<h1>マイページ</h1>
<p><a href="/mypage/account">アカウント</a></p>
[sl_logout_button]
```

### アカウント編集ページ

```php
[sl_nologin_redirect url="/login"]
[sl_account]
```
