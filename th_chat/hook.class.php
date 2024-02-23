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
			$forums = unserialize($config['category_post']);
			if ($forums && in_array($_GET['fid'], $forums)) {
				if ($config['new_post'] > 0 && $_GET['action'] == 'newthread') {
					if ($config['new_post'] > 1) {
						return '<input type="checkbox" id="th_chat_notify" name="th_chat_notify" value="1"' . ($config['new_post'] == 2 ? ' checked' : '') . '><label for="th_chat_notify"> โพสต์กระทู้นี้ไปยังห้องแชท</label><br>';
					}
				} elseif ($config['edit_post'] > 0 && $_GET['action'] == 'edit') {
					if ($config['edit_post'] > 1 && DB::fetch_first("SELECT * FROM " . DB::table('forum_post') . " WHERE tid=" . intval($_GET['tid']) . " AND pid=" . intval($_GET['pid']) . " AND position=1")) {
						return '<input type="checkbox" id="th_chat_notify" name="th_chat_notify" value="1"' . ($config['edit_post'] == 2 ? ' checked' : '') . '><label for="th_chat_notify"> โพสต์การแก้ไขกระทู้นี้ไปยังห้องแชท</label><br>';
					}
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
			$forums = unserialize($config['category_post']);
			if ($forums && in_array($args['param'][2]['fid'], $forums)) {
				if ($config['new_post'] == 1 || $_POST['th_chat_notify']) {
					if ($post = DB::fetch_first("SELECT * FROM " . DB::table('forum_post') . " WHERE `fid` = " . $args['param'][2]['fid'] . " AND `tid` = " . $args['param'][2]['tid'] . " AND `pid` = " . $args['param'][2]['pid'])) {
						$msg = "โพสต์ <a target=\"_blank\" href=\"forum.php?mod=viewthread&tid=" . $post['tid'] . "\">" . addslashes($post['subject']) . "</a>";
						if ($config['show_category']) {
							$cat = DB::fetch_first("SELECT * FROM " . DB::table('forum_forum') . " WHERE `fid` = " . $post['fid']);
							$msg .= " ใน <a target=\"_blank\" href=\"forum.php?mod=forumdisplay&fid=" . $post['fid'] . "\">" . addslashes($cat['name']) . "</a>";
						}
						DB::insert(
							'newz_data',
							array(
								'uid' => $_G['uid'],
								'touid' => 0,
								'icon' => 'bot',
								'text' => $msg,
								'time' => time(),
								'ip' => $_G['clientip']
							)
						);
					}
				}
			}
		} else if ($config['edit_post'] > 0 && $args['param'][0] == 'post_edit_succeed') {
			$forums = unserialize($config['category_post']);
			if ($forums && in_array($args['param'][2]['fid'], $forums)) {
				if ($config['edit_post'] == 1 || $_POST['th_chat_notify']) {
					if ($post = DB::fetch_first("SELECT * FROM " . DB::table('forum_post') . " WHERE `fid` = " . $args['param'][2]['fid'] . " AND `tid` = " . $args['param'][2]['tid'] . " AND `pid` = " . $args['param'][2]['pid'])) {
						$msg = "อัปเดตโพสต์ <a target=\"_blank\" href=\"forum.php?mod=viewthread&tid=" . $post['tid'] . "\">" . addslashes($post['subject']) . "</a>";
						if ($config['show_category']) {
							$cat = DB::fetch_first("SELECT * FROM " . DB::table('forum_forum') . " WHERE `fid` = " . $post['fid']);
							$msg .= " ใน <a target=\"_blank\" href=\"forum.php?mod=forumdisplay&fid=" . $post['fid'] . "\">" . addslashes($cat['name']) . "</a>";
						}
						DB::insert(
							'newz_data',
							array(
								'uid' => $_G['uid'],
								'touid' => 0,
								'icon' => 'bot',
								'text' => $msg,
								'time' => time(),
								'ip' => $_G['clientip']
							)
						);
					}
				}
			}
		}
	}
}
