<?php
/**
 * app核心包管理管理模块
 * 
 */
class Ezstudio_ApppackageController extends ZendX_Controller_Action
{
	const TYPE_LIMIT = 100;
	public function init() {
		parent::init();
		$this->platform_map = array(
            'ios'=>'ios',
            'android'=>'android',
		);
		$this->status_map = array(
				Model_App_Package::STATUS_ENABLE=>'生效',
				Model_App_Package::STATUS_DISABLE=>'失效',
		);
		
	}
    
    public function indexAction() {
    	$search_data = $this->getRequest()->get('search');
    	$page = $this->_request->get("page");
    	$page = !empty($page) ? $page : 1;
    	$offset = ($page-1) * $this->page_size;
    	$packageModel = new Model_App_Package();
    	$packageList = $packageModel->getList($search_data, $offset,$this->page_size);
    	$this->view->package_list = $packageList['counts'] ? $packageList['list'] : array();
    	$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($packageList['counts']));
    	$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($this->page_size);
    	$this->view->platform_map = $this->platform_map;
    	$this->view->pagenation = $pagenation;
    	$this->view->search = $search_data;
    	$this->view->status_map = $this->status_map;
    }
	
	public function addAction() {
		if($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost('resource');
			if(!$this->checkPost($postData)) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
			$packageModel = new Model_App_Package();
			$row = $packageModel->getRowByField(array('id'), array('version'=>$postData['version'],'platform'=>$postData['platform']));
			if($row) {
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('app_package_exists'));
			}
			$user_info = Common_Auth::getUserInfo();
			$postData['cuser'] = $user_info['user_name'];
			$postData['status'] = Model_App_Package::STATUS_ENABLE;
			$packageModel->insert($postData);
			return Common_Protocols::generate_json_response();
		}
		$this->view->platform_map = $this->platform_map;
	}
	
	/* 更新 */
	public function updateAction() {
		$packageModel = new Model_App_Package();
		if($this->getRequest()->isPost()) {
			$postData = $this->getRequest()->getPost('resource');
			if(empty($postData['id']) || !is_numeric($postData['id'])) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
			if(!$this->checkPost($postData)) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
			$user_info = Common_Auth::getUserInfo();
			$postData['muser'] = $user_info['user_name'];
			$postData['mtime'] = date('Y-m-d H:i:s');
			$packageModel->update($postData, "id = '{$postData['id']}'");
			return Common_Protocols::generate_json_response();
		}
		$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
		$this->view->uploadConfig = $uploadConfig;
		$row = $packageModel->getRowByField(array('id','version','description','file_path','name','platform','check_sum'), array('id'=>$this->getRequest()->get('id')));
		$this->view->package = $row;
		$this->view->platform_map = $this->platform_map;
	}
	
	
	public function deleteAction() {
		if($this->getRequest()->isPost()) {
			$id = $this->getRequest()->getPost('id');
			if(!is_numeric($id)) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
			$packageModel = new Model_App_Package();
			$user_info = Common_Auth::getUserInfo();
			$postData['muser'] = $user_info['user_name'];
			$postData['status'] = Model_App_Package::STATUS_DELETE;
			$packageModel->update($postData, "id = '{$id}'");
			return Common_Protocols::generate_json_response();
		}
	}
	
	/* 核心包输入检测 */
	private function checkPost($data) {
		$post = ZendX_Validate::factory($data)->labels(array(
				'name' => 'name',
				'version' => 'version',
				'file_path' => 'file path',
				'platform' => 'platform',
				'description' => 'description',
		));
		$post->rules(
				'name',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',64))
				)
		);
		$post->rules(
				'version',
				array(
						array('not_empty'),
						array('min_length',	array(':value',	5)),
						array('max_length',array(':value',16))
				)
		);
		$post->rules(
				'description',
				array(
						array('min_length',	array(':value',	5)),
						array('max_length',array(':value',1024))
				)
		);
		$post->rules(
				'file_path',
				array(
						array('not_empty'),
						array('min_length',	array(':value',	5)),
						array('max_length',array(':value',512))
				)
		);
		return $post->check();
	}
}