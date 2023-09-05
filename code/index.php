<?php
// すべてのcomponentsを読み込む
$pattern = sprintf('%s*.php', plugin_dir_path(__FILE__));
foreach (glob($pattern) as $filename) {
  require_once $filename;
}
