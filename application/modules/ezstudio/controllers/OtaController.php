<?php
/**
 * Ota升级管理模块
 * 
 */
class Ezstudio_OtaController extends ZendX_Controller_Action
{
	const TYPE_LIMIT = 100;
	public function init() {
		parent::init();
		$this->status_map = array(
            Model_ControlType::STATUS_ENABLE=>'生效',
            Model_ControlType::STATUS_DISABLE=>'失效',
		);
		$this->device_type_map = array(
            Model_Upgrade::DEVICE_TYPE_TEST=>'测试发布',
            Model_Upgrade::DEVICE_TYPE_FORMAL=>'正式发布'
        );
		$this->device_exec_type_map = array(
            Model_Upgrade::DEVICE_EXEC_TYPE_ALL =>'全量升级',
            Model_Upgrade::DEVICE_EXEC_TYPE_INCREMENT =>'增量升级'
        );
	}
    
    public function indexAction() {
        
    }
	
	/**
     * OTA资源列表列表
     * 
     */
	public function listAction() {
		$search_data = $this->getRequest()->get('search');
		$page = $this->_request->get("page");
		$page = !empty($page) ? $page : 1;
		$offset = ($page-1) * $this->page_size;
		$controlModel = new Model_Upgrade();
        $controlList = $controlModel->getList($search_data, $offset,$this->page_size);
        $typeMap = array();
 		$typeList = $controlModel->getTypeList();
        foreach($typeList as $type) {
			$typeMap[$type['id']] = $type;
		}
        $this->view->control_list = $controlList['counts'] ? $controlList['list'] : array();
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($controlList['counts']));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($this->page_size);
		$this->view->type_map = $typeMap;
		$this->view->pagenation = $pagenation;
		$this->view->search = $search_data;
		$this->view->status_map = $this->status_map;
	}
    
	/**
     * OTA资源列表,按版本分类的
     * 
     */
	public function versionAction() {
		$typeId = $this->getRequest()->get('tid');
		$page = $this->_request->get("page");
		$page = !empty($page) ? $page : 1;
		$offset = ($page-1) * $this->page_size;
		$controlModel = new Model_Upgrade();
        $controlList = $controlModel->getListByType(array('tid' => $typeId), $offset, $this->page_size);
        // 
        // $typeId = $this->getRequest()->get('tid');
 		$typeInfo = $controlModel->getFieldsById('name', $typeId);
        $typeInfo['id'] = $typeId;
        // print_r($typeInfo);exit;
        $this->view->control_list = $controlList['counts'] ? $controlList['list'] : array();
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($controlList['counts']));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($this->page_size);
        $this->view->type_info = $typeInfo;
		$this->view->pagenation = $pagenation;
		$this->view->status_map = $this->status_map;
        
	}
	
	/**
	 * 删除版本信息
	 */
	public function delversionAction(){
		if($this->getRequest()->isPost()) {
			$versionId = $this->getRequest()->getPost('id');
			if($versionId && is_numeric($versionId)) {
				$upgradeModel = new Model_Upgrade();
				$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
				try {
					$upgradeTypeId = $upgradeModel->getVersionUpgradeId($versionId);
					$upgradeModel->begin_transaction();
					if($upgradeModel->deleteVersion($versionId, $uploadConfig['basePath'])) {
						$latestVersion = $upgradeModel->GetMaxVersionByIds(array($upgradeTypeId));
						if(empty($latestVersion)) {
							$latestVersion[0]['version'] = '-';
						}
						$upgradeModel->update(array('version' => $latestVersion[0]['version']), 'id=' . $upgradeTypeId);
						$upgradeModel->commit();
						return Common_Protocols::generate_json_response();
					}
				} catch(Exception $e){
					$upgradeModel->rollback();
					return Common_Protocols::generate_json_response(NULL, Common_Protocols::ERROR, $e->getMessage());
				}
			}
		}
		return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
	}
	
	/**
	 * 删除资源
	 */
	public function delresourceAction(){
		if($this->getRequest()->isPost()) {
			$upgradeId = $this->getRequest()->getPost('id');
			if($upgradeId && is_numeric($upgradeId)) {
				$upgradeModel = new Model_Upgrade();
				$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
				try {
					$version = $upgradeModel->getVersionIds($upgradeId);
					$upgradeModel->begin_transaction();
					if($version) {
						foreach ($version as $versionId) {
							$upgradeModel->deleteVersion($versionId, $uploadConfig['basePath']);
						}
					}
					$upgradeModel->deleteUpgradeType($upgradeId);
					$upgradeModel->commit();
					return Common_Protocols::generate_json_response();
				} catch(Exception $e){
					$upgradeModel->rollback();
					return Common_Protocols::generate_json_response(NULL, Common_Protocols::ERROR, $e->getMessage());
				}
			}
		}
		return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
	}
    
	/**
     * OTA类型列表
     * 
     */
	public function typeAction() {
		$search_data = $this->getRequest()->get('search');
		$page = $this->_request->get("page");
		$page = !empty($page) ? $page : 1;
		$offset = ($page-1) * $this->page_size;
		$upgradeModel = new Model_Upgrade();
        $typeListCount = $upgradeModel->getTypeTotal();
		$typeList = $upgradeModel->getTypeList($offset, $this->page_size);
        // print_r($typeListCount);exit(',ln='.__line__);
        $this->view->type_list = $typeList;
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($typeListCount));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($this->page_size);
		$this->view->pagenation = $pagenation;
		$this->view->search = $search_data;
		$this->view->status_map = $this->status_map;
	}
    
	/**
     * 添加资源
     * @return type
     */
	public function addAction() {
		// post方法提交，则保存数据 
		if($this->getRequest()->isPost()) {
            $resourceData = $this->getRequest()->getPost('resource');
			if(!$this->checkPost($resourceData)) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
			$upgradeModel = new Model_Upgrade();
			$row = $upgradeModel->getRowByField(array('id'), array('label'=>$resourceData['label']));
			if($row) {
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('resource_label_exists'));
			}
            $resourceData['cid'] = $resourceData['ota_type'];
            unset($resourceData['ota_type']);
			$upgradeModel->insert($resourceData);
			return Common_Protocols::generate_json_response();
		}
		$otaTypeModel = new Model_Upgrade();
		$typeList = $otaTypeModel->getTypeList();
        $this->view->ota_type = $typeList;
        $this->view->device_type = $this->device_type_map;
        $this->view->device_exec_type = $this->device_exec_type_map;
	}
    
	/**
     * 添加资源版本
     * @return type
     */
	public function addversionAction() {
		// post方法提交，则保存数据 
		if($this->getRequest()->isPost()) {
            $typeId = $this->getRequest()->get('tid');
			$versionData = $this->getRequest()->getPost('version');
			$check = $this->checkVersionPost($versionData);
            if($check !== true) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $check);
			}
            $product_id = '0';
            if(isset($versionData['product_type']) && $versionData['product_type'] == 'special'){
                $product_id = $versionData['product_id']?$versionData['product_id']:'0';
            }
            unset($versionData['product_type']);
            $versionData['product_id'] = $product_id;
			$user_info = Common_Auth::getUserInfo();
			$upgradeModel = new Model_Upgrade();
			try {
				$upgradeModel->begin_transaction();
				if($versionData['device_type'] == Model_Upgrade::DEVICE_TYPE_FORMAL) { 
                    // 如果是正式设备，则将其他的版本全部设置为disable
                    // 去掉以上限制
					// $upgradeModel->UpdateVersion(array('status'=>Model_Upgrade::STATUS_DISABLE), array(" `upgrade_type_id` = '{$versionData['upgrade_type_id']}' AND device_type = '". Model_Upgrade::DEVICE_TYPE_FORMAL ."'"));
				}
				$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
				$versionData['cuser'] = $user_info['user_name'];
				$versionData['publish_status'] = Model_Upgrade::PUBLISHED_STATUS;
				$versionData['status'] = Model_Upgrade::STATUS_ENABLE;
				$upgradeModel->InsertVersion($versionData);
                $latestVersion = $versionData['version'];
                $upgradeModel->UpdateLatestVersion(array('version' => $latestVersion), array('id ="'.$typeId.'"'));
				$upgradeModel->commit();
			} catch(Exception $e) {
				$upgradeModel->rollback();
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::ERROR, $e->getMessage());
			}
			return Common_Protocols::generate_json_response();
		}
		$otaTypeModel = new Model_Upgrade();
		$typeList = $otaTypeModel->getTypeList();
        $typeId = $this->getRequest()->get('tid');
 		$typeInfo = $otaTypeModel->getFieldsById(array('id','name', 'label'), $typeId);
        // $typeInfo['id'] = $typeId;
        // 由于版本号生成特殊，所以需要为新增页面默认分配一个版本号
        $defaultVersion = ZendX_App_Version::make();

        $this->view->default_version = $defaultVersion;
        $this->view->type_info = $typeInfo;
        $this->view->ota_type = $typeList;
        $this->view->device_type = $this->device_type_map;
        $this->view->device_exec_type = $this->device_exec_type_map;
	}
    
	/**
     * 发布资源版本
     * @return type
     */
	public function postversionAction() {
		// post方法提交，则保存数据 
		if($this->getRequest()->isPost()) {
            $versionId = $this->getRequest()->getPost('id');
            if(is_integer($versionId) && $versionId) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
			$upgradeModel = new Model_Upgrade();
            
			try {
                // publish_status   ENUM('unpublished','published'
                $upgradeModel->begin_transaction();
				$upgradeModel->UpdateVersion(array('device_type' => 'formal'), array('id' => $versionId));
				$upgradeModel->commit();
			} catch(Exception $e) {
				$upgradeModel->rollback();
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::ERROR, $e->getMessage());
			}
			return Common_Protocols::generate_json_response();
		}
        return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
	}
    
	/**
     * 编辑资源版本
     * @return type
     */
	public function editversionAction() {
		// post方法提交，则保存数据 
		if($this->getRequest()->isPost()) {
            $typeId = $this->getRequest()->get('tid');
			$versionData = $this->getRequest()->getPost('version');
            $versionData['version'] = '1.0.0.143326';
			$check = $this->checkVersionPost($versionData);
            unset($versionData['version']);
            if($check !== true) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $check);
			}
            $product_id = '0';
            if(isset($versionData['product_type']) && $versionData['product_type'] == 'special'){
                $product_id = $versionData['product_id']?$versionData['product_id']:'0';
            }
            unset($versionData['product_type']);
            $versionData['product_id'] = $product_id;
			$user_info = Common_Auth::getUserInfo();
			$upgradeModel = new Model_Upgrade();
			try {
				$upgradeModel->begin_transaction();
				if($versionData['device_type'] == Model_Upgrade::DEVICE_TYPE_FORMAL) { 
                    // 如果是正式设备，则将其他的版本全部设置为disable
                    // 去掉以上限制
					// $upgradeModel->UpdateVersion(array('status'=>Model_Upgrade::STATUS_DISABLE), array(" `upgrade_type_id` = '{$versionData['upgrade_type_id']}' AND device_type = '". Model_Upgrade::DEVICE_TYPE_FORMAL ."'"));
				}
				$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
				$versionData['cuser'] = $user_info['user_name'];
				$versionData['publish_status'] = Model_Upgrade::PUBLISHED_STATUS;
				$versionData['status'] = Model_Upgrade::STATUS_ENABLE;
                $id = $this->getRequest()->get('id');
                $where = array('id="'.$id.'"');
                // print_r($where);exit;
				$upgradeModel->UpdateVersion($versionData, $where);
				$upgradeModel->commit();
			} catch(Exception $e) {
				$upgradeModel->rollback();
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::ERROR, $e->getMessage());
			}
			return Common_Protocols::generate_json_response();
		}
		$otaTypeModel = new Model_Upgrade();
		$typeList = $otaTypeModel->getTypeList();
        $id = $this->getRequest()->get('id');
        $info = $otaTypeModel->getVersionByField('*', array('id' => $id));
        $productNames = '';
        $productId = $info['product_id'];
        $productNameArr = array();
        
        if('0' != $productId){
            $productModel = new Model_Product();
            $productArr = explode(',', $productId);
            // print_r($productArr);exit(',ln='.__line__);
            $productInfo = $productModel->FindByIds($productArr);
            foreach($productInfo as $item){
                $productNames .= $item['name'];
                $productNameArr[$item['id']] = $item['name'];
            }
        }
        $typeId = $this->getRequest()->get('tid');
 		$typeInfo = $otaTypeModel->getFieldsById(array('id','name', 'label'), $typeId);
        // $typeInfo['id'] = $typeId;   static_domain
 		$settings = ZendX_Config::get('application', 'settings');
 		$this->view->static_domain = "http://{$settings['download_domain']}";
        $this->view->type_info = $typeInfo;
        $this->view->ota_type = $typeList;
        $this->view->info = $info;
        $this->view->product_names = $productNames;
        $this->view->product_json = json_encode($productNameArr);
        $this->view->device_type = $this->device_type_map;
        $this->view->device_exec_type = $this->device_exec_type_map;
	}
    
	/**
     * 添加资源类型
     * @return type
     */
	public function addtypeAction() {
		// post方法提交，则保存数据 
		if($this->getRequest()->isPost()) {
            $typeData = $this->getRequest()->getPost('type');
            
			if(!$this->checkTypePost($typeData)) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
			$upgradeModel = new Model_Upgrade();
            $where = array('name'=>$typeData['name']);
            $whereWithout = array();
            $tid = $this->getRequest()->get('tid');
            if($tid){
                $whereWithout = array('id' => $tid);
            }
			$row = $upgradeModel->getCategoryRowByField(array('id'), $where, $whereWithout);
			if($row) {
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('resource_category_exists'));
			}
            if($tid){
                // print_r($tid);exit;
                $upgradeModel->updateCategory($typeData, array('`id` = "'.$tid.'"'));
                
            }else{
                $upgradeModel->insertCategory($typeData);
            }
			
			return Common_Protocols::generate_json_response();
		}
        $tid = $typeData = $this->getRequest()->get('tid');
        $info = array(
            'name' => '',
            'description' => ''
        );
        if($tid){
           $this->view->tid = $tid;
           $upgradeModel = new Model_Upgrade();
           $info = $upgradeModel->getCategoryRowByField(array('name', 'description'), array('id' => $tid));
        }
        $this->view->info = $info;
	}
    
    public function updatedataAction(){
        include APPLICATION_PATH."/../library/third_party/Flurry.class.php";
        $access = '77NFR797HKZ72TWDYZGX';
        $appkey = 'HYHS59M9JY8KYBX9TJGS';
        $startDate = '2014-10-01';
        $endDate = '2014-10-10';
        $obj = new Flurry($access, $appkey);
        $data = $obj->getMetricsByType($typeArr = array('ActiveUsers'), $startDate, $endDate);
        print_r($data);exit(',ln='.__line__);
    }
    
	/**
     * 删除资源类型
     * @return type
     */
	public function deletetypeAction() {
		// post方法提交，则保存数据 
		if($this->getRequest()->isPost()) {
            $typeId = $this->getRequest()->getPost('tid');
            if(is_integer($typeId) && $typeId) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
			$upgradeModel = new Model_Upgrade();
			try {
                $upgradeModel->begin_transaction();
                //删除之前要判断是否该类型下存在相关的类型
                $categoryInfo = $upgradeModel->getList(array('cid' => $typeId) , 0, 10);
                if(isset($categoryInfo['counts']) && $categoryInfo['counts'] > 0 ){
                    return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('resource_category_remove_notice'));
                }
				$upgradeModel->deleteCategory('id="'.$typeId.'"');
				$upgradeModel->commit();
			} catch(Exception $e) {
				$upgradeModel->rollback();
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::ERROR, $e->getMessage());
			}
			return Common_Protocols::generate_json_response();
		}
        return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
	}
    
    /* 检查版本提交的内容是否合法 */
	private function checkVersionPost($data) {
		$post = ZendX_Validate::factory($data)->labels(array(
				'version' => 'Version',
				'file_path' => 'File Path',
                'exec_type' => 'exec type',
                'device_type' => 'device type'
		));
		$post->rules(
				'version',
				array(
						array('not_empty'),
						array('regex', array(':value', '/^[1-9]\.[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{6}$/'))
				)
		);
		$post->rules(
				'exec_type',
				array(
						array('not_empty')
				)
		);
		$post->rules(
				'device_type',
				array(
						array('not_empty')
				)
		);
		$post->rules(
				'product_type',
				array(
						array('not_empty')
				)
		);
		$post->rules(
				'file_path',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',512))
				)
		);
		$check = $post->check();
		if($check) return true;
		$errors = $post->errors('validate');
		return array_shift($errors);
	}
    
	/* 增加、更新控件类型是输入检测 */
	private function checkPost($data) {
		$post = ZendX_Validate::factory($data)->labels(array(
				'name' => 'name',
				'label' => 'label',
                'ota_type' => 'ota_type',
                'description' => 'description',
		));
		$post->rules(
				'name',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',20))
				)
		);
		$post->rules(
				'label',
				array(
                        array('not_empty'),
						array('min_length',	array(':value',	2)),
						array('max_length',array(':value',50))
				)
		);
		$post->rules(
				'description',
				array(
						array('min_length',	array(':value',	2)),
						array('max_length',array(':value',100))
				)
		);
        
        
		return $post->check();
	}
    
	/* 增加、更新控件类型是输入检测 */
	private function checkTypePost($data) {
		$post = ZendX_Validate::factory($data)->labels(array(
				'name' => 'name',
                'description' => 'description',
		));
		$post->rules(
				'name',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value', 20))
				)
		);
		$post->rules(
				'description',
				array(
						array('min_length',	array(':value',	2)),
						array('max_length',array(':value', 100))
				)
		);
		return $post->check();
	}
}