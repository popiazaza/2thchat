<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('commonblock_html', 'class/block/html');

class block_thchat extends commonblock_html {

	function name() {
		return '2th Chat';
	}

	function block_forumtree() {}
	function getsetting() {}
	
	function getdata($style, $parameter) {
		global $_G;
		include DISCUZ_ROOT. './source/plugin/th_chat/include.php';
		include template('th_chat:discuz');
		return array('html' => $return, 'data' => null);
	}
}

?>