<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
include DISCUZ_ROOT . 'source/discuz_version.php';
$finish = false;
$plugin_id =  'upyun';
switch(DISCUZ_VERSION) {
	case 'X3':
		$dir = 'discuz_3_0';
		$finish = install_x3($plugin_id);
		break;
	case 'X3.1':
		$dir = 'discuz_3_1';
		$finish = install_x3_1();
		break;
	case 'X3.2':
		$dir = 'discuz_3_2';
		$finish = install_x3_2();
		break;
	default:
		echo 'unsupport version';
}

function install_x3($plugin_id) {
	$result = move_file($plugin_id);
	if(!$result) {
		move_file($plugin_id, false);
		return false;
	}
	return true;
}

function move_file($plugin_id,  $is_install = true) {
	$install_dir = DISCUZ_ROOT . "source/plugin/$plugin_id/discuz_3_0/" . ($is_install ? 'install' : 'uninstall');
	$changed = 0;
	$changed += copy($install_dir . "/forum_attachment.php", DISCUZ_ROOT . "/source/module/forum/forum_attachment.php");
	$changed += copy($install_dir . "/portal_attachment.php", DISCUZ_ROOT . "/source/module/portal/portal_attachment.php");
	$changed += copy($install_dir . "/function_core.php", DISCUZ_ROOT . "/source/function/function_core.php");
	$changed += copy($install_dir . "/discuz_ftp.php", DISCUZ_ROOT . "/source/class/discuz/discuz_ftp.php");
	$changed += copy($install_dir . "/function_post.php", DISCUZ_ROOT . "/source/function/function_post.php");
	if($changed == 5) {
		return true;
	} else {
		return false;
	}
}