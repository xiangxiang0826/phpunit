<?php
/*
 * 统计模块
*  */
class Analyze_IndexController extends ZendX_Controller_Action {
	const PRODUCT_LIMITS = 100;
	const DAY_DIFF = 60;
	const DEFAULT_PRODUCT_ID = 0;
	private $mongo_db = NULL;
	/* 初始化 */
	public function init() {
		parent::init();
		$this->period_map = array(
				'current_month'=>array('start_date'=>date('Y-m-01'),'end_date'=>date('Y-m-d',strtotime('-1 days'))),
				'last_month'=>array('start_date'=>date('Y-m-01',strtotime('-1 month',strtotime("first day of this month"))),'end_date'=>date('Y-m-t', strtotime('last month',strtotime("first day of this month")))),
				//'last_month'=>array('start_date'=>date('Y-m-01',strtotime('-1 month')),'end_date'=>date('Y-m-t', strtotime('last month'))),
				'seven_days'=>array('start_date'=>date('Y-m-d',strtotime('-7 days')),'end_date'=>date('Y-m-d',strtotime('-1 days'))),
				'thirty_days'=>array('start_date'=>date('Y-m-d',strtotime('-30 days')),'end_date'=>date('Y-m-d',strtotime('-1 days'))),
		);
		//获得mongodb配置
		$mongo_cfg = ZendX_Config::get("mongodb","mongodb");
		$connections = "mongodb://{$mongo_cfg['host']}:{$mongo_cfg['port']}";
		//mongodb数据库连接
		$this->mongo = ZendX_Tool::getMongoClient($connections);
	}
	public function indexAction() {
		return false;
	}

	/* 新增用户统计 */
	public function newuserAction() {
		$period = $this->_request->get("period");
		$start_date = $this->_request->get("start_date");
		$end_date = $this->_request->get("end_date");
		$product_id = $this->_request->get("product");
		$enterprise_id = $this->_request->get("enterprise");
		if(empty($start_date) && empty($end_date) && empty($period)) {
			$period = 'thirty_days';
		}
		$start_date = $start_date ? $start_date : date('Y-m-d',strtotime('-30 days'));
		$end_date = $end_date ? $end_date : date('Y-m-d',strtotime('-1 days'));
		if($period) { // 根据时间段设置起始时间
			$period_array = $this->period_map[$period];
			$start_date = $period_array['start_date'];
			$end_date = $period_array['end_date'];
		}
		if($start_date < $end_date) { // 无论如何最小的日期是$starttime，最大的是$endtime；
			$starttime = $start_date;
			$endtime = $end_date;
		} else {
			$starttime = $end_date;
			$endtime = $start_date;
		}
		$product_id = $product_id ? $product_id : self::DEFAULT_PRODUCT_ID;// 默认产品id
		$product_model = new Model_Product();
		$product_row = $product_model->getFieldsById(array('id'),$product_id);
		if(empty($product_row)) {
			return Common_Protocols::generate_json_response(null,Common_Protocols::VALIDATE_FAILED);
		}
		$enterprise = new Model_Enterprise();
		$enterprises =  $enterprise->droupDownData();
		if(empty($enterprise_id)) {
			$enterprise_id = current(array_keys($enterprises));	// 默认是第一个厂商
		}
		$diff_date = Common_Func::timediff($start_date,$end_date);
		if($diff_date['day'] > self::DAY_DIFF) {
			return Common_Protocols::generate_json_response(null,Common_Protocols::VALIDATE_FAILED);
		}
		$products = array();
		$products = $product_model->GetList(array('enterprise_id'=>$enterprise_id, 'status'=>Model_Product::STATUS_ENABLE, 'product_id'=>0), 0, self::PRODUCT_LIMITS);
		/* 以下进行mongodb的数据查询并统计 */
		$charts_data = array();
		$db = "ez_statistics_{$product_id}";
		for($i = $diff_date['day'];$i >= -1;$i--) { // 要比实际的时间跨度多一天，以实现每天与后一天相比
			$date_to_times = strtotime($starttime." +{$i} day");
			$tmp_date = date('Y-m-d',$date_to_times);
			$date_start = new MongoDate(strtotime("{$tmp_date} 00:00:00"));
			$date_end = new MongoDate(strtotime("{$tmp_date} 23:59:59"));
			$charts_data[$tmp_date] = $this->mongo->$db->device_info->find(array('activate_time'=>array('$gte'=>$date_start,'$lte' => $date_end)))->count();
		}
		$this->view->list_data = array_values($charts_data); //保存原始数据，用于列表展示
		$this->view->list_date = array_keys($charts_data);
		array_pop($charts_data);
		$yny_start_date = date('Y-m-d',strtotime("{$starttime} -{$diff_date['day']} days"));
		$yny_end_date = date('Y-m-d',strtotime("{$starttime} -1 day"));
		$yny_start_date = new MongoDate(strtotime("{$yny_start_date} 00:00:00"));
		$yny_end_date = new MongoDate(strtotime("{$yny_end_date} 23:59:59"));
		$yny_total = $this->mongo->$db->device_info->find(array('activate_time'=>array('$gte'=>$yny_start_date,'$lte' => $yny_end_date)))->count();
		/* mondodb 统计数据结束 */
		$this->view->total_data_yny = $yny_total;
		$this->view->total = array_sum(array_values($charts_data));
		$this->view->charts_data = json_encode(array_reverse(array_values($charts_data)));
		$this->view->charts_date = json_encode(array_reverse(array_keys($charts_data)));
		$this->view->products = $products;
		$this->view->enterprises = $enterprises;
		$this->view->enterprise_id = $enterprise_id;
		$this->view->period = $period;
		$this->view->start_date = $start_date;
		$this->view->end_date = $end_date;
		$this->view->product_id = $product_id;
		$this->view->date_step = floor($diff_date['day'] / 10);
	}

	/* 活跃用户统计 */
	public function activeuserAction() {
		$period = $this->_request->get("period");
		$start_date = $this->_request->get("start_date");
		$end_date = $this->_request->get("end_date");
		$product_id = $this->_request->get("product");
		$enterprise_id = $this->_request->get("enterprise");
		if(empty($start_date) && empty($end_date) && empty($period)) {
			$period = 'thirty_days';
		}
		$start_date = $start_date ? $start_date : date('Y-m-d',strtotime('-30 days'));
		$end_date = $end_date ? $end_date : date('Y-m-d',strtotime('-1 days'));
		if($period) { // 根据时间段设置起始时间
			$period_array = $this->period_map[$period];
			$start_date = $period_array['start_date'];
			$end_date = $period_array['end_date'];
		}
		if($start_date < $end_date) { // 无论如何最小的日期是$starttime，最大的是$endtime；
			$starttime = $start_date;
			$endtime = $end_date;
		} else {
			$starttime = $end_date;
			$endtime = $start_date;
		}
		$product_id = $product_id ? $product_id : self::DEFAULT_PRODUCT_ID;// 默认产品id
		$product_model = new Model_Product();
		$product_row = $product_model->getFieldsById(array('id'),$product_id);
		if(empty($product_row)) {
			return Common_Protocols::generate_json_response(null,Common_Protocols::VALIDATE_FAILED);
		}
		$enterprise = new Model_Enterprise();
		$enterprises =  $enterprise->droupDownData();
		if(empty($enterprise_id)) {
			$enterprise_id = current(array_keys($enterprises));	// 默认是第一个厂商
		}
		$diff_date = Common_Func::timediff($start_date,$end_date); // 计算时差
		if($diff_date['day'] > self::DAY_DIFF) {
			return Common_Protocols::generate_json_response(null,Common_Protocols::VALIDATE_FAILED);
		}
		$products = array();
		$products = $product_model->GetList(array('enterprise_id'=>$enterprise_id, 'status'=>Model_Product::STATUS_ENABLE, 'product_id'=>0), 0, self::PRODUCT_LIMITS);

		/* 以下进行mongodb的数据查询并统计 */
		$charts_data = array();
		$db = "ez_statistics_{$product_id}";
		$statistics_data = array();
		$online_cursor = $this->mongo->$db->online_user->find(array('date'=>array('$gte'=>$starttime,'$lte' => $endtime)));
		$active_cursor = $this->mongo->$db->active_user->find(array('date'=>array('$gte'=>$starttime,'$lte' => $endtime)));
		$online_user_data = iterator_to_array($online_cursor);
		$active_user_data = iterator_to_array($active_cursor);
		$statistics_online_map = array();
		$statistics_active_map = array();
		foreach($online_user_data as $item) {
			$statistics_online_map[$item['date']] = $item['num'];
		}
		foreach($active_user_data as $item) {
			$statistics_active_map[$item['date']] = $item['num'];
		}
		$statistics_data = array('online'=>array(),'active'=>array());
		$sort_date = array();
		for($i=$diff_date['day'];$i >= 0;$i--) {
			$date_to_times = strtotime($start_date." +{$i} day");
			$key_date = date('Y-m-d',$date_to_times);
			if(!isset($statistics_online_map[$key_date])) {
				$statistics_data['online'][$key_date] = 0;
			} else {
				$statistics_data['online'][$key_date] = $statistics_online_map[$key_date];
			}
			if(!isset($statistics_active_map[$key_date])) {
				$statistics_data['active'][$key_date] = 0;
			} else {
				$statistics_data['active'][$key_date] = $statistics_active_map[$key_date];
			}
			$sort_date[] = date('Y-m-d',$date_to_times); // 横坐标的日期
		}
		/* mondodb 统计数据结束 */
		$this->view->statistics_data = $statistics_data;
		$this->view->online_data = json_encode(array_reverse(array_values($statistics_data['online'])));
		$this->view->active_data = json_encode(array_reverse(array_values($statistics_data['active'])));
		$this->view->charts_date = json_encode(array_reverse($sort_date));
		$this->view->products = $products;
		$this->view->enterprises = $enterprises;
		$this->view->product_id = $product_id;
		$this->view->enterprise_id = $enterprise_id;
		$this->view->period = $period;
		$this->view->start_date = $start_date;
		$this->view->end_date = $end_date;
		$this->view->date_step = floor($diff_date['day'] / 10);
	}

	/* 使用时长统计 */
	public function usetimeAction() {
		$start_date = $this->_request->get("start_date");
		$product_id = $this->_request->get("product");
		$enterprise_id = $this->_request->get("enterprise");

		$startDate = isset($start_date)?$start_date:date('Y-m-d',strtotime('-1 days'));


		$product_id = $product_id ? $product_id : self::DEFAULT_PRODUCT_ID;// 默认产品id
		$enterprise = new Model_Enterprise();
		$enterprises =  $enterprise->droupDownData();
		if(empty($enterprise_id)) {
			$enterprise_id = current(array_keys($enterprises));	// 默认是第一个厂商
		}
		$product_model = new Model_Product();
		$products = array();
		$products = $product_model->GetList(array('enterprise_id'=>$enterprise_id, 'status'=>Model_Product::STATUS_ENABLE, 'product_id'=>0), 0, self::PRODUCT_LIMITS);
		$analyzeModel = new Model_Analyze();

		$dayStat = array();

		//测试时间
		$db = "ez_statistics_{$product_id}";
		$dayCursor = $this->mongo->$db->cumulative_run_time->find(array('date'=>$startDate));
		$dataSum = 0;
		$data = array();
		$dataPortion = array();
		$dayStat = iterator_to_array($dayCursor);
		if(!empty($dayStat)){
			foreach($dayStat as $v){
				$data = $v['data'];
				$dataSum = array_sum(array_values($data));
			}
			//处理目前 未开机 尚未统计的情况
			if(!empty($data) && count($data)<10)
				array_unshift($data,0);
			
			foreach($data as $vv){
				$dataPortion[] = number_format($vv/$dataSum*100,0).'%';
			}
		}
		if(empty($data)){
			$data = array(0,0,0,0,0,0,0,0,0,0);
			$dataPortion = array('0%','0%','0%','0%','0%','0%','0%','0%','0%','0%');
		}
		$this->view->enterprises = $enterprises;
		$this->view->products = $products;
		$this->view->data = $data;
		$this->view->portion = $dataPortion;
		$this->view->enterprise_id = $enterprise_id;
		$this->view->product_id = $product_id;
		$this->view->start_date = $startDate;
		$this->view->useIndex = $analyzeModel->getUseTimeIndex();
	}

	/* 时间段统计 */
	public function timeAction() {
		$start_date = $this->_request->get("start_date");
		$product_id = $this->_request->get("product");
		$enterprise_id = $this->_request->get("enterprise");

		$startDate = isset($start_date)?$start_date:date('Y-m-d',strtotime('-1 days'));


		$product_id = $product_id ? $product_id : self::DEFAULT_PRODUCT_ID;// 默认产品id
		$enterprise = new Model_Enterprise();
		$enterprises =  $enterprise->droupDownData();
		if(empty($enterprise_id)) {
			$enterprise_id = current(array_keys($enterprises));	// 默认是第一个厂商
		}
		$product_model = new Model_Product();
		$products = array();
		$products = $product_model->GetList(array('enterprise_id'=>$enterprise_id, 'status'=>Model_Product::STATUS_ENABLE, 'product_id'=>0), 0, self::PRODUCT_LIMITS);
		$analyzeModel = new Model_Analyze();

		$dayStat = array();
		$data = array();
		$db = "ez_statistics_{$product_id}";
		$dayCursor = $this->mongo->$db->device_boot_time->find(array('date'=>$startDate));
		$dayStat = iterator_to_array($dayCursor);
		if(!empty($dayStat)){
			foreach($dayStat as $v){
				$data = $v['data'];
			}
		}
		if(empty($data)){
			$data = array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
		}
		$this->view->enterprises = $enterprises;
		$this->view->products = $products;
		$this->view->data = $data;
		$this->view->dataSum = array_sum(array_values($data));
		$this->view->chartsX = json_encode(array_keys($data));
		$this->view->chartsData = json_encode(array_values($data));
		$this->view->enterprise_id = $enterprise_id;
		$this->view->product_id = $product_id;
		$this->view->start_date = $startDate;
		$this->view->tableIndex = $analyzeModel->getTimeIndex();
	}


	/* 用户地域分布统计 */
	public function regionAction() {
		//测试 省市数组
		$analyzeModel = new Model_Analyze();
		$arr = $analyzeModel->getRegionIndex();
		$regionAllPerioad = $regionAll = $arr[0];
		$regionProvinceAll = $regionProvince = $arr[1];
		//测试 END
		$period = $this->_request->get("period");
		$start_date = $this->_request->get("start_date");
		$end_date = $this->_request->get("end_date");
		$product_id = $this->_request->get("product");
		$enterprise_id = $this->_request->get("enterprise");
		if(empty($start_date) && empty($end_date) && empty($period)) {
			$period = 'thirty_days';
		}
		$start_date = $start_date ? $start_date : date('Y-m-d',strtotime('-30 days'));
		$end_date = $end_date ? $end_date : date('Y-m-d',strtotime('-1 days'));
		if($period) { // 根据时间段设置起始时间
			$period_array = $this->period_map[$period];
			$start_date = $period_array['start_date'];
			$end_date = $period_array['end_date'];
		}
		if($start_date < $end_date) { // 无论如何最小的日期是$starttime，最大的是$endtime；
			$starttime = $start_date;
			$endtime = $end_date;
		} else {
			$starttime = $end_date;
			$endtime = $start_date;
		}

		$enterprise = new Model_Enterprise();
		$enterprises =  $enterprise->droupDownData();
		if(empty($enterprise_id)) {
			$enterprise_id = current(array_keys($enterprises));	// 默认是第一个厂商
		}
		$product_id = $product_id ? $product_id : self::DEFAULT_PRODUCT_ID;// 默认产品id
		$product_model = new Model_Product();
		$products = array();
		$products = $product_model->GetList(array('enterprise_id'=>$enterprise_id, 'status'=>Model_Product::STATUS_ENABLE, 'product_id'=>0), 0, self::PRODUCT_LIMITS);

		$product_row = $product_model->getFieldsById(array('id'),$product_id);
		if(empty($product_row)) {
			return Common_Protocols::generate_json_response(null,Common_Protocols::VALIDATE_FAILED);
		}

		$diff_date = Common_Func::timediff($start_date,$end_date);
		if($diff_date['day'] > self::DAY_DIFF) {
			return Common_Protocols::generate_json_response(null,Common_Protocols::VALIDATE_FAILED);
		}

		$dataAll = array();
		$db = "ez_statistics_{$product_id}";
		$date_start = new MongoDate(strtotime("{$starttime} 00:00:00"));
		$date_end = new MongoDate(strtotime("{$endtime} 23:59:59"));
		$cursor = $this->mongo->$db->device_info->find(array('activate_time'=>array('$gte'=>$date_start,'$lte' => $date_end)));
		$cursorAll = $this->mongo->$db->device_info->find(array('activate_time'=>array('$lte' => $date_end)));
		$dataNow = iterator_to_array($cursor);
		$dataAll = iterator_to_array($cursorAll);
		foreach($dataAll as $v){
			if( isset($v['city']) && isset($regionAll[$v['city']])){
				$regionAll[$v['city']]['dataAll']++;//	累计数   对应的city统计++
				if(!empty($regionAll[ $regionAll[$v['city']]['pname']]) || $regionAll[$v['city']]['layer'] == 0){
					//如果是第一级的数据，则按照city字段增加，否则按照父级名增加
					//!empty($regionAll[$regionAll[$v['city']]['pname']])?$regionAll[ $regionAll[$v['city']]['pname']]['dataAll']++ :'' ;
					!empty($regionAll[ $regionAll[$v['city']]['pname']])?$regionProvinceAll[$regionAll[$v['city']]['pname']]++ : $regionProvinceAll[$v['city']]++;
				}
			}else{
				//$regionAll['其他地区']['dataAll']++;
				$regionProvinceAll['其他地区']++;
			}
		}

		foreach($dataNow as $v){
			if( isset($v['city']) && isset($regionAll[$v['city']])){
				$regionAll[$v['city']]['data']++;//新增数  对应的city统计
				if(!empty($regionAll[ $regionAll[$v['city']]['pname']]) || $regionAll[$v['city']]['layer'] == 0){
					//如果是第一级的数据，则按照city字段增加，否则按照父级名增加
					//!empty($regionAll[ $regionAll[$v['city']]['pname']])?$regionAll[ $regionAll[$v['city']]['pname']]['data']++ :'' ;
					!empty($regionAll[ $regionAll[$v['city']]['pname']])?$regionProvince[$regionAll[$v['city']]['pname']]++ : $regionProvince[$v['city']]++;
				}
			}else{
				//$regionAll['其他地区']['data']++;
				$regionProvince['其他地区']++;
			}
		}
		if(isset($_GET['tree']) && $_GET['tree']==1){
			sort($regionAll);
			return Common_Protocols::generate_json_response($regionAll);
		}
		$this->view->queryString = "enterprise=$enterprise_id&product=$product_id&start_date=$start_date&end_date=$end_date";
		$this->view->chartsX = json_encode(array_keys($regionProvince));
		$this->view->chartsAll = json_encode(array_values($regionProvinceAll));
		$this->view->chartsPerioad = json_encode(array_values($regionProvince));
		$this->view->dataPerioad = $regionAll;
		$this->view->dataAll = $regionAllPerioad;
		$this->view->products = $products;
		$this->view->enterprises = $enterprises;
		$this->view->product_id = $product_id;
		$this->view->enterprise_id = $enterprise_id;
		$this->view->period = $period;
		$this->view->start_date = $start_date;
		$this->view->end_date = $end_date;
	}
	
	public function gettreeAction(){
		$analyzeModel = new Model_Analyze();
		$arr = $analyzeModel->getRegionIndex();
		$regionAllPerioad = $regionAll = $arr[0];
		$regionProvinceAll = $regionProvince = $arr[1];
		
		$start_date = $this->_request->get("start_date");
		$end_date = $this->_request->get("end_date");
		$product_id = $this->_request->get("product");
		$enterprise_id = $this->_request->get("enterprise");
		
		$starttime = $start_date;
		$endtime = $end_date;
		
		
		if(empty($enterprise_id)) {
			$enterprise_id = current(array_keys($enterprises));	// 默认是第一个厂商
		}
		$product_id = $product_id ? $product_id : self::DEFAULT_PRODUCT_ID;// 默认产品id
				
		$diff_date = Common_Func::timediff($start_date,$end_date);
		if($diff_date['day'] > self::DAY_DIFF) {
			return Common_Protocols::generate_json_response(null,Common_Protocols::VALIDATE_FAILED);
		}
		
		$dataAll = array();
		$db = "ez_statistics_{$product_id}";
		$date_start = new MongoDate(strtotime("{$starttime} 00:00:00"));
		$date_end = new MongoDate(strtotime("{$endtime} 23:59:59"));
		$cursor = $this->mongo->$db->device_info->find(array('activate_time'=>array('$gte'=>$date_start,'$lte' => $date_end)));
		$cursorAll = $this->mongo->$db->device_info->find(array('activate_time'=>array('$lte' => $date_end)));
		$dataNow = iterator_to_array($cursor);
		$dataAll = iterator_to_array($cursorAll);
		foreach($dataAll as $v){
			if( isset($v['city']) && isset($regionAll[$v['city']])){
				$regionAll[$v['city']]['dataAll']++;//	累计数   对应的city统计++
				if(!empty($regionAll[ $regionAll[$v['city']]['pname']]) || $regionAll[$v['city']]['layer'] == 0){
					//如果是第一级的数据，则按照city字段增加，否则按照父级名增加
					!empty($regionAll[$regionAll[$v['city']]['pname']])?$regionAll[ $regionAll[$v['city']]['pname']]['dataAll']++ :'' ;
				}
			}else{
				$regionAll['其他地区']['dataAll']++;
			}
		}
		
		foreach($dataNow as $v){
			if( isset($v['city']) && isset($regionAll[$v['city']])){
				$regionAll[$v['city']]['data']++;//新增数  对应的city统计
				if(!empty($regionAll[ $regionAll[$v['city']]['pname']]) || $regionAll[$v['city']]['layer'] == 0){
					//如果是第一级的数据，则按照city字段增加，否则按照父级名增加
					!empty($regionAll[ $regionAll[$v['city']]['pname']])?$regionAll[ $regionAll[$v['city']]['pname']]['data']++ :'' ;
				}
			}else{
				$regionAll['其他地区']['data']++;
			}
		}
		sort($regionAll);
		return Common_Protocols::generate_json_response($regionAll);
	}

	/* 构造测试数据,临时接口，只做前端模块构造数据之用 */
	public function createdataAction() {
		return;
		/*

		/* <!----- 以下生成活跃用户和在线用户 */
		/*
		 $base_time = '2014-05-01';
		$db = "ez_statistics_1";
		for($i = 1;$i < 70;$i++) {
		$date_to_times = date('Y-m-d',strtotime($base_time." +{$i} day"));
		$data = array(
				'date' => $date_to_times,
				'num' => rand(0,5000)
		);
		$rtn = $this->mongo->$db->online_user->insert($data);
		$data['num'] = rand(0,5000);
		$rtn = $this->mongo->$db->active_user->insert($data);
		}
		*/
		/* 活跃用户和在线用户生成结束-----> */


		/* 以下生成device_info基本数据 */
		/* 生成device_info数据
		 * count(id) product_id
		2	0
		144	1
		196	2
		140	3
		11	5
		1	13
		1	14
		1	15
		*
		*
		* */
		/*  */
		$device_model = new Model_Device();
		$devices =  $device_model->fetch_all();  //new MongoDate(strtotime('-1 day'));
		foreach($devices as $device) {
			$db = "ez_statistics_{$device['product_id']}";
			$activate_time = new MongoDate(strtotime($this->randomDate('2013-01-01 00:00','2014-07-19 23:59:59')));
			$last_run_time = new MongoDate($this->randomDate('2013-01-01 00:00','2014-07-19 23:59:59'));
			$data = array(
					'rid' => $device['id'],
					'activate_time' => $activate_time,
					'last_run_time' => $last_run_time,
					'last_run_ip' => $this->randomIp(),
					'version' => '1.0.0',
					'country'=>'中国',
					'province'=>'广东',
					'city'=> '深圳',
					'network_access'=>'',
					'upgrade_history'=>'wifi',
					'total_run_time' => rand(10,86220)
			);
			$rtn = $this->mongo->$db->device_info->insert($data);
		}
		/* device_info 生成结束 */
	}

	/**
	 *   生成某个范围内的随机时间
	 * @param <type> $begintime  起始时间 格式为 Y-m-d H:i:s
	 * @param <type> $endtime    结束时间 格式为 Y-m-d H:i:s
	 */
	private function randomDate($begintime, $endtime="") {
		$begin = strtotime($begintime);
		$end = $endtime == "" ? mktime() : strtotime($endtime);
		$timestamp = rand($begin, $end);
		return date("Y-m-d H:i:s", $timestamp);
	}

	private function randomIp() {
		$ip_long = array(

				array('607649792', '608174079'), //36.56.0.0-36.63.255.255

				array('1038614528', '1039007743'), //61.232.0.0-61.237.255.255

				array('1783627776', '1784676351'), //106.80.0.0-106.95.255.255

				array('2035023872', '2035154943'), //121.76.0.0-121.77.255.255

				array('2078801920', '2079064063'), //123.232.0.0-123.235.255.255

				array('-1950089216', '-1948778497'), //139.196.0.0-139.215.255.255

				array('-1425539072', '-1425014785'), //171.8.0.0-171.15.255.255

				array('-1236271104', '-1235419137'), //182.80.0.0-182.92.255.255

				array('-770113536', '-768606209'), //210.25.0.0-210.47.255.255

				array('-569376768', '-564133889'), //222.16.0.0-222.95.255.255

		);
		$rand_key = mt_rand(0, 9);
		return  long2ip(mt_rand($ip_long[$rand_key][0], $ip_long[$rand_key][1]));
	}

}