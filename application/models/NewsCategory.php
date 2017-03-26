<?php

/**
 * 扩展分类Model
 *
 * @author xiangxiang<xiangxiang@wondershare.cn>
 * @date 2014-10-10
 */
class Model_NewsCategory extends ZendX_Model_Base
{
	/**
	 * 设置是否允许删除
	 *
	 * @var boolean
	 */
	public $isDelete = TRUE;

	protected $_schema = 'website';
	protected $_table = 'EZ_NEWS_CATEGORY';
    protected $_map_table = 'EZ_NEWS';
	protected $pk = 'id';
	function __construct()
	{
		parent::__construct('website');
	}


	/**
	 * 树显示类别
	 */
	public function dumpTree($all = false) {
		$where = !$all ? "status='enable'" : 1;
		$sql = "SELECT id,name,parent_id,layer,sort FROM `{$this->_table}` WHERE {$where} ORDER BY sort DESC";
		$category = $this->get_db()->fetchAll($sql);
		$treeArr = ZendX_Array::array_to_tree($category, 'id', 'parent_id');
		return  ZendX_Array::dumpArrayTree($treeArr);
	}
    
	function getList($start, $count, $search=array())
	{
        $where = '1';
        $search[] = 'status = "enable"';
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
		$total = $this->get_db()->fetchOne("SELECT count(*) FROM `{$this->_map_table}` WHERE status='enable' AND {$where}");
		$sql = "SELECT *
				FROM `{$this->_map_table}`
                WHERE status='enable' AND {$where}
				ORDER BY `id` ASC
				LIMIT {$start}, {$count}";     
		$rows = $this->get_db()->fetchAll($sql);
		return compact('total', 'rows');
	}

	function getIdByName($name)
	{
		return $this->get_db()->fetchOne("SELECT `id` FROM `{$this->_table}` WHERE `name`='{$name}'");
	}

	//根据uid,批量获取分类权限
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
			$sql = 'SELECT category_id AS cid, COUNT(*) AS total FROM '.$this->_map_table.' as t WHERE status="enable" GROUP BY category_id';
		}else{
            $sql = 'SELECT category_id AS cid, COUNT(*) AS total FROM '.$this->_map_table.' as t WHERE  status="enable" AND'.$query.' GROUP BY user_group_id';
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

	/* 设置分类 */
	public function updateUserGroup($group_id, $user_id) {
		if(empty($user_id) || empty($group_id) || !is_array($group_id)) return false;
		$sql = "DELETE FROM {$this->_table} WHERE user_id = '{$user_id}'";
		$this->get_db()->exec($sql);
		$tmp_ary = array();
		foreach($group_id as $gid) {
			$tmp_ary[] = "('{$user_id}','{$gid}')";
		}
		$insert_str = implode(',',$tmp_ary);
		$sql = "INSERT INTO {$this->_table}(user_id,user_group_id) VALUES {$insert_str}";
		return $this->get_db()->exec($sql);
	}
	
	/* 插入分类 */
	public function addCategory($data) {
		$ret = $this->get_db()->insert($this->_table, $data);
		return $ret ? $this->get_db()->lastInsertId($this->_table) : false;
	}
    
    
}