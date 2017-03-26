<?php
/**
 * 已经发布产品管理
 * @author zhouxh
 *
 */
class Model_Product extends ZendX_Model_Base
{
	protected $_schema = 'cloud';
	protected $_table = 'EZ_PRODUCT';
	CONST STATUS_ENABLE = 'enable';
	CONST STATUS_DISABLE = 'disable';
	CONST STATUS_DELETED= 'deleted';
	
	/**
	 * 获得总记录数
	 */
	public function getTotal($query = "")
	{
		$join = ' LEFT JOIN EZ_PRODUCT_CATEGORY c ON t.category_id=c.id  LEFT JOIN EZ_ENTERPRISE e ON t.enterprise_id=e.id';
		if(empty($query)){
			$sql = "SELECT count(*) AS total FROM `{$this->_table}` AS t" . $join;
		}else{
			$sql = "SELECT count(*) AS total FROM `{$this->_table}` AS t {$join} WHERE ".$query;
		}
		return $this->get_db()->fetchOne($sql);
	}
	
	/**
	 * 获取站点所有产品
	 */
	public  function getPublishList($query = "", $offset, $rows, $orderField='t.mtime', $sort= 'DESC')
	{
		if(empty($query)){
			$sql = "SELECT distinct t.*,c.name AS c_name,e.company_name AS e_name,t.status AS p_status,t.ctime AS p_time,m.logo FROM `{$this->_table}` AS t LEFT JOIN EZ_PRODUCT_CATEGORY c ON t.category_id=c.id  LEFT JOIN EZ_ENTERPRISE e ON t.enterprise_id=e.id LEFT JOIN EZ_MAKE_PRODUCT m ON t.id = m.id ORDER BY $orderField $sort limit :offset,:limit";
		}else{
		    $sql = "SELECT  distinct t.*,c.name AS c_name,e.company_name AS e_name,t.status AS p_status,t.ctime AS p_time,m.logo FROM `{$this->_table}` AS t LEFT JOIN EZ_PRODUCT_CATEGORY c ON t.category_id=c.id  LEFT JOIN EZ_ENTERPRISE e ON t.enterprise_id=e.id LEFT JOIN EZ_MAKE_PRODUCT m ON t.id = m.id WHERE {$query} ORDER BY $orderField $sort limit :offset,:limit";
		}
		$smtp = $this->get_db()->prepare($sql);
		// $smtp->bindParam(':orderby', $orderField);
		// $smtp->bindParam(':sort', $sort);
		$smtp->bindParam(':offset', $offset, PDO::PARAM_INT);
		$smtp->bindParam(':limit', $rows, PDO::PARAM_INT);
		$smtp->execute();
		return $smtp->fetchAll();
	}
	
	/**
	 * 查询产品的状态
	 * @param int|array $id
	 * @return boolean|Ambigous <multitype:, multitype:mixed >
	 */
	public function getStatus($id) {
		if(is_int($id)) {
			$idArr = array($id);
		} elseif(is_array($id)) {
			$idArr = $id;
		} else {
			return false;
		}
		$sql = 'SELECT id,status FROM ' . $this->_table . ' WHERE id IN('. implode(',', $idArr) . ')';
		return $this->get_db()->fetchPairs($sql);
	}
	

	/* 根据产品ID批量查询产品信息 */
	public function FindByIds($id_array) {
		if(empty($id_array)) return array();
		$ids = implode(',',$id_array);
		$sql = "SELECT * FROM {$this->_table} WHERE id IN({$ids}) ";
		return $this->get_db()->fetchAll($sql);
	}
	
	/* 根据条件列出所有产品 */
	public function GetList($query = "",$offset,$rows) {
		$condition_string = '1';  // 默认是 where 1
		$queryString = array();
		if(!empty($query['enterprise_id'])) {
			$str =  $this->get_db()->quoteInto("a.`enterprise_id` = ?", "{$query['enterprise_id']}");
			$queryString[] = "({$str})";
		}
		if(!empty($query['status'])) {
			$queryString[] = $this->get_db()->quoteInto("a.`status` =  ?", $query['status']);
		}
		
		if(!empty($query['category_id'])) {
			$queryString[] = $this->get_db()->quoteInto("a.`category_id` =  ?", $query['category_id']);
		}
		
		if(isset($query['product_id'])) {
			$queryString[] = "a.`id` > 0";
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
	 * 获取产品下拉列表数据
	 */
	public function getDroupdownData(){
		$sql = 'SELECT id,name FROM EZ_PRODUCT WHERE status="enable" AND id>0';
		return $this->get_db()->fetchPairs($sql);
	}
	
	/**
	 * 获取正在使用的产品列表
	 */
	public function getEnableed()
	{
		$select = $this->select()->from($this->_table,array('id','name'))
		->where('status=?', 'enable');
		return $this->get_db()->fetchAll($select);
	}
	
	/**
	 * 根据分类ID获取产品数
	 */
	public function queryNumsByCid($idArr){
		if(empty($idArr)) return array();
		if(is_int($idArr)) {
			$idArr = array($idArr);
		}
		$sql = "SELECT category_id, COUNT(*) AS number FROM {$this->_table} WHERE category_id IN(". implode(',', $idArr) . ") AND (status='". self::STATUS_ENABLE . "') GROUP BY category_id";
		return $this->get_db()->fetchPairs($sql);
	}
}