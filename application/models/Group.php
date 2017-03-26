<?php

/**
 * User Group
 * 
 * @author 
 * liujd@wondershare.cn
 */
class Model_Group extends ZendX_Model_Base {
	protected $_schema = 'oss';
	protected $_table = 'SYSTEM_USER_GROUP';
    protected $_map_table = 'SYSTEM_USER_GROUP_MAP';
	protected $pk = 'id';
	
	function GetGroupByUid($user_id) {
		return $this->get_db()->fetchALl("SELECT a.* FROM `{$this->_table}`  a INNER JOIN
		SYSTEM_USER_GROUP_MAP b ON a.id = b.user_group_id  WHERE b.`user_id` = ?", array($user_id));
	}
	
	function getList($start, $count, $search=array())
	{
        $where = '1';
        $search[] = 'status != "deleted"';
        $where = implode(' AND ', $search);
		$total = $this->get_db()->fetchOne("SELECT count(*) FROM `{$this->_table}` WHERE {$where}");
		$sql = "SELECT *
				FROM `{$this->_table}`
                WHERE {$where}
				ORDER BY `id` ASC  LIMIT {$start}, {$count}";
		$rows = $this->get_db()->fetchAll($sql);
		return compact('total', 'rows');
	}
    
	function getMapList($start, $count, $search=array())
	{
        $where = '1';
        if($search && !empty($search)){
            $where = implode(' AND ', $search);
        }
		$total = $this->get_db()->fetchOne("SELECT count(*) FROM `{$this->_map_table}` WHERE {$where}");
		$sql = "SELECT *
				FROM `{$this->_map_table}`
                WHERE {$where}
				ORDER BY `id` ASC
				LIMIT {$start}, {$count}";     
		$rows = $this->get_db()->fetchAll($sql);
		return compact('total', 'rows');
	}
	
	function getIdByName($name)
	{
		return $this->get_db()->fetchOne("SELECT `id` FROM `{$this->_table}` WHERE `name`='{$name}'");
	}

	//根据uid,批量获取用户组权限
	function GetGroupByUids($uids) {
		if(!$uids || !is_array($uids)) return false;
		$uids = implode(',',$uids);
		return $this->get_db()->fetchALl("SELECT a.*,b.user_id FROM `{$this->_table}`  a INNER JOIN
		SYSTEM_USER_GROUP_MAP b ON a.id = b.user_group_id  WHERE b.`user_id` IN ({$uids})");
	}

    
	/**
	 * 获得总记录数
	 */
	public function getTotal($query = "")
	{
		if(empty($query)){
			$sql = "SELECT count(*) AS total FROM `{$this->_table}` AS t";
		}else{
            $sql = "SELECT count(*) AS total FROM `{$this->_table}` AS t WHERE ".$query;
		}
		return $this->get_db()->fetchAll($sql);
	}
    
	/**
	 * 获得总记录数
	 */
	public function getTotalByGroup($query = "")
	{
		if(empty($query)){
			$sql = 'SELECT user_group_id AS gid, COUNT(*) AS total FROM '.$this->_map_table.' as t WHERE 1 GROUP BY user_group_id';
		}else{
            $sql = 'SELECT user_group_id AS gid, COUNT(*) AS total FROM '.$this->_map_table.' as t WHERE '.$query.' GROUP BY user_group_id';
		}
		return $this->get_db()->fetchAll($sql);
	}
    
   
	/* 根据key=>value找记录 */
	public function getCategoryRowByField($fields, $params = array(), $paramsWithout = array()) {
		$select = $this->select();
		$select->from($this->_table, $fields);
		foreach($params as $field => $value) {
			$select->where("`{$field}` = ?", $value);
		}
		foreach($paramsWithout as $field => $value) {
			$select->where("`{$field}` != ?", $value);
		}
		$result = $this->get_db()->fetchRow($select);
		return $result;
	}
    
	/**
	 * 插入记录
	 * @param array $data
	 * @return bool
	 */
	public function insertCategory($data)
	{
		$ret = $this->get_db()->insert($this->_table, $data);
		return $ret ? $this->get_db()->lastInsertId($this->_table) : false;
	}
    
	/**
	 * 插入记录
	 * @param array $data
	 * @return bool
	 */
	public function updateCategory($data, $where)
	{
		$ret = $this->get_db()->update($this->_table, $data, $where);
		return $ret ? TRUE : false;
	}
    
	/**
	 * 删除记录
	 * @param array $where
	 * @return bool
	 */
	public function deleteCategory($where) {
		$result = $this->get_db()->delete($this->_table, $where);
		return $result;
	}

	/* 设置用户组 */
	public function updateUserGroup($group_id, $user_id) {
		if(empty($user_id) || empty($group_id) || !is_array($group_id)) return false;
		$sql = "DELETE FROM {$this->_map_table} WHERE user_id = '{$user_id}'";
		$this->get_db()->exec($sql);
		$tmp_ary = array();
		foreach($group_id as $gid) {
			$tmp_ary[] = "('{$user_id}','{$gid}')";
		}
		$insert_str = implode(',',$tmp_ary);
		$sql = "INSERT INTO {$this->_map_table}(user_id,user_group_id) VALUES {$insert_str}";
		return $this->get_db()->exec($sql);
	}
	
	/* 插入用户组 */
	public function addUserGroup($data) {
		$ret = $this->get_db()->insert($this->_map_table, $data);
		return $ret ? $this->get_db()->lastInsertId($this->_map_table) : false;
	}
	
	/* 批量调整用户组 */
	public function changeUserGroup($originalID,$targetID) {
		if(empty($originalID) || empty($targetID)) return false;
		$sql = "UPDATE {$this->_map_table} SET user_group_id='{$targetID}' WHERE user_group_id='{$originalID}'";
		return $this->get_db()->exec($sql);
	}
	
	/* 获取默认组的id */
	public function getDefaultGroupId() {
		$sql = "SELECT id FROM {$this->_table} WHERE name='默认组'";
		$rows = $this->get_db()->fetchRow($sql);
		return empty($rows) ? null : $rows['id'];
	}
}