<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

include DISCUZ_ROOT . 'source/discuz_version.php';

function upyun_install($plugin_id, $version, $files) {
	$result = upyun_move_file($plugin_id, $version, $files);
	if(!$result) {
		upyun_move_file($plugin_id, $version, $files, false);
		return false;
	}
	return true;
}

function upyun_uninstall($plugin_id, $version, $files) {
	return upyun_move_file($plugin_id, $version, $files, false);
}

function upyun_get_discuz_version() {
	switch(DISCUZ_VERSION) {
		case 'X3':
			$version = 'discuz_3_0';
			break;
		case 'X3.1':
			$version = 'discuz_3_1';
			break;
		case 'X3.2':
			$version = 'discuz_3_2';
			break;
		default:
			$version = false;
	}
	return $version;
}

function upyun_move_file($plugin_id,  $version, $files, $is_install = true) {
	$install_dir = DISCUZ_ROOT . "source/plugin/$plugin_id/$version/" . ($is_install ? 'install' : 'uninstall');

	$changed = 0;
	foreach($files as $file_path) {
		$result = copy($install_dir . '/' . basename($file_path), $file_path);
		$changed += $result;
	}
	if($changed == count($files)) {
		return true;
	} else {
		return false;
	}
}

function upyun_file_check($files) {
	$msg = array();
	if(! is_array($files)) {
		return false;
	}
	foreach($files as $file_path) {
		if(! fopen($file_path, 'wb')) {
			$msg[] = $file_path . ' is not writable; please exec: ' . $file_path;
		}
	}
	if(!empty($msg)) {
		return implode("\n", $msg);
	}
	return true;
}

function upyun_attachment_download($attach, $module) {
	global $_G;
	$upyun_config = $_G['cache']['plugin']['upyun'];
	$url = rtrim($upyun_config['url'], '/') . "/$module/";
	if($attach['remote'] && !$_G['setting']['ftp']['hideurl']){
		if(strtolower(CHARSET) == 'gbk') {
			$attach['filename'] = urlencode(iconv('GBK', 'UTF-8', $attach['filename']));
		} elseif (strtolower(CHARSET) == 'big5'){
			$attach['filename'] = urlencode(iconv('BIG5', 'UTF-8', $attach['filename']));
		} else {
			$attach['filename'] = urlencode($attach['filename']);
		}
		$path = $module ? "/$module/{$attach['attachment']}" : $attach['attachment'];
		$sign = upyun_gen_sign($path);
		dheader('location:' . $url . $attach['attachment'] . "?_upd={$attach['filename']}" . ($sign ? '&_upt=' . $sign : ''));
	}
}

function upyun_gen_sign($path = '/') {
	global $_G;
	$upyun_config = $_G['cache']['plugin']['upyun'];

	if($upyun_config['token'] && $upyun_config['token_timeout']){
		$etime = time() + $upyun_config['token_timeout'];
		$sign = substr(md5($upyun_config['token'].'&'.$etime.'&'.$path), 12,8).$etime;
	} else {
		$sign = '';
	}
	return $sign;
}