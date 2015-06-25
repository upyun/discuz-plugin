<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
$plugin_id = 'upyun';
include DISCUZ_ROOT . "source/plugin/$plugin_id/function_upyun.php";
$files = upyun_get_install_files();
$result = upyun_file_check($files);
if($result !== true) {
	echo nl2br( $result );
	die;
}
