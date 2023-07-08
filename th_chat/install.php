<?php
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
DB::query("DROP TABLE IF EXISTS `" . DB::table('newz_data') . "`;");
DB::query("CREATE TABLE IF NOT EXISTS `" . DB::table('newz_data') . "` (
	`id` int(12) unsigned NOT NULL auto_increment,
	`uid` mediumint(8) unsigned NOT NULL,
	`touid` mediumint(8) unsigned NOT NULL,
	`icon` mediumtext NOT NULL,
	`text` mediumtext NOT NULL,
	`time` int(10) unsigned NOT NULL,
	`ip` varchar(25) NOT NULL,
	`unread` int(1) NOT NULL DEFAULT 0,
	PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;");
DB::query("DROP TABLE IF EXISTS `" . DB::table('newz_nick') . "`;");
DB::query("CREATE TABLE IF NOT EXISTS `" . DB::table('newz_nick') . "` (
	`uid` mediumint(8) unsigned NOT NULL,
	`setting` TEXT NOT NULL DEFAULT '',
	`ban` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
DB::insert(
	'newz_data',
	array(
		'uid' => 1,
		'touid' => 0,
		'icon' => 'alert',
		'text' => 'ยินดีต้อนรับสู่ห้องแชท คุณสามารถเริ่มพิมพ์ข้อความของคุณได้ด้านล่า งนี้~!',
		'time' => TIMESTAMP,
		'ip' => $_G['clientip']
	)
);
$finish = TRUE;
