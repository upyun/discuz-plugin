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
	global $operation;
	$msg = array();
	if(! is_array($files)) {
		return false;
	}
	$md5_check_files = upyun_get_file_md5();
	foreach($files as $file_path) {
		$handle = fopen($file_path, 'ab');
		if(! $handle) {
			$msg[] = $file_path . ' 不能写入; 请执行命令修改: chmod 666 ' . $file_path;
		}
		fclose($handle);
		$filename = basename($file_path);
		//仅在安装时校验文件
		if($operation == 'import' &&
		   md5_file($file_path) !== $md5_check_files[$filename]) {
			$msg[] = $file_path . ' 已经被修改，请手动安装。';
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

function upyun_get_install_files() {
	$files = array(
		DISCUZ_ROOT . "source/module/forum/forum_attachment.php",
		DISCUZ_ROOT . "source/module/portal/portal_attachment.php",
		DISCUZ_ROOT . "source/class/discuz/discuz_ftp.php",
		DISCUZ_ROOT . "source/function/function_attachment.php",
		DISCUZ_ROOT . "source/function/function_home.php",
	);
	return $files;
}

function upyun_get_file_md5() {
	switch(DISCUZ_VERSION) {
		case 'X3':
			return array(
				'discuz_ftp.php' => 'd2343fb3bea0e16b574a1ea601a9f871',
				'forum_attachment.php' => 'df9a7925d66ed5aa69e87a713d9aed9e',
				'function_attachment.php' => '7fd243cd20ec44c2033401535828c6c4',
				'function_home.php' => 'd3b81c420b7e98158fa2a818399969b1',
				'portal_attachment.php' => 'e5fc1bbd71d087e81243f45e61219d50',
			);
			break;
		case 'X3.1':
			return array(
				'discuz_ftp.php' => 'd2343fb3bea0e16b574a1ea601a9f871',
				'forum_attachment.php' => '207db1330d130f4425ad0bfa5b5064d5',
				'function_attachment.php' => '7fd243cd20ec44c2033401535828c6c4',
				'function_home.php' => 'd3b81c420b7e98158fa2a818399969b1',
				'portal_attachment.php' => 'e5fc1bbd71d087e81243f45e61219d50',
			);
			break;
		case 'X3.2':
			return array(
				'discuz_ftp.php' => 'd2343fb3bea0e16b574a1ea601a9f871',
				'forum_attachment.php' => '015002fd98d4ef2d509142d5ac97b256',
				'function_attachment.php' => '7fd243cd20ec44c2033401535828c6c4',
				'function_home.php' => 'd3b81c420b7e98158fa2a818399969b1',
				'portal_attachment.php' => 'e5fc1bbd71d087e81243f45e61219d50',
			);
			break;
		default:
			return array();
	}
}
