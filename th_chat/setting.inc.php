<?php
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
if($_G['uid']<1){
	exit('Please Login');
}
loadcache('plugin');
$config = $_G['cache']['plugin']['th_chat'];
if($_POST['sound_general']!=""&&$_POST['sound_private']!="")
{
	$uid = $_G['uid'];
	$setting = array();
	$setting['sound_general'] = $_POST['sound_general'] == 1 ? 1 : 0;
	$setting['sound_private'] = $_POST['sound_private'] == 1 ? 1 : 0;
	if(in_array($_POST['theme'],array('light','dark'))){
		echo '<script>nzchatobj("#nzchat").removeClass();nzchatobj("#nzchat").addClass("nz'.$_POST['theme'].'");</script>';
		$setting['theme'] =  $_POST['theme'];
	}
	if(in_array($_POST['font_size'],array('12px','14px','16px'))){
		echo '<script>nzchatobj("#nzchatcontent").animate({fontSize:"'.$_POST['font_size'].'"}, 200, function() {nzScrollChat(true);});</script>';
		$setting['font_size'] =  $_POST['font_size'];
	}
	if(in_array($_POST['chat_height'],array('300','350','400','450','500','550','600','650','700','750','800'))){
		$setting['chat_height'] =  $_POST['chat_height'];
		echo '<script>
		nzsetting.chatheight = '.$_POST['chat_height'].';
		nzchatobj("#nzchatcontent").animate({height:'.$_POST['chat_height'].'}, 200, function() {nzScrollChat(true);});
		if(nzchatobj(".nzquoteboxo").is(":visible")){
			nzchatobj("#nzchatcontent").animate({height:'.$_POST['chat_height'].'  - nzchatobj(".nzquoteboxo").height()}, 200, function() {nzScrollChat(true);});
		}else{
			nzchatobj("#nzchatcontent").animate({height:'.$_POST['chat_height'].'}, 200, function() {nzScrollChat(true);});
		}
		nzchatobj("#nzchatolcontent").animate({height:'.$_POST['chat_height'].'}, 200);
		</script>';
	}
	$setting = json_encode($setting);
	if($uid<1){
		die('Login');
	}
	$re = DB::query("SELECT * FROM ".DB::table('newz_nick')." WHERE uid='{$uid}'");
	if($re = DB::fetch($re)){
		DB::query("UPDATE ".DB::table('newz_nick')." SET setting='{$setting}' WHERE uid='{$uid}' LIMIT 1");
	}else{
		DB::query("INSERT INTO ".DB::table('newz_nick')." (uid,setting) VALUES ('{$uid}','{$setting}')");
	}
	exit('เปลี่ยนการตั้งค่าสำเร็จ!<script>hideWindow("th_chat_setting", 0, 1);nzalert("เปลี่ยนการตั้งค่าสำเร็จ!");</script>');
}else{
	$setting_data = DB::fetch_first("SELECT setting FROM ".DB::table('newz_nick')." WHERE uid='{$_G['uid']}'");
	$setting = json_decode($setting_data['setting'],1);
	$setting['sound_general'] = $setting['sound_general'] == 0 ? 0 : 1;
	$setting['sound_private'] = $setting['sound_private'] == 0 ? 0 : 1;
	if(!in_array($setting['theme'],array('light','dark'))){
		$setting['theme'] =  $config['default_theme'];
	}
	if(!in_array($setting['font_size'],array('14px','16px','18px'))){
		$setting['font_size'] =  $config['default_font_size'];
	}
	if(!in_array($setting['chat_height'],array('300','350','400','450','500','550','600','650','700','750','800'))){
		$setting['chat_height'] =  $config['default_chat_height'];
	}
}
include template('common/header_ajax');
include template('th_chat:window');
include template('common/footer_ajax');
?>