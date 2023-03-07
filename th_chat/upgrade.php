<?php
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

if ($_GET['fromversion'] <= "1.11") {
    $sql = <<<EOF
ALTER TABLE  `pre_newz_data` CHANGE  `icon`  `icon` MEDIUMTEXT NOT NULL;
ALTER TABLE  `pre_newz_nick` ADD  `sound_1` INT(1) NOT NULL DEFAULT 0, ADD  `sound_2` INT(1) NOT NULL DEFAULT 1;
EOF;
    runquery($sql);
}
if ($_GET['fromversion'] <= "2.10") {
    $sql = <<<EOF
ALTER TABLE  `pre_newz_nick` CHANGE  `point_total`  `point_total` SMALLINT(3) NOT NULL DEFAULT 0;
EOF;
    runquery($sql);
}

if ($_GET['fromversion'] <= "2.15") {
    $sql = <<<EOF
ALTER TABLE `pre_newz_nick` DROP `name`;
ALTER TABLE `pre_newz_data` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `pre_newz_nick` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EOF;
    runquery($sql);
}

if ($_GET['fromversion'] < "2.19") {
    $sql = <<<EOF
ALTER TABLE `pre_newz_nick` ADD `ban` INT(10) UNSIGNED NOT NULL DEFAULT '0' AFTER `sound_2`;
ALTER TABLE `pre_newz_nick` DROP `point_time`, DROP `point_total`;
EOF;
    runquery($sql);
}

if ($_GET['fromversion'] <= "2.21") {
    DB::query("ALTER TABLE `pre_newz_data` RENAME `" . DB::table('newz_data') . "`;");
    DB::query("ALTER TABLE `pre_newz_nick` RENAME `" . DB::table('newz_nick') . "`;");
    DB::query("ALTER TABLE `" . DB::table('newz_data') . "` ENGINE=InnoDB;");
    DB::query("ALTER TABLE `" . DB::table('newz_nick') . "` ENGINE=InnoDB;");
}

if ($_GET['fromversion'] <= "2.22") {
    DB::query("ALTER TABLE `" . DB::table('newz_nick') . "` DROP `time`;");
}

if ($_GET['fromversion'] <= "3.0.0") {
    DB::query("ALTER TABLE `" . DB::table('newz_data') . "` ADD `unread` INT(1) NOT NULL DEFAULT 0 AFTER `ip`;");
}

if ($_GET['fromversion'] <= "3.0.1") {
    DB::query("ALTER TABLE `" . DB::table('newz_nick') . "` ADD `setting` TEXT NOT NULL DEFAULT '' AFTER `sound_2`;");
    $previous_setting_query = DB::query("SELECT * FROM " . DB::table('newz_nick'));
    while ($previous_setting_fetch = DB::fetch($previous_setting_query)) {
        DB::update('newz_nick', array(
            'setting' =>
            json_encode(
                array(
                    'sound_general' => $previous_setting_fetch['sound_1'],
                    'sound_private' => $previous_setting_fetch['sound_2']
                )
            )
        ), "`uid`='" . $previous_setting_fetch['uid'] . "'");
    }
    DB::query("ALTER TABLE `" . DB::table('newz_nick') . "` DROP `total`, DROP `sound_1`, DROP `sound_2`;");
}

$finish = true;
