<?php

/* 
 * 系统模块
 * 
 */
class Enterprise_ApipermissionController extends ZendX_Controller_Action{
    
	public function indexAction() {
		$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$rows = isset($_GET['rows']) ? intval($_GET['rows']) : $this->page_size;
		$offset = ($page-1)*$rows;
		$query = 'status != "deleted"';
		$result = array();
		$Model_ApiPermission = new Model_ApiPermission();
		$result["total"] = $Model_ApiPermission->getTotal($query);
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($result["total"]));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($rows);
		$items = $Model_ApiPermission->getList($query, $offset, $rows);
		$result['rows'] = $items;
		$this->view->results =  $result;
		$this->view->pagenation = $pagenation;
	}
    
	/**  
	 * 增加权限
     */
	public function addAction() {
		// post方法提交，则保存数据
        
		if($this->getRequest()->isPost()) {
			if(!$this->_checkPost()) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
            $name = $this->getRequest()->getParam('name');
            $uri = $this->getRequest()->getParam('uri');
            $status = $this->getRequest()->getParam('status');
			$Model_ApiPermission = new Model_ApiPermission();
			$rowName = $Model_ApiPermission->getRowByField(array('id'), array('name'=>$name));
            $rowUri = $Model_ApiPermission->getRowByField(array('id'), array('uri'=>$uri));
			if(!$rowName && !$rowUri) {
                $insert = array(
                    'name' => $name,
                    'uri' => $uri,
                    'status' => $status
                );
                $Model_ApiPermission->insert($insert);
                return Common_Protocols::generate_json_response();
            }else{
                if($rowName){
                    return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('api_auth_name_exists'));
                }
                if($rowUri){
                    return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('api_auth_uri_exists'));
                }
                return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('action_exists'));
            }
		}
        $model = new Model_ApiPermission();
        $this->view->groups = $model->getGroup();
	}
    
/**  
	 * 编辑权限
     */
	public function editAction() {
		// post方法提交，则保存数据
        $id = $this->getRequest()->getParam('id');
        $title = $this->getRequest()->getParam('title');
        $condition = array(
            'D.id' => $id
        );
        $Model_ApiPermission = new Model_ApiPermission();
        $items = $Model_ApiPermission->getItemsByWhere($condition);
        $item = array();
        if(isset($items[0]) && !empty($items[0])){
            $item = $items[0];
        }
		if($this->getRequest()->isPost()) {
			if(!$this->_checkPost()) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
            $name = $this->getRequest()->getParam('name');
            $uri = $this->getRequest()->getParam('uri');
            $status = $this->getRequest()->getParam('status');
            $group_name = $this->getRequest()->getParam('group_name');
			$Model_ApiPermission = new Model_ApiPermission();
            $rowName = $rowUri = FALSE;
            if($name != $item['name']){
                $rowName = $Model_ApiPermission->getRowByField(array('id'), array('name'=>$name));
            }
			if($uri != $item['uri']){
                $rowUri = $Model_ApiPermission->getRowByField(array('id'), array('uri'=>$uri));
            }
			if(!$rowName && !$rowUri) {
                $update = array(
                    'name' => $name,
                    'uri' => $uri,
                    'status' => $status,
                    'group_name' => $group_name
                );
                $where = 'id="'.$id.'"';
                $Model_ApiPermission->update($update, $where);
                return Common_Protocols::generate_json_response();
            }else{
                if($rowName){
                    return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('api_auth_name_exists'));
                }
                if($rowUri){
                    return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('api_auth_uri_exists'));
                }
                return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('action_exists'));
            }
		}
        
        $this->view->requestId =  $id;
        $this->view->item =  $item;
        $this->view->requestTitle =  $title;
        $model = new Model_ApiPermission();
        $this->view->groups = $model->getGroup();
	}
	
	/**
     *  更新action,主要是修改状态，启用，禁用，删除
     */
	public function updateAction() {
        $id = $this->getRequest()->getParam('id');
        $status = $this->getRequest()->getParam('status');
        if(!$id || !$status) {
            return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED);
        }
        $Model_ApiPermission = new Model_ApiPermission();
        $update = array('status' => $status);
        $Model_ApiPermission->update($update, array("id='{$id}'"));
        $this->redirect($this->view->url(array('controller'=>'apipermission', 'action'=>'index')));
    }
    
	/**
     *  更新app,主要是修改状态，启用，禁用，删除
     */
	public function updateappAction() {
        $id = $this->getRequest()->getParam('id');
        $status = $this->getRequest()->getParam('status');
        if(!$id || !$status) {
            return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED);
        }
        $Model_ApiEnterpriseGrant = new Model_ApiEnterpriseGrant();
        $mtime = date('Y-m-d H:i:s');
        $update = array(
            'status' => $status,
            'mtime' => $mtime
        );
        $Model_ApiEnterpriseGrant->update($update, array("id='{$id}'"));
        $this->redirect($this->view->url(array('controller'=>'apipermission', 'action'=>'applist')));
    }
    
    /**
     * app列表页面
     * 
     */
	public function applistAction() {
		$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$rows = isset($_GET['rows']) ? intval($_GET['rows']) : $this->page_size;
        $enterprise_id = isset($_GET['enterprise_id']) ? intval($_GET['enterprise_id']) : '';
		$offset = ($page-1)*$rows;
		$query = 't.status != "deleted"';
		$result = array();
		if(!empty($enterprise_id)) {
			$query .= " AND t.enterprise_id =  ". $enterprise_id ."";
		}
		$Model_ApiEnterpriseGrant = new Model_ApiEnterpriseGrant();
		$result["total"] = $Model_ApiEnterpriseGrant->getTotal($query);
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($result["total"]));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($rows);
		$items = $Model_ApiEnterpriseGrant->getListMixed($query, $offset, $rows);
		$result['rows'] = $items;
		//查询列表数据
        $enterprise = new Model_Enterprise();
		$this->view->enterprises =  $enterprise->droupDownData();
        $this->view->enterprise_id = $enterprise_id;
		$this->view->results =  $result;
		$this->view->pagenation = $pagenation;
	}
    
    /**
     * app模板列表页面
     * 
     */
	public function templateAction() {
		$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$rows = isset($_GET['rows']) ? intval($_GET['rows']) : $this->page_size;
		$offset = ($page-1)*$rows;
		$query = '1=1';
		$result = array();
		$Model_ApiPermissionTemplate = new Model_ApiPermissionTemplate();
		$row = $Model_ApiPermissionTemplate->getTotal();
		$result["total"] = $row[0]['total'];
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($result["total"]));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($rows);
		$items = $Model_ApiPermissionTemplate->getList($query, $offset, $rows);
		$result['rows'] = $items;
		$this->view->results =  $result;
		$this->view->pagenation = $pagenation;
	}
    
	/**  
	 * 增加权限模板
     */
	public function addtemplateAction() {
		// post方法提交，则保存数据
        
		if($this->getRequest()->isPost()) {
			if(!$this->_checkTemplatePost()) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
            $name = $this->getRequest()->getParam('name');
            $label = $this->getRequest()->getParam('label');
            $ids = $this->getRequest()->getParam('ids');
            if(substr($ids, -1) == ','){
                $ids = substr($ids, 0, -1);
            }
			$Model_ApiPermissionTemplate = new Model_ApiPermissionTemplate();
			$rowName = $Model_ApiPermissionTemplate->getRowByField(array('id'), array('name'=>$name));
            $rowLabel = $Model_ApiPermissionTemplate->getRowByField(array('id'), array('label'=>$label));
			if(!$rowName && !$rowLabel) {
                $insert = array(
                    'name' => $name,
                    'label' => $label,
                    'permission' => $ids
                );
                $Model_ApiPermissionTemplate->insert($insert);
                return Common_Protocols::generate_json_response();
            }else{
                if($rowName){
                    return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('api_template_name_exists'));
                }
                if($rowLabel){
                    return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('api_template_label_exists'));
                }
                return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('action_exists'));
            }
		}
        
        $selAuth = array();
        $Model_ApiPermission = new Model_ApiPermission();
        $condition = array(
            'D.status' => 'enable',
            'D.group_name' => 'auth'
        );
        $allAuth = $Model_ApiPermission->getItemsByWhere($condition);
        $condition = array(
            'D.status' => 'enable',
            'D.group_name' => 'intranet'
        );
        $intranetAuth = $Model_ApiPermission->getItemsByWhere($condition);
        $condition = array(
            'D.status' => 'enable',
            'D.group_name' => 'common'
        );
        $commonAuth = $Model_ApiPermission->getItemsByWhere($condition);
		$this->view->selAuth =  $selAuth;
        $this->view->allAuth =  $allAuth;
        $this->view->intranetAuth =  $intranetAuth;
        $this->view->commonAuth =  $commonAuth;
	}
    
	/**  
	 * 编辑权限模板
     */
	public function edittemplateAction() {
		// post方法提交，则保存数据
        $id = $this->getRequest()->getParam('id');
        $title = $this->getRequest()->getParam('title');
        $condition = array(
            'D.id' => $id
        );
        $Model_ApiPermissionTemplate = new Model_ApiPermissionTemplate();
        $items = $Model_ApiPermissionTemplate->getItemsByWhere($condition);
        $selAuth = $item = array();
        if(isset($items[0]) && !empty($items[0])){
            $perms = $items[0]['permission'];
            $selAuth = explode(',', $perms);
            $item = $items[0];
        }
        
		if($this->getRequest()->isPost()) {
			if(!$this->_checkTemplatePost()) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
            $name = $this->getRequest()->getParam('name');
            $label = $this->getRequest()->getParam('label');
            $ids = $this->getRequest()->getParam('ids');
            if(substr($ids, -1) == ','){
                $ids = substr($ids, 0, -1);
            }
			$Model_ApiPermissionTemplate = new Model_ApiPermissionTemplate();
            $rowName = $rowLabel = FALSE;
            if($name != $item['name']){
                $rowName = $Model_ApiPermissionTemplate->getRowByField(array('id'), array('name'=>$name));
            }
            if($label != $item['label']){
                $rowLabel = $Model_ApiPermissionTemplate->getRowByField(array('id'), array('label'=>$label));
            }
			if(!$rowName && !$rowLabel) {
                $update = array(
                    'name' => $name,
                    'label' => $label,
                    'permission' => $ids
                );
                $where = 'id = '.$id;
                // todo
                $Model_ApiPermissionTemplate->update($update, $where);
                return Common_Protocols::generate_json_response();
            }else{
                if($rowName){
                    return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('api_template_name_exists'));
                }
                if($rowLabel){
                    return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('api_template_label_exists'));
                }
                return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('action_exists'));
            }
		}
        
        $Model_ApiPermission = new Model_ApiPermission();
        $condition = array(
            'D.status' => 'enable',
            'D.group_name' => 'auth'
        );
        $allAuth = $Model_ApiPermission->getItemsByWhere($condition);
        $condition = array(
            'D.status' => 'enable',
            'D.group_name' => 'intranet'
        );
        $intranetAuth = $Model_ApiPermission->getItemsByWhere($condition);
        $condition = array(
            'D.status' => 'enable',
            'D.group_name' => 'common'
        );
        $commonAuth = $Model_ApiPermission->getItemsByWhere($condition);
        $this->view->requestId =  $id;
        $this->view->item =  $item;
        $this->view->requestTitle =  $title;
		$this->view->selAuth =  $selAuth;
        $this->view->allAuth =  $allAuth;
        $this->view->intranetAuth =  $intranetAuth;
        $this->view->commonAuth =  $commonAuth;
        //　获取原始的数据
        
	}
    
	/**
     *  删除模板
     */
	public function deletetemplateAction() {
        $id = $this->getRequest()->getParam('id');
        if(!$id) {
            return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED);
        }
        $Model_ApiPermissionTemplate = new Model_ApiPermissionTemplate();
        $Model_ApiPermissionTemplate->deleteByWhere("id='{$id}'");
        $this->redirect($this->view->url(array('controller'=>'apipermission', 'action'=>'template')));
    }
    
    /**
     * app权限设置页面
     * 
     * @todo 前端的JS交互
     */
	public function appauthAction() {
		$id = $this->getRequest()->getParam('id');
        $title = $this->getRequest()->getParam('title');
		$authList = array();
        // 要分开获取，以方便前端的js交互。
        $condition = array(
            'D.api_id' => $id
        );
        $Model_ApiPermissionMap = new Model_ApiPermissionMap();
        $result = $Model_ApiPermissionMap->getItemsByWhere($condition);
        $selAuth = array();
        foreach ($result as $v){
            $selAuth[] = $v['perm_id'];
        }
        $Model_ApiPermission = new Model_ApiPermission();
        $condition = array(
            'D.status' => 'enable',
            'D.group_name' => 'auth'
        );
        $allAuth = $Model_ApiPermission->getItemsByWhere($condition);
        $condition = array(
            'D.status' => 'enable',
            'D.group_name' => 'intranet'
        );
        $intranetAuth = $Model_ApiPermission->getItemsByWhere($condition);
        $condition = array(
            'D.status' => 'enable',
            'D.group_name' => 'common'
        );
        $commonAuth = $Model_ApiPermission->getItemsByWhere($condition);
		$Model_ApiPermissionTemplate = new Model_ApiPermissionTemplate();
		$templateAuth = $Model_ApiPermissionTemplate->getAllList();
        $this->view->templateAuth = $templateAuth;
		$this->view->results =  $result;
        $this->view->requestId =  $id;
        $this->view->requestTitle =  $title;
		$this->view->selAuth =  $selAuth;
        $this->view->allAuth =  $allAuth;
        $this->view->intranetAuth =  $intranetAuth;
        $this->view->commonAuth =  $commonAuth;
	}
    
    /**
     * app权限保存页面
     * 
     * @todo 前端的JS交互保存结果
     */
	public function setauthAction() {
		$id = $this->getRequest()->getParam('id');
        $ids = $this->getRequest()->getParam('ids');
        if(substr($ids, -1) == ','){
            $ids = substr($ids, 0, -1);
        }
        $permIds = array();
        if($ids){
            $permIds = explode(',', $ids);
        }
        
        $condition = array(
            'D.api_id' => $id
        );
        $Model_ApiPermissionMap = new Model_ApiPermissionMap();
        $result = $Model_ApiPermissionMap->getItemsByWhere($condition);
        $selAuth = array();
        foreach ($result as $v){
            $selAuth[] = $v['perm_id'];
        }
        
        // 消除重复的元素
        foreach($selAuth as $k => $v){
            if(in_array($v, $permIds)){
                unset($selAuth[$k]);
                $searchKey = array_search($v, $permIds);
                unset($permIds[$searchKey]);
            }
        }
        
        $insertIdsArray = $permIds;
        if(!empty($selAuth)){
            $deleteIds = implode(',', $selAuth);
            $where = 'api_id = '.$id.' AND perm_id in ('.$deleteIds.')';
            $Model_ApiPermissionMap->deleteByWhere($where);
        }
        
        if(!empty($insertIdsArray)){
            $template = array(
                'api_id' => $id,
                'perm_id' => 0
            );
            foreach($insertIdsArray as $v){
                $data = $template;
                $data['perm_id'] = $v;
                $Model_ApiPermissionMap->insert($data);
            }
        }
        //
        return  Common_Protocols::generate_json_response();
	}
    
	/* 
     * 添加app
     */
	public function addappAction() {
		// post方法提交，则保存数据
		if($this->getRequest()->isPost()) {
			if(!$this->_checkAppPost()) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
            $Model_ApiEnterpriseGrant = new Model_EnterpriseGrant();
            $name = $this->getRequest()->getParam('name');
            $remark = $this->getRequest()->getParam('remark');
            $status = $this->getRequest()->getParam('status');
            $enterprise_id = $this->getRequest()->getParam('enterprise_id');
            // 目前限制为只有遥控E族可以添加app
			if($enterprise_id != 1) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
            $rowName = FALSE;
            $rowName = $Model_ApiEnterpriseGrant->getRowByField(array('id'), array('name'=>$name));
			if(!$rowName) {
                $isRun = 1;
                $i = 0;
                $app_key = '';
                while($isRun > 0){
                   if($i > 4){
                       // 超过指定次数，则退出
                       $app_key = '';
                       $isRun = 0;
                       break;
                   }
                   $app_key = md5(uniqid().mt_rand(1, 1000).$enterprise_id);
                   $i++;
                   $rowAppKey = $Model_ApiEnterpriseGrant->getRowByField(array('id'), array('app_key'=>$app_key));
                   if(!$rowAppKey){
                       // 获取到不重复的app_key，则退出
                       $isRun = 0;
                       break;
                   }else{
                       $rowAppKey = '';
                   }
                }
                // 判断app_key是否存在
                if(!$app_key){
                    return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
                }
                $app_salt = md5(md5(uniqid()).mt_rand(1000, 2000).$enterprise_id);
                $insert = array(
                    'name' => $name,
                    'remark' => $remark,
                    'app_key' => $app_key,
                    'app_salt' => $app_salt,
                    'enterprise_id' => $enterprise_id,
                    'status' => $status
                );
                $apiId = $Model_ApiEnterpriseGrant->insert($insert);
                if($apiId) {
                    $Model_Enterprise = new Model_Enterprise();
                    $db = $Model_Enterprise->get_db();
                    $enterpriseInfo = $Model_Enterprise->getDetail($enterprise_id);
                    if($enterpriseInfo['user_type'] == 'company') {
                         $permissionLabel = 'general_et_api'; 
                    } elseif($enterpriseInfo['user_type'] == 'personal') {
                         $permissionLabel = 'personal_et_api';
                    }
                    $sql = "SELECT permission FROM `EZ_API_PERMISSION_TEMPLATE` WHERE label='$permissionLabel'";
                    $permissionStr = $db->fetchOne($sql);
                    $permissionArr = explode(',', $permissionStr);
                    foreach ($permissionArr as $row) {
                        $bind = array(
                            'api_id' => $apiId,
                            'perm_id' => $row
                        );
                        $db->insert('EZ_API_PERMISSION_MAP', $bind);
                    }
                }
                return Common_Protocols::generate_json_response();
            }else{
                return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('api_app_name_exists'));
            }
		}
		//查询列表数据
        $enterprise = new Model_Enterprise();
		$this->view->enterprises =  $enterprise->droupDownData();
	}
    
	/* 
     * app详情
     */
	public function appdetailAction() {
		$id = $this->getRequest()->getParam('id');
        $title = $this->getRequest()->getParam('title');
        $Model_ApiEnterpriseGrant = new Model_ApiEnterpriseGrant();
        $query = 't.id = '.$id; 
		$items = $Model_ApiEnterpriseGrant->getItemMixed($query);
        $item = array();
        if(isset($items[0]) && !empty($items[0])){
            $item = $items[0];
        }
        
		// post方法提交，则保存数据
		if($this->getRequest()->isPost()) {
			if(!$this->_checkDetailPost()) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
            $name = $this->getRequest()->getParam('name');
            $remark = $this->getRequest()->getParam('remark');
            $appKey = $this->getRequest()->getParam('app_key');
            $appSalt = $this->getRequest()->getParam('app_salt');
            $rowName = FALSE;
            if($name !== $item['name']){
                $rowName = $Model_ApiEnterpriseGrant->getRowByField(array('id'), array('name'=>$name));
            }
			if(!$rowName) {
                $mtime = date('Y-m-d H:i:s');
                // 0000-00-00 00:00:00
                $update = array(
                    'name' => $name,
                    'remark' => $remark,
                    'mtime' => $mtime,
                    'app_key' => $appKey,
                    'app_salt' => $appSalt
                );
                $where = 'id="'.$id.'"';
                $Model_ApiEnterpriseGrant->update($update, $where);
                return Common_Protocols::generate_json_response();
            }else{
                return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('api_app_name_exists'));
            }
		}
        
		$this->view->app_info = $item;
        $this->view->requestTitle =  $title;
		$this->view->status_enable = Model_Member::STATUS_ENABLE;
	}
    
    /**
     * 检查提交的数据是否合法
     * 
     * @return bool
     */
    private function _checkPost() {
		$post = ZendX_Validate::factory($_POST)->labels(array(
				'name' => 'name',
				'uri' => 'uri',
				'status' => 'status',
		));
		$post->rules(
				'name',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',16))
				)
		);
		$post->rules(
				'uri',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',128))
				)
		);
		$post->rules(
				'status',
				array(
						array('not_empty')
				)
		);
		return $post->check();
	}
    
    /**
     * 检查提交的数据是否合法
     * 
     * @return bool
     */
    private function _checkDetailPost() {
		$post = ZendX_Validate::factory($_POST)->labels(array(
				'name' => 'name',
				'remark' => 'remark',
                'app_key' => 'app_key',
                'app_salt' => 'app_salt',
		));
		$post->rules(
				'name',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',32))
				)
		);
		$post->rules(
				'remark',
				array(
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',128))
				)
		);
		$post->rules(
				'app_key',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',32))
				)
		);
		$post->rules(
				'app_salt',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',64))
				)
		);
		return $post->check();
	}
    
    /**
     * 检查提交的数据是否合法
     * 
     * @return bool
     */
    private function _checkAppPost() {
		$post = ZendX_Validate::factory($_POST)->labels(array(
				'name' => 'name',
				'remark' => 'remark',
                'status' => 'status'
		));
		$post->rules(
				'name',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',32))
				)
		);
		$post->rules(
				'remark',
				array(
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',128))
				)
		);
  		$post->rules(
				'enterprise_id',
				array(
                        array('not_empty')
				)
		);
  		$post->rules(
				'status',
				array(
                        array('in_array', array(':value', array('enable','disable')))
				)
		);
		return $post->check();
	}
    
    /**
     * 检查提交的模板数据是否合法
     * 
     * @return bool
     */
    private function _checkTemplatePost() {
		$post = ZendX_Validate::factory($_POST)->labels(array(
				'name' => 'name',
				'label' => 'label',
		));
		$post->rules(
				'name',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',32))
				)
		);
		$post->rules(
				'label',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',16))
				)
		);
		return $post->check();
	}
}