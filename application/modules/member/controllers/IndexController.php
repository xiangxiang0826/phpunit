<?php
/* 
 * 会员模块
 * 2014-07-08 17:01
 * liujd@wondershare.cn
 */
class Member_IndexController extends ZendX_Controller_Action {
	
	private $status_map = array();
	const ENTERPRISE_LIMITS = 50;
	const DAY_DIFF = 60;
	public function init() {
		parent::init();
		$this->status_map = array(
			Model_Member::STATUS_ENABLE=>'正常',
			Model_Member::STATUS_LOCKED=>'冻结',
			// Model_Member::STATUS_REMOVE=>'移除',
			// Model_Member::STATUS_UNACTIVATED=>'未激活',
		);
		$this->period_map = array(
				'current_month'=>array('start_date'=>date('Y-m-01'),'end_date'=>date('Y-m-d',strtotime('-1 days'))),
				'last_month'=>array('start_date'=>date('Y-m-01',strtotime('-1 month',strtotime("first day of this month"))),'end_date'=>date('Y-m-t', strtotime('last month',strtotime("first day of this month")))),
				'seven_days'=>array('start_date'=>date('Y-m-d',strtotime('-7 days')),'end_date'=>date('Y-m-d',strtotime('-1 days'))),
				'thirty_days'=>array('start_date'=>date('Y-m-d',strtotime('-30 days')),'end_date'=>date('Y-m-d',strtotime('-1 days'))),
		);
	}
	public function indexAction() {
		$ez_cfg = ZendX_Config::get('application', 'ez');
		$page = $this->_request->get("page");
		$page = !empty($page) ? $page : 1;
		$offset = ($page-1) * $this->page_size;
		$enterprise_model = new Model_Enterprise();
		$enterprise_list = $enterprise_model->getList("`status` = '". Model_Enterprise::STATUS_ENABLE ."'", 0, self::ENTERPRISE_LIMITS);
		// array_unshift($enterprise_list, $ez_cfg);
		$member_model = new Model_Member();
		$search_data = $this->getRequest()->get('search');
		$this->view->search = $search_data;
		$list = $member_model->getList($search_data, $offset,$this->page_size);
		$this->view->list = $list['counts'] ? $list['list'] : array();
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($list['counts']));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($this->page_size);
		$this->view->pagenation = $pagenation;
		$this->view->enterprise_list = $enterprise_list;
		$this->view->status_map = $this->status_map;
	}
	
	public function overviewAction() {
		$period = $this->_request->get("period");
		$start_date = $this->_request->get("start_date");
		$end_date = $this->_request->get("end_date");
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
		$diff_date = Common_Func::timediff($start_date,$end_date);
		if($diff_date['day'] > self::DAY_DIFF) {
			return Common_Protocols::generate_json_response(null,Common_Protocols::VALIDATE_FAILED);
		}
		/* 以下进行Mysql的数据查询并统计 */
		$ez_config = ZendX_Config::get('application','ez');
		$ez_total_map = array();
		$et_total_map = array();
		$member_model = new Model_Member();
		$member_list = $member_model->statByDate($starttime, "{$endtime} 23:59:59");
		foreach($member_list as $list) {
			$date_key = date('Y-m-d', strtotime($list['reg_time']));
			if(!isset($ez_total_map[$date_key]))  $ez_total_map[$date_key] = 0;
			if(!isset($et_total_map[$date_key]))  $et_total_map[$date_key] = 0;
			if(in_array($list['platform'],explode(',',$ez_config['ez_platform_label']))) {
				$ez_total_map[$date_key]++;
			} else {
				$et_total_map[$date_key]++;
			}
		}
		$statistics_total = array();
		$statistics_ez = array();
		$statistics_et = array();
		$statistics_sum = array();
		for($i = $diff_date['day'];$i >= 0;$i--) { // 每天统计
			$date_to_times = strtotime($starttime." +{$i} day");
			$tmp_date = date('Y-m-d',$date_to_times);
			$statistics_ez[$tmp_date] = isset($ez_total_map[$tmp_date]) ? $ez_total_map[$tmp_date] : 0;
			$statistics_et[$tmp_date] = isset($et_total_map[$tmp_date]) ? $et_total_map[$tmp_date] : 0;
			$statistics_total[$tmp_date] = $statistics_ez[$tmp_date] + $statistics_et[$tmp_date];
			$sum_data = $member_model->StatByEndDate("{$tmp_date} 23:59:59"); //到今天为止，累计会员总数
			$statistics_sum[$tmp_date] = $sum_data ? $sum_data['counts'] : 0;
		}
		$this->view->statistics_sum = $statistics_sum;
		$this->view->statistics_total = $statistics_total;
		$this->view->statistics_ez = $statistics_ez;
		$this->view->statistics_et = $statistics_et;
		$this->view->charts_date = json_encode(array_reverse(array_keys($statistics_total)));
		$this->view->statistics_total_json = json_encode(array_reverse(array_values($statistics_total)));
		$this->view->statistics_ez_json = json_encode(array_reverse(array_values($statistics_ez)));
		$this->view->statistics_et_json = json_encode(array_reverse(array_values($statistics_et)));
		$this->view->period = $period;
		$this->view->start_date = $start_date;
		$this->view->end_date = $end_date;
		$this->view->date_step = floor($diff_date['day'] / 10);
	}
	
	/* 会员详情 */
	public function detailAction() {
		$id = $this->_request->get("id");
		$device_model = new Model_Device();
		$member_model = new Model_Member();
		$member_info = $member_model->getInfoByUid($id);
		$bind_devices = $device_model->getDeviceByUid($id);
		$product_model = new Model_Product();
		if($bind_devices) {
			$product_ids = array();
			foreach($bind_devices as $device) {
				$product_ids[] = $device['product_id'];
			}
			$products = $product_model->FindByIds($product_ids);
			foreach($products as $product) {
				$product_map[$product['id']] = $product;
			}
			$this->view->product_map = $product_map;
		}
		$ez_config = ZendX_Config::get('application','ez');
		if(in_array($member_info['platform'],explode(',',$ez_config['ez_platform_label']))) {
			$member_info['platform'] =  $this->view->getHelper('t')->t('ez');
		} else {
			$model_enterprise = new Model_Enterprise();
			$platform = explode('_',$member_info['platform']);
			$et_info = $model_enterprise->getRowByField('name',array('label'=>$platform[0]));
			$member_info['platform'] = isset($et_info['name']) ? $et_info['name'] : '';
		}
		$member_info['bind_devices'] = $bind_devices;
		$this->view->member_info = $member_info;
		$this->view->status_enable = Model_Member::STATUS_ENABLE;
	}
	
	/* 设置会员状态 */
	public function setstatusAction() {
		$status = $this->_request->getPost("status");
		$uid = $this->_request->getPost("uid");
		$member_model = new Model_Member();
		$result = $member_model->update(array('status'=>$status),array("id = '{$uid}'"));
		return  Common_Protocols::generate_json_response();
	}
	
}