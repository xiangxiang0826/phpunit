<?php
/**
 * 设备报修
 * @author zhouxh
 *
 */
class Model_DeviceRepair extends ZendX_Model_Base {
	/**
	 * 数据库配置
	 * @var string
	 */
	protected $_schema = 'cloud';
	/**
	 * 表名
	 * @var string
	 */
	protected $_table = 'EZ_SERVICE_REPAIR';
	
	const STATUS_NEW = 'new';
	const STATUS_PROCESS = 'process';
	const STATUS_FINISH = 'finish';
	const STATUS_CANCLE = 'cancel';
	const STATUS_CLOSE = 'closed';
	
	/**
	 * 创建列表查询条件
	 * @param array $condition
	 * @return Ambigous <multitype:, multitype:mixed Ambigous <string, boolean, mixed> >
	 */
	public function createSelect($condition, $type = 'pending') {
		$select = $this->get_db()->select();
		$select->from(array('t' => $this->_table));
		if(!empty($condition)) {
			$select->joinLeft(array('p' => 'EZ_PRODUCT'), 't.product_id=p.id', array('p_id'=>'id', 'p_name' => 'name'));
			foreach ($condition as $key=>$value) {
				if($key == 'number') {
					$select->where("t.number like '%{$value}%'");
				} else if($key == 'category') {
					$select->where("p.category_id IN({$this->get_db()->quote($value)})");
				} else if($key == 'sn') {
					$select->where("t.sn LIKE '%{$value}%'");
				} else if($key =='enterprise'){
					$select->where("p.enterprise_id = ?", $value);
			    } else {
					$select->where("$key=?", $value);
				}
			}
		}
		if($type == 'pending') {
			$select->where("t.`status` IN('" . self::STATUS_NEW . "','" . self::STATUS_PROCESS . "')");
		}
		$select->order('t.id DESC');
		return $select;
	}
	
	/**
	 * 查询状态数组列表
	 */
	public function statusDroupDownData($type = 'pending') {
		if($type == 'pending') {
			return array(
					self::STATUS_NEW => '新的',
					self::STATUS_PROCESS => '正在处理',
			);
		} else {
			return array(
					self::STATUS_NEW => '新的',
					self::STATUS_CANCLE => '已取消',
					self::STATUS_PROCESS => '正在处理',
					self::STATUS_FINISH => '已完成',
					self::STATUS_CLOSE => '已关闭',
			);
		}
	}
	
	/**
	 * 查询报修单详细信息
	 * @param int $id
	 */
	public function getDetailInfo($id){
		$select = $this->get_db()->select();
		$select->from(array('t' => $this->_table));
		$select->joinLeft(array('p' => 'EZ_PRODUCT'), 't.product_id=p.id', array('p_id'=>'id', 'p_name' => 'name'));
		$select->where('t.id = ?', $id);
		$result = $this->get_db()->fetchRow($select);
		if($result) {
			$conditon = $select->reset();
			$conditon->from(array('t' => 'EZ_SERVICE_REPAIR_ATTACHMENT'), array('filename', 'filepath'));
			$conditon->where("t.repair_id = ?", $id);
			$result['attachment'] = $this->get_db()->fetchAll($conditon);
		}
		return $result;
	}
	
	/**
	 * 查询设备历史记录
	 * @param int $deviceId
	 * @return array
	 */
	public function getHistoryList($deviceId){
		$select = $this->get_db()->select();
		$select->from(array('t' => $this->_table));
		$select->where('device_id = ?' , $deviceId);
		$select->order('t.id DESC');
		return $this->get_db()->fetchAll($select);
	}
	
	
}