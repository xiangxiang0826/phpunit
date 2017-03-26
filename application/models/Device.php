<?php
/**
 * 设备管理
 * @author zhouxh
 *
 */
class Model_Device extends ZendX_Model_Base
{
	protected $_schema = 'cloud';
	protected $_table = 'EZ_DEVICE';
	const STATUS_NEW = 'new';
	const STATUS_FACT_ACTIVATE = 'factory_activate';
	const STATUS_USER_ACTIVATE = 'user_activate';
	const STATUS_FAULT = 'fault';
	const STATUS_SCRAP = 'scrap';
	const STATUS_DISABLE = 'disable';
	//设备类型
	const DEVICE_TYPE_TEST = 'test';
	const DEVICE_TYPE_FORMAL = 'formal';
	
	/**
	 * 根据ID获取绑定的用户信息
	 *
	 * @param string $id
	 */
	function getBinderById($id) {
		$id = (string) $id;
		$sql = "SELECT a.device_id,b.* FROM EZ_USER_DEVICE_MAP a INNER JOIN EZ_USER b ON a.user_id = b.id WHERE a.device_id = '{$id}'";
		return $this->get_db()->fetchAll($sql);
	}
	
	/**
	 * 根据ID获取绑定的用户信息
	 *
	 * @param string $uid
	 */
	function getDeviceByUid($uid) {
		$uid = (int) $uid;
		$sql = "SELECT a.device_id,b.* FROM EZ_USER_DEVICE_MAP a INNER JOIN {$this->_table} b ON a.device_id = b.id  WHERE a.user_id = '{$uid}'";
		return $this->get_db()->fetchAll($sql);
	}
	/**
	 * 获取查询条件
	 * @param array $condition
	 */
	public function getCondition(array $condition) {
		$select = $this->get_db()->select();
		$select->from(array('D' => $this->_table));
		$select->join(array('P' => 'EZ_PRODUCT'), 'D.product_id=P.id', array('p_id'=>'id', 'p_name' => 'name'));
		$select->join(array('E' => 'EZ_ENTERPRISE'), 'P.enterprise_id=E.id', array('e_id' => 'id', 'company_name'));
		$select->join(array('T' => 'EZ_DEVICE_DETAIL'), 'D.id=T.device_id', array('version'));
		if(!empty($condition)) {
			foreach ($condition as $key=>$value) {
				if($key == 'device_id') {
					$select->where("D.id like '%{$value}%'");
				} else if($key == 'category') {
					 $select->where("P.category_id IN({$this->get_db()->quote($value)})");
				} else if($key == 'D.status') {
					 $select->where("D.status IN($value)");
			    }else if($key == 'name') {
					 $select->where("D.name LIKE '%{$value}%'");
			    } else {
					$select->where("$key=?", $value);
				}
			}
		}
		$select->order('D.user_activate_time DESC');
		return $select;
	}
	/**
	 * 查询设备状态数组列表
	 */
	public  function getStatusMap() {
		return array(
				        self::STATUS_NEW => '新的',
				        self::STATUS_FACT_ACTIVATE => '出厂激活',
				        self::STATUS_USER_ACTIVATE => '用户激活',
				        self::STATUS_FAULT => '故障',
				        self::STATUS_DISABLE => '停用',
				   );
	}
	/**
	 * 查询设备详细信息
	 * @param unknown_type $id
	 */
	public function getDetailById($id) {
		$select = $this->get_db()->select();
		$select->from(array('D' => $this->_table));
		$select->join(array('P' => 'EZ_PRODUCT'), 'D.product_id=P.id', array('p_id'=>'id', 'p_name' => 'name'));
		$select->join(array('E' => 'EZ_ENTERPRISE'), 'P.enterprise_id=E.id', array('e_id' => 'id', 'company_name'));
		$select->join(array('T' => 'EZ_DEVICE_DETAIL'), 'D.id=T.device_id', array('version', 'ip', 'online_time'));
		$select->join(array('C' => 'EZ_PRODUCT_CATEGORY'), 'P.category_id=C.id', array('c_name'=>'name'));
		$select->where('D.id=?', $id );
		return $this->get_db()->fetchRow($select);
	}
	
	/* 根据日期统计拉取所有设备 */
	public function statByDate($start_date, $end_date) {
		$sql = "SELECT id,user_activate_time,product_id FROM `{$this->_table}` a  WHERE user_activate_time >= '{$start_date}' AND user_activate_time <= '{$end_date}'";
		return $this->get_db()->fetchAll($sql);
	}
	
	/* 根据日期、产品ID统计激活 */
	public function CountByProduct($start_date, $end_date, $product_id_ary) {
		if(!$product_id_ary) return false;
		$product_ids = implode(',', $product_id_ary);
		$sql = "SELECT count(id) as counts FROM `{$this->_table}` a  WHERE user_activate_time >= '{$start_date}' AND user_activate_time <= '{$end_date}' AND product_id IN ({$product_ids})";
		return $this->get_db()->fetchRow($sql);
	}
	
	/* 获取所有设备数 */
	public function getTotal($query){
		if(empty($query)){
			$sql = "SELECT count(*) AS total FROM `{$this->_table}` " ;
		}else{
			$sql = "SELECT count(*) AS total FROM `{$this->_table}`  WHERE ".$query;
		}
		return $this->get_db()->fetchOne($sql);
	}
	
	/* 根据日期统计设备接入 */
	public function countByDate($start_date, $end_date) {
		$sql = "SELECT count(1) count FROM `{$this->_table}` a  WHERE user_activate_time >= '{$start_date}' AND user_activate_time <= '{$end_date}'";
		return $this->get_db()->fetchOne($sql);
	}
	/**
	 * 删除设备
	 * @param string $deviceId
	 * @param int $relation  是否删除设备的绑定关系
	 * @return boolean
	 */
	public function deleteDevice($deviceId, $relation = 0) {
		if(empty($deviceId)) {
			return false;
		}
		try {
			$db = $this->get_db();
			$db->beginTransaction();
			$sql = $db->quoteInto("DELETE FROM EZ_DEVICE_DETAIL WHERE device_id = ?", $deviceId);
			$db->query($sql);
			$sql = $db->quoteInto("DELETE FROM {$this->_table} WHERE id = ?", $deviceId);
			$db->query($sql);
			if($relation) {
				$sql = $db->quoteInto("DELETE FROM EZ_USER_DEVICE_MAP WHERE device_id = ?", $deviceId);
				$db->query($sql);
			}
			$db->commit();
			return true;
		} catch(Exception $e) {
			$db->rollBack();
			return false;
		}
	}
	
	
}