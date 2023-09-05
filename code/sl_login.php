<?php

/**
 * ログインフォーム
 *
 * @return void
 */
function sl_login_form()
{
  global $siteguard_captcha;
  $error = get_query_var('sl_login_message_error');
?>
  <form class="sl-form sl-login-form" method="post">
    <?php if ($error) : ?>
      <p class="sl-message-error"><?php echo $error; ?></p>
    <?php endif; ?>
    <p class="sl-field">
      <label class="sl-field-label" for="user_login">ユーザー名</label>
      <input class="sl-field-input" type="text" name="log" id="user_login" autocomplete="username" value="<?php echo get_input_post('log') ?: "" ?>">
    </p>
    <p class="sl-field">
      <label class="sl-field-label" for="user_pass">パスワード</label>
      <input class="sl-field-input" type="password" name="pwd" id="user_pass" autocomplete="current-password" spellcheck="false" value="">
    </p>
    <?php $siteguard_captcha->put_captcha(); ?>
    <p class="sl-action">
      <button class="sl-action-button" type="submit">ログイン</button>
    </p>
    <input type="hidden" name="sl_login_action" value="login">
    <input type="hidden" name="rememberme" value="1">
    <?php wp_nonce_field('sl_login_form', 'login_nonce') ?>
  </form>
<?php
}
/**
 * ログイン処理
 *
 * @return void
 */
function sl_login_form_action()
{
  if (
    isset($_POST['sl_login_action']) &&
    isset($_POST['log']) &&
    isset($_POST['pwd']) &&
    isset($_POST['login_nonce']) &&
    $_POST['sl_login_action'] === 'login' &&
    wp_verify_nonce($_POST['login_nonce'], 'sl_login_form')
  ) {
    try {
      $user_data = get_user_by('login', get_input_post('log'));
      if (!$user_data || $user_data->roles[0] !== 'subscriber') {
        throw new Exception('ユーザー名とパスワードの組み合わせが間違っています');
      }
      $user = wp_signon();
      if (is_wp_error($user)) {
        if (isset($user->errors['siteguard-captcha-error'])) {
          throw new Exception("画像認証が間違っています");
        } else {
          throw new Exception("ユーザー名とパスワードの組み合わせが間違っています");
        }
      }
      wp_set_current_user($user->ID);
    } catch (Exception $error) {
      set_query_var('sl_login_message_error', $error->getMessage());
    }
  }
}
add_action('template_redirect', 'sl_login_form_action');

/**
 * admin barは購読者には表示しない
 *
 * @param boolean $content
 * @return void
 */
function sl_login_form_hide_admin_bar($content)
{
  return current_user_can('subscriber') ? false : $content;
}
add_filter('show_admin_bar', 'sl_login_form_hide_admin_bar');

/**
 * ログイン中の場合のメッセージ
 *
 * @return void
 */
function sl_login_logout()
{
?>
  <div class="sl-login-logout">
    <p class="sl-message-info">
      すでにログイン中です。
    </p>
    <?php sl_logout_button(); ?>
  </div>
<?php
}

/**
 * ログインフォームをよしなに生成
 *
 * @return void
 */
function sl_login()
{
  // すでにログイン中の表示
  if (is_user_logged_in()) {
    sl_login_logout();
    return;
  }
  // ログインフォームを表示
  sl_login_form();
  return;
}

/**
 * ショートコード版
 */
add_shortcode('sl_login', function ($attr = []) {
  ob_start();
  sl_login();
  $result = ob_get_contents();
  ob_end_clean();
  return $result;
});
