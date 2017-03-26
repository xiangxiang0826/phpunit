<?php
/**
 * 企业模型
 * @author zhouxh
 *
 */
class Model_Enterprise extends ZendX_Model_Base 
{
	protected $_schema = 'cloud';
	protected $_table = 'EZ_ENTERPRISE';
	const STATUS_ENABLE = 'enable';
	const STATUS_DISABLE = 'disable';
	const STATUS_PENDING = 'pending';
	const STATUS_DELETED = 'deleted';
	const DEFAULT_EZ_ID = 1;
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
		return $this->get_db()->fetchOne($sql);
	}
	
	/**
	 * 获取站点所有会员
	 */
	function getList($query = "", $offset, $rows)
	{
		if(empty($query)){
			$sql = "SELECT  t.* FROM `{$this->_table}` AS t   ORDER BY t.id DESC limit $offset,$rows";
		}else{
			$sql = "SELECT  t.* FROM `{$this->_table}` AS t   WHERE ".$query." ORDER BY t.id DESC limit {$offset},{$rows}";
		}
		return $this->get_db()->fetchAll($sql);
	}
	
	/**
	 * 查询产品数量
	 * @param int $id
	 */
	public function getProductNumber($id){
		$sql = "SELECT COUNT(*) AS p_number FROM EZ_PRODUCT WHERE enterprise_id =? AND status = ?";
		return $this->get_db()->fetchOne($sql,array($id, Model_Product::STATUS_ENABLE));
	}
	
	/**
	 * 查看详情
	 * @param int $id
	 * @return Ambigous <string, boolean, mixed>
	 */
	public function getDetail($id) {
		$sql = "SELECT t.* FROM `{$this->_table}` AS t   WHERE t.id=?";
		$sql = $this->get_db()->quoteInto($sql, $id);
		$result = $this->get_db()->fetchRow($sql);
		if($result) {
			$result['product_num'] = $this->getProductNumber($id);
			$result['is_ezapp'] = $this->isHasSelfApp($id);
		}
		return $result;
	}
	/**
	 * 查看详情
	 * @param int $id
	 * @return Ambigous <string, boolean, mixed>
	 */
	public function getByLabel($label) {
		$sql = "SELECT t.* FROM `{$this->_table}` AS t   WHERE t.label=?";
		$sql = $this->get_db()->quoteInto($sql, $label);
		$result = $this->get_db()->fetchRow($sql);
		return $result;
	}
	/**
	 * 通过状态查询厂商的数量
	 */
	public function getNumberBystatus($status){
		$sql = "SELECT COUNT(*) AS number FROM EZ_ENTERPRISE WHERE status =?";
		$sql = $this->get_db()->quoteInto($sql, $status);
		return $this->get_db()->fetchOne($sql);
	}
	
	/**
	 * 是否有自己的APP
	 */
	public  function isHasSelfApp($id){
		$sql = 'SELECT COUNT(*) FROM EZ_ENTERPRISE_APP WHERE enterprise_id=?';
		$sql = $this->get_db()->quoteInto($sql, $id);
		$number = $this->get_db()->fetchOne($sql);
		if(empty($number)) {
			return 'no';
		} else {
		    return 'yes';	
		}
	}
	/**
	 * 查询厂商下拉列表数据
	 */
	public function droupDownData(){
		$sql = "SELECT id,company_name FROM `{$this->_table}` WHERE status='enable' ";
		return $this->get_db()->fetchPairs($sql);
	}
	
	/**
	 * 查询企业标识是否存在
	 * @param string $label
	 * @return boolean
	 */
	public function labelExists($label) {
		$sql = 'SELECT COUNT(*) FROM ' . $this->_table . ' WHERE label=?';
		$sql = $this->get_db()->quoteInto($sql, $label);
		$number = $this->get_db()->fetchOne($sql);
		if(empty($number)) {
			return false;
		} else {
			return true;
		}
	}
	
	/* 根据企业ID批量查询企业信息 */
	public function FindByIds($id_array) {
		if(empty($id_array)) return array();
		$ids = implode(',',$id_array);
		$sql = "SELECT * FROM {$this->_table} WHERE id IN({$ids}) ";
		$list = $this->get_db()->fetchAll($sql);
		return $list ? $list : array();
	}
	
	/**
	 * 查询企业标识是否存在
	 * @param string $name
	 * @return boolean
	 */
	public function nameExists($name) {
		$sql = 'SELECT COUNT(*) FROM ' . $this->_table . ' WHERE name=?';
		$sql = $this->get_db()->quoteInto($sql, $name);
		$number = $this->get_db()->fetchOne($sql);
		if(empty($number)) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * 查询企业标识是否存在
	 * @param string $email
	 * @return boolean
	 */
	public function emailExists($email) {
		$sql = 'SELECT COUNT(*) FROM ' . $this->_table . ' WHERE email=?';
		$sql = $this->get_db()->quoteInto($sql, $email);
		$number = $this->get_db()->fetchOne($sql);
		if(empty($number)) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * 查询企业标识是否存在
	 * @param string $mobile
	 * @return boolean
	 */
	public function mobileExists($mobile) {
		$sql = 'SELECT COUNT(*) FROM ' . $this->_table . ' WHERE mobile=?';
		$sql = $this->get_db()->quoteInto($sql, $mobile);
		$number = $this->get_db()->fetchOne($sql);
		if(empty($number)) {
			return false;
		} else {
			return true;
		}
	}
	
}