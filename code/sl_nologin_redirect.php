<?php

/**
 * ログインしている場合リダイレクトする
 *
 * @return void
 */
function sl_nologin_redirect($url)
{
  if (!$url) return;
  if (!is_user_logged_in()) {
    wp_redirect($url);
    exit;
  }
}

/**
 * ショートコード版
 */
add_shortcode('sl_nologin_redirect', function ($attr = []) {
  sl_nologin_redirect(isset($attr['url']) ? $attr['url'] : "");
});
