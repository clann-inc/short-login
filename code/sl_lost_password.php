<?php

/**
 * パスワードを忘れた方へのフォーム
 *
 * @return void
 */
function sl_lost_password_mail_form()
{
  $error = get_query_var('sl_lost_password_message_error');
?>
  <form class="sl-form sl-lost-password-mail-form" method="post">
    <?php if ($error) : ?>
      <p class="sl-message-error"><?php echo $error; ?></p>
    <?php endif; ?>
    <p class="sl-field">
      <label class="sl-field-label" for="user_mail">登録済みのメールアドレス</label>
      <input class="sl-field-input" type="mail" name="mail" id="user_mail" value="<?php echo get_input_post('mail') ?: "" ?>">
    </p>
    <p class="sl-action">
      <button class="sl-action-button" type="submit">パスワードリセット用URLを送る</button>
    </p>
    <input type="hidden" name="sl_lost_password_action" value="mail">
    <input type="hidden" name="reset_url_base" value="<?php echo get_permalink(); ?>">
    <?php wp_nonce_field('sl_lost_password_mail', 'password_nonce') ?>
  </form>
<?php
}

/**
 * メール送信完了メッセージ
 *
 * @return void
 */
function sl_lost_password_mail_form_complete()
{
?>
  <div class="sl-lost-password-mail-form-complete">
    <p class="sl-message-complete">パスワードリセット用URLを送信しました。メールボックスを確認してください。</p>
  </div>
<?php
}

/**
 * リセットURLの送信アクション
 *
 * @return void
 */
function sl_lost_password_mail_form_action()
{
  if (
    isset($_POST['mail']) &&
    isset($_POST['reset_url_base']) &&
    isset($_POST['password_nonce']) &&
    isset($_POST['sl_lost_password_action']) &&
    $_POST['sl_lost_password_action'] === 'mail' &&
    wp_verify_nonce($_POST['password_nonce'], 'sl_lost_password_mail')
  ) {
    try {
      $user_data = get_user_by('email', get_input_post('mail'));
      if (!$user_data || $user_data->roles[0] !== 'subscriber') {
        throw new Exception('該当するアカウントが見つかりませんでした。');
      }

      // リセットURLを作成
      $reset_url = add_query_arg([
        'action' => 'rp',
        'key' => get_password_reset_key($user_data),
        'login' => rawurlencode($user_data->user_login)
      ], get_input_post('reset_url_base'));

      // 送信完了をフロントに通知
      set_query_var('sl_lost_password_message_complete', "mail");

      // サイト名を取得
      $blogname = stripslashes(get_option('blogname'));

      // メッセージを作成
      $message = $user_data->user_login . ' 様' . "\r\n";
      $message .= "\r\n";
      $message .= 'あなたのアカウントに対して、パスワードのリセットが要求されました。' . "\r\n";
      $message .= "\r\n";
      $message .= 'もしこのリクエストが間違いだった場合は、このメールを無視してください。' . "\r\n";
      $message .= '何も操作をしなければ、これまでのパスワードがそのまま使用できます。' . "\r\n";
      $message .= "\r\n";
      $message .= 'パスワードをリセットする場合は、次のリンクをクリックしてください。' . "\r\n";
      $message .= 'パスワード変更画面にアクセスしますので、新しいパスワードを入力してください。' . "\r\n";
      $message .= "\r\n";
      $message .= $reset_url;
      $message .= "\r\n\r\n";
      $message .= $blogname;

      // メール送信
      wp_mail($user_data->user_email, "【{$blogname}】パスワードリセットのお知らせ", $message);
    } catch (Exception $error) {

      // エラー内容をフロントに通知
      set_query_var('sl_lost_password_message_error', $error->getMessage());
    }
  }
}
add_action('template_redirect', 'sl_lost_password_mail_form_action');

/**
 * パスワード変更フォーム
 *
 * @return void
 */
function sl_lost_password_set_form($login, $key)
{
  $error = get_query_var('sl_lost_password_message_error');
?>
  <form class="sl-form sl-lost-password-set-form" method="post">
    <?php if ($error) : ?>
      <p class="sl-message-error"><?php echo $error; ?></p>
    <?php endif; ?>
    <p class="sl-field">
      <label class="sl-field-label" for="password">変更したいパスワード</label>
      <input class="sl-field-input" type="password" name="password" id="password" value="">
    </p>
    <p class="sl-field">
      <label class="sl-field-label" for="password2">再度入力</label>
      <input class="sl-field-input" type="password" name="password2" id="password2" value="">
    </p>
    <p class="sl-action">
      <button class="sl-action-button" type="submit">パスワードを更新</button>
    </p>
    <?php wp_nonce_field('sl_lost_password_set', 'password_nonce') ?>
    <input type="hidden" name="key" value="<?php echo $key; ?>">
    <input type="hidden" name="login" value="<?php echo $login; ?>">
    <input type="hidden" name="sl_lost_password_action" value="set">
  </form>
<?php
}

/**
 * パスワード変更完了メッセージ
 *
 * @return void
 */
function sl_lost_password_set_form_complete($redirect)
{
?>
  <div class="sl-lost-password-set-form-complete">
    <p class="sl-message-info">
      パスワードを更新しました。<a href="<?php echo $redirect ?>">ログインフォームへ</a>
    </p>
  </div>
<?php
}

/**
 * パスワード変更アクション
 *
 * @return void
 */
function sl_lost_password_set_form_action()
{
  if (
    isset($_POST['login']) &&
    isset($_POST['password']) &&
    isset($_POST['password_nonce']) &&
    isset($_POST['sl_lost_password_action']) &&
    $_POST['sl_lost_password_action'] === 'set' &&
    wp_verify_nonce($_POST['password_nonce'], 'sl_lost_password_set')
  ) {
    try {
      $key = get_input_post('key');
      $password = get_input_post('password');
      $password2 = get_input_post('password2');
      $user = get_user_by('login', get_input_post('login'));
      $check = check_password_reset_key($key, $user->user_login);

      // エラー処理
      if (!$password) throw new Exception('パスワードを入力してください。');
      if (!preg_match("/^(?=.*[0-9])[a-zA-Z0-9]{8,}$/", $password)) throw new Exception('パスワードは8文字以上の半角英数字で少なくとも1つ以上の数字が含まれている必要があります。');
      if (is_wp_error($check)) throw new Exception('不正なアクセスです。');
      if (!$user || $user->roles[0] !== 'subscriber') throw new Exception('該当するユーザーが見つかりませんでした。');
      if ($password !== $password2) throw new Exception('パスワードが一致しません。');

      // パスワードを変更
      wp_set_password($password, $user->ID);

      // 送信完了をフロントに通知
      set_query_var('sl_lost_password_message_complete', "set");

      // サイト名を取得
      $blogname = stripslashes(get_option('blogname'));

      // メッセージを作成
      $message = $user->user_login . ' 様' . "\r\n";
      $message .= "\r\n";
      $message .= 'あなたのアカウントのパスワードが変更されました。' . "\r\n";
      $message .= "\r\n";
      $message .= 'もしこのメールに心当たりが無い場合は事務局までご連絡をお願いします。' . "\r\n";
      $message .= '事務局メールアドレス: consortium_tobunken@nich.go.jp' . "\r\n";
      $message .= "\r\n\r\n";
      $message .= $blogname;

      // メール送信
      wp_mail($user->user_email, "【{$blogname}】パスワードリセットのお知らせ", $message);
    } catch (Exception $error) {

      // エラー内容をフロントに通知
      set_query_var('sl_lost_password_message_error', $error->getMessage());
    }
  }
}
add_action('template_redirect', 'sl_lost_password_set_form_action');


/**
 * ログイン中の場合のメッセージ
 *
 * @return void
 */
function sl_lost_password_login()
{
?>
  <div class="sl-lost-password-login">
    <p class="sl-message-info">
      すでにログイン中です。
    </p>
    <?php sl_logout_button(); ?>
  </div>
<?php
}

/**
 * ルーティングしてフォームをよしなに表示
 *
 * @return void
 */
function sl_lost_password($login_form_url)
{
  // すでにログイン中の表示
  if (is_user_logged_in()) {
    sl_lost_password_login();
    return;
  }

  $action = get_url_query('action');
  $complete = get_query_var('sl_lost_password_message_complete');

  if ($action === 'rp') {
    $key = get_url_query('key');
    $login = get_url_query('login');
    $check = check_password_reset_key($key, $login);

    // パスワード変更フォームを表示
    if ($key && $login && !is_wp_error($check)) {
      sl_lost_password_set_form($login, $key);
      return;
    }

    // パスワード変更完了メッセージを表示
    if ($complete === 'set') {
      sl_lost_password_set_form_complete($login_form_url);
      return;
    }
  }

  // メール送信完了メッセージを表示
  if ($complete === 'mail') {
    sl_lost_password_mail_form_complete();
    return;
  }

  // パスワードリセットフォームを表示
  sl_lost_password_mail_form();
  return;
}

/**
 * ショートコード版
 * 使い方 echo do_shortcode('[sl_lost_password login_form_url="http://localhost:8080"]');
 */
add_shortcode('sl_lost_password', function ($attr = []) {
  ob_start();
  sl_lost_password(isset($attr['login_form_url']) ? $attr['login_form_url'] : get_permalink());
  $result = ob_get_contents();
  ob_end_clean();
  return $result;
});
