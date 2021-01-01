<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
DB::query("DROP TABLE IF EXISTS `".DB::table('newz_data')."`;");
DB::query("CREATE TABLE IF NOT EXISTS `".DB::table('newz_data')."` (
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
DB::query("DROP TABLE IF EXISTS `".DB::table('newz_nick')."`;");
DB::query("CREATE TABLE IF NOT EXISTS `".DB::table('newz_nick')."` (
	`uid` mediumint(8) unsigned NOT NULL,
	`total` tinyint(1) unsigned NOT NULL DEFAULT '0',
	`sound_1` int(1) NOT NULL DEFAULT '0',
	`sound_2` int(1) NOT NULL DEFAULT '1',
	`ban` INT(10) UNSIGNED NOT NULL DEFAULT '0',
	PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
DB::query("INSERT INTO `".DB::table('newz_data')."` (`uid`, `touid`, `icon`, `text`, `time`,`ip`) VALUES (1, 0, 'alert', 'ยินดีต้อนรับสู่ห้องแชท คุณสามารถเริ่มพิมพ์ข้อความของคุณได้ด้านล่างนี้~!', ".TIMESTAMP.", '".$_G['clientip']."');");
$finish = TRUE;
?>