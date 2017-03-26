<?php

class Ezstudio_FirmwareController extends ZendX_Controller_Action {
	/**
	 * 厂商MCU固件、自研通讯固件管理
	 */
	const ENTERPRISE_LIMITS = 100;
	const PRODUCT_LIMITS = 100;
	
	public function init() {
		parent::init();
		$this->status_map = array(
			Model_FirmwareMcu::STATUS_ENABLE => '启用',
			Model_FirmwareMcu::STATUS_DISABLE =>'停用',
		);
		$this->exec_type_map = array(
			Model_FirmwareMcu::EXEC_ALL => '全量',
			Model_FirmwareMcu::EXEC_INCREMENT =>'增量',
		);
		$this->device_type_map = array(Model_Upgrade::DEVICE_TYPE_TEST=>'测试发布',Model_Upgrade::DEVICE_TYPE_FORMAL=>'正式发布');
	}
	
	public function indexAction() {
		return true;
	}
	
	/* 通讯固件 */
	public function commAction() {
		$search_data = $this->getRequest()->get('search');
		$page = $this->_request->get("page");
		$page = !empty($page) ? $page : 1;
		$offset = ($page-1) * $this->page_size;
		$fireware_comm_model = new Model_FirmwareComm();
		$upgrade_model = new Model_Upgrade();
		$fireware_comm_list = $fireware_comm_model->getList($search_data, $offset,$this->page_size);
		$this->view->fireware_comm_list = $fireware_comm_list['counts'] ? $fireware_comm_list['list'] : array();
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($fireware_comm_list['counts']));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($this->page_size);
		$id_ary = array();
		$upgrade_type_id_ary = array();
		foreach($fireware_comm_list['list'] as $fireware) {
			$id_ary[] = $fireware['id'];
			$upgrade_type_id_ary[] = $fireware['upgrade_type_id'];
		}
		$products = $id_ary ?  $fireware_comm_model->GetProducts($id_ary) : array();
		$max_versions = $upgrade_model->GetMaxVersionByIds($upgrade_type_id_ary);
		$max_version_map = array();
		$product_count = array();
		foreach($products as $count) {
			$product_count[$count['firmware_comm_id']] = $count['counts'];
		}
		foreach($max_versions as $version) {
			$max_version_map[$version['upgrade_type_id']] = $version['version'];
		}
		$firmware_type_map = array();
		$type_model = new Model_FirmwareType();
		$type_list = $type_model->getList(array('id'=>1));//只找wifi固件
		foreach($type_list as $type) {
			$firmware_type_map[$type['id']] = $type;
		}
		$this->view->product_count_map = $product_count;
		$this->view->version_map = $max_version_map;
		$this->view->firmware_type_list = $firmware_type_map;
		$this->view->pagenation = $pagenation;
		$this->view->search = $search_data;
	}
	
	/**
	 * 删除固件
	 */
	public function delrecordAction(){
		if($this->getRequest()->isPost()) {
			$id = $this->getRequest()->getPost('id');
			if($id && is_numeric($id)) {
				$firewareModel = new Model_FirmwareComm();
				$where = 'id =' . $firewareModel->quote($id);
				if($firewareModel->update(array('status' => 'deleted'), $where)) {
					return Common_Protocols::generate_json_response();
				}
			}
		}
		return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
	}
	
	/* 通讯固件版本信息 */
	public function commversionAction() {
		$page = $this->_request->get("page");
		$page = !empty($page) ? $page : 1;
		$offset = ($page-1) * $this->page_size;
		$id = $this->_request->get("id");
		$fireware_comm_model = new Model_FirmwareComm();
		$fireware_comm = $fireware_comm_model->getRowByField(array('firmware_type_id','name','description','upgrade_type_id','sdk_url','label'),array('id'=>$id));
		$upgrade_model = new Model_Upgrade();
		$version_list = $upgrade_model->ListVersion(array('upgrade_type_id'=>array($fireware_comm['upgrade_type_id']),'product_id'=>0), $offset,$this->page_size);
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($version_list['counts']));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($this->page_size);
		$this->view->version_list = $version_list['counts'] ? $version_list['list'] : array() ;
		$this->view->device_type_map = $this->device_type_map;
		$this->view->status_map = $this->status_map;
		$this->view->fireware_comm = $fireware_comm;
		$this->view->id = $id;
		$this->view->pagenation = $pagenation;
	}
	
	/* 通讯固件详情 */
	public function commdetailAction() {
		$id = $this->_request->get("id");
		$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
		$type_model = new Model_FirmwareType();
		$firmware_model = new Model_FirmwareComm();
		$fireware_comm_detail = $firmware_model->getRowByField(array('id','firmware_type_id','name','description','upgrade_type_id','sdk_url','label','ctime','mtime'),array('id'=>$id));
		$products = $firmware_model->GetProductMap($id);
		$this->view->products = $products;
		$type_info = $type_model->getRowByField(array('id','name','status','label'),array('id'=>$fireware_comm_detail['firmware_type_id']));
		$this->view->type_info = $type_info;
		$images = $firmware_model->GetImages($id);
		$fireware_comm_detail['images'] = $images;
		$upgrade_model = new Model_Upgrade();
		$latest_version = $upgrade_model->GetLatestVersion($fireware_comm_detail['upgrade_type_id']);
		$this->view->latest_version = $latest_version;
		$this->view->fireware_comm_detail = $fireware_comm_detail;
		$this->view->uploadConfig = $uploadConfig;
	}
	
	/* 添加通讯固件 */
	public function addAction() {
		/* post方法提交，则保存数据 */
		if($this->getRequest()->isPost()) {
			$firmware_data = $this->getRequest()->getPost('firmware');
			$version_data = $this->getRequest()->getPost('version');
			$firmware_imgs = isset($firmware_data['images']) ? $firmware_data['images'] : NULL;
			unset($firmware_data['images']);	
			if(empty($version_data['file_path'])) { // 无固件包
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('firmware_package_not_null'));
			}
			$check = $this->checkFirmwarePost($firmware_data);
			if($check !== true) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $check);
			}
			$firmware_model = new Model_FirmwareComm();
			$firmware_row = $firmware_model->getRowByField(array('id'),array('label'=>$firmware_data['label']));
			if($firmware_row) { // 判断label是否存在
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('firmware_label_exists'));
			}
			$check = $this->checkVersionPost($version_data);
			if($check !== true) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $check);
			}
			$user_info = Common_Auth::getUserInfo();
			$upgrade_model = new Model_Upgrade();
			try {
				$firmware_model->begin_transaction();
				// 插入新的upgrade_type
				$upgrade_type['label'] = Common_Func::CreateFirmwareLabel($firmware_data['label']);
				$upgrade_type['name'] = $firmware_data['name'];
				$upgrade_type['description'] = $firmware_data['description'];
				$upgrade_type['version'] = $version_data['version'];
				$upgrade_type_id = $upgrade_model->insert($upgrade_type);// 保存upgrade_type
				$firmware_data['upgrade_type_id'] = $upgrade_type_id;
				$version_data['cuser'] = $firmware_data['cuser'] = $user_info['user_name'];
				$firmware_id = $firmware_model->insert($firmware_data);// 保存通讯固件
				$version_data['upgrade_type_id'] = $upgrade_type_id;
				$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
				$version_data['check_sum'] = $version_data['check_sum'];// 保存版本
				$version_data['name'] = $firmware_data['name'];
				$version_data['publish_status'] = Model_Upgrade::PUBLISHED_STATUS;
				$version_data['status'] = Model_Upgrade::STATUS_ENABLE;
				$upgrade_model->InsertVersion($version_data);
				if($firmware_imgs) {
					$firmware_model->SaveImages($firmware_imgs,$firmware_id);
				}
				$firmware_model->commit();
			} catch(Exception $e) {
				$firmware_model->rollback();
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::ERROR, $e->getMessage());
			}
			return Common_Protocols::generate_json_response();
		}
		$firmware_type_map = array();
		$type_model = new Model_FirmwareType();
		$type_list = $type_model->getList(array('id'=>1)); //只有wifi固件
		foreach($type_list as $type) {
			$firmware_type_map[$type['id']] = $type;
		}
		$this->view->firmware_type_list = $firmware_type_map;
		$this->view->device_type = $this->device_type_map;
		$this->view->upgrade_type = array('提示升级','强制升级');
	}
	
	/* 更新通讯固件 */
	public function editAction() {
		/* post方法提交，则保存数据 */
		if($this->getRequest()->isPost()) {
			$firmware_data = $this->getRequest()->getPost('firmware');
			$firmware_imgs = isset($firmware_data['images']) ? $firmware_data['images'] : NULL;
			unset($firmware_data['images']);
			$check = $this->checkFirmwarePost($firmware_data);
			if($check !== true) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $check);
			}
			
			$user_info = Common_Auth::getUserInfo();
			$firmware_model = new Model_FirmwareComm();
			try {
				$firmware_model->begin_transaction();
				$firmware_data['muser'] = $user_info['user_name'];
				$firmware_data['mtime'] = date('Y-m-d H:i:s');
				$firmware_model->update($firmware_data, array("id = '{$firmware_data['id']}'"));// 保存通讯固件
				if($firmware_imgs) {
					$firmware_model->SaveImages($firmware_imgs,$firmware_data['id']);
				}
				$firmware_model->commit();
			} catch(Exception $e) {
				$firmware_model->rollback();
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::ERROR, $e->getMessage());
			}
			return Common_Protocols::generate_json_response();
		}
		$id = $this->_request->get("id");
		$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
		$firmware_type_map = array();
		$type_model = new Model_FirmwareType();
		$firmware_model = new Model_FirmwareComm();
		$fireware_comm_detail = $firmware_model->getRowByField(array('id','firmware_type_id','name','description','upgrade_type_id','sdk_url','label'),array('id'=>$id));
		$type_list = $type_model->getList();
		$images = $firmware_model->GetImages($id);
		foreach($type_list as $type) {
			$firmware_type_map[$type['id']] = $type;
		}
		$fireware_comm_detail['images'] = $images;
		$this->view->firmware_type_list = $firmware_type_map;
		$this->view->item = $fireware_comm_detail;
		$this->view->uploadConfig = $uploadConfig;
	}
	
	/* 版本升级 */
	public function upgradeAction() {
		/* post方法提交，则保存数据 */
		$id = $this->_request->get("id");
		$firmware_model = new Model_FirmwareComm();
		if($this->getRequest()->isPost()) {
			$version_data = $this->getRequest()->getPost('version');
			$check = $this->checkVersionPost($version_data);
			if($check !== true) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $check);
			}
			$user_info = Common_Auth::getUserInfo();
			$upgrade_model = new Model_Upgrade();
			try {
				$upgrade_model->begin_transaction();
				if($version_data['device_type'] == Model_Upgrade::DEVICE_TYPE_FORMAL) { //如果是正式设备，则将其他的版本全部设置为disable
					//$upgrade_model->UpdateVersion(array('status'=>Model_Upgrade::STATUS_DISABLE), array(" `upgrade_type_id` = '{$version_data['upgrade_type_id']}' AND device_type = '". Model_Upgrade::DEVICE_TYPE_FORMAL ."'"));
				}
				$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
				$version_data['cuser'] = $user_info['user_name'];
				$version_data['publish_status'] = Model_Upgrade::PUBLISHED_STATUS;
				$version_data['status'] = Model_Upgrade::STATUS_ENABLE;
				$upgrade_model->InsertVersion($version_data);
				$firmware_model->update(array('mtime'=>date('Y-m-d H:i:s'),'muser'=>$user_info['user_name']), "id = '{$id}'");
				$upgrade_model->commit();
			} catch(Exception $e) {
				$upgrade_model->rollback();
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::ERROR, $e->getMessage());
			}
			return Common_Protocols::generate_json_response();
		}
		$upgrade_model = new Model_Upgrade();
		$fireware_detail = $firmware_model->getRowByField(array('id','firmware_type_id','name','description','upgrade_type_id','sdk_url','label'),array('id'=>$id));
		$firmware_type_map = array();
		$type_model = new Model_FirmwareType();
		$fireware_detail['version'] = $upgrade_model->GetLatestVersion($fireware_detail['upgrade_type_id']);
		$fireware_detail['firmware_type'] = $type_model->getRowByField(array('id','name'),array('id'=>$fireware_detail['firmware_type_id']));
		$this->view->fireware_detail = $fireware_detail;
		$this->view->device_type = $this->device_type_map;
		$this->view->upgrade_type = array('提示升级','强制升级');
	}
	
	/* 设置固件发布类型 */
	public function setdevicetypeAction() {
		if($this->getRequest()->isPost()) { 
			$id = $this->getRequest()->getPost('id');
			$device_type = $this->getRequest()->getPost('device_type');
			$device_type_map = $this->device_type_map;
			unset($device_type_map[$device_type]);
			$device_type = current(array_keys($device_type_map));
			$version_data['device_type'] = $device_type;
			$user_info = Common_Auth::getUserInfo();
			$version_data['muser'] = $user_info['user_name'];
			$version_data['mtime'] = date('Y-m-d H:i:s');
			$upgrade_model = new Model_Upgrade();
			$version_info = $upgrade_model->getVersionByField(array('id','upgrade_type_id','device_type','status'),array('id'=>$id));
			try {
				$upgrade_model->begin_transaction();
				if($device_type == Model_Upgrade::DEVICE_TYPE_FORMAL && $version_info['status'] == Model_Upgrade::STATUS_ENABLE) {
					$upgrade_model->UpdateVersion(array('status'=>Model_Upgrade::STATUS_DISABLE), array(" `upgrade_type_id` = '{$version_info['upgrade_type_id']}' AND device_type = '". Model_Upgrade::DEVICE_TYPE_FORMAL ."'"));
				}
				$upgrade_model->UpdateVersion($version_data, array(" `id` = '{$id}'"));
				$upgrade_model->commit();
			} catch(Exception $e) {
				$upgrade_model->rollback();
			}
			return Common_Protocols::generate_json_response();
		}
		
	}
	
	/* 设置固件发布状态 */
	public function setstatusAction() {
		if($this->getRequest()->isPost()) {
			$id = $this->getRequest()->getPost('id');
			$status = $this->getRequest()->getPost('status');
			$status_map = $this->status_map;
			unset($status_map[$status]);
			$status = current(array_keys($status_map)); // 状态切换
			$version_data['status'] = $status;
			$user_info = Common_Auth::getUserInfo();
			$version_data['muser'] = $user_info['user_name'];
			$version_data['mtime'] = date('Y-m-d H:i:s');
			$upgrade_model = new Model_Upgrade();
			$version_info = $upgrade_model->getVersionByField(array('id','upgrade_type_id','device_type','status'),array('id'=>$id));
			try {
				$upgrade_model->begin_transaction();
				if($version_info['device_type'] == Model_Upgrade::DEVICE_TYPE_FORMAL && $status == Model_Upgrade::STATUS_ENABLE) {
					//$upgrade_model->UpdateVersion(array('status'=>Model_Upgrade::STATUS_DISABLE), array(" `upgrade_type_id` = '{$version_info['upgrade_type_id']}' AND device_type = '". Model_Upgrade::DEVICE_TYPE_FORMAL ."'"));
				}
				$upgrade_model->UpdateVersion($version_data, array(" `id` = '{$id}'"));
				$upgrade_model->commit();
			} catch(Exception $e) {
				$upgrade_model->rollback();
			}
			return Common_Protocols::generate_json_response();
		}
	}
	
	/* 更新版本 */
	public function editversionAction() {
		/* post方法提交，则保存数据 */
		if($this->getRequest()->isPost()) {
			$version_data = $this->getRequest()->getPost('version');
			$check = $this->checkVersionPost($version_data);
			if($check !== true) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $check);
			}
			$user_info = Common_Auth::getUserInfo();
			$upgrade_model = new Model_Upgrade();
			try {
				$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
				$version_data['muser'] = $user_info['user_name'];
				$version_data['mtime'] = date('Y-m-d H:i:s');
				$upgrade_model->UpdateVersion($version_data, array(" `id` = '{$version_data['id']}'"));
			} catch(Exception $e) {
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::ERROR, $e->getMessage());
			}
			return Common_Protocols::generate_json_response();
		}
		$id = $this->_request->get("id");
		$vid = $this->_request->get("vid");
		$firmware_model = new Model_FirmwareComm();
		$upgrade_model = new Model_Upgrade();
		$type_model = new Model_FirmwareType();
		$version_detail = $upgrade_model->getVersionByField(array('id','upgrade_type_id','version','name','description','check_sum','file_path','device_type','is_force'),array('id'=>$id));
		$fireware_detail = $firmware_model->getRowByField(array('id','firmware_type_id','name','description','upgrade_type_id','sdk_url','label'),array('upgrade_type_id'=>$version_detail['upgrade_type_id']));
		$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
		$version_detail['full_path'] = "{$uploadConfig['baseUrl']}{$version_detail['file_path']}";
		$version_detail['file_name'] = basename($version_detail['file_path']);
		$fireware_detail['firmware_type'] = $type_model->getRowByField(array('id','name'),array('id'=>$fireware_detail['firmware_type_id']));
		$this->view->fireware_detail = $fireware_detail;
		$this->view->version_detail = $version_detail;
		$this->view->device_type = $this->device_type_map;
		$this->view->upgrade_type = array('提示升级','强制升级');
		$this->view->vid = $vid;// 返回版本页面的记录id
	}
	
	/* mcu固件 */
	public function mcuAction() {
		$search_data = $this->getRequest()->get('search');
		$page = $this->_request->get("page");
		$page = !empty($page) ? $page : 1;
		$offset = ($page-1) * $this->page_size;
		
		$enterprise_model = new Model_Enterprise();
		$enterprise_list = array();
		$enterprise_ary = $enterprise_model->getList("`status` = '". Model_Enterprise::STATUS_ENABLE ."'", 0, self::ENTERPRISE_LIMITS);
		foreach($enterprise_ary as $enterprise) {
			$enterprise_list[$enterprise['id']] = $enterprise;
		}
		$fireware_mcu_model = new Model_FirmwareMcu();
		$product_model = new Model_Product();
		$products = $product_model->GetList(array('product_id'=>0), 0, self::PRODUCT_LIMITS);
		$product_list = array();
		foreach($products['list'] as $product) {
			$product_list[$product['id']] = $product;
		}
		$fireware_mcu_list = $fireware_mcu_model->getList($search_data, $offset,$this->page_size);
		$this->view->fireware_mcu_list = $fireware_mcu_list['counts'] ? $fireware_mcu_list['list'] : array();
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($fireware_mcu_list['counts']));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($this->page_size);
		$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
		$upgrade_type_map = array();
		$version_map = array();
		if($fireware_mcu_list['counts']) {
			$id_ary = array();
			$upgrade_type_map = array();
			foreach($fireware_mcu_list['list'] as $item) {
				$id_ary[] = $item['upgrade_type_id'];
			}
			$upgrade_model = new Model_Upgrade();
			$type_list = $upgrade_model->ListType(array('id'=>$id_ary), 0, count($id_ary));
			if($type_list['counts']) {
				foreach($type_list['list'] as $upgrade_type) {
					$upgrade_type_map[$upgrade_type['id']] = $upgrade_type;
				}
			}
			
			$version_list = $upgrade_model->ListVersion(array('upgrade_type_id'=>$id_ary), 0, 1000);
			if($version_list['counts']) {
				foreach($version_list['list'] as $item) {
					$item['file_path'] = "{$uploadConfig['baseUrl']}{$item['file_path']}";
					if(!isset($version_map[$item['upgrade_type_id']])) {
						$version_map[$item['upgrade_type_id']]['counts'] = 0;
						$version_map[$item['upgrade_type_id']]['last_version'] = $item['version'];
						$version_map[$item['upgrade_type_id']]['version'] = $item;
					}
					$version_map[$item['upgrade_type_id']]['counts']++;
					if($version_map[$item['upgrade_type_id']]['last_version'] < $item['version']) {
						$version_map[$item['upgrade_type_id']]['version'] = $item;
						$version_map[$item['upgrade_type_id']]['last_version'] = $item['version'];
					}
				}
			}
		}
		
		$this->view->version_list = $version_map;
		$this->view->type_list = $upgrade_type_map;
		$this->view->pagenation = $pagenation;
		$this->view->product_list = $product_list;
		$this->view->enterprise_list = $enterprise_list;
		$this->view->search = $search_data;
	}
	
	/* MCU版本信息 */
	public function mcuversionAction() {
		$page = $this->_request->get("page");
		$page = !empty($page) ? $page : 1;
		$offset = ($page-1) * $this->page_size;
		$upgrade_type_id = $this->_request->get("id");
		$upgrade_model = new Model_Upgrade();
		$version_list = $upgrade_model->ListVersion(array('upgrade_type_id'=>array($upgrade_type_id)), $offset, $this->page_size);
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($version_list['counts']));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($this->page_size);
		$this->view->version_list = $version_list['counts'] ? $version_list['list'] : array() ;
		$this->view->status_map = $this->status_map;
		$this->view->pagenation = $pagenation;
	}
	
	private function checkFirmwarePost($data) {
		$post = ZendX_Validate::factory($data)->labels(array(
				'label' => 'label',
				'firmware_type_id' => 'firmware type',
				'name' => 'name',
				'description' => 'Description',
		));
		$post->rules(
				'label',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',32))
				)
		);
		$post->rules(
				'name',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',64))
				)
		);
		$post->rules(
				'description',
				array(
						array('min_length',	array(':value',	10)),
						array('max_length',array(':value',512))
				)
		);
		$post->rules(
				'firmware_type_id',
				array(
						array('not_empty'),
						array('numeric')
				)
		);
		$check = $post->check();
		if($check) return true;
		$errors = $post->errors('validate');
		return array_shift($errors);
	}
	
	/* 检查版本提交的内容是否合法 */
	private function checkVersionPost($data) {
		$post = ZendX_Validate::factory($data)->labels(array(
				'version' => 'Version',
				'file_path' => 'File Path'
		));
		$post->rules(
				'version',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',32))
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
}