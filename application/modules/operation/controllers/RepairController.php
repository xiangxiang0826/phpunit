<?php
/**
 * 设备报修控制器
 * @author zhouxh
 *
 */
class Operation_RepairController extends ZendX_Controller_Action {
	
	/**
	 * 设备报修单
	 * @var Model_DeviceRepair
	 */
	protected $modelDeviceRepair;
	/**
	 * 处理记录模型
	 * @var Model_DeviceRepairRecord
	 */
	protected $modelDeviceRepairRecord;
	/**
	 * 产品模型
	 * @var Model_Product
	 */
	protected $modelProduct;
	/**
	 * 企业模型
	 * @var Model_Enterprise
	 */
	protected $modelEnterprise;
	/**
	 * 产品类别模型
	 * @var Model_ProductCate
	 */
	protected $modelProductCate;
	/**
	 * 我的E族会员模型
	 * @var Model_Member
	 */
	protected $modelUser;
	
	/**
	 * 报修列表 
	 */
	public function indexAction(){
		$page = $this->_request->get("page");
		$type = $this->_request->getParam('type', 'pending');
		$search = $this->getRequest()->getQuery('search');
		$page = !empty($page) ? $page : 1;
		$offset = ($page-1) * $this->page_size;
		$where = array();
		if($search) {
			if (!empty($search['number'])) {
				$where['number'] = trim($search['number']);
			}
			if (!empty($search['sn'])) {
				$where['sn'] = trim($search['sn']);
			}
			if(!empty($search['enterprise'])) {
				$where['enterprise'] = $search['enterprise'];
			}
			if(!empty($search['product_id'])) {
				$where['t.product_id'] = $search['product_id'];
			}
			if(!empty($search['status'])) {
				$where["t.`status`"] = $search['status'];
			}
			if(!empty($search['category'])) {
				$categoryIds = $this->getModelProductCate()->getAllChildrenById($search['category']);
				if($categoryIds) {
					$categoryIds[] = $search['category'];
				} else {
					$categoryIds = array($search['category']);
				}
				$where['category'] = $categoryIds;
			}
		}
		$select = $this->getModelDeviceReair()->createSelect($where, $type);
		$paginator = Zend_Paginator::factory($select);
		$paginator->setItemCountPerPage($this->page_size);
		$paginator->setCurrentPageNumber($page);
		$this->view->enterprise = $this->getModelEnterprise()->droupDownData();
		$this->view->category = $this->getModelProductCate()->dumpTree();
		$this->view->products = $this->getModelProduct()->getDroupdownData();
		$this->view->search = $search;
		$this->view->type = $type;
		$this->view->status = $this->getModelDeviceReair()->statusDroupDownData($type);
		$this->view->pagination = $paginator;
	}
	
	/**
	 * 报修详情
	 */
	public function detailAction(){
		$id = $this->_request->get("id");
		if($id) {
			$result = $this->getModelDeviceReair()->getDetailInfo($id);
		}
		if(empty($result)) {
			throw new Zend_Exception('页面不存在', 404);
		}
		if(!empty($result['user_id'])) {
			$result['member'] = $this->getModelUser()->getFieldsById(array('id', 'email', 'name', 'phone', 'reg_time'), $result['user_id']);
		} else {
			$result['member'] = '';
		}
		$cfg = ZendX_Config::get("application","production");
		$baseUrl = $cfg['images']['baseUrl'];
		$records = $this->getModelDeviceRepairRecord()->getRecordList($id);
		$this->view->info = $result;
		$this->view->baseUrl = $baseUrl;
		$this->view->recores = $records;
		$this->view->status = $this->getModelDeviceReair()->statusDroupDownData('all');
	}
	
	/**
	 * 处理
	 */
	public function dealAction(){
		if($this->getRequest()->isPost()) {
			$content = $_POST['content'];
			$_POST['content'] = preg_replace('/\s/', '', $_POST['content']);
			$post = ZendX_Validate::factory($_POST)->labels(array(
							'content'=>'内容',
							'status' => '状态',
							'repair_id'=> 'repair_id',
					));
			$post->rules('repair_id', array(array('not_empty')));
			$post->rules('status', array(
					array('not_empty'),
					array('regex',array(':value','/(^(new|process|finish|cancel|closed)$)/'))));
			$post->rules('content', array(array('not_empty'),array('max_length',array(':value',200))));
			if(!$post->check()) {
				return Common_Protocols::generate_json_response('', Common_Protocols::VALIDATE_FAILED);
			}
			$info = $this->getModelDeviceReair()->getFieldsById('status', $_POST['repair_id']);
			if($info['status'] == Model_DeviceRepair::STATUS_CANCLE) {
				return Common_Protocols::generate_json_response('', Common_Protocols::VALIDATE_FAILED);
			}
			$userInfo = Common_Auth::getUserInfo();
			if($this->getModelDeviceRepairRecord()->addRecord($content, $_POST['repair_id'], $_POST['status'], $userInfo['real_name'])) {
				return Common_Protocols::generate_json_response();
			} 
		}
		return Common_Protocols::generate_json_response('', Common_Protocols::ERROR);
	}
	
	/**
	 * 报修历史
	 */
	public function historyAction(){
		$id = $this->_request->get("id");
		$info = $this->getModelDeviceReair()->getFieldsById('device_id', $id);
		if(empty($info)) {
			throw new Zend_Exception('页面不存在', 404);
		}
		$list = $this->getModelDeviceReair()->getHistoryList($info['device_id']);
		return Common_Protocols::generate_json_response($list);
	}
	
	/**
	 * 获取类实例
	 * @return Model_DeviceRepair
	 */
	public function getModelDeviceReair(){
		return $this->getModel('Model_DeviceRepair');
	}
	
	/**
	 * 报修记录模型
	 * @return Model_DeviceRepairRecord
	 */
	public function getModelDeviceRepairRecord(){
		return $this->getModel('Model_DeviceRepairRecord');
	}
	
	/**
	 * 产品
	 * @return Model_Product
	 */
	public function getModelProduct(){
		return $this->getModel('Model_Product');
	}
	
	/**
	 * 产品类别
	 * @return Model_ProductCate
	 */
	public function getModelProductCate(){
		return $this->getModel('Model_ProductCate');
	}
	
	/**
	 * 企业模型
	 * @return Model_Enterprise
	 */
	public function getModelEnterprise(){
		return $this->getModel('Model_Enterprise');
	}
	
	/**
	 * 获取e族用户模型
	 * @return Model_Member
	 */
	public function getModelUser(){
		return $this->getModel('Model_Member');
	}
	
	
}