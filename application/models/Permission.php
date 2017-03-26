<?php
/** 权限模型
 * @author 
 * liujd@wondershare.cn
 */
class Model_Permission extends ZendX_Model_Base {
	protected $_schema = 'oss';
	protected $_table = 'SYSTEM_PERMISSION';
	protected $pk = 'id';
	
	function getList($where = '1')
	{
		$sql = "SELECT `id`, `name`, `i18n_name`, `parent_id`, `module_id`, `type`, `sort`, `url`, `ext_menu_id`
		FROM `{$this->_table}`
		WHERE {$where}
		ORDER BY `sort`";
        // print_r($sql);exit(',ln='.__line__);
		return $this->get_db()->fetchAll($sql);
	}
	
	function save($data)
	{
		$menu_id = (int) $data['menu_id'];
		$data = $this->data($data);
		unset($data['menu_id']);
		
		if($menu_id)
		{
			$this->get_db()->update($this->table, $data, "`menu_id`={$menu_id}");
		} else {
			$this->get_db()->insert($this->table, $data);
			$menu_id = $this->get_db()->lastInsertId();
		}
		
		return $menu_id;
	}
	
	/**
	 * 根据模块ID获取所有菜单
	 * @param integer $module_id
	 */
	function getMenuByModuleId($module_id)
	{
		$sql = "SELECT `menu_id` AS `id`, `menu_id`, `name`, `i18n_name`,`type`,`parent_id` AS `parentId`, `url`, 'closed' AS 'status', `ext_menu_id`
		FROM `{$this->table}`
		WHERE `site_id`='{$module_id}' AND {$this->able_condition}
		ORDER BY `sort`";
		return $this->get_db()->fetchAll($sql);
	}
	
}