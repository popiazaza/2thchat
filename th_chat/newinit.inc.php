<?php
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
loadcache('plugin');
$config = $_G['cache']['plugin']['th_chat'];
$uid = $_G['uid'];
$is_mod = in_array($_G['adminid'], array(1, 2, 3));
require_once libfile('class/THChatMessage', 'plugin/th_chat');
$msg_func = new THChatMessage();
$room = intval($_POST['room']);
if ($room && $uid) {
    DB::update('newz_data', array(
        'unread' => 0,
    ), "`unread`=1 AND `uid`='$room' AND `touid`='$uid'");
    $re = DB::query("SELECT n.*,m.username AS name,mt.username AS toname,g.color,gt.color AS tocolor
FROM " . DB::table('newz_data') . " n
LEFT JOIN " . DB::table('common_member') . " m ON n.uid=m.uid
LEFT JOIN " . DB::table('common_member') . " mt ON n.touid=mt.uid
LEFT JOIN " . DB::table('common_usergroup') . " g ON m.groupid=g.groupid
LEFT JOIN " . DB::table('common_usergroup') . " gt ON mt.groupid=gt.groupid
WHERE (n.uid='$room' AND n.touid='$uid') OR (n.uid='$uid' AND n.touid='$room') AND n.ip NOT IN ('delete','edit','notice')
ORDER BY id DESC LIMIT {$config['chat_init']}");
} else {
    $re = DB::query("SELECT n.*,m.username AS name,mt.username AS toname,g.color,gt.color AS tocolor
FROM " . DB::table('newz_data') . " n
LEFT JOIN " . DB::table('common_member') . " m ON n.uid=m.uid
LEFT JOIN " . DB::table('common_member') . " mt ON n.touid=mt.uid
LEFT JOIN " . DB::table('common_usergroup') . " g ON m.groupid=g.groupid
LEFT JOIN " . DB::table('common_usergroup') . " gt ON mt.groupid=gt.groupid
WHERE n.touid='0' AND n.ip NOT IN ('delete','edit','notice')
ORDER BY id DESC LIMIT {$config['chat_init']}");
}
$body = array();
$lastid = DB::fetch_first("SELECT max(id) as lastid FROM " . DB::table('newz_data'));
$lastid = $lastid['lastid'];
while ($c = DB::fetch($re)) {
    $c['text'] = preg_replace('/\[quota\](.*?)\[\/quota\]/s', '$1', $c['text']);
    if ($c['ip'] == 'delete') {
        continue;
    } elseif ($c['ip'] == 'edit') {
        continue;
    } elseif ($c['ip'] == 'notice') {
        continue;
    }
    if ($c['ip'] == 'clear') {
        $seedd = $time . '_' . $uid . '_' . rand(1, 999);
        $c['text'] = '<span style="color:red" id="del_' . $seedd . '">แจ้งเตือน:</span> <span id="nzchatcontent' . $c['id'] . '">ล้างข้อมูล' . '</span>';
    } elseif ($c['icon'] == 'alert') {
        $c['text'] = '<span id="nzchatcontent' . $c['id'] . '">' . $c['text'] . '</span>';
    } elseif ($c['touid'] == 0) {
        $c['text'] = '<span id="nzchatcontent' . $c['id'] . '">' . $c['text'] . '</span>';
    } elseif ($c['touid'] == $uid) {
        $c['text'] = '<span id="nzchatcontent' . $c['id'] . '">' . $c['text'] . '</span>';
    } elseif ($c['uid'] == $uid) {
        $c['text'] = '<span id="nzchatcontent' . $c['id'] . '">' . $c['text'] . '</span>';
    }
    $body[] = $msg_func->chat_row($c['id'], $c['text'], $c['uid'], $c['name'], $c['time'], $c['icon'], $is_mod);
    if ($c['ip'] == 'clear') {
        break;
    }
}
$body[] = '<script>var formhash = "&formhash=' . formhash() . '";</script>';
include 'online.php';
$body = array_reverse($body);
$body = implode('', $body);
$chat_unread = DB::fetch_first("SELECT count(*) as count FROM " . DB::table('newz_data') . " WHERE `touid`='$uid' AND `unread`='1'");
$chat_unread = $chat_unread['count'];
$body = array('lastid' => $lastid, 'datahtml' => $body, 'datachatonline' => $body_online, 'chat_online_total' => $oltotal, 'chat_unread' => $chat_unread, 'welcometext' => $config['welcometext']);
echo json_encode($body);
