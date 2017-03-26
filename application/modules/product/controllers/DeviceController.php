<?php

class Product_DeviceController extends ZendX_Controller_Action {
	const  ENTERPRISE_LIMITS = 100;
	const  PRODUCT_LIMITS = 100;
	private $_enterpriseModel, $_categoryModel, $_deviceManagerModel, $_productModel, $_deviceModel, $_gearmanModel;
	public function init() {
		parent::init();
		$this->_enterpriseModel = new Model_Enterprise();
		$this->_categoryModel = new Model_ProductCate();
		$this->_deviceManagerModel = new Model_DeviceManager();
		$this->_productModel = new Model_Product();
		$this->_deviceModel = new Model_Device();
		//$this->_gearmanModel = new ZendX_GearmanClient();
		$this->apply_type_map = array(
				Model_DeviceManager::SUPPLY_FORMAL=>'正式',
				Model_DeviceManager::SUPPLY_TEST=>'测试',
		);
		
		$this->status_map = array(
				Model_DeviceManager::AUDIT_FAILED  => '审核失败',
				Model_DeviceManager::AUDIT_PENDING => '待审核',
				Model_DeviceManager::AUDIT_SUCCESS =>'审核成功',
				Model_DeviceManager::STATUS_PROCESSING =>'生成中',
				Model_DeviceManager::STATUS_PENDING =>'待生成',
				Model_DeviceManager::STATUS_COMPLETE =>'可用',
		);
	}
		
	public function applyidAction() {
		$upload_cfg = $this->getInvokeArg('bootstrap')->getOption('upload');
		$search_data = $this->getRequest()->get('search');
		$page = $this->_request->get("page");
		$page = !empty($page) ? $page : 1;
		$offset = ($page-1) * $this->page_size;
		$enterprise_list = array();
		$enterprise_ary = $this->_enterpriseModel->getList("`status` != '". Model_Enterprise::STATUS_DELETED ."'", 0, self::ENTERPRISE_LIMITS);
		foreach($enterprise_ary as $enterprise) {
			$enterprise_list[$enterprise['id']] = $enterprise;
		}
		$category_array = $this->_categoryModel->getList();
		$category_list = array();
		foreach($category_array as $category) {
			$category_list[$category['id']] = $category;
		}
		$products = $this->_productModel->GetList(array(), 0, self::PRODUCT_LIMITS);
		$product_list = array();
		foreach($products['list'] as $product) {
			$product_list[$product['id']] = $product;
		}
		$device_list = $this->_deviceManagerModel->getList($search_data, $offset,$this->page_size);
		$this->view->apply_list = $device_list['counts'] ? $device_list['list'] : array();
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($device_list['counts']));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($this->page_size);
		
		$this->view->upload_cfg = $upload_cfg;
		$this->view->category_list = $category_list;
		$this->view->pagenation = $pagenation;
		$this->view->product_list = $product_list;
		$this->view->enterprise_list = $enterprise_list;
		$this->view->apply_type_map = $this->apply_type_map;
		$this->view->search = $search_data;
		$this->view->status_map = $this->status_map;
	}
	
	/* 申请详情 */
	public function applydetailAction() {
		$id = (int)$this->_request->get("id");
		$detail_info = $this->_deviceManagerModel->GetDetail(array('id'=>$id));
		return  Common_Protocols::generate_json_response($detail_info, Common_Protocols::SUCCESS);
	}
	
	/* 审核设备ID的申请 */
	public function auditapplyAction() {
		$notice_map = $this->_request->getPost("audit_notice");
		$id = (int)$this->_request->getPost("id");
		$data['status'] = $this->_request->getPost("audit_status",Model_DeviceManager::AUDIT_FAILED);
		$data['remark'] = $this->_request->getPost("remark");
		$result = $this->_deviceManagerModel->Audit($data,$id);
		if($notice_map) {
			$detail_info = $this->_deviceManagerModel->GetDetail(array('id'=>$id));
			$enterpriseInfo = $this->_enterpriseModel->getFieldsById(array('name', 'mobile', 'email'), $detail_info['enterprise_id']);
			if($data['status'] == Model_DeviceManager::AUDIT_FAILED) {
				$audit_msg = "未能通过审核，原因是：{$data['remark']}";
			} else {
				$audit_msg = "通过审核";
				// 以下调用gearman异步发起任务
				$gearman = new ZendX_GearmanClient();
				$gearman->call('Device', 'export', array('manager_id'=>$id));
			}
			$body = "您提交的DeviceID申请{$audit_msg}。（遥控e族企业云平台）";
			/* 短信提醒 */
			if(in_array('email',$notice_map)) {
				$to_address =  array(array('email' => $enterpriseInfo['email'], 'name' => $enterpriseInfo['name']));
				$subject = '遥控e族企业云平台通知';
				ZendX_Tool::sendEmail($body, $to_address, $subject);
			}
			if(in_array('sms',$notice_map)) {
				ZendX_Tool::sendSms($enterpriseInfo['mobile'], $body);
			}
		}
		return  Common_Protocols::generate_json_response();
	}
	
	/**
	 * 设备管理列表
	 */
	public function indexAction()
	{
		//查询版本列表
		$page = $this->getRequest()->getParam('page', 1);
		$search = $this->_request->getParam("search");
		$condition = array();
		if(!empty($search['device_id'])) {
			$condition['device_id'] = trim($search['device_id']);
		}
		if(!empty($search['name'])) {
			$condition['name'] = trim($search['name']);
		}
		if(!empty($search['status'])) {
			$condition['D.status'] = $this->_getRealStatus($search['status']);
		}
		if(!empty($search['device_type'])) {
			$condition['type'] = $search['device_type'];
		}
		if(!empty($search['enterprise_id'])) {
			$condition['E.id'] = $search['enterprise_id'];
		}
		if(!empty($search['category'])) {
			$categoryIds = $this->_categoryModel->getAllChildrenById($search['category']);
			if($categoryIds) {
				$categoryIds[] = $search['category'];
			} else {
				$categoryIds = array($search['category']);
			}
			$condition['category'] =  $categoryIds;
		}
		$select = $this->_deviceModel->getCondition($condition);
		$paginator = Zend_Paginator::factory($select);
		$paginator->setItemCountPerPage($this->page_size);// 测试分页 =1
		$paginator->setCurrentPageNumber($page);
        $statusArr = $this->_deviceModel->getStatusMap();
        $status = $this->_renewStatus($statusArr);
		$this->view->paginator = $paginator;
		$this->view->enterprises =  $this->_enterpriseModel->droupDownData();
		$this->view->search = $search;
		$this->view->category = $this->_categoryModel->dumpTree();
		$this->view->status = $status;
	}
    
	/**
	 * 设备详情
	 */
	public function detailAction(){
		$id = $this->_request->getParam('id');
		if($id) {
			$info = $this->_deviceModel->getDetailById($id);
			$bindUser = $this->_deviceModel->getBinderById($info['id']);
			$info['bind_user'] = $bindUser;
            $info['status'] = $this->_getNewStatus($info['status']);
			$this->view->device = $info;
            $statusArr = $this->_deviceModel->getStatusMap();
            $statusArr = $this->_renewStatus($statusArr);
			$this->view->status = $statusArr;
		}
	}
	/**
	 * 更新设备状态，类型
     * 
     * @modified by etong<zhoufeng@wondershare.cn> 
	 */
	public function saveAction(){
        // 
		$deviceId = $this->getParam('id');
		$data = $_POST;
		$validate = true;
		if(empty($data['type'])) {
			$validate = false;
		}
		if(empty($data['status'])) {
			$validate = false;
		}
		if(!in_array($data['type'], array(Model_Device::DEVICE_TYPE_FORMAL, Model_Device::DEVICE_TYPE_TEST))) {
			$validate = false;
		}
		$statusArr = $this->_deviceModel->getStatusMap();
        $statusArr = $this->_renewStatus($statusArr);
		if(!in_array($data['status'], array_keys($statusArr))) {
			$validate = false;
		}
		//验证通过
		if($validate && $deviceId) {
			try {
				$where = $this->_deviceModel->get_db()->quoteInto("id=?", $deviceId);
                if($data['status'] == 'new'){
                    $data['status'] = Model_Device::STATUS_FACT_ACTIVATE;
                }
				$this->_deviceModel->update($data, $where);
                return  Common_Protocols::generate_json_response();
			} catch (Exception $e) {
				throw new Zend_Exception('更新失败');
			}
		} else {
			throw new Zend_Exception('验证失败');
		}
	}
    
    /**
     * 刷新状态数组在前端的显示
     * 
     * @param array $statusArr
     * @return array
     */
    private function _renewStatus($statusArr = array()){
        $checkArr = array(Model_Device::STATUS_NEW, Model_Device::STATUS_FACT_ACTIVATE);
        $newStatusArr = array();
        foreach($statusArr as $k => $v){
            if(in_array($k, $checkArr)){
                $newStatusArr['new'] = '新的';
            }else{
                $newStatusArr[$k] = $v;
            }
        }
        return $newStatusArr;
    }
    
    /**
     * 获取状态的真实条件
     * 
     * @param string $status
     * @return string
     */
    private function _getRealStatus($status){
        $checkArr = array(Model_Device::STATUS_NEW, Model_Device::STATUS_FACT_ACTIVATE);
        if($status == 'new'){
            $newStatus = '"'.  implode('", "', $checkArr).'"';
        }else{
            $newStatus = '"'.$status.'"';
        }
        return $newStatus;
    }
    
    /**
     * 获取状态的新的对外的名称
     * 
     * @param string $status
     * @return string
     */
    private function _getNewStatus($status){
        $checkArr = array(Model_Device::STATUS_NEW, Model_Device::STATUS_FACT_ACTIVATE);
        $newStatus = $status;
        if(in_array($status, $checkArr)){
            $newStatus = 'new';
        }
        return $newStatus;
    }
    /**
     * 删除设备
     */
    public function deleteAction(){
    	$deviceId = $this->getParam('id');
    	$relation = $this->getParam('del_relation');
    	if(empty($deviceId)) {
    		throw new Zend_Exception('参数错误');
    	}
    	$devcieType = $this->_deviceModel->getFieldsById('type', $deviceId);
    	if($devcieType == 'formal') {
    		throw new Zend_Exception('正式设备不能删除');
    	}
    	$res = $this->_deviceModel->deleteDevice($deviceId, $relation);
    	if($res) {
    		return Common_Protocols::generate_json_response();
    	} else {
    		return Common_Protocols::generate_json_response(null, Common_Protocols::ERROR);
    	}
    }
    
}