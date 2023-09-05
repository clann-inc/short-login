<?php

/**
 * $_POSTを安全に取得
 */
function sl_get_input_post($key)
{
  return esc_attr(filter_input(INPUT_POST, $key));
}
