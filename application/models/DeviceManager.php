<?php
/**
 * 设备管理
 * @author liujd@wondershare.cn
 *
 */
class Model_DeviceManager extends ZendX_Model_Base {
	protected $_schema = 'cloud';
	protected $_table = 'EZ_DEVICEID_MANAGER';
	const AUDIT_FAILED = 'audit_failed';
	const AUDIT_PENDING = 'audit_pending';
	const AUDIT_SUCCESS = 'audit_success';
	
	const STATUS_PROCESSING = 'processing';
	const STATUS_PENDING = 'pending';
	const STATUS_COMPLETE = 'complete';
    const STATUS_FAILED = 'failed';
	
	const SUPPLY_FORMAL = 'formal';
	const SUPPLY_TEST = 'test';
	
	/**
	 * 列出申请生产deviceID
	 */
	function getList($query = "",$offset,$rows) {
		$condition_string = '1';  // 默认是 where 1
		$queryString = array();
		if(isset($query['product_id']) && $query['product_id'] !== '' ) {
			$str =  $this->get_db()->quoteInto("a.`product_id` = ?", "{$query['product_id']}");
			$queryString[] = "({$str})";
		}
		if(!empty($query['supply_type'])) {
			$queryString[] = $this->get_db()->quoteInto("a.`supply_type` =  ?", $query['supply_type']);
		}
	
		if(!empty($query['enterprise_id'])) {
			$queryString[] = $this->get_db()->quoteInto("a.`enterprise_id` =  ?", $query['enterprise_id']);
		}
		
		if(!empty($query['batch'])) {
			$queryString[] = $this->get_db()->quoteInto("a.`batch` LIKE  ?", "%{$query['batch']}%");
		}
		
		if(!empty($queryString)) {
			$condition_string = implode(" AND ", $queryString);
		}
		$sql = "SELECT count(a.id) as counts FROM `{$this->_table}`  a WHERE  {$condition_string} LIMIT 1";
		$data = $this->get_db()->fetchRow($sql);
		if($data['counts'] < 1) {
			return $data;
		}
		$sql = "SELECT a.* FROM `{$this->_table}` a  WHERE {$condition_string} ORDER BY a.`ctime` DESC LIMIT {$offset},{$rows}";
		$data['list'] = $this->get_db()->fetchAll($sql);
		return $data;
	}
	
	/* 获取申请详情 */
	public function GetDetail($query) {
		$condition_string = '1';  // 默认是 where 1
		$queryString = array();
		if(!empty($query['id'])) {
			$str =  $this->get_db()->quoteInto("a.`id` = ?", "{$query['id']}");
			$queryString[] = "({$str})";
		}
		
		if(!empty($queryString)) {
			$condition_string = implode(" AND ", $queryString);
		}
		$sql = "SELECT a.* FROM `{$this->_table}` a  WHERE {$condition_string} LIMIT 1";
		$row = $this->get_db()->fetchRow($sql);
		return $row;
	}
	
	/* 审核申请 */
	public function Audit($data, $id) {
		$data['run_status'] = self::STATUS_PENDING;
		$where = array("id = '{$id}'");
		return $this->get_db()->update($this->_table, $data, $where);
	}
}