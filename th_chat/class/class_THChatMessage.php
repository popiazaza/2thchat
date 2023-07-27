<?php
class THChatMessage
{
	public function get_at($attextn)
	{
		if (preg_match_all('/@(.*?)(\s|\z)/', $attextn, $atmatch)) {
			foreach ($atmatch[1] as $atvalue) {
				$atuser = DB::fetch_first("SELECT m.uid,m.groupid,g.color FROM " . DB::table('common_member') . " m LEFT JOIN " . DB::table('common_usergroup') . " g ON m.groupid=g.groupid WHERE m.username='{$atvalue}' LIMIT 1");
				if ($atuser) {
					$attext = addslashes('<a class="nzuserat nzat_' . $atuser['uid'] . '" onclick="nzAt(\'' . ($atvalue) . '\');"' . ($atuser['color'] ? ' style="color:' . $atuser['color'] . '"' : '') . '>@' . stripslashes($atvalue) . '</a> ');
				} else {
					$attext = '@' . $atvalue;
				}
				$attextn = str_replace("@" . $atvalue, $attext, $attextn);
			}
		}
		return $attextn;
	}
	public function get_at2($uid)
	{
		$atuser = DB::fetch_first("SELECT m.uid,m.username,m.groupid,g.color FROM " . DB::table('common_member') . " m LEFT JOIN " . DB::table('common_usergroup') . " g ON m.groupid=g.groupid WHERE m.uid='{$uid}' LIMIT 1");
		$attext = '<a class="nzuserat2 nzat_' . $atuser['uid'] . '" onclick="showWindow(\'th_chat_profile\', \'plugin.php?id=th_chat:profile&uid=' . $uid . '\');return false;"' . ($atuser['color'] ? ' style="color:' . $atuser['color'] . '"' : '') . '>' . $atuser['username'] . '</a>';
		return $attext;
	}
	public function get_at3($uid)
	{
		$atuser = DB::fetch_first("SELECT m.uid,m.username,m.groupid,g.color FROM " . DB::table('common_member') . " m LEFT JOIN " . DB::table('common_usergroup') . " g ON m.groupid=g.groupid WHERE m.uid='{$uid}' LIMIT 1");
		$attext = '<a class="nzuserat nzat_' . $atuser['uid'] . '" onclick="nzAt(\'' . addslashes($atuser['username']) . '\');"' . ($atuser['color'] ? ' style="color:' . $atuser['color'] . '"' : '') . '>' . $atuser['username'] . '</a>';
		return $attext;
	}
	public function get_quota($quota)
	{
		if ($quo = DB::query("SELECT `uid`,`text` FROM " . DB::table('newz_data') . " WHERE `id`='" . $quota . "'")) {
			$quo = DB::fetch($quo);
			$quo['text'] = preg_replace('/\[quota\](.*?)\[\/quota\]/', '', $quo['text']);
			$attext = $this->get_at3($quo['uid']);
			$text = '[quota]<div class="nzblockquote">' . $attext . ': ' . $quo['text'] . '</div>[/quota]';
		}
		return $text;
	}
	public function chat_row($id, $text, $uid_p, $username, $time, $icon, $mod)
	{
		global $config, $_G;
		$tag = '';
		if ($icon == 'alert') {
			$tag = '<span class="nztag" style="background:#e53935">แจ้งเตือน</span>';
		} elseif ($icon == 'bot') {
			$tag = '<span class="nztag" style="background:#546E7A">อัตโนมัติ</span>';
		}
		if ($uid_p == $_G['uid']) {
			return '<div class="nzchatrow" id="nzrows_' . $id . '" onMouseOver="nzchatobj(\'#nzchatquota' . $id . '\').css(\'opacity\',\'1\');" onMouseOut="nzchatobj(\'#nzchatquota' . $id . '\').css(\'opacity\',\'0\');">
	<div></div>
	<div class="nzcontentt nzme">
		<div class="nzchatimenu">
			<span id="nzchatquota' . $id . '" class="nzcq"><a href="javascript:void(0);" onClick="nzQuota(' . $id . ')">อ้างอิง</a> ' . (($config['editmsg'] == 1) && $mod || ($config['editmsg'] == 2) && $mod || (($config['editmsg'] == 3)) ? ' <a href="javascript:;" onClick=\'nzCommand("edit","' . $id . '");\'>แก้ไข</a>' : '') . ($mod ? ' <a href="javascript:;" onClick=\'nzCommand("del","' . $id . '");\'>ลบ</a>' : '') . '</span>
			<br>
			<span class="nztime" title="' . date("c", $time) . '">' . $this->get_date($time) . '</span>
		</div>
		<div class="nzinnercontent">' . $tag . $text . '</div>
	</div>
</div>
<script>nzchatobj("#nzrows_' . $id . ' span.nztime").timeago();</script>';
		} else {
			return '<div class="nzchatrow" id="nzrows_' . $id . '" onMouseOver="nzchatobj(\'#nzchatquota' . $id . '\').css(\'opacity\',\'1\');" onMouseOut="nzchatobj(\'#nzchatquota' . $id . '\').css(\'opacity\',\'0\');">
	<div class="nzavatart">
		<a href="javascript:void(0);" onclick="showWindow(\'th_chat_profile\', \'plugin.php?id=th_chat:profile&uid=' . $uid_p . '\');return false;"><img src="' . avatar($uid_p, 'small', 1) . '" title="' . $username . '" class="nzchatavatar" onError="this.src=\'uc_server/images/noavatar_small.gif\';" /></a>
	</div>
	<div class="nzcontentt">
		' . $this->get_at2($uid_p) . '
		<br>
		<div style="display:flex;align-items:flex-end;">
		<div class="nzinnercontent">' . $tag . $text . '</div>
		<div class="nzchatimenu">
			<span id="nzchatquota' . $id . '" class="nzcq"><a href="javascript:void(0);" onClick="nzQuota(' . $id . ')">อ้างอิง</a> <a href="javascript:void(0);" onclick="nzAt(\'' . addslashes($username) . '\')">@</a> <a href="javascript:void(0);" onclick="nzTouid(' . $uid_p . ')">แชทส่วนตัว</a> ' . (($config['editmsg'] == 1) && $mod ? ' <a href="javascript:;" onClick=\'nzCommand("edit","' . $id . '");\'>แก้ไข</a>' : '') . ($mod ? ' <a href="javascript:;" onClick=\'nzCommand("del","' . $id . '");\'>ลบ</a>' : '') . '</span>
			<br>
			<span class="nztime" title="' . date("c", $time) . '">' . $this->get_date($time) . '</span>
		</div>
		</div>
	</div>
</div>
<script>nzchatobj("#nzrows_' . $id . ' span.nztime").timeago();</script>';
		}
	}
	function get_date($timestamp)
	{
		$strYear = substr(date("Y", $timestamp) + 543, 2, 2);
		$strMonth = date("n", $timestamp);
		$strDay = date("j", $timestamp);
		$strHour = date("H", $timestamp);
		$strMinute = date("i", $timestamp);
		$strMonthCut = array("", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค.");
		$strMonthThai = $strMonthCut[$strMonth];
		return "$strDay $strMonthThai $strYear $strHour:$strMinute น.";
	}
}
