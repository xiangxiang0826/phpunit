<?php
/**
 * 通讯固件模型
 * @author liujd@wondershare.cn
 * 2014-07-11 10:00:00
 */
class Model_FirmwareComm extends ZendX_Model_Base {
	protected $_schema = 'cloud';
	protected $_table = 'EZ_FIRMWARE_COMM';
	/**
	 * 获取所有通讯固件列表
	 */
	function getList($query = "",$offset,$rows) {
		$condition_string = '1';  // 默认是 where 1
		$queryString = array();
		if(!empty($query['firmware_type_id'])) {
			$str =  $this->get_db()->quoteInto("a.`firmware_type_id` = ?", "{$query['firmware_type_id']}");
			$queryString[] = "({$str})";
		}
		if(!empty($query['upgrade_type_id'])) {
			$queryString[] = $this->get_db()->quoteInto("a.`upgrade_type_id` =  ?", $query['upgrade_type_id']);
		}
		if(!empty($query['label'])) {
			$queryString[] = $this->get_db()->quoteInto("a.`label` =  ?", $query['label']);
		}
		if(!empty($query['name'])) {
			$queryString[] = $this->get_db()->quoteInto("a.`name` LIKE  ?", "%{$query['name']}%");
		}
		if(!empty($queryString)) {
			$condition_string = implode(" AND ", $queryString);
		}
		$sql = "SELECT count(a.id) as counts FROM `{$this->_table}`  a WHERE  status='enabled' AND {$condition_string} LIMIT 1";
		$data = $this->get_db()->fetchRow($sql);
		if($data['counts'] < 1) {
			return array('counts'=>0,'list'=>array());
		}
		$sql = "SELECT a.* FROM `{$this->_table}` a  WHERE status='enabled' AND {$condition_string} ORDER BY a.`id` DESC LIMIT {$offset},{$rows}";
		$data['list'] = $this->get_db()->fetchAll($sql);
		return $data;
	}
	
	/* 获取使用此固件的所有产品 */
	public function GetProductMap($firmware_comm_id) {
		$sql = "SELECT a.*,b.name FROM  `EZ_PRODUCT_FIRMWARE_MAP` a INNER JOIN EZ_PRODUCT b ON a.product_id = b.id WHERE a.firmware_comm_id = ?";
		return $this->get_db()->fetchAll($sql, array($firmware_comm_id));
	}
	
	/* 根据固件ID获取固件的产品数 */
	public function GetProducts($firmware_comm_ids) {
		if(empty($firmware_comm_ids)) return false;
		$ids = implode(',',$firmware_comm_ids);
		$sql = "SELECT a.firmware_comm_id,count(a.product_id) as counts FROM  `EZ_PRODUCT_FIRMWARE_MAP` a  WHERE firmware_comm_id IN ({$ids}) GROUP BY a.firmware_comm_id";
		$list =  $this->get_db()->fetchAll($sql);
		return $list ? $list : array();
	}
	
	/* 保存图片 */
	public function SaveImages($firmware_imgs, $id) {
		if(empty($firmware_imgs)) return false;
		$sql = "DELETE FROM EZ_FIRMWARE_COMM_IMAGE WHERE firmware_comm_id = '{$id}'";
		$this->get_db()->exec($sql);
		$values = array();
		foreach($firmware_imgs as $k => $img) {
			$values[] = "('{$img}','{$id}','{$k}')";
		}
		$str = implode(',', $values);
		$sql = "INSERT INTO EZ_FIRMWARE_COMM_IMAGE(image, firmware_comm_id, `sort`) VALUES {$str}";
		return $this->get_db()->exec($sql);
	}
	
	/* 获取固件图片 */
	public function GetImages($id) {
		$sql = "SELECT * FROM EZ_FIRMWARE_COMM_IMAGE WHERE firmware_comm_id = ?";
		return $this->get_db()->fetchAll($sql, $id);
	}
	
	/* 更新固件与产品相关表 EZ_PRODUCT_FIRMWARE_MAP */
	public function UpdateProductMap($data, $where) {
		return $this->get_db()->update('EZ_PRODUCT_FIRMWARE_MAP', $data, $where);
	}
 }