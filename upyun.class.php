<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
include_once 'function_upyun.php';
class plugin_upyun {
	function global_header() {
		global $_G;
		//防盗链 token 写入用户网站的一级域名
		$cookie_domain = substr($_SERVER['SERVER_NAME'], strpos($_SERVER['SERVER_NAME'], '.'));
		setcookie('_upt', upyun_gen_sign(), $_SERVER['REQUEST_TIME'] + 180, '/', $cookie_domain);
	}
}
