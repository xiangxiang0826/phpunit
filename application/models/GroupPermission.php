<?php

/**
 * system_group_menus
 * 
 * @author 
 * liujd@wondershare.cn
 * 
 */
class Model_GroupPermission extends ZendX_Model_Base
{
	protected $_schema = 'oss';
	protected $_table = 'SYSTEM_GROUP_PERMISSION';
	protected $pk = 'id';
	const PERM_TYPE_MENU = 'menu';
	const PERM_TYPE_ACTION = 'action';
	const STATUS_ENABLE = 'enable';
	const STATUS_DISABLE = 'disable';
	const STATUS_DELETED = 'delete';
	
	/**
	 * 根据一组用户组ID获取对应的权限信息，登录时用
	 * 
	 * @param string $group_ids
	 */
	function getPermsByGroupIds($group_ids) {
		if(is_array($group_ids)) {
			$group_ids = implode(',', $group_ids);
		}
		
		if(!$group_ids) {
			return array();
		}
		
		$menu_table = 'SYSTEM_PERMISSION';
		$sql = "SELECT DISTINCT st.`id` AS `id`, st.`name`, st.`i18n_name`, st.`parent_id`, st.`url`, 
		st.`ext_menu_id`,st.`module_id` ,st.`sort`,`type`
				FROM `{$this->_table}` t INNER JOIN `{$menu_table}` st 
				ON t.`perm_id` = st.`id` 
			    WHERE  t.`group_id` IN ({$group_ids})  AND st.status = '". self::STATUS_ENABLE. "'
				ORDER BY st.`sort`, st.`id`";
		$rows = $this->get_db()->fetchAll($sql);
		return $rows;
	}
    
	/**
	 * 根据一组用户组ID获取对应的权限信息，登录时用
	 * 
	 * @param string $group_ids
	 */
	function getInfoByGroupIds($group_ids, $fields = '*') {
		if(is_array($group_ids)) {
			$group_ids = implode(',', $group_ids);
		}
		if(!$group_ids) {
			return array();
		}
		$sql = 'SELECT '.$fields.' FROM '.$this->_table.' WHERE `group_id` IN ('.$group_ids.')';
		$rows = $this->get_db()->fetchAll($sql);
		return $rows;
	}
	
	/* 根据module_id获取用户可以显示的菜单
	 *  */
	public function GetMenuByModuleId($module_id) {
		$user_info = Common_Auth::getUserInfo(); // 获取用户登录态
		if(Common_Auth::isAdmin($user_info['group_ids'])) { // 是管理员
			$menu_table = 'SYSTEM_PERMISSION';
			$sql = "SELECT st.`id` AS `id`, st.`name`, st.`i18n_name`, st.`parent_id`, st.`url`,
			st.`ext_menu_id`,st.`module_id` ,st.`sort`,st.`type`
			FROM  `{$menu_table}` st
			WHERE  st.`module_id`  = '{$module_id}' AND st.type = '". self::PERM_TYPE_MENU ."' AND st.status = '". self::STATUS_ENABLE. "'
			ORDER BY st.`sort` ASC";
			$rows = $this->get_db()->fetchAll($sql);
			$perm_menu = array();
			foreach($rows as $perm) {
				if($perm['type'] == self::PERM_TYPE_MENU) { // 组合可见的菜单
					$perm_menu[$perm['module_id']][$perm['parent_id']][] = $perm;
				}
			}
			return isset($perm_menu[$module_id]) ? $perm_menu[$module_id] : array();
		}
		
		$session = Zend_Registry::get('user_session');
		return isset($session->perm_menu[$module_id]) ?  $session->perm_menu[$module_id] : array();
	}
	
	/**
	 * 删除菜单对应信息
	 */
	function delete($where)
	{
		return $this->get_db()->delete($this->_table, $where);
	}
}