<?php
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class plugin_th_chat_forum
{
	function index_top()
	{
		global $_G;
		include 'include.php';
		include template('th_chat:discuz');
		return $return;
	}

	function post_middle()
	{
		global $_G, $_GET;
		if ($_G['uid']) {
			loadcache('plugin');
			$config = $_G['cache']['plugin']['th_chat'];
			if ($config['new_post'] > 0 && $_GET['action'] == 'newthread') {
				if ($config['new_post'] > 1) {
					return '<input type="checkbox" id="th_chat_notify" name="th_chat_notify" value="1"' . ($config['new_post'] == 2 ? ' checked' : '') . '><label for="th_chat_notify"> โพสต์กระทู้นี้ไปยังห้องแชท</label><br>';
				}
			} elseif ($config['edit_post'] > 0 && $_GET['action'] == 'edit') {
				if ($config['edit_post'] > 1 && $post = DB::fetch_first("SELECT * FROM " . DB::table('forum_post') . " WHERE tid=" . intval($_GET['tid']) . " AND pid=" . intval($_GET['pid']) . " AND position=1")) {
					return '<input type="checkbox" id="th_chat_notify" name="th_chat_notify" value="1"' . ($config['edit_post'] == 2 ? ' checked' : '') . '><label for="th_chat_notify"> โพสต์การแก้ไขกระทู้นี้ไปยังห้องแชท</label><br>';
				}
			}
		}
	}

	function post_middle_message($args)
	{
		global $_G, $_POST;
		loadcache('plugin');
		$config = $_G['cache']['plugin']['th_chat'];
		if ($config['new_post'] > 0 && $args['param'][0] == 'post_newthread_succeed') {
			if ($config['new_post'] == 2 || $_POST['th_chat_notify']) {
				if ($post = DB::fetch_first("SELECT * FROM " . DB::table('forum_post') . " WHERE `fid` = " . $args['param'][2]['fid'] . " AND `tid` = " . $args['param'][2]['tid'] . " AND `pid` = " . $args['param'][2]['pid'])) {
					DB::query("INSERT INTO " . DB::table('newz_data') . " (uid,touid,icon,text,time,ip) VALUES (" . $_G['uid'] . ",0,'bot','โพสต์ <a target=\"_blank\" href=\"forum.php?mod=viewthread&tid=" . $post['tid'] . "\">" . addslashes($post['subject']) . "</a>'," . time() . ",'" . $_G['clientip'] . "')");
				}
			}
		} else if ($config['edit_post'] > 0 && $args['param'][0] == 'post_edit_succeed') {
			if ($config['edit_post'] == 2 || $_POST['th_chat_notify']) {
				if ($post = DB::fetch_first("SELECT * FROM " . DB::table('forum_post') . " WHERE `fid` = " . $args['param'][2]['fid'] . " AND `tid` = " . $args['param'][2]['tid'] . " AND `pid` = " . $args['param'][2]['pid'])) {
					DB::query("INSERT INTO " . DB::table('newz_data') . " (uid,touid,icon,text,time,ip) VALUES (" . $_G['uid'] . ",0,'bot','อัปเดตโพสต์ <a target=\"_blank\" href=\"forum.php?mod=viewthread&tid=" . $post['tid'] . "\">" . addslashes($post['subject']) . "</a>'," . time() . ",'" . $_G['clientip'] . "')");
				}
			}
		}
	}
}
