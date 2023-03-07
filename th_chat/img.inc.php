<?php
if (!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
loadcache('plugin');
$config = $_G['cache']['plugin']['th_chat'];
if ($_G['uid'] < 1) {
	die('Login');
}
$maximgsize = 0;
if (!empty($_G['group']['maxattachsize'])) {
	$maximgsize = intval($_G['group']['maxattachsize']);
} else {
	$maximgsize = @ini_get(upload_max_filesize);
	$unit = strtolower(substr($maximgsize, -1, 1));
	$maximgsize = intval($maximgsize);
	if ($unit == 'k') {
		$maximgsize = $maximgsize * 1024;
	} elseif ($unit == 'm') {
		$maximgsize = $maximgsize * 1024 * 1024;
	} elseif ($unit == 'g') {
		$maximgsize = $maximgsize * 1024 * 1024 * 1024;
	}
}
$todayimgcount = 0;
$todayimgsize = 0;
foreach (glob(__DIR__ . '/img_up/' . $_G['uid'] . '_' . date('Ymd', TIMESTAMP) . '*') as $filename) {
	$todayimgcount++;
	$todayimgsize += filesize($filename);
}
if ($_G['group']['maxattachnum']) {
	if ($todayimgcount >= $_G['group']['maxattachnum']) {
		header('Content-Type: application/json');
		echo json_encode(array('error' => 'ขออภัย กลุ่มสมาชิกของคุณอัปโหลดภาพเพียงวันละ ' . $_G['group']['maxattachnum'] . ' ภาพเท่านั้น'));
		exit();
	}
}
if ($_G['group']['maxsizeperday']) {
	if ($todayimgsize >= $_G['group']['maxsizeperday']) {
		header('Content-Type: application/json');
		echo json_encode(array('error' => 'ขออภัย กลุ่มสมาชิกของคุณอัปโหลดภาพเพียงวันละ ' . $_G['group']['maxsizeperday'] . ' Kb เท่านั้น'));
		exit();
	}
}
require_once "bulletproof.php";
$image = new Bulletproof\Image($_FILES);
$image->setSize(1, $maximgsize);
$image->setLocation(__DIR__ . "/img_up");
$image->setName($_G['uid'] . '_' . date('YmdHis', TIMESTAMP));
if ($image["pictures"]) {
	$upload = $image->upload();
	header('Content-Type: application/json');
	if ($upload) {
		echo json_encode(array('url' => $_G['siteurl'] . 'source/plugin/th_chat/img_up/' . $image->getName() . '.' . $image->getMime()));
	} else {
		echo json_encode(array('error' => $image->getError()));
	}
}
