<?php

/**
 * 权限控制
 * 
 * 1.开发者不受权限控制
 * 2.下列权限只给管理员组
 * 		a) 系统管理
 * 		b) 用户管理
 */
class Common_Auth {
	/**
	 * 权限用户组验证
	 * 管理员不需要验证权限
	 * 
	 * @param string	$visit_url  请求的uri
	*/
	static function auth($visit_url) {
		$session = Zend_Registry::get('user_session');
		$user_info = $session->user_info;
		if(self::isAdmin($user_info['group_ids'])) {
			return true;
		}
		
		$allow_url = $session->perm_action;
        if(is_array($allow_url)){
            foreach($allow_url as $url) {
                if(strpos($url, $visit_url) !== false) return true;
            }
        }
		return false;
	}
	
	/**
	 * 是否是管理员权限
	 * 		group_id:0	超级用户，相当于Linux系统的root用户，本系统中时admin
	 * 		group_id:1	开发组，除了删除开发组用户和更改开发组用户的用户组，没有其它的权限限制
	 * @param array $group_ids
	 */
	static function isAdmin(array $group_ids)
	{
		return (bool)array_intersect(array(0, 1), $group_ids);
	}
	
	/**
	 * 是否超级管理员
	 * 
	 * @param array $group_ids
	 */
	static function isSuper(array $group_ids)
	{
		return in_array(0, $group_ids);
	}
	
	/**
	 * 获取用户信息
	 */
	static function getUserInfo() {
		$session = Zend_Registry::get('user_session');
		return $session->user_info;
	}
	
	/**
	 * 获取用户权限
	 */
	static function getUserPermission() {
		$permissions = array();
		$session = Zend_Registry::get('user_session');
		$permissions['modules'] = $session->modules;
		$permissions['perm_menu'] = $session->perm_menu;
		$permissions['perm_action'] = $session->perm_action;
		return $permissions;
	}
	
	/**
	 * 设置用户session
	 */
	static function setUserInfo($key, $value) {
		$session = new Zend_Session_Namespace('UserInfo');
		$session->user_info[$key] = $value;
		return true;
	}
	
	/* 调用公司登录接口验证用户状态 */
	static function authFromWondershare($data) {
		$userName = 'dm'; 
		$passWord = 'Dm2014';
		$api_url = "http://webservice.wondershare.cn/services/wpsUserService/checkLogin?userName={$data['user_name']}&password={$data['password']}&response=application/json";
		$basicSets = array();
		$basicSets['http'] = array('header' => 'Authorization: Basic ' . base64_encode("{$userName}:{$passWord}"));
		$context = stream_context_create($basicSets);
		$ret = file_get_contents($api_url,false, $context);
		return json_decode($ret, true);
	}
}