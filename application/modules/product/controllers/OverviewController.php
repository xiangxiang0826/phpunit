<?php
/* 
 * 产品概况模块
 * 2014-07-26 19:36:30
 * liujd@wondershare.cn
 */
class Product_OverviewController extends ZendX_Controller_Action {
	const DAY_DIFF = 60;
	public function init() {
		parent::init();
		$this->period_map = array(
				'current_month'=>array('start_date'=>date('Y-m-01'),'end_date'=>date('Y-m-d',strtotime('-1 days'))),
				'last_month'=>array('start_date'=>date('Y-m-01',strtotime('-1 month',strtotime("first day of this month"))),'end_date'=>date('Y-m-t', strtotime('last month',strtotime("first day of this month")))),
				'seven_days'=>array('start_date'=>date('Y-m-d',strtotime('-7 days')),'end_date'=>date('Y-m-d',strtotime('-1 days'))),
				'thirty_days'=>array('start_date'=>date('Y-m-d',strtotime('-30 days')),'end_date'=>date('Y-m-d',strtotime('-1 days'))),
		);
	}
	
	public function indexAction() {
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
		$device_model = new Model_Device();
		$device_list = $device_model->statByDate($starttime, "{$endtime} 23:59:59");
		$device_total_map = array();
		$product_total_map = array();
		$product_id_array = array();
		$enterprise_id_array = array();
		foreach($device_list as $list) {
			$date_key = date('Y-m-d', strtotime($list['user_activate_time']));
			if(!isset($device_total_map[$date_key]))  $device_total_map[$date_key] = 0;
			if(!isset($product_total_map[$list['product_id']]))  $product_total_map[$list['product_id']] = 0;
			$device_total_map[$date_key]++;
			$product_total_map[$list['product_id']]++;
			$product_id_array[] = $list['product_id'];
		}
		$statistics_total = array();
		for($i = $diff_date['day'];$i >= 0;$i--) { // 每天统计
			$date_to_times = strtotime($starttime." +{$i} day");
			$tmp_date = date('Y-m-d',$date_to_times);
			$statistics_total[$tmp_date] = isset($device_total_map[$tmp_date]) ? $device_total_map[$tmp_date] : 0;
		}
		$enterprise_stat_map = array();
		$product_map = array();
		$product_model = new Model_Product();
		$products = $product_model->FindByIds($product_id_array);
		foreach($products as $product) {
			$product_map[$product['id']] = $product;
			$enterprise_id_array[] = $product['enterprise_id'];
			if(!isset($enterprise_stat_map[$product['enterprise_id']]))  $enterprise_stat_map[$product['enterprise_id']] = $product_total_map[$product['id']];
			else $enterprise_stat_map[$product['enterprise_id']] += $product_total_map[$product['id']]; //根据产品的企业ID组合企业的设备数
		}
		$enterprise_map = array();
		$enterprise_model = new Model_Enterprise();
		$enterprises = $enterprise_model->FindByIds($enterprise_id_array);
		foreach($enterprises as $ent) {
			$enterprise_map[$ent['id']] = $ent;
		}
		$yny_start_date = date('Y-m-d',strtotime("{$starttime} -{$diff_date['day']} days"));
		$yny_end_date = date('Y-m-d 23:59:59',strtotime("{$starttime} -1 day"));
		$yny_product = array();
		foreach($product_map as $product_id => $value) {
			$counts = $device_model->CountByProduct($yny_start_date, $yny_end_date, array($product_id));
			$yny_product[$product_id] = $counts['counts'];
		}
		$yny_enterprise = array(); // 商家环比数据
		foreach($enterprise_map as $enterprise_id => $et) {
			$products_array = array();
			$et_products = $product_model->GetList(array('enterprise_id'=>$enterprise_id), 0, 100);
			foreach($et_products['list'] as $li) {
				$products_array[] = $li['id'];
			}
			$counts = $device_model->CountByProduct($yny_start_date, $yny_end_date, $products_array);
			$yny_enterprise[$enterprise_id] = $counts['counts'];
		}
		arsort($product_total_map);
		arsort($enterprise_stat_map);
		$this->view->yny_enterprise = $yny_enterprise;
		$this->view->yny_product = $yny_product;
		$this->view->enterprise_map = $enterprise_map;
		$this->view->product_map = $product_map;
		$this->view->product_rank_list = $product_total_map;
		$this->view->enterprise_rank_list = $enterprise_stat_map;
		$this->view->statistics_total = array_sum(array_values($statistics_total));
		$this->view->yny_total = array_sum(array_values($yny_product));
		$this->view->yny_total_et = array_sum(array_values($yny_enterprise));
		$this->view->charts_date = json_encode(array_reverse(array_keys($statistics_total)));
		$this->view->statistics_total_json = json_encode(array_reverse(array_values($statistics_total)));
		$this->view->period = $period;
		$this->view->start_date = $start_date;
		$this->view->end_date = $end_date;
		$this->view->date_step = floor($diff_date['day'] / 10);
	}
}