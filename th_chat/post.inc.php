<?php
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
loadcache('plugin');
$config = $_G['cache']['plugin']['th_chat'];
$uid = $_G['uid'];
$gid = $_G['groupid'];
$is_mod = in_array($_G['adminid'], array(1, 2, 3));
require_once libfile('class/THChatMessage', 'plugin/th_chat');
$msg_func = new THChatMessage();
function error_response($error, $script = '')
{
    $response =
        array(
            'type' => 1,
            'error' => $error,
        );
    if ($script) {
        $response['script'] = $script;
    }
    die(json_encode($response));
}
if ($uid < 1) {
    error_response('ขออภัย กรุณาเข้าสู่ระบบ');
}
$newz_nick = DB::fetch_first("SELECT * FROM " . DB::table('newz_nick') . " WHERE uid='{$uid}'");
if ($newz_nick['ban']) {
    error_response('ขออภัย คุณถูกแบนอยู่');
}
if (!$is_mod && $config['chat_delay']) {
    $last_msg = DB::fetch_first("SELECT `time` FROM " . DB::table('newz_data') . " WHERE `uid`='{$uid}' ORDER BY `time` DESC LIMIT 1");
    if ($last_msg['time'] > (TIMESTAMP - ceil($config['chat_delay'] / 1000))) {
        error_response('ขออภัย คุณส่งข้อความบ่อยไป');
    }
}
$text = $_POST['text'];
$id = intval($_POST['lastid']);
$touid = intval($_POST['touid']);
$quota = intval($_POST['quota']);
$command = $_POST['command'];
$ip = $_G['clientip'];
if (substr($text, 0, 4) == "!del" && $is_mod) {
    $id = intval(substr($text, 4));
    DB::delete('newz_data', DB::field('id', $id));
    DB::insert('newz_data', array(
        'uid' => $uid,
        'touid' => 0,
        'text' => 'ลบข้อความ',
        'time' => TIMESTAMP,
        'ip' => 'delete',
    ));
    error_response('ลบสำเร็จแล้ว!', 'nzchatobj("#nzrows_' . $id . '").fadeOut(200);');
} elseif (substr($text, 0, 4) == "!ban" && $is_mod) {
    $ban = explode(" ", $text);
    $uid_ban = intval($ban[1]);
    $time = intval($ban[2]);
    if (!$time) {
        $time = 4294967295;
    } else {
        $time = TIMESTAMP + $time;
    }
    if (DB::fetch_first("SELECT uid FROM " . DB::table('newz_nick') . " WHERE `uid`='{$uid_ban}'")) {
        DB::update('newz_nick', array(
            'ban' => $time,
        ), DB::field('uid', $uid_ban));
    } else {
        DB::insert('newz_nick', array(
            'uid' => $uid_ban,
            'ban' => $time,
        ));
    }
    $username_ban = DB::fetch_first("SELECT username FROM " . DB::table('common_member') . " WHERE uid='{$uid_ban}'");
    $username_ban = '@' . $username_ban['username'];
    $icon = 'alert';
    $touid = 0;
    $text = $username_ban . ' [color=red]ถูกแบน' . ($time == 4294967295 ? 'ถาวร' : 'ถึง ' . date("d/m/Y H:i:s", $time)) . '[/color]';
} elseif (substr($text, 0, 6) == "!unban" && $is_mod) {
    $ban = explode(" ", $text);
    $uid_ban = intval($ban[1]);
    DB::update('newz_nick', array(
        'ban' => 0,
    ), DB::field('uid', $uid_ban));
    $username_ban = DB::fetch_first("SELECT username FROM " . DB::table('common_member') . " WHERE uid='{$uid_ban}'");
    $username_ban = '@' . $username_ban['username'];
    $icon = 'alert';
    $touid = 0;
    $text = '[color=red]ปลดแบน[/color] ' . $username_ban;
}
if ($command == "notice" && $is_mod) {
    $icon = 'alert';
    $touid = 0;
    $ip = 'notice';
} elseif (substr($command, 0, 4) == "edit" && ($config['editmsg'] != 0)) {
    $editid = intval(substr($command, 5));
    if ($config['editmsg'] == 1 && !$is_mod) {
        error_response('ขออภัย คุณไม่มีสิทธิ์แก้ไขข้อความนี้');
    }
    $editmsg = DB::fetch_first("SELECT * FROM " . DB::table('newz_data') . " WHERE id='{$editid}'");
    if ($config['editmsg'] == 2 && (!$is_mod || $editmsg['uid'] != $uid)) {
        error_response('ขออภัย คุณไม่มีสิทธิ์แก้ไขข้อความนี้');
    } else if ($config['editmsg'] == 3 && ($editmsg['uid'] != $uid)) {
        error_response('ขออภัย คุณไม่มีสิทธิ์แก้ไขข้อความนี้');
    }
    $edittext = 'ถูกแก้ไข';
    if ($editmsg['uid'] != $uid) {
        $edittext .= 'โดย ' . $_G['username'];
    }
    $ip = 'edit';
    $icon = $editid;
}
$txtlen = mb_strlen($text);
if ($txtlen > $config['chat_strlen']) {
    error_response('ขออภัย ข้อความยาวเกินไป');
}
if ($uid == $touid) {
    error_response('ขออภัย ไม่สามารถส่งข้อความหาตัวเองได้');
}
include DISCUZ_ROOT . '/source/function/function_discuzcode.php';
$config['useemo'] = $config['useemo'] ? 0 : 1;
$config['usedzc'] = $config['usedzc'] ? 0 : 1;
$config['useunshowdzc'] = $config['useunshowdzc'] ? 0 : 1;
if ($config['autourl']) {
    $text = preg_replace('/(?<=^|\s)((http|https):\/\/)([a-z0-9-]+\.)?[a-z0-9-]+(\.[a-z]{2,6}){1,3}(\/[a-z0-9.,_\/~#&=;%+?-]*)?/is', '[url]$0[/url]', $text);
}
preg_match_all('/\[url\]' . str_replace('/', '\/', preg_quote($_G['siteurl'])) . '(.*?)\[\/url\]/s', $text, $urls, PREG_SET_ORDER);
foreach ($urls as $url) {
    if (preg_match('/tid=(\d+)/i', $url[0], $matches)) {
        $tid = $matches[1];
    } elseif (in_array('forum_viewthread', $_G['setting']['rewritestatus']) && $_G['setting']['rewriterule']['forum_viewthread']) {
        preg_match_all('/(\{tid\})|(\{page\})|(\{prevpage\})/', $_G['setting']['rewriterule']['forum_viewthread'], $matches);
        $matches = $matches[0];
        $tidpos = array_search('{tid}', $matches);
        if ($tidpos !== false) {
            $tidpos = $tidpos + 1;
            $rewriterule = str_replace(
                array('\\', '(', ')', '[', ']', '.', '*', '?', '+'),
                array('\\\\', '\(', '\)', '\[', '\]', '\.', '\*', '\?', '\+'),
                $_G['setting']['rewriterule']['forum_viewthread']
            );
            $rewriterule = str_replace(array('{tid}', '{page}', '{prevpage}'), '(\d+?)', $rewriterule);
            $rewriterule = str_replace(array('{', '}'), array('\{', '\}'), $rewriterule);
            preg_match("/" . str_replace('/', '\/', $rewriterule) . "/i", $url[0], $match_result);
            $tid = $match_result[$tidpos];
        }
    }
    if ($tid) {
        $thread = C::t('forum_thread')->fetch($tid);
        if ($thread) {
            $text = str_replace($url[0], '[url=' . $url[1] . ']' . $thread['subject'] . '[/url]', $text);
        }
    }
}
$query_bw = DB::query("SELECT * FROM " . DB::table('common_word'));
while ($bw = DB::fetch($query_bw)) {
    $text = str_replace($bw['find'], $bw['replacement'], $text);
}
$text = preg_replace('/\[quota\](.*?)\[\/quota\]/s', '[quota]$1[[color=#fff][/color]/quota]', $text);
$text = str_replace("[media]", "[media=x,320,180]", $text);
if ($config['usemore']) {
    $usemore = -$_G['groupid'];
} else {
    $usemore = 1;
}
$text = discuzcode($text, $config['useemo'], $config['usedzc'], $config['usehtml'], 1, $usemore, $config['useimg'], 1, 0, $config['useunshowdzc'], 0, $config['mediacode']);
if ($ip == 'notice') {
    $plugin = C::t('common_plugin')->fetch_by_identifier('th_chat');
    C::t('common_pluginvar')->update_by_variable($plugin['pluginid'], 'welcometext', array('value' => $text));
    include_once libfile('function/cache');
    updatecache('plugin');
    DB::insert('newz_data', array(
        'uid' => $uid,
        'touid' => 0,
        'icon' => 'alert',
        'text' => $text,
        'time' => TIMESTAMP,
        'ip' => $_G['clientip'],
    ));
}
include_once libfile('class/EmojiPattern', 'plugin/th_chat');
$emoji_pattern = EmojiPattern::getEmojiPattern();
if ($txtlen == 1) {
    if (preg_match('/' . $emoji_pattern . '/u', $text)) {
        $text = '<span style="font-size:30px">' . $text . '</span>';
    }
} else {
    $text = preg_replace('/' . $emoji_pattern . '/u', '<span class="nzemojit">$0</span>', $text);
}
if (($is_mod > 0) && $text == '!clear') {
    $ip = 'clear';
    $icon = 'alert';
    $touid = 0;
    $text = 'ล้างข้อมูล';
    $needClear = 1;
}
$text = $msg_func->get_at(addcslashes($text, "'"));
if ($ip == 'edit') {
    preg_match('/\[quota\](.*?)\[\/quota\]/', $editmsg['text'], $editquota);
    if ($editquota[0]) {
        $text = addslashes($editquota[0]) . $text;
    }
    $text .= ' <span class="nztag3" title="' . $msg_func->get_date($time) . '">' . $edittext . '</span>';
    DB::update('newz_data', array(
        'text' => $text,
    ), DB::field('id', $icon));
}
if ($quota > 0 && $ip != 'clear') {
    $text = $msg_func->get_quota($quota) . $text;
}
$unread = 0;
if ($touid !== 0) {
    $unread = 1;
}
DB::insert('newz_data', array(
    'uid' => $uid,
    'touid' => $touid,
    'icon' => $icon,
    'text' => $text,
    'time' => TIMESTAMP,
    'ip' => $ip,
    'unread' => $unread,
));

// READ BACK

$last = DB::insert_id();
if ($needClear) {
    DB::delete('newz_data', DB::field('id', $last, '<'));
} else {
    if ($config['chat_log']) {
        DB::delete('newz_data', DB::field('id', ($last - $config['chat_log']), '<'));
    }
}
if ($_POST['touid']) {
    $room = intval($_POST['room']);
    $re = DB::query("SELECT n.*,m.username AS name,mt.username AS toname,g.color,gt.color AS tocolor
FROM " . DB::table('newz_data') . " n
LEFT JOIN " . DB::table('common_member') . " m ON n.uid=m.uid
LEFT JOIN " . DB::table('common_member') . " mt ON n.touid=mt.uid
LEFT JOIN " . DB::table('common_usergroup') . " g ON m.groupid=g.groupid
LEFT JOIN " . DB::table('common_usergroup') . " gt ON mt.groupid=gt.groupid
WHERE  id>{$id} AND ((n.uid='$room' AND n.touid='$uid') OR (n.uid='$uid' AND n.touid='$room'))
ORDER BY id DESC LIMIT {$config['chat_init']}");
} else {
    $re = DB::query("SELECT n.*,m.username AS name,mt.username AS toname,g.color,gt.color AS tocolor
FROM " . DB::table('newz_data') . " n
LEFT JOIN " . DB::table('common_member') . " m ON n.uid=m.uid
LEFT JOIN " . DB::table('common_member') . " mt ON n.touid=mt.uid
LEFT JOIN " . DB::table('common_usergroup') . " g ON m.groupid=g.groupid
LEFT JOIN " . DB::table('common_usergroup') . " gt ON mt.groupid=gt.groupid
WHERE  id>{$id} AND (n.touid='0' OR n.touid='{$uid}' OR n.uid='{$uid}')
ORDER BY id DESC LIMIT {$config['chat_init']}");
}

$body = array();
while ($c = DB::fetch($re)) {
    $c['text'] = preg_replace('/\[quota\](.*?)\[\/quota\]/s', '$1', $c['text']);
    if ($c['ip'] == 'delete') {
        $body[$c['id']] .= '<script>nzchatobj("#nzrows_' . $c['text'] . '").fadeOut(200);</script>';
        continue;
    } elseif ($c['ip'] == 'notice') {
        $body[$c['id']] .= '<script>nzchatobj("#nzchatnotice").html("' . addslashes($c['text']) . '");</script>';
        continue;
    } elseif ($c['ip'] == 'edit') {
        $body[$c['id']] .= '<script>nzchatobj("#nzchatcontent' . $c['icon'] . '").html("' . addslashes($c['text']) . '");</script>';
        continue;
    } elseif ($room != $c['uid'] && $c['touid'] == $uid) {
        if ($config['pm_sound'] && $sounddata['sound_2']) {
            $body[$c['id']] .= '<audio autoplay><source src="' . $config['pm_sound'] . '" type="audio/mpeg"></audio>';
        }
        continue;
    }
    if ($c['ip'] == 'clear') {
        $seedd = $time . '_' . $uid . '_' . rand(1, 999);
        $c['text'] = '<span style="color:red" id="del_' . $seedd . '"></span><span id="nzchatcontent' . $c['id'] . '">ล้างข้อมูล</span><script type="text/javascript">nzchatobj("#del_' . $seedd . '").parent().parent().parent().' . ($config['chat_type'] == 1 ? 'next' : 'prev') . 'Until().remove();</script>';
    } elseif ($c['icon'] == 'alert') {
        $c['text'] = '<span id="nzchatcontent' . $c['id'] . '">' . $c['text'] . '</span>';
    } elseif ($c['touid'] == 0) {
        $c['text'] = '<span id="nzchatcontent' . $c['id'] . '">' . $c['text'] . '</span>';
    } elseif ($c['touid'] == $uid) {
        $c['text'] = ($config['pm_sound'] ? '<audio autoplay><source src="' . $config['pm_sound'] . '" type="audio/mpeg"></audio>' : '') . '<span id="nzchatcontent' . $c['id'] . '">' . $c['text'] . '</span>';
    } elseif ($c['uid'] == $uid) {
        $c['text'] = '<span id="nzchatcontent' . $c['id'] . '">' . $c['text'] . '</span>';
    }
    $body[$c['id']] .= $msg_func->chat_row($c['id'], $c['text'], $c['uid'], $c['name'], $c['time'], $c['icon'], $is_mod);
    if ($c['ip'] == 'clear') {
        break;
    }
}
echo json_encode($body);
