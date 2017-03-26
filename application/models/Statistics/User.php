<?php
/**
 * 统计功能（用户）
 * @author xiangxiang
 *
 */
class Model_Statistics_User extends ZendX_Model_Base {
	protected $_schema = 'cloud';
	protected $_table = 'EZ_USER';
	private $_model, $_analyzeModel, $_mongo_db;
	
	public function __construct(){
		parent::__construct();
		$this->_model = new Model_Member();
		$this->_analyzeModel = new Model_Analyze();
		$this->_mongo = $this->_analyzeModel->getMongoClient();
	}
	
	/* 获取所有注册用户总数 */
	public function getTotal(){
		return $this->_model->getTotal('');
	}
	
	/* 按起止时间获取注册用户列表  */
	public function statsByDate($startTime, $endTime){
		$userList = $this->_model->statsByDateGroup($startTime, $endTime);
		if(empty($userList)){
			return array();
		} 
		$userMap = array();
		foreach($userList as $k=>$v){
			$userMap[$v['date']] = (int)$v['total'];
		}
		return $userMap;
	}
	
	/* 按其实时间获取注册用户总数 */
	public function countByDate($startTime, $endTime){
		return $this->_model->countByDate($startTime, $endTime);
	}
	
	/* 获取活跃用户数 */
	public function getActiveUser($productIds, $startTime, $endTime, $lastStartTime, $lastEndTime){
		$lastActiveUserCount = 0;
		$mongoResult = $mongoToArray = $lastActiveUserMap = array();
		foreach($productIds as $k=>$v){
			$db = "ez_statistics_{$v['id']}";
			$active_cursor = $this->_mongo->$db->active_user->find(array('date'=>array('$gte'=>$startTime,'$lte' => $endTime)));
			$last_active_cursor = $this->_mongo->$db->active_user->find(array('date'=>array('$gte'=>$lastStartTime,'$lte' => $lastEndTime)));
			$lastActiveUserMap = iterator_to_array($last_active_cursor);
			//计算上一个阶段的活跃用户
			if(!empty($lastActiveUserMap)){
				foreach($lastActiveUserMap as $lv){
					$lastActiveUserCount += $lv['num'];
				}
			}
			//把当前阶段的活跃用户数组组合成视图显示的数组
			$mongoResult[$v['id']] = iterator_to_array($active_cursor);
			if(!empty($mongoResult[$v['id']])){
				foreach($mongoResult[$v['id']] as $vv){
					$mongoToArray[$vv['date']] = !isset($mongoToArray[$vv['date']]) ? $vv['num'] : ($mongoToArray[$vv['date']] + $vv['num']);
				}
			}
		}
		return array('activeUserMap'=>$mongoToArray, 'lastActiveUserCount'=>$lastActiveUserCount);
	}
}