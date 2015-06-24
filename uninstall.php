<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
$plugin_id = 'upyun';
include DISCUZ_ROOT . "source/plugin/$plugin_id/function_upyun.php";
$files = array(
    DISCUZ_ROOT . "/source/module/forum/forum_attachment.php",
    DISCUZ_ROOT . "/source/module/portal/portal_attachment.php",
    DISCUZ_ROOT . "/source/class/discuz/discuz_ftp.php"
);
$version = upyun_get_discuz_version();
$finish = upyun_uninstall($plugin_id, $version, $files);


