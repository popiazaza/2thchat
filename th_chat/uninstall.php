<?php
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
DB::query("DROP TABLE IF EXISTS `" . DB::table('newz_data') . "`;");
DB::query("DROP TABLE IF EXISTS `" . DB::table('newz_nick') . "`;");

$img_up_path = DISCUZ_ROOT . './source/plugin/th_chat/img_up';
if (is_dir($img_up_path)) {
	$files = glob($img_up_path . '/*');
	foreach ($files as $file) {
		if (is_file($file)) {
			unlink($file);
		}
	}
	rmdir($img_up_path);
}

$finish = TRUE;
