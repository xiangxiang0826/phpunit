<?php
/**
 * 通讯固件类型模型
 * @author liujd@wondershare.cn
 * 2014-07-11 11:23:00
 */
class Model_FirmwareType extends ZendX_Model_Base {
	protected $_schema = 'cloud';
	protected $_table = 'EZ_FIRMWARE_TYPE';
	/**
	 * 获取所有通讯固件连接类型列表
	 */
	function getList($query = "") {
		$condition_string = '1';  // 默认是 where 1
		$queryString = array();
		if(!empty($query['id'])) {
			$queryString[] =  $this->get_db()->quoteInto("a.`id` = ?", "{$query['id']}");
		}
		if($queryString) {
			$condition_string = implode(' AND ',$queryString);
		}
		$sql = "SELECT a.* FROM `{$this->_table}` a  WHERE {$condition_string} ORDER BY a.`id` DESC";
		$data = $this->get_db()->fetchAll($sql);
		return $data;
	}
	
}