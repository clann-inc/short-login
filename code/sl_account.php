<?php

/**
 * アカウント編集フォーム
 *
 * @return void
 */
function sl_account()
{
  if (!is_user_logged_in()) return;
  $complete = get_query_var('sl_account_message_complete');
  $error = get_query_var('sl_account_message_error');
  $user = wp_get_current_user();
?>
  <form class="sl-form sl-account" method="post">
    <?php if ($complete) : ?>
      <p class="sl-message-complete">更新しました</p>
    <?php endif; ?>
    <?php if ($error) : ?>
      <p class="sl-message-error"><?php echo $error; ?></p>
    <?php endif; ?>
    <p class="sl-field">
      <label class="sl-field-label">ユーザ名</label>
      <input class="sl-field-input" type="text" name="user_login" readonly value="<?php echo $user->user_login; ?>">
    </p>
    <p class="sl-field">
      <label class="sl-field-label">メールアドレス</label>
      <input class="sl-field-input" type="text" name="user_email" readonly value="<?php echo $user->user_email; ?>">
    </p>
    <p class="sl-field">
      <label class="sl-field-label">変更したいパスワード</label>
      <input class="sl-field-input" type="password" name="password" value="">
    </p>
    <p class="sl-field">
      <label class="sl-field-label">再度入力</label>
      <input class="sl-field-input" type="password" name="password2" value="">
    </p>
    <p class="sl-action">
      <button class="sl-action-button" type="submit">更新する</button>
    </p>
    <?php wp_nonce_field('sl_account', 'edit_nonce') ?>
    <input type="hidden" name="sl_account_action" value="edit">
  </form>
<?php
}

/**
 * アカウント編集処理
 *
 * @return void
 */
function sl_account_action()
{
  if (
    isset($_POST['sl_account_action']) &&
    isset($_POST['password']) &&
    isset($_POST['edit_nonce']) &&
    $_POST['sl_account_action'] === 'edit' &&
    wp_verify_nonce($_POST['edit_nonce'], 'sl_account')
  ) {
    try {
      $password = get_input_post('password');
      $password2 = get_input_post('password2');
      $user = wp_get_current_user();

      // エラー処理
      if (!$user || $user->roles[0] !== 'subscriber') throw new Exception('該当するユーザーが見つかりませんでした。');
      if (!$password) throw new Exception('パスワードを入力してください。');
      if (!preg_match("/^(?=.*[0-9])[a-zA-Z0-9]{8,}$/", $password)) throw new Exception('パスワードは8文字以上の半角英数字で少なくとも1つ以上の数字が含まれている必要があります。');
      if ($password !== $password2) throw new Exception('パスワードが一致しません。');

      // パスワードを変更
      wp_update_user([
        'ID' => $user->ID,
        'user_pass' => $password
      ]);

      // 送信完了をフロントに通知
      set_query_var('sl_account_message_complete', "edited");

      // サイト名を取得
      $blogname = stripslashes(get_option('blogname'));

      // メッセージを作成
      $message = $user->user_login . ' 様' . "\r\n";
      $message .= "\r\n";
      $message .= 'あなたのアカウントの情報が変更されました。' . "\r\n";
      $message .= "\r\n";
      $message .= 'もしこのメールに心当たりが無い場合は事務局までご連絡をお願いします。' . "\r\n";
      $message .= "\r\n\r\n";
      $message .= $blogname;

      // メール送信
      wp_mail($user->user_email, "【{$blogname}】パスワードリセットのお知らせ", $message);
    } catch (Exception $error) {
      set_query_var('sl_account_message_error', $error->getMessage());
    }
  }
}
add_action('template_redirect', 'sl_account_action');

/**
 * ショートコード版
 */
add_shortcode('sl_account', function ($attr = []) {
  ob_start();
  sl_account();
  $result = ob_get_contents();
  ob_end_clean();
  return $result;
});
