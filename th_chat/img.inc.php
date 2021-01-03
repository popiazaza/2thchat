<?php
if (!defined('IN_DISCUZ')) {exit('Access Denied');}
loadcache('plugin');
$config = $_G['cache']['plugin']['th_chat'];
if ($_G['uid'] < 1) {
    die('Login');
}

$fileSystemIterator = new FilesystemIterator(__DIR__ . '/img_up');
foreach ($fileSystemIterator as $file) {
    if (TIMESTAMP - $file->getCTime() >= 1209600) // 14 days
    {
        unlink(__DIR__ . '/img_up/' . $file->getFilename());
    }
}

$files = glob(__DIR__ . '/img_up/' . $_G['uid'] . '_*');
if ($files !== false) {
    $filecount = count($files);
    if ($filecount > 99) {
        echo json_encode(array('error' => 'ขออภัย คุณอัปโหลดภาพได้สูงสุด 100 ภาพต่อ 2 สัปดาห์เท่านั้น'));
        exit();
    }
}

require_once "bulletproof.php";

$image = new Bulletproof\Image($_FILES);
$image->setSize(1, 1048576);
$image->setLocation(__DIR__ . "/img_up");
$image->setName($_G['uid'] . '_' . TIMESTAMP);
if ($image["pictures"]) {
    $upload = $image->upload();
    header('Content-Type: application/json');
    if ($upload) {
        echo json_encode(array('url' => $_G['siteurl'] . 'source/plugin/th_chat/img_up/' . $image->getName() . '.' . $image->getMime()));
    } else {
        echo json_encode(array('error' => $image->getError()));
    }
}
