<?php

/**
 * 系统的日志记录类
 * 
 * @author 刘通
 */
class Cms_Log
{
	/**
	 * 记录动作日志
	 * 
	 * @param string	$table
	 * @param integer	$entitiy_id
	 * @param string	$desc
	 */
	static function log() {
		static $action_model = null;
		if(null === $action_model)
		{
			$action_model = new Log_Models_Action();
		}
		
		$user_info = Common_Auth::getUserInfo();
		if(empty($user_info)) return false;
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$ip = Cms_Func::getIp();
		$set = array(
			'user_id'	=> $user_info['id'],
			'user_name'	=> $user_info['user_name'],
			'module'	=> $request->getModuleName(),
			'controller'	=> $request->getControllerName(),
			'action'	=> $request->getActionName(),
			'post_data'	=> $_POST ? json_encode($_POST) : '',
			'ip'		=> $ip,
		);
		return $action_model->insert($set);
	}
}