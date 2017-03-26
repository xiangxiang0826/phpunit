<?php
/**
 * mcu固件模型
 * @author liujd@wondershare.cn
 *
 */
class Model_FirmwareMcu extends ZendX_Model_Base {
	protected $_schema = 'cloud';
	protected $_table = 'EZ_FIRMWARE_MCU';
	const STATUS_ENABLE = 'enable';
	const STATUS_DISABLE = 'disable';
	
	const EXEC_ALL = 'all';
	const EXEC_INCREMENT = 'increment';
	/**
	 * 获取商家提交的所有mcu列表
	 */
	function getList($query = "",$offset,$rows) {
		$condition_string = '1';  // 默认是 where 1
		$queryString = array();
		if(!empty($query['enterprise_id'])) {
			$str =  $this->get_db()->quoteInto("a.`enterprise_id` = ?", "{$query['enterprise_id']}");
			$queryString[] = "({$str})";
		}
		if(isset($query['product_id']) && $query['product_id'] !== '' ) {
			$queryString[] = $this->get_db()->quoteInto("a.`product_id` =  ?", $query['product_id']);
		}
		if(!empty($query['upgrade_type_id'])) {
			$queryString[] = $this->get_db()->quoteInto("a.`upgrade_type_id` =  ?", $query['upgrade_type_id']);
		}
		if(!empty($query['name'])) {
			$queryString[] = $this->get_db()->quoteInto("a.`name` LIKE  ?", "%{$query['name']}%");
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
}