<?php
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
loadcache('plugin');
$config = $_G['cache']['plugin']['th_chat'];
$uid = $_G['uid'];
$id = intval($_POST['lastid']);
$is_mod = in_array($_G['adminid'], array(1, 2, 3));
require_once libfile('class/THChatMessage', 'plugin/th_chat');
$msg_func = new THChatMessage();
$room = intval($_POST['room']);
if ($room) {
    DB::update('newz_data', array(
        'unread' => 0,
    ), "`id`>'$id' AND `unread`=1 AND `uid`='$room' AND `touid`='$uid'");
    $re = DB::query("SELECT n.*,m.username AS name,mt.username AS toname,g.color,gt.color AS tocolor
FROM " . DB::table('newz_data') . " n
LEFT JOIN " . DB::table('common_member') . " m ON n.uid=m.uid
LEFT JOIN " . DB::table('common_member') . " mt ON n.touid=mt.uid
LEFT JOIN " . DB::table('common_usergroup') . " g ON m.groupid=g.groupid
LEFT JOIN " . DB::table('common_usergroup') . " gt ON mt.groupid=gt.groupid
WHERE  id>{$id} AND ((n.touid='$uid') OR (n.uid='$uid' AND n.touid!=0) OR (n.uid='$uid' AND n.touid!=0))
ORDER BY id DESC LIMIT {$config['chat_init']}");
    $setting_data = DB::fetch_first("SELECT setting FROM " . DB::table('newz_nick') . " WHERE uid='{$_G['uid']}'");
} else {
    $re = DB::query("SELECT n.*,m.username AS name,mt.username AS toname,g.color,gt.color AS tocolor
FROM " . DB::table('newz_data') . " n
LEFT JOIN " . DB::table('common_member') . " m ON n.uid=m.uid
LEFT JOIN " . DB::table('common_member') . " mt ON n.touid=mt.uid
LEFT JOIN " . DB::table('common_usergroup') . " g ON m.groupid=g.groupid
LEFT JOIN " . DB::table('common_usergroup') . " gt ON mt.groupid=gt.groupid
WHERE  id>{$id} AND ((n.touid='$uid') OR (n.touid=0))
ORDER BY id DESC LIMIT {$config['chat_init']}");
    $setting_data = DB::fetch_first("SELECT setting FROM " . DB::table('newz_nick') . " WHERE uid='{$_G['uid']}'");
}
if ($setting_data) {
    $setting = json_decode($setting_data['setting'], 1);
} else {
    $setting['sound_general'] = 1;
    $setting['sound_private'] = 1;
}
$body = array();
while ($c = DB::fetch($re)) {
    $c['text'] = preg_replace('/\[quota\](.*?)\[\/quota\]/s', '$1', $c['text']);
    if ($c['ip'] == 'delete') {
        $body[$c['id']] .= '<script>nzchatobj("#nzrows_' . $c['text'] . '").fadeOut(200);</script>';
        continue;
    } elseif ($c['ip'] == 'edit') {
        $body[$c['id']] .= '<script>nzchatobj("#nzchatcontent' . $c['icon'] . '").html("' . addslashes($c['text']) . '");</script>';
        continue;
    } elseif ($c['ip'] == 'notice') {
        $body[$c['id']] .= '<script>nzchatobj("#nzchatnotice").html("' . addslashes($c['text']) . '");</script>';
        continue;
    } elseif ($room != $c['uid'] && $c['touid'] == $uid) {
        if ($config['pm_sound'] && $setting['sound_private']) {
            $body[$c['id']] .= '<audio autoplay><source src="' . $config['pm_sound'] . '" type="audio/mpeg"></audio>';
        } else {
            $body[$c['id']] = '';
        }
        continue;
    } elseif ($room && $c['touid'] == 0) {
        if ($config['pm_sound'] && $setting['sound_general']) {
            $body[$c['id']] .= '<audio autoplay><source src="' . $config['pm_sound'] . '" type="audio/mpeg"></audio>';
        } else {
            $body[$c['id']] = '';
        }
        continue;
    }
    if ($c['ip'] == 'clear') {
        $seedd = $time . '_' . $uid . '_' . rand(1, 999);
        $c['text'] = '<span style="color:red" id="del_' . $seedd . '">แจ้งเตือน:</span> <span id="nzchatcontent' . $c['id'] . '">ล้างข้อมูล</span><script type="text/javascript">nzchatobj("#del_' . $seedd . '").parent().parent().parent().' . ($config['chat_type'] == 1 ? 'next' : 'prev') . 'Until().remove();</script>';
    } elseif ($c['icon'] == 'alert') {
        $c['text'] = '<span id="nzchatcontent' . $c['id'] . '">' . $c['text'] . '</span>';
    } elseif ($c['touid'] == 0) {
        $c['text'] = (($config['pm_sound'] && $setting['sound_general']) ? '<audio autoplay><source src="' . $config['pm_sound'] . '" type="audio/mpeg"></audio>' : '') . '<span id="nzchatcontent' . $c['id'] . '">' . $c['text'] . '</span>';
    } elseif ($c['touid'] == $uid) {
        $c['text'] = (($config['pm_sound'] && $setting['sound_private']) ? '<audio autoplay><source src="' . $config['pm_sound'] . '" type="audio/mpeg"></audio>' : '') . '<span id="nzchatcontent' . $c['id'] . '">' . $c['text'] . '</span>';
    } elseif ($c['uid'] == $uid) {
        $c['text'] = '<span id="nzchatcontent' . $c['id'] . '">' . $c['text'] . '</span>';
    }
    $body[$c['id']] .= $msg_func->chat_row($c['id'], $c['text'], $c['uid'], $c['name'], $c['time'], $c['icon'], $is_mod);
    if ($c['ip'] == 'clear') {
        break;
    }
}
session_start();
if (TIMESTAMP - $_SESSION['th_chat_online'] > 15 || $_POST['list'] != $_SESSION['th_chat_list']) {
    $_SESSION['th_chat_online'] = TIMESTAMP;
    $_SESSION['th_chat_list'] = ($_POST['list'] ? 1 : 0);
    include 'online.php';
}
$chat_unread = DB::fetch_first("SELECT count(*) as count FROM " . DB::table('newz_data') . " WHERE `touid`='$uid' AND `unread`='1'");
$chat_unread = $chat_unread['count'];
echo json_encode(array('chat_row' => $body, 'chat_online' => $body_online, 'chat_online_total' => $oltotal, 'chat_unread' => $chat_unread));
