<?php

/**
 * ログイン中の場合のメッセージ
 *
 * @return void
 */
function sl_logout_button($label = 'ログアウトする')
{
?>
  <form class="sl-form sl-logout-button" method="post">
    <p class="sl-action">
      <button class="sl-action-button" type="submit"><?php echo $label; ?></button>
    </p>
    <?php wp_nonce_field('sl_logout_button', 'logout_nonce') ?>
    <input type="hidden" name="sl_logout_button_action" value="logout">
  </form>
<?php
}

/**
 * ログアウトアクション
 *
 * @return void
 */
function sl_logout_button_action()
{
  if (
    isset($_POST['sl_logout_button_action']) &&
    $_POST['sl_logout_button_action'] === 'logout' &&
    wp_verify_nonce($_POST['logout_nonce'], 'sl_logout_button')
  ) {
    wp_logout();
  }
}
add_action('template_redirect', 'sl_logout_button_action');

/**
 * ショートコード版
 * 使い方 echo do_shortcode('[sl_logout_button label="ログアウト"]');
 */
add_shortcode('sl_logout_button', function ($attr = []) {
  ob_start();
  sl_logout_button(isset($attr['label']) ? $attr['label'] : '');
  $result = ob_get_contents();
  ob_end_clean();
  return $result;
});
