<?php

/**
 * User
 * 
 * @author
 * modify by liujd@wondershare.com
 */
class Model_User extends ZendX_Model_Base
{
	const STATUS_ENABLE = 'enable';
	const STATUS_DISABLE = 'disable';
	protected $_schema = 'oss';
	protected $_table = 'SYSTEM_USER';
	protected $pk = 'id';
	
	function getList($start, $count, $search = array()) {
		$condition[] = " 1 ";
		if(isset($search['group_id']) && $search['group_id']) {
			$group_id = (int) $search['group_id'];
			$condition[] = " b.user_group_id = '{$group_id}'";
		}
		if(isset($search['name'])) {
			$condition[] = " a.user_name LIKE '%{$search['name']}%'";
		}
		$conditionstr = implode(' AND ',$condition);
		$total = $this->get_db()->fetchOne("SELECT count(DISTINCT(a.id)) FROM `{$this->_table}` a INNER JOIN SYSTEM_USER_GROUP_MAP b   ON a.id = b.user_id  WHERE {$conditionstr}");
		$sql = "SELECT a.`id`, a.`user_name`, a.`real_name`, a.`department`, a.`ctime`, a.`last_login_time`, a.`status`
				FROM `{$this->_table}` a INNER JOIN SYSTEM_USER_GROUP_MAP b ON a.id = b.user_id
				WHERE {$conditionstr}  GROUP BY a.id ORDER BY `id` DESC
				LIMIT {$start}, {$count}";
		$rows = $this->get_db()->fetchAll($sql);
		return compact('total', 'rows');
	}
	
	/**
	 * 根据用户名获取用户信息，登录时用
	 * 
	 * @param string $user_name
	 */
	function getInfoByName($user_name) {
		return $this->get_db()->fetchRow("SELECT *,id AS user_id FROM `{$this->_table}` WHERE `user_name` = ?", array($user_name));
	}
	
	/**
	 * 根据用户id获取用户信息
	 *
	 * @param int $uid
	 */
	function getInfoByUid($uid) {
		return $this->get_db()->fetchRow("SELECT * FROM `{$this->_table}` WHERE `id` = ?", array($uid));
	}
	
	/**
	 * 根据用户名获取用户ID
	 * 			验证用户名重复时用
	 * @param string $user_name
	 */
	function getIdByName($user_name)
	{
		return $this->get_db()->fetchOne("SELECT `id` FROM `{$this->_table}` WHERE `user_name` = ?", array($user_name));
	}
	
	
	/**
	 * 记录最后登录时间
	 */
	function logLogin($ip)
	{
		$user_info = Common_Auth::getUserInfo();
		return $this->get_db()->update($this->_table, array('last_login_time'=>date('Y-m-d H:i:s')), "`id`='{$user_info['id']}'");
	}
	
	function setStatus($status,$where) {
		return $this->get_db()->update($this->_table, array('status'=>$status), $where);
	}
	
	// 更新操作
	public function update($data, $where) {
		return $this->get_db()->update($this->_table, $data, $where);
	}
 
	/**
	 * 获取所有用户的ID和用户名，并用ID为键
	 * 
	 * @return array
	 */
	function getNameAll() {
		$sql = "SELECT `user_id`, `user_name` FROM `{$this->_table}`";
		$rows = $this->get_db()->fetchAll($sql);
		
		$users = array();
		foreach($rows as $row)
		{
			$users[$row['user_id']] = $row['user_name'];
		}
		
		return $users;
	}
	
	/* 批量调整(启用/禁用)用户组内成员 */
	public function changeGroupUser($newStatus,$idLists) {
		if(empty($newStatus) || empty($idLists) || !in_array($newStatus, array('disable','enable'))) return false;
		$sql = "UPDATE {$this->_table} SET status='{$newStatus}' WHERE id in {$idLists}";
		return $this->get_db()->exec($sql);
	}
}