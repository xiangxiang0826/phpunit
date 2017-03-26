<?php
/**
 * 统计功能（app）
 * @author xiangxiang
 *
 */
class Model_Statistics_App extends ZendX_Model_Base {
	protected $_schema = 'cloud';
	protected $_table = 'EZ_APP_USER';
	private $_model;
	
	public function __construct(){
		parent::__construct();
		$this->_model = new Model_AppUser();
	}
	
	/* 获取所有app安装量 */
	public function getTotal(){
		return $this->_model->getAppCount(array());
	}
	
	/* 按起止时间统计app安装列表 */
	public function statByDate($startTime, $endTime){
		$appList = $this->_model->statByDate(array(), $startTime, $endTime); 
		if(empty($appList)){
			return array();
		}
		$appMap = array();
		foreach ($appList as $k=>$v){
			$appMap[$v['date']] = (int)$v['total'];
		}
		return $appMap;
	}
	
	/* 按起止时间统计app安装总数 */
	public function getAppCount($startTime, $endTime){
		return $this->_model->getAppCount(array(), $startTime, $endTime);
	}
}