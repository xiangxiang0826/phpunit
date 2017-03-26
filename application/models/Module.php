<?php

/**
 * 模块模型
 * 
 * @author liujd@wondershare.cn
 */
class Model_Module extends ZendX_Model_Base {
	protected $_schema = 'oss';
	protected $_table = 'SYSTEM_MODULE';
	protected $pk = 'id';
	const STATUS_ENABLE = 'enable';
	const STATUS_DISABLE = 'disable';
	const STATUS_DELETED = 'deleted';
	/**
	 * 获取所有站点
	 */
	function GetALlModules() {
		$sql = "SELECT * FROM `{$this->_table}` WHERE `status` = '".self::STATUS_ENABLE ."' ORDER BY `sort` ASC";
		return $this->get_db()->fetchAll($sql);
	}
	
	/**
	 * 超级管理员和管理员的数据时实时提取的，其它用户组是登陆时写入session中的，下同
	 */
	function GetUserModules() {
		$user_info = Common_Auth::getUserInfo();
		$modules = array();
		// 是管理员
		if(Common_Auth::isAdmin($user_info['group_ids'])) {
			$module_list =  $this->GetALlModules();
			foreach($module_list as $module) {
				$modules[$module['id']] = $module;
			}
			return $modules;
		} 
		$session = Zend_Registry::get('user_session');
		return $session->modules;
	}
	
	/* 根据模块label找模块信息*/
	public function FindByLabel($label) {
		return $this->get_db()->fetchRow("SELECT * FROM {$this->_table} WHERE label = ?",array($label));
	}
	
	/* 根据Url获取所属的模块信息 */
	public function FindByUrl($url) {
		return $this->get_db()->fetchRow("SELECT a.* FROM {$this->_table} a INNER JOIN SYSTEM_PERMISSION b ON a.id = b.module_id  WHERE b.url = ?",array($url));
	}
	
}