<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$plugin_id = 'upyun';
include DISCUZ_ROOT . "source/plugin/$plugin_id/function_upyun.php";
$version = upyun_get_discuz_version();
$files = upyun_get_install_files();
$finish = upyun_install($plugin_id, $version, $files);
