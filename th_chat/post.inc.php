<?php
if(!defined('IN_DISCUZ')) { exit('Access Denied'); }
loadcache('plugin');
$config = $_G['cache']['plugin']['th_chat'];
$uid = $_G['uid'];
$gid = $_G['groupid'];
$is_mod = in_array($_G['adminid'],array(1,2,3));
include 'functions.php';
if($uid<1){
	die(json_encode(array('type'=>1,'error'=>''.lang('plugin/th_chat', 'jdj_th_chat_text_php_05').'')));
}
$banned = DB::query("SELECT value FROM ".DB::table('common_pluginvar')." WHERE variable='chat_ban' AND displayorder='15' LIMIT 1");
$banned = DB::fetch($banned);
eval("\$banned = array({$banned['value']});");
if((in_array($gid,array(4,5))||in_array($uid,$banned))&&!$is_mod){
	die(json_encode(array('type'=>1,'error'=>lang('plugin/th_chat', 'jdj_th_chat_text_php_11'))));
}
if (get_magic_quotes_gpc()) {
	$text = stripslashes($_POST['text']);
}
else {
	$text = $_POST['text'];
}
$f = file_get_contents(DISCUZ_ROOT.'/source/plugin/th_chat/template/discuz.htm');
$id = intval($_POST['lastid']);
$touid = intval($_POST['touid']);
$quota = intval($_POST['quota']);
$command = $_POST['command'];
$at = intval($_POST['at']);
$color = str_replace(array('\'','\\','"','<','>'),'',$_POST['color']);
$ip = $_SERVER['REMOTE_ADDR'];
$a = file_get_contents(DISCUZ_ROOT.'/source/plugin/th_chat/template/big.htm');
if($config['oldcommand']==1){
	if(substr($text,0,4)=="!ban"&&$is_mod){
		$uid_ban = intval(substr($text,4));
		if($uid_ban && !in_array($uid_ban,$banned) && $uid_ban != $uid){
			$banned[] = $uid_ban;
			$username_ban = DB::query("SELECT m.username AS name,m.groupid,g.color,n.name AS nick FROM ".DB::table('common_member')." m LEFT JOIN ".DB::table('newz_nick')." n ON m.uid=n.uid LEFT JOIN ".DB::table('common_usergroup')." g ON m.groupid=g.groupid WHERE m.uid='{$uid_ban}' LIMIT 1");
			$username_ban = DB::fetch($username_ban);
			if($username_ban['nick']&&$config['namemode']==2){
				$username_banz = $username_ban['nick'];
			}else{
				$username_banz = $username_ban['name'];
			}
			$icon = 'alert';
			$touid = 0;
			$username_ban = empty($username_ban['color'])?htmlspecialchars_decode($username_banz):'[color='.$username_ban['color'].']'.htmlspecialchars_decode($username_banz).'[/color]';
			$text = '[url=home.php?mod=space&uid='.$uid_ban.'][b]'.$username_ban.'[/b][/url] [color=red]'.lang('plugin/th_chat', 'jdj_th_chat_text_php_23').'[/color]';
			$banned_new = array();
			foreach($banned as $uid_banned){
				if($uid_banned&&!in_array($uid_banned,$banned_new)){
					$banned_new[] = $uid_banned;
				}
			}
			$banned = implode(',',$banned_new);
			DB::query("UPDATE ".DB::table('common_pluginvar')." SET value='{$banned}' WHERE variable='chat_ban' AND displayorder='15' LIMIT 1");
		}
	}elseif(substr($text,0,6)=="!unban"&&$is_mod){
		$uid_ban = intval(substr($text,6));
		if($uid_ban && in_array($uid_ban,$banned)){
			$key = array_search($uid_ban, $banned);
			if($key !== FALSE) unset($banned[$key]);
			$username_ban = DB::query("SELECT m.username AS name,m.groupid,g.color,n.name AS nick FROM ".DB::table('common_member')." m LEFT JOIN ".DB::table('newz_nick')." n ON m.uid=n.uid LEFT JOIN ".DB::table('common_usergroup')." g ON m.groupid=g.groupid WHERE m.uid='{$uid_ban}' LIMIT 1");
			$username_ban = DB::fetch($username_ban);
			if($username_ban['nick']&&$config['namemode']==2){
				$username_banz = $username_ban['nick'];
			}else{
				$username_banz = $username_ban['name'];
			}
			$icon = 'alert';
			$touid = 0;
			$username_ban = empty($username_ban['color'])?htmlspecialchars_decode($username_banz):'[color='.$username_ban['color'].']'.htmlspecialchars_decode($username_banz).'[/color]';
			$text = '[color=red]'.lang('plugin/th_chat', 'jdj_th_chat_text_php_28').'[/color] [url=home.php?mod=space.php&uid='.$uid_ban.'][b]'.$username_ban.'[/b][/url]';
			$banned_new = array();
			foreach($banned as $uid_banned){
				if($uid_banned&&!in_array($uid_banned,$banned_new)){
					$banned_new[] = $uid_banned;
				}
			}
			$banned = implode(',',$banned_new);
			DB::query("UPDATE ".DB::table('common_pluginvar')." SET value='{$banned}' WHERE variable='chat_ban' AND displayorder='15' LIMIT 1");
		}
	}elseif(substr($text,0,6)=="!point"&&$config['chat_point']){
		$point = explode('|',substr($text,6));
		$uid_point = intval($point[0]);
		$res = $point[2];
		$point = intval($point[1]);
		if($uid_point&&($point==1||$point==-1)&&($uid_point!=$uid)||$uid==1){
			$re = DB::query("SELECT uid,point_time FROM ".DB::table('newz_nick')." WHERE uid='{$uid}'");
			if($re = DB::fetch($re)){
				if($time-$re['point_time']<10){
					die(json_encode(array('type'=>1,'error'=>lang('plugin/th_chat', 'jdj_th_chat_text_php_12'))));
				}else{
					DB::query("UPDATE ".DB::table('newz_nick')." SET point_time='{$time}' WHERE uid='{$uid}' LIMIT 1");
				}
			}else{
				DB::query("INSERT INTO ".DB::table('newz_nick')." (uid,point_time) VALUES ('{$uid}','{$time}')");
			}
			if($point>0){
				$point = '+'.$point;
			}
			if($touid!=$uid_point){
				$touid=0;
			}
			DB::query("UPDATE ".DB::table('common_member_count')." SET extcredits{$config['chat_point']}=extcredits{$config['chat_point']}{$point} WHERE uid='{$uid_point}' LIMIT 1");
			$username_point = DB::query("SELECT extcredits{$config['chat_point']} AS point FROM ".DB::table('common_member_count')." WHERE uid='{$uid_point}' LIMIT 1");
			$username_point = DB::fetch($username_point);
			$total_point = $username_point['point'];
			if($point>0){
				$point='[color=green]'.$point.'[/color]';
			}else{
				$point='[color=red]'.$point.'[/color]';
			}
			$color = 'default';
			$icon = 'alert';
			$touid = 0;
			$text = ' '.$point.' = '.$total_point.' '.$res;
			$at = $uid_point;
			$quota = 0;
		}
	}
	if($command=="notice"&&$is_mod){
		$icon = 'alert';
		$touid = 0;
		$ip = 'notice';
	}elseif(substr($command,0,4)=="edit"&&($config['editmsg']!=0)){
		$editid = intval(substr($command,5));
		if($config['editmsg']==1&&!$is_mod){
			die(json_encode(array('type'=>1,'error'=>'Access Denied')));
		}
		$user = DB::fetch(DB::query("SELECT uid FROM ".DB::table('newz_data')." WHERE id='{$editid}'"));
		if($config['editmsg']==2&&(!$is_mod||$user['uid']!=$uid)){
			die(json_encode(array('type'=>1,'error'=>'Access Denied')));
		}else if($config['editmsg']==3&&($user['uid']!=$uid)){
			die(json_encode(array('type'=>1,'error'=>'Access Denied')));
		}
		$text .=' @'.get_date($time);
		if($user['uid']!=$uid){
			$text .=' '.lang('plugin/th_chat', 'jdj_th_chat_text_php_17').' '.$_G['username'];
		}
		$ip = 'edit';
		$icon = $editid;
	}
}
if(strpos($f,'&copy; <a href="http://2th.me/" target="_blank">2th Chat</a>')===false||strpos($a,'&copy; <a href="http://2th.me/" target="_blank">2th Chat</a>')===false)die();
$txtlen = strlen($text);
if($txtlen>$config['chat_strlen']){
	$text = '... '.substr($text,$txtlen-$config['chat_strlen']);
}
if($uid==$touid){
	die();
}
include(DISCUZ_ROOT.'/source/function/function_discuzcode.php');
$config['useemo'] = $config['useemo']?0:1;
$config['usedzc'] = $config['usedzc']?0:1;
$config['useunshowdzc'] = $config['useunshowdzc']?0:1;
if(strpos($f,'&copy; <a href="http://2th.me">2th</a>')===false)die();
if($config['autourl']){
	$text= preg_replace('#(^|\s)([a-z]+://([^\s\w/]?[\w/])*)#is', '\\1[url]\\2[/url]', $text);
	$text = preg_replace('#(^|\s)((www|ftp)\.([^\s\w/]?[\w/])*)#is', '\\1[url]\\2[/url]', $text);
}
if($config['mediacode']){
$text = preg_replace("/\[media=([\w,]+)\]\s*([^\[\<\r\n]+?)\s*\[\/media\]/ies", "", $text);
if($config['spoiler']){
$text = str_replace("[media]", "[spoil][media=x,480,360]", $text);
$text = str_replace("[/media]", "[/media][/spoil]", $text);
}else{
$text = str_replace("[media]", "[media=x,252,189]", $text);
}
$query_bw = DB::query("SELECT * FROM ".DB::table('common_word'));
while ($bw = DB::fetch($query_bw))
{
	if($bw['replacement']=='{MOD}'&&$config['spoiler']){$bw['replacement'] = '[spoil]'.$bw['find'].'[/spoil]';}
	$text = str_replace($bw['find'],$bw['replacement'],$text);
}
}
$text = preg_replace('/\[quota\](.*?)\[\/quota\]/', '[quota]$1[[color=#fff][/color]/quota]', $text);
$text = paddslashes(discuzcode($text,$config['useemo'],$config['usedzc'],$config['usehtml'],1,1,$config['useimg'],1,0,$config['useunshowdzc'],0, $config['mediacode']));
if(($is_mod>0)&&$text=='!clear'&&$config['oldcommand']==1){
$ip = 'clear';
$icon = 'alert';
$touid = 0;
$text = lang('plugin/th_chat', 'jdj_th_chat_text_php_46');
$needClear = 1;
}
$text = getat($text);
if($color!='default'){
	$text = '<span style="color:#'.$color.';">'.$text.'</span>';
}
if($ip == 'notice'){
	DB::query("UPDATE ".DB::table('common_pluginvar')." SET value='{$text}' WHERE variable='welcometext' AND displayorder='1' LIMIT 1");
	include_once libfile('function/cache');
	updatecache('plugin');
}elseif($ip == 'edit'){
	DB::query("UPDATE ".DB::table('newz_data')." SET text='{$text}' WHERE id='{$icon}' LIMIT 1");
}
if($quota>0 && $config['quota'] && $ip != 'clear'){
	if($quo = DB::query("SELECT text FROM ".DB::table('newz_data')." WHERE id='{$quota}'"))
	{
		$quo = DB::fetch($quo);
		$quo['text'] = preg_replace('/\[quota\](.*?)\[\/quota\]/', '', $quo['text']);
		$text = '[quota]'.paddslashes($quo['text']).' // [/quota]'.$text;
	}
}else if($at>0 && $ip != 'clear'){
	$user = DB::query("SELECT m.username,m.groupid,g.color,n.name FROM ".DB::table('common_member')." m LEFT JOIN ".DB::table('newz_nick')." n ON m.uid=n.uid LEFT JOIN ".DB::table('common_usergroup')." g ON m.groupid=g.groupid WHERE m.uid='{$at}' LIMIT 1");
	$user = DB::fetch($user);
	if($user['name']&&$config['namemode']==2){
		$userz = $user['name'];
	}else{
		$userz = $user['username'];
	}
	$userz = addslashes(htmlspecialchars_decode($userz));
	$text = '@<a class="nzca"><font color="'.$user['color'].'">'.$userz.'</font></a> '.$text;
}

	/**
	 * Log chat within txt files
	 * @add Jaieejung007
	 *
	 * @since 2.04.2
	 *
	 * @param string $logf   Config your path of log files.
	 * @param string $tab  Add a tab between the characters in the file.
	 */
$logf = DISCUZ_ROOT.'./data/thzaa_log/'.date('d-m-Y');
dmkdir($logf);
	$f = fopen($logf . '/all.txt', 'a');
	if (get_magic_quotes_gpc()) {
		$oldtext = stripslashes($_POST['text']);
	} else {
		$oldtext = $_POST['text'];
	}
	$tab .= "\t";
	$msg = $uid . ( $touid ? ' To ' . $touid : '' ) . ''.$tab.'Say: ' . $oldtext . ''.$tab.'Time: ' . time() . ''.$tab.'Time(Simple): '.date('d-m-Y H:i:s').''.$tab.'IP: ' . $_SERVER['REMOTE_ADDR'];
	
	if($ip == "clear"&&$is_mod) {
		$msg .= ' Cmd: Clear';
	} elseif(substr($text, 0, 4) == "/ban"&&$is_mod) {
		$msg .= ' Cmd: Ban ' . intval(substr($text, 4));
	} elseif(substr($text, 0, 6) == "/unban"&&$is_mod) {
		$msg .= ' Cmd: Un Ban ' . intval(substr($text, 6));
	}
	
	$msg .= "\r\n";

	fwrite($f, $msg);
	fclose($f);
	
	if($touid) {
		$sort = array($uid, $touid); asort($sort);
		$logfile = $logf . '/' . implode('-', $sort) . '.txt';
		
		$f = fopen($logfile, 'a');
		fwrite($f, $msg);
		fclose($f);
	}

$icon==''?$icon=checkOs():$icon=$icon;
DB::query("INSERT INTO ".DB::table('newz_data')." (uid,touid,icon,text,time,ip) VALUES ('$uid','$touid','$icon','$text','".time()."','$ip')");

/*RESEND*/

$last = DB::insert_id();
if($needClear){
	DB::query("DELETE FROM ".DB::table('newz_data')." WHERE id<".$last);
}else{
	DB::query("DELETE FROM ".DB::table('newz_data')." WHERE id<".($last-$config['chat_log']));
}
$re = DB::query("SELECT n.*,m.username AS name,mt.username AS toname,g.color,gt.color AS tocolor,ni.name AS nick,nt.name AS tonick 
FROM ".DB::table('newz_data')." n 
LEFT JOIN ".DB::table('common_member')." m ON n.uid=m.uid 
LEFT JOIN ".DB::table('common_member')." mt ON n.touid=mt.uid 
LEFT JOIN ".DB::table('common_usergroup')." g ON m.groupid=g.groupid 
LEFT JOIN ".DB::table('common_usergroup')." gt ON mt.groupid=gt.groupid 
LEFT JOIN ".DB::table('newz_nick')." ni ON n.uid=ni.uid 
LEFT JOIN ".DB::table('newz_nick')." nt ON n.touid=nt.uid 
WHERE  id>{$id} AND (n.touid='0' OR n.touid='{$uid}' OR n.uid='{$uid}') 
ORDER BY id DESC LIMIT 30");
$body=array();
while($c = DB::fetch($re)){
$c['text'] = preg_replace('/\[quota\](.*?)\[\/quota\]/', '$1', $c['text']);
	if ($c['ip'] == 'changename'){
		$body[$c['id']] .= '<script>nzchatobj(".nzu'.($config['namemode']==1?'status':'name').'_'.$c['uid'].'").html("'.addcslashes(htmlspecialchars_decode($c['text']),'"').'");</script>';
		continue;
	}elseif($c['ip'] == 'delete'){
		$body[$c['id']] .= '<script>nzchatobj("#nzrows_'.$c['text'].'").fadeOut(200);</script>';
		continue;
	}elseif($c['ip'] == 'notice'){
		DB::query("UPDATE ".DB::table('common_pluginvar')." SET value='".addslashes($c['text'])."' WHERE variable='welcometext' AND displayorder='1' LIMIT 1");
		include_once libfile('function/cache');
		updatecache('plugin');
		$body[$c['id']] .= '<script>nzchatobj("#nzsendingmsg").hide();nzchatobj("#nzcharnum").show();window.clearInterval(nzdot);nzchatobj("#nzchatnotice").html("'.addcslashes($c['text'],'"').'");</script>';
		continue;
	}elseif($c['ip'] == 'edit'){
		$body[$c['id']] .= '<script>nzchatobj("#nzsendingmsg").hide();nzchatobj("#nzcharnum").show();window.clearInterval(nzdot);nzchatobj("#nzchatcontent'.$c['icon'].'").html("'.addcslashes($c['text'],'"').'");</script>';
		continue;
	}
	if($config['namemode']==1){$c['status'] = $c['nick'];}
	if((strval($c['nick'])===''&&$config['namemode']==2)||$config['namemode']!=2){$c['nick'] = $c['name'];}
	if((strval($c['tonick'])===''&&$config['namemode']==2)||$config['namemode']!=2){$c['tonick'] = $c['toname'];}
	$c['tonick'] = htmlspecialchars_decode($c['tonick']);
	$c['text'] .='<script type="text/javascript">nzchatobj("#nzsendingmsg").hide();nzchatobj("#nzcharnum").show();window.clearInterval(nzdot);</script>';
	if($c['ip']=='clear'){
		$seedd = $time.'_'.$uid.'_'.rand(1,999);
		$c['text'] = '<span style="color:red" id="del_'.$seedd.'">'.lang('plugin/th_chat', 'jdj_th_chat_text_php_14').'</span> <span id="nzchatcontent'.$c['id'].'">'.lang('plugin/th_chat', 'jdj_th_chat_text_php_46').'<script type="text/javascript">nzchatobj("#del_'.$seedd.'").parent().parent().parent().'.($config['chat_type']==1?'next':'prev').'Until().remove();nzchatobj("#nzsendingmsg").hide();nzchatobj("#nzcharnum").show();window.clearInterval(nzdot);</script>';
	}elseif($c['icon']=='alert'){
		$c['text'] = '<span style="color:red">'.lang('plugin/th_chat', 'jdj_th_chat_text_php_14').'</span> <span id="nzchatcontent'.$c['id'].'">' . $c['text'];
	}elseif($c['touid']==0){
		$c['text'] = '<span style="color:#3366CC">'.lang('plugin/th_chat', 'jdj_th_chat_text_php_38').'</span> <span id="nzchatcontent'.$c['id'].'">' . $c['text'];
	}elseif($c['touid']==$uid){
		$c['text'] = ($config['pm_sound']?'<embed name="pmsoundplayer" width="0" height="0" src="source/plugin/th_chat/images/player.swf" flashvars="sFile='.$config['pm_sound'].'" menu="false" allowscriptaccess="sameDomain" swliveconnect="true" type="application/x-shockwave-flash"></embed>':'').'<span style="color:#FF9900">'.lang('plugin/th_chat', 'jdj_th_chat_text_php_03').' <a href="javascript:;" onClick="nzTouid('.$c['uid'].')">(�ͺ��Ѻ)</a>:</span> <span id="nzchatcontent'.$c['id'].'">' . $c['text'];
	}elseif($c['uid']==$uid){
		$c['text'] = '<span style="color:#FF9900">'.lang('plugin/th_chat', 'jdj_th_chat_text_php_02').' <a href="home.php?mod=space&uid='.$c['touid'].'" class="nzca" target="_blank"><font color="'.$c['tocolor'].'"><span class="nzuname_'.$c['touid'].'">'.$c['tonick'].'</span></font></a>:</span> <span id="nzchatcontent'.$c['id'].'">' . $c['text'];
	}
	if(!$config['showos']&&$c['icon']!='alert')$c['icon']='';
	$body[$c['id']]  .= chatrow($c['id'],$c['text'],$c['uid'],$c['name'],$c['nick'],$c['time'],$c['color'],$c['touid'],0,$c['icon'],$is_mod,$c['status']);
	if($c['ip']=='clear'){
		break;
	}
}
echo json_encode($body);
?>