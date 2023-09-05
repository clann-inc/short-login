# ショートログイン (Short Login)

このファイルは WordPress のプラグインです。  
ショートコードを使ってログイン、パスワード忘れた方、アカウント編集ページを生成します。  
siteguard wp plugin の画像キャプチャに対応したものになります。  
※siteguard wp plugin を使っていない場合は直接コードを触って該当箇所を削除なりして使用してください。  
※もしこのプラグインに需要があれば需要の分で開発しますので、ISSUE 等でご連絡ください。

## ショートコード一覧

- `[c_login]` ログインフォームを表示
- `[c_lost_password login_form_url="/login"]` パスワードリセットフォームを表示
  login_form_url にはログインフォームへの URL を入力してください。
- `[c_login_redirect url="/mypage"]` ログインしているユーザー向けリダイレクト
  url にはリダイレクト先 URL を入力してください。一番最初の行に配置してください。  
  ※使用するにはショートコードの実行タイミングを get_header より前に実施するように変更が必要です。
- `[c_nologin_redirect url="/login"]` ログインしていないユーザー向けリダイレクト
  url にはリダイレクト先 URL を入力してください。一番最初の行に配置してください。  
  ※使用するにはショートコードの実行タイミングを get_header より前に実施するように変更が必要です。
- `[c_logout_button label="ログアウト"]` ログアウトボタンを表示
- `[c_account]` アカウント編集フォームを表示(パスワードのみ変更が可能)

## 補足

- コードはスタイル関連は一切付与してないプレーンな状態です。デザインをする際はクラス名からスタイルを充ててください。
- ショートコードでログインできるユーザーの権限は「購読者」になります。（購読者以外はセキュリティリスク回避のためログインできないようにしています）
- リダイレクト関連のショートコードは get_header より前に実行しておく必要があります。テーマを作成している方は下記のコードを参考にしてください。

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