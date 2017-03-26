<?php
/**
 * 用户组的模块权限模型
 * @author liujd@wondershare.cn
 */
class  Model_GroupModule extends ZendX_Model_Base {
	protected $_schema = 'oss';
	protected $_table = 'SYSTEM_GROUP_MODULE';
	protected $pk = 'id';
	
	function getModuleIdByGroupId($group_id) {
		$rows = $this->get_db()->fetchAll("SELECT `module_id` FROM `{$this->_table}` WHERE `group_id`='{$group_id}'");
		$return = array();
		foreach($rows as $row)
		{
			$return[] = $row['module_id'];
		}
		
		return $return;
	}
	
	
	/**
	 * 根据用户组ID获取可见的module，按照sort排序。登录时用。
	 * 
	 * @param string $group_ids
	 */
	function getModulesByGroupIds($group_ids) {
		if(is_array($group_ids))
		{
			$group_ids = implode(',', $group_ids);
		}
		
		if(!$group_ids)
		{
			return array();
		}
		
		$module_table = 'SYSTEM_MODULE';
		$sql = "SELECT DISTINCT st.`id`, st.`name`,st.`default_controller`,st.`label`
				FROM `{$this->_table}` t INNER JOIN `{$module_table}` st  ON  t.`module_id`=st.`id`
				WHERE t.`group_id` IN ({$group_ids}) ORDER BY st.`sort` ASC";
		$rows = $this->get_db()->fetchAll($sql);
		return $rows;
	}
	
	/**
	 * 删除对应关系
	 * 
	 * @param string $where
	 */
	function delete($where)
	{
		return $this->get_db()->delete($this->_table, $where);
	}
	
	function saveGroupModule($group_id, $module_id) {
		if(empty($group_id) || empty($module_id) || !is_array($module_id)) return false;
		$sql = "DELETE FROM {$this->_table} WHERE group_id = '{$group_id}'";
		$this->get_db()->exec($sql);
		$tmp_ary = array();
		foreach($module_id as $mid) {
			$tmp_ary[] = "('{$group_id}','{$mid}')";
		}
		$insert_str = implode(',',$tmp_ary);
		$sql = "INSERT INTO {$this->_table}(group_id,module_id) VALUES {$insert_str}";
		return $this->get_db()->exec($sql);
	}
}