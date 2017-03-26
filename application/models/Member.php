<?php
/**
 * 会员模型
 * 
 * @author
 * modify by liujd@wondershare.com
 */
class Model_Member extends ZendX_Model_Base {
	const STATUS_ENABLE = 'enable';
	const STATUS_REMOVE = 'remove';
	const STATUS_LOCKED = 'locked';
	const STATUS_UNACTIVATED = 'unactivated';
	protected $_schema = 'cloud';
	protected $_table = 'EZ_USER';
	protected $pk = 'id';
	
/**
	 * 获取站点所有会员
	 */
	function getList($query = "",$offset,$rows) {
		$condition_string = '1';  // 默认是 where 1
		$queryString = array();
		if(!empty($query['user_id'])) {
			$queryString[] =  $this->get_db()->quoteInto("a.`id` = ?", trim($query['user_id']));
		}
		if(!empty($query['status'])) {
			$queryString[] = $this->get_db()->quoteInto("a.`status` =  ?", $query['status']);
		}
		if(!empty($query['phone'])) {
			$queryString[] = $this->get_db()->quoteInto("a.`phone` LIKE  ?", trim($query['phone']) . '%');
		}
		if(!empty($query['email'])) {
			$queryString[] = $this->get_db()->quoteInto("a.`email` LIKE  ?", trim($query['email']) . '%');
		}
		if(!empty($query['platform'])) {
            // 由于EZ_USER表区分了 ez_mobile_app_android 和 ez_mobile_app_android，所以采用模糊查询
            // modified by etong <zhoufeng@wondershare.cn> 2014/08/09 16:20 v1 
			$queryString[] = $this->get_db()->quoteInto("a.`platform` like ?", $query['platform'].'%');
		}
		if(!empty($queryString)) {
			$condition_string = implode(" AND ", $queryString);
		}
		$sql = "SELECT count(a.id) as counts FROM `{$this->_table}`  a WHERE  {$condition_string} LIMIT 1";
		$data = $this->get_db()->fetchRow($sql);
		if($data['counts'] < 1) {
			return $data;
		}
		$sql = "SELECT a.* FROM `{$this->_table}` a  WHERE {$condition_string} ORDER BY a.`id` DESC LIMIT {$offset},{$rows}";
		$data['list'] = $this->get_db()->fetchAll($sql);
		return $data;
	}
	
	/**
	 * 根据用户名获取用户信息，登录时用
	 * 
	 * @param string $user_name
	 */
	function getInfoByName($user_name) {
		return $this->get_db()->fetchRow("SELECT * FROM `{$this->_table}` WHERE `name` = ?", array($user_name));
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
		return $this->get_db()->fetchOne("SELECT `id` FROM `{$this->_table}` WHERE `name` = ?", array($user_name));
	}
	
	// 更新操作
	public function update($data, $where) {
		return $this->get_db()->update($this->_table, $data, $where);
	}
	
	/* 统计相关，根据日期与来源类型统计相应总数 */
	public function statByDate($start_date, $end_date) {
		$sql = "SELECT id,reg_time,platform FROM `{$this->_table}` a  WHERE reg_time >= '{$start_date}' AND reg_time <= '{$end_date}'";
		return $this->get_db()->fetchAll($sql);
	}
 
	/* 统计相关，根据结束日期统计会员总数 */
	public function StatByEndDate($end_date) {
		$sql = "SELECT count(id) as counts FROM `{$this->_table}` a  WHERE reg_time <= '{$end_date}' LIMIT 1";
		return $this->get_db()->fetchRow($sql);
	}
	
	/* 获取所有用户数 */
	function getTotal($query){
		if(empty($query)){
			$sql = "SELECT count(*) AS total FROM `{$this->_table}` " ;
		}else{
			$sql = "SELECT count(*) AS total FROM `{$this->_table}`  WHERE ".$query;
		}
		return $this->get_db()->fetchOne($sql);
	}
	
	/* 统计相关，根据日期与来源类型统计相应总数 */
	public function countByDate($start_date, $end_date) {
		$sql = "SELECT count(1) count  FROM `{$this->_table}` a  WHERE reg_time >= '{$start_date}' AND reg_time <= '{$end_date}'";
		return $this->get_db()->fetchOne($sql);
	}
	
	/**
	 * 由appid获取用户信息
	 */
	public function getByAppID($app_id){
		$sql = "SELECT u.* FROM EZ_USER u
				JOIN EZ_APP_USER au ON au.user_id=u.id
				WHERE au.id=?";
		$sql = $this->get_db()->quoteInto($sql, $app_id);
		return $this->get_db()->fetchRow($sql);
	
	}
	
	/**
	 * 按时间统计注册用户数
	 */
	public function statsByDateGroup($startTime='', $endTime=''){
		$select = $this->select()->from($this->_table,array('total'=>'COUNT(1)','date'=>'DATE_FORMAT(reg_time, \'%Y-%m-%d\')'));
		if(!empty($startTime))
			$select->where('reg_time >= ?', $startTime);
		if(!empty($endTime))
			$select->where('reg_time < ?', $endTime);
		$select->group('date');
		return $this->get_db()->fetchAll($select);
	}
}