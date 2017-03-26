<?php
/* 
 * 运营模块
 *  */
class Operation_IndexController extends ZendX_Controller_Action
{
	const DAY_DIFF = 60;
	/**
	 * 初始化
	 */
	public function init() {
		parent::init();
	
		$this->period_map = array(
				'current_month'=>array(
						'start_date'=>date('Y-m-01'),
						'end_date'=>date('Y-m-d',strtotime('-1 days')),
				),
				'last_month'=>array(
						'start_date'=>date('Y-m-01',strtotime('-1 month',strtotime("first day of this month"))),
						'end_date'=>date('Y-m-t',strtotime('-1 month',strtotime("first day of this month"))),
						'former_start'=>date('Y-m-01',strtotime('-2 month',strtotime("first day of this month"))),
						'former_end'=>date('Y-m-t',strtotime('-2 month ',strtotime("first day of this month"))),
				),
				'seven_days'=>array(
						'start_date'=>date('Y-m-d',strtotime('-7 days')),
						'end_date'=>date('Y-m-d',strtotime('-1 days')),
				),
				'thirty_days'=>array(
						'start_date'=>date('Y-m-d',strtotime('-30 days')),
						'end_date'=>date('Y-m-d',strtotime('-1 days')),
				),
		);
	}
	
	public function indexAction() {
		$dateTypes = array('current_month'=>'本月', 'last_month'=>'上月', 'seven_days'=> '近7天', 'thirty_days'=>'近30天');
		$date = $this->getRequest()->getParam('date');
		$start_date = $this->getRequest()->getParam('start_date');
		$end_date = $this->getRequest()->getParam('end_date');
		
		if((empty($start_date) && empty($end_date)) && (empty($date) || !array_key_exists($date, $dateTypes))) {
			$date = 'thirty_days';
		}
		
		$start_date = $start_date ? $start_date : date('Y-m-01');
		$end_date = $end_date ? $end_date : date('Y-m-d',strtotime('-1 days'));
		
		if($date) { // 根据时间段设置起始时间
			$period_array = $this->period_map[$date];
			$start_date = $period_array['start_date'];
			$end_date = $period_array['end_date'];
			$start_former = isset($period_array['former_start']) ? $period_array['former_start'] : 0;
			$end_former = isset($period_array['former_end']) ? $period_array['former_end'] : 0;
		}

		if($start_date < $end_date) { // 无论如何最小的日期是$starttime，最大的是$endtime
			$starttime = $start_date;
			$endtime = $end_date;
			$date_diff = round( abs(strtotime($end_date)-strtotime($start_date)) / 86400, 0 );
			$date_diff += 1;
			$start_former = (isset($start_former) && $start_former !=0) ? $start_former : date('Y-m-d',strtotime("-{$date_diff} days",strtotime($starttime)));
			$end_former = (isset($end_former) && $end_former !=0) ? $end_former : date('Y-m-d',strtotime("-1 days",strtotime($starttime)));
		} else {
			$starttime = $end_date;
			$endtime = $start_date;
			$date_diff = round( abs(strtotime($start_date)-strtotime($end_date)) / 86400, 0 );
			$date_diff += 1;
			$start_former = (isset($start_former) && $start_former !=0) ? $start_former : date('Y-m-d',strtotime('-1 days',strtotime($end_date)));
			$end_former = (isset($end_former) && $end_former !=0) ? $end_former : date('Y-m-d',strtotime("-{$date_diff} month",strtotime($endtime)));
		}
		
		$diff_date = Common_Func::timediff($start_date,$end_date);
		if($diff_date['day'] > self::DAY_DIFF) {
			return Common_Protocols::generate_json_response(null,Common_Protocols::VALIDATE_FAILED);
		}
		
		$ModelFeedback = new Model_Feedback();
		$charts_data = array();
		for($i = $diff_date['day'];$i >= 0;$i--) {
			$date_to_times = strtotime($starttime." +{$i} day");
			$tmp_date = date('Y-m-d',$date_to_times);
			$date_start = "{$tmp_date} 00:00:00";
			$date_end = "{$tmp_date} 23:59:59";
			$todayCount = $ModelFeedback->getCountInDay($date_start,$date_end);
			$charts_data[$tmp_date] = intval($todayCount['total']);
		}

		// 查询范围内的总数 根据 deviceid 分组
		$start_date = "{$start_date} 00:00:00";
		$end_date = "{$end_date} 23:59:59";
		$total_this = $ModelFeedback->getCountByGroupDeviceId($start_date,$end_date);
		$start_former = "{$start_former} 00:00:00";
		$end_former = "{$end_former} 23:59:59";
		$total_former = $ModelFeedback->getCountByGroupDeviceId($start_former,$end_former);

		$showlist = $data_total = array();
		$count_old = $count_new = $count_baifen = $count_up = '0';// 数据段部分
		if(!empty($total_this)){
			foreach ($total_this as $k=>$v){
				$showlist[$v['pid']]['name'] = $v['p_name'];
				$showlist[$v['pid']]['this'] = $v['total'];
				$showlist[$v['pid']]['former'] = '0';
				$showlist[$v['pid']]['upper'] = '';
				$showlist[$v['pid']]['load'] = '-';
				$count_new += $v['total'];
			}
		}
		
		if(!empty($total_former)){
			foreach ($total_former as $v){
				if(!isset($showlist[$v['pid']]['this'])){
					$showlist[$v['pid']]['this'] = 0;
					//continue;
				}
				$showlist[$v['pid']]['former'] = $v['total'];
				$count_old += $v['total'];
				if(!isset($showlist[$v['pid']]['name'])){
					$showlist[$v['pid']]['name'] = $v['p_name'];
				}
				if($v['total'] != 0 && $v['total'] != ''){
					$showlist[$v['pid']]['load'] = round((($showlist[$v['pid']]['this'] - $v['total'] ) / $v['total']) * 100 , 2);
					$showlist[$v['pid']]['load'] .= '%';
				}else{
					$showlist[$v['pid']]['load'] = '-';
				}

				if($v['total'] > $showlist[$v['pid']]['this']){
					$showlist[$v['pid']]['upper'] = 'no';
				}else if($v['total'] < $showlist[$v['pid']]['this']){
					$showlist[$v['pid']]['upper'] = 'yes';
				}
			}
		}
		
		if($count_old != 0){
			$count_baifen = round((($count_new - $count_old) / $count_old) * 100, 2) . '%';
		}else{
			$count_baifen = '-';
		}
		if($count_new > $count_old){
			$count_up = 1;
		}else{
			$count_up = 0;
		}
		
		$data_total = array('count_old'=>$count_old,
				'count_new'=>$count_new,
				'count_baifen'=>$count_baifen,
				'count_up'=>$count_up
		);
		
		$this->view->date = $date;
		$this->view->start_date=$start_date;
		$this->view->end_date=$end_date;
		$this->view->showlist=$showlist;// 环比对比数据
		$this->view->data_total=$data_total;// 环比对比数据
		$this->view->total = array_sum(array_values($charts_data));
		$this->view->charts_data = json_encode(array_reverse(array_values($charts_data)));
		$this->view->charts_date = json_encode(array_reverse(array_keys($charts_data)));
		$this->view->date_step = floor($diff_date['day'] / 10);
	}
}