<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
include_once 'function_upyun.php';
class plugin_upyun {
	function global_header() {
		setcookie('_upt', upyun_gen_sign());
	}
}
