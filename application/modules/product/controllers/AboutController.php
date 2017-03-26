<?php
/**
 * 产品类别管理
 * @author zhouxh
 *
 */
class Product_AboutController extends ZendX_Controller_Action
{
	const DAY_DIFF = 366;
	private $_analyzeModel, $_enterpriseModel ,$_productModel, $_deviceModel, $_appModel, $_userModel;
	private $mongo_db = NULL;
	public function init() {
		parent::init();	
		$this->_analyzeModel = new Model_Analyze();
		$this->_enterpriseModel = new Model_Statistics_Enterprise();
		$this->_productModel = new Model_Statistics_Product();
		$this->_deviceModel = new Model_Statistics_Device();
		$this->_userModel = new Model_Statistics_User();
		$this->_appModel = new Model_Statistics_App();
		//mongodb数据库连接
		$this->_mongo = $this->_analyzeModel->getMongoClient();
	}
	/**
	 * 分类列表
	 */
	public function indexAction() {
		//get 传参处理
		$period = $this->_request->get("period");
		$startDate = $this->_request->get("start_date");
		$endDate = $this->_request->get("end_date");
		if(empty($startDate) && empty($endDate) && empty($period)) {
			$period = 'yesterday';
		}
		
		//获取起止时间以及环比时间
		if(empty($startDate) && $period) { // 根据时间段设置起始时间
			$period_array = $this->_analyzeModel->getDatePeriod($period);
			$startDate = $period_array['start_date'];
			$endDate = $period_array['end_date'];
		}
		if($startDate < $endDate) { // 无论如何最小的日期是$startTime，最大的是$endTime；
			$startTime = $startDate;
			$endTime = $endDate;
		} else {
			$startTime = $endDate;
			$endTime = $startDate;
		}		
		$lastStartTime = date('Y-m-d',2*strtotime($startTime)-strtotime($endTime)-86400);
		$lastEndTime = date('Y-m-d',strtotime($startTime)-86400);
		
		
		//时间跨度计算
		$diff_date = Common_Func::timediff($startDate,$endDate);
		if($diff_date['day'] > self::DAY_DIFF) {
			return Common_Protocols::generate_json_response(null,Common_Protocols::VALIDATE_FAILED);
		}
			
		//所有厂商
		$enterprise_count = $this->_enterpriseModel->getTotal();
		
		//待审核厂商
		$enterprise_unaudit_count = $this->_enterpriseModel->getUnaudit();
		
		//所有产品
		$product_count = $this->_productModel->getTotal();
		
		//待审核产品
		$product_unaudit_count = $this->_productModel->getUnaudit();
		
		//所有设备
		$device_count = $this->_deviceModel->getTotal();
		
		//所有注册用户
		$user_count = $this->_userModel->getTotal();
		
		//所有app安装量
		$app_count = $this->_appModel->getTotal();
		
		/*业务概览 图表统计数据提取*/
		//注册用户数
		$userMap = $this->_userModel->statsByDate($startTime.' 00:00:00' ,$endTime.' 23:59:59');
		$lastUserList = $this->_userModel->countByDate($lastStartTime.' 00:00:00' ,$lastEndTime.' 23:59:59');//计算注册用户环比时使用
		
		//设备接入量
		$deviceMap = $this->_deviceModel->statByDate($startTime.' 00:00:00' ,$endTime.' 23:59:59');
		$lastDeviceList = $this->_deviceModel->countByDate($lastStartTime.' 00:00:00' ,$lastEndTime.' 23:59:59');//计算安装设备环比时使用
		
		//app安装量
		$appMap = $this->_appModel->statByDate($startTime.' 00:00:00' ,$endTime.' 23:59:59');
		$lastAppList = $this->_appModel->getAppCount($lastStartTime.' 00:00:00' ,$lastEndTime.' 23:59:59');//计算安装APP环比时使用	
		
		//MONGODB活跃用户数数据统计
		$productIdList = $this->_productModel->getList();
		//活跃用户数
		$activeUserArray = $this->_userModel->getActiveUser($productIdList['list'], $startTime, $endTime, $lastStartTime, $lastEndTime);
		$activeUserMap = $activeUserArray['activeUserMap'];
		$lastActiveUserCount = $activeUserArray['lastActiveUserCount'];
		
		$userTotalMap = $deviceTotalMap = $appTotalMap = array();
		for($i = $diff_date['day'];$i >= 0;$i--) { // 每天统计
			$date_to_times = strtotime($startTime." +{$i} day");
			$tmp_date = date('Y-m-d',$date_to_times);
			$userTotalMap[$tmp_date] = isset($userMap[$tmp_date]) ? $userMap[$tmp_date] : 0;
			$deviceTotalMap[$tmp_date] = isset($deviceMap[$tmp_date]) ? $deviceMap[$tmp_date] : 0;
			$appTotalMap[$tmp_date] = isset($appMap[$tmp_date]) ? $appMap[$tmp_date] : 0;
			if(!isset($activeUserTotalMap[$tmp_date])) $activeUserTotalMap[$tmp_date] = 0;
			$activeUserTotalMap[$tmp_date] = (!isset($activeUserMap[$tmp_date])) ? 0 : ( $activeUserMap[$tmp_date] );
		}
		/*业务概览 图表统计数据提取   end*/
		
		//模板数据赋值
		$this->view->date_step = floor($diff_date['day'] / 10);
		$this->view->start_date = $startDate;
		$this->view->end_date = $endDate;
		$this->view->period = $period;
		$this->view->enterprise_count = $enterprise_count;
		$this->view->product_count = $product_count;
		$this->view->device_count = $device_count;
		$this->view->user_count = $user_count;
		$this->view->app_count = $app_count;
		$this->view->enterprise_unaudit_count = $enterprise_unaudit_count;
		$this->view->product_unaudit_count = $product_unaudit_count;

		$this->view->chartsDate = json_encode(array_reverse(array_keys($userTotalMap)));
		$this->view->statisticsTotalJson = json_encode(array_reverse(array_values($userTotalMap)));
		$this->view->statisticsAppTotalJson = json_encode(array_reverse(array_values($appTotalMap)));
		$this->view->statisticsDeviceTotalJson = json_encode(array_reverse(array_values($deviceTotalMap)));
		$this->view->statisticsActiveUserTotalJson = json_encode(array_reverse(array_values($activeUserTotalMap)));


		$this->view->tableUserData = $userTotalMap;
		$this->view->tableAppData = $appTotalMap;
		$this->view->tableDeviceData = $deviceTotalMap;
		$this->view->tableActiveUserData = $activeUserTotalMap;
		$this->view->tableDeviceSum = array_sum(array_values($deviceTotalMap));
		$this->view->tableUserSum = array_sum(array_values($userTotalMap));
		$this->view->tableAppSum = array_sum(array_values($appTotalMap));
		$this->view->regUserRate = $this->getPortion(($this->view->tableUserSum - $lastUserList),$lastUserList);
		$this->view->deviceInRate =  $this->getPortion(($this->view->tableDeviceSum - $lastDeviceList),$lastDeviceList);
		$this->view->appRate =  $this->getPortion(($this->view->tableAppSum - $lastAppList),$lastAppList);
		$this->view->tableActiveUserSum = array_sum(array_values($activeUserTotalMap));
		$this->view->activeUserRate = $this->getPortion(($this->view->tableActiveUserSum - $lastActiveUserCount),$lastActiveUserCount);
	}
	 
	//计算环比
	private function getPortion($dividend ,$divisor ){
		if($divisor == $dividend){
			return 0;
		}elseif($divisor == 0){
			return '∞';
		}else{
			return $dividend/$divisor;
		}
	}

}