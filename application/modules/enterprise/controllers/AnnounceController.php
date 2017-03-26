<?php
/**
 * 厂商消息管理控制器
 * @author zhouxh
 *
 */
class EnterPrise_AnnounceController extends ZendX_Controller_Action
{
	private $enterpriseModel;
	private $announceModel;
	public function init() {
		parent::init();
		$this->enterpriseModel = new Model_Enterprise();
		$this->announceModel = new Model_Announce();
	}
	/**
	 * 厂商消息列表
	 */
	public function indexAction(){
		$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$rows = isset($_GET['rows']) ? intval($_GET['rows']) : $this->page_size;
		$offset = ($page-1)*$rows;
		$query = "";
		$arrQuery = array();
		$search = $this->_request->getParam("search");
		if(!empty($search['enterprise_id'])){
			$queryString = "CONCAT(CONCAT(',',t.enterprise_ids),',') LIKE CONCAT(CONCAT('%,', {$search['enterprise_id']}),',%')";
			array_push($arrQuery, $queryString);
		}
		if(!empty($search['status'])){
			$queryString = "t.status = '". $search['status'] ."'";
			array_push($arrQuery, $queryString);
		}
		if(!empty($search['start']) && !empty($search['end'])) {
			$queryString = "(DATE_FORMAT(t.actual_time, '%Y-%m-%d') >='{$search['start']}' AND DATE_FORMAT(t.actual_time, '%Y-%m-%d') <= '{$search['end']}')";
			array_push($arrQuery, $queryString);
		} else if(!empty($search['start'])) {
			$queryString = "(DATE_FORMAT(t.actual_time, '%Y-%m-%d') >='{$search['start']}' )";
			array_push($arrQuery, $queryString);
		} else if(!empty($search['end'])) {
			$queryString = "(DATE_FORMAT(t.actual_time, '%Y-%m-%d') <='{$search['end']}' )";
			array_push($arrQuery, $queryString);
		}
		if(!empty($arrQuery)){
			$query = implode(" AND ", $arrQuery);
		}

		$result = array();
		$row = $this->announceModel->getTotal($query);
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($row));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($rows);
		$items = $this->announceModel->getList($query, $offset, $rows);
		$status = $this->announceModel->getStatusMap();
		$statusAll = $this->announceModel->getStatusMapAll();
		$enterpriseArr = $this->enterpriseModel->droupDownData();
		$readPortionIDArr = array();
		foreach($items as $k=>$v){
			$items[$k]['status_name'] = $statusAll[$v['status']];
			$items[$k]['enterprise_name'] = $this->announceModel->ids2Name($v['enterprise_ids'],$enterpriseArr);
			if($v['status'] == 'finished')
				$readPortionIDArr[] = $v['id'];
		}

		$readPortionArr = $this->announceModel->getPortion($readPortionIDArr);
		foreach($items as $k=>$v){
			if(in_array($v['status'],array(Model_Announce::STATUS_FINISHED,Model_Announce::STATUS_AUDIT_SUCCESS)) && isset($readPortionArr[$v['id']])){
				$items[$k]['portion'] = number_format($readPortionArr[$v['id']]['old']/$readPortionArr[$v['id']]['alls']*100,2).'%';
			}else{
				$items[$k]['portion'] = '-';
			}
		}

		$result['rows'] = $items;
		$this->view->enterprises =  $enterpriseArr;
		$this->view->status =  $status;
		$this->view->results =  $result;
		$this->view->pagenation = $pagenation;
		$this->view->search = $search;
	}

	/**
	 * 审核公告
	 */
	public function auditAction() {
		$announceId  = $this->getRequest()->getParam('id');
		if(empty($announceId)) {
			throw new Zend_Exception('参数错误');
		}
		if($this->getRequest()->isPost()) {
			$this->announceModel->update(array('status'=>$_POST['pass'],'remark'=>$_POST['remark'],'actual_time'=>date('Y-m-d H:i:s', time())), array("id = '{$_POST['id']}'"));
			return Common_Protocols::generate_json_response();
		}
		$info = $this->announceModel->getFieldsById('*', $announceId);
		if (!empty($info['enterprise_ids'])) {
			$enterpriseNameArr = $this->announceModel->getEnterpriseName($info['enterprise_ids']);
			$info['enterprise_name'] = '';
			foreach($enterpriseNameArr as $v){
				$info['enterprise_name'] .= $v['company_name'].'，';
			}
			$info['enterprise_name'] = rtrim($info['enterprise_name'],'，');
		}
		$this->view->info = $info;
	}

	/**
	 * 查看公告
	 */
	public function viewAction(){
		$announceId  = $this->getRequest()->getParam('id');
		if($announceId) {
			$info = $this->announceModel->getFieldsById('*', $announceId);
			if (!empty($info['enterprise_ids'])) {
				$enterpriseNameArr = $this->announceModel->getEnterpriseName($info['enterprise_ids']);
				$info['enterprise_name'] = '';
				foreach($enterpriseNameArr as $v){
					$info['enterprise_name'] .= $v['company_name'].'，';
				}
				$info['enterprise_name'] = rtrim($info['enterprise_name'],'，');
			}
			$this->view->info = $info;
		} else {
			throw new Zend_Exception('参数错误');
		}
	}

	/**
	 * 添加公告
	 */
	public function addAction(){
		$announceId  = $this->getRequest()->getParam('id');
		$this->view->announceId = $announceId;
		if($this->getRequest()->isPost()) {
			$data = $_POST['announce'];
			$send_type = isset($_POST['send_type']) ? $_POST['send_type'] : NULL;
			$validate = $this->validateMessage($data, $send_type);
			if($validate !== true) {
				return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED, $validate);
			}
			$user_info = Common_Auth::getUserInfo();
			$data['status'] = Model_Announce::STATUS_PENDING;
			$data['sender'] = $user_info['user_name'];
			$data['ctime'] = date('Y-m-d H:i:s', time());
			$data['content'] = addslashes($data['content']);
			if($this->announceModel->insert($data)) {
				return Common_Protocols::generate_json_response();
			}
			return Common_Protocols::generate_json_response(null, Common_Protocols::ERROR);
		}
	}

	/**
	 * 添加或者修改公告
	 */
	public function editAction(){
		$announceId  = $this->getRequest()->getParam('id');
		$this->view->announceId = $announceId;
		if($this->getRequest()->isPost()) {
			$data = $_POST['announce'];
			$validate = $this->validateMessage($data,$_POST['send_type']);
			if($validate !== true) {
				return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED, $validate);
			}
			if($_POST['send_type'] == 'all'){
				$data['enterprise_ids'] = '';
			}
			$data['content'] = addslashes($data['content']);
			$data['status'] = Model_Announce::STATUS_PENDING;
			$this->announceModel->update($data, "id = '{$announceId}'");
			return  Common_Protocols::generate_json_response();
		}
		$info = $this->announceModel->getFieldsById('*', $announceId);
		if (!empty($info['enterprise_ids'])) {
			$sql = 'SELECT id,company_name FROM EZ_ENTERPRISE WHERE id IN (' . $info['enterprise_ids'] .')';
			$enterpriseArr = $this->announceModel->get_db()->fetchAll($sql);
			$enterpriseNameArr = $enterpriseJsonArr = array();
			foreach($enterpriseArr as $v){
				$enterpriseNameArr[] = $enterpriseJsonArr[$v['id']] = $v['company_name'];
			}
			$enterprise = join("，",$enterpriseNameArr);
			$info['enterprise_name'] = $enterprise;
			$info['enterprise_str_info'] = json_encode($enterpriseJsonArr);
		}
		$this->view->info = $info;
	}

	/**
	 * 获取弹框中的企业信息
	 */
	public function enterpriseAction(){
		//查询版本列表
		$page = $this->getRequest()->getParam('page', 1);
		$search = $this->_request->getParam("search");
		$condition = array();
		$select = $this->enterpriseModel->get_db()->select();
		$select->from(array('t'=>'EZ_ENTERPRISE'));
		if(!empty($search['enterprise_id'])) {
			$select->where('t.id =?', $search['enterprise_id']);
		}
		$select->where("t.status = 'enable'");
		$select->order('t.reg_time DESC');
		$paginator = Zend_Paginator::factory($select);
		$paginator->setItemCountPerPage(10);// 测试分页 =1
		$paginator->setCurrentPageNumber($page);
		$this->view->paginator = $paginator;
		$this->view->enterprises =  $this->enterpriseModel->droupDownData();
		$this->view->search = $search;
		echo $this->view->render('announce_enterprise.phtml');
	}

	/**
	 * 删除消息
	 */
	public function deleteAction(){
		$announceId  = $this->getRequest()->getParam('id');
		if($announceId) {
			$result = $this->announceModel->getFieldsById(array( 'status'), $announceId);
			$sql = $this->announceModel->get_db()->quoteInto('id = ?',$announceId);
			if($this->announceModel->get_db()->delete('EZ_ANNOUNCE', $sql)) {
				return Common_Protocols::generate_json_response();
			}
			return Common_Protocols::generate_json_response(null, Common_Protocols::ERROR);
		}
	}

	/**
	 * 消息添加验证
	 * @param array $data
	 * @return boolean|Ambigous <multitype:, multitype:string >
	 */
	protected  function validateMessage($data,$sendType){
		if($sendType=='special' && empty($data['enterprise_ids'])) return '特定厂商没有指定厂商！';
		$post = ZendX_Validate::factory($data)->labels(array(
				'title' => 'Title',
				'content' => 'content',
				'send_time' => 'send_time'
		));
		$post->rules(
				'title',
				array(
						array('not_empty'),
						array('max_length',array(':value',32))
				)
		);
		$post->rules(
				'content',
				array(
						array('not_empty')
				)
		);
		if($post->check()){
			return true;
		}
		return current($post->errors('validate'));

	}
	/* 检查待审核 */
	public function needchecknumAction() {
        $where = "status IN ('". Model_Announce::STATUS_PENDING ."')";
		$info = $this->announceModel->getTotal($where);
		$info['result'] = $info['total'];
		return Common_Protocols::generate_json_response($info);
	}

	public function __set($name,$val){
		$this->$name = $val;
	}

}