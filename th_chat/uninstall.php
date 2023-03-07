<?php
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
DB::query("DROP TABLE IF EXISTS `" . DB::table('newz_data') . "`;");
DB::query("DROP TABLE IF EXISTS `" . DB::table('newz_nick') . "`;");
$finish = TRUE;
