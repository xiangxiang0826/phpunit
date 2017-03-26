<?php
/**
 * 统计功能（设备）
 * @author xiangxiang
 *
 */
class Model_Statistics_Device extends ZendX_Model_Base {
	protected $_schema = 'cloud';
	protected $_table = 'EZ_DEVICE';
	private $_model;
	
	public function __construct(){
		parent::__construct();
		$this->_model = new Model_Device();
	}
	
	/* 获取所有设备总数  */
	public function getTotal(){
		return $this->_model->getTotal("status IN ('new', 'user_activate', 'disable', 'fault')");
	}
	
	/* 按起止时间统计激活设备列表 */
	public function statByDate($startTime, $endTime){
		$deviceList = $this->_model->statByDate($startTime, $endTime);
		if(empty($deviceList)){
			return array();
		} 
		$deviceMap = array();
		foreach($deviceList as $k=>$v){
			$dateKey = date('Y-m-d',strtotime($v['user_activate_time']));
			if(!isset($deviceMap[$dateKey])) $deviceMap[$dateKey] = 0;
			$deviceMap[$dateKey]++;
		}
		return $deviceMap;
	}
	
	/*按起止时间获取激活设备总数 */
	public function countByDate($startTime, $endTime){
		return $this->_model->countByDate($startTime, $endTime);
	}
}