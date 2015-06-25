<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
include_once 'function_upyun.php';
class plugin_upyun {
	function global_header() {
		global $_G;
		setcookie('_upt', upyun_gen_sign());
	}
}
