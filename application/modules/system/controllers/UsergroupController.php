<?php
/* 
 * 用户组管理。用户分组，用户权限
 * 
 */
class System_UsergroupController extends ZendX_Controller_Action {
		
	public function indexAction() {
		$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$rows = isset($_GET['rows']) ? intval($_GET['rows']) : $this->page_size;
		$offset = ($page-1)*$rows;
		$query = array();
		$result = array();
		$groupModel = new Model_Group();
		$row = $groupModel->getTotal();
        
        $groupInfo = $groupModel->getTotalByGroup();
        $groupMap = array();
        foreach($groupInfo as $item){
            $groupMap[$item['gid']] = $item['total'];
        }
        
        $result = $groupModel->getList($offset, $rows);
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($result["total"]));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($rows);
		$this->view->results =  $result;
        $this->view->group_map =  $groupMap;
		$this->view->pagenation = $pagenation;
	}
    
    /**
     * 权限设置
     */
	public function privilegeAction() {
        $gid = $this->getRequest()->get('gid');
        // 获取权限
        $group_model = new Model_GroupModule();
        $authModel = new Model_GroupPermission();
        $authInfo = $authModel->getInfoByGroupIds(array('group_id' => $gid), $fields = 'perm_id');
        $authArr = array();
        foreach($authInfo as $item){
            $authArr[] = $item['perm_id'];
        }
        if($this->getRequest()->isPost()) {
            $authData = $this->getRequest()->getPost('auth_id');
			if(!$authData) {
				$authData = array();
			}
            $authId = '';
            //去掉待处理2组数组中相同的元素，则新提交数组中残余的数据即为新增，旧数组中残余的数据即为删除
            foreach($authData as $key => $item){
                if(in_array($item, $authArr)){
                    unset($authData[$key]);
                    $keyIndex = array_search($item, $authArr);
                    if(isset($authArr[$keyIndex])){
                        unset($authArr[$keyIndex]);
                    }
                }
            }
            $authAddArr = $authData;
            $authDeleteArr = $authArr;
            if(!empty($authAddArr)){
                foreach($authAddArr as $item){
                    $data = array(
                        'group_id' => $gid,
                        'perm_id' => $item
                    );
                    $authModel->insert($data);
                }
            }
            if(!empty($authDeleteArr)){
                foreach($authDeleteArr as $item){
                    $data = array(
                        'group_id = "'.$gid.'"',
                        'perm_id = "'.$item.'"'
                    );
                    $authModel->delete($data);
                }
            }
            $module_id = isset($_POST['module_id']) ? $_POST['module_id'] : array();
            
            $group_model->saveGroupModule($gid, $module_id);
            return Common_Protocols::generate_json_response();
        }
        
        $group_module_map = $group_model->getModulesByGroupIds(array($gid));
        $tmp_map = array();
        if($group_module_map) {
        	foreach($group_module_map as $gp) {
        		$tmp_map[$gp['id']] = $gp;
        	}
        }
        $this->view->group_module = $tmp_map;
		$groupModel = new Model_Group();
        $groupInfo = $groupModel->getFieldsById('*', $gid);
        
        //生成菜单的选择框
        $root = array();
        // module
        $moduleModel = new Model_Module();
        $section = $moduleModel->GetALlModules();
        $menuIdArr = array();
        foreach ($section as $key => $row) {
            $root[$row['id']] = $row;
            $menuIdArr[] = $row['id'];
            unset($section[$key]);
        }

        $secondRoot = array();
        $menuModel = new Model_Permission();
        $where = implode(',', $menuIdArr);
        $where = $where?(' module_id in ('.$where.') AND status = "enable"'):'1';
        $sectionMenu = $menuModel->getList($where);
        foreach ($sectionMenu as $key => $row) {
            if (isset($root[$row['module_id']]) && $row['parent_id'] == '0') {
                $secondRoot[$row['module_id']][] = $row;
                unset($sectionMenu[$key]);
            }
        }

        $authId = implode(',', $authArr);
        $lastRoot = array();
        foreach ($sectionMenu as $key => $row) {
            $lastRoot[$row['parent_id']][] = $row;
        }
        // print_r($root);exit(',ln='.__line__);
        $this->view->root =  $root;
        $this->view->second_root =  $secondRoot;
        $this->view->last_root =  $lastRoot;
        $this->view->gid =  $gid;
        $this->view->group_info =  $groupInfo;
        $this->view->user_permission_ids = $authId;
        $this->view->user_permission_arr = $authArr;
	}
	
	/**
     * 添加用户组类型
     * @return type
     */
	public function addAction() {
		// post方法提交，则保存数据 
		if($this->getRequest()->isPost()) {
            $typeData = $this->getRequest()->getPost('type');
            
			if(!$this->checkTypePost($typeData)) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
			$upgradeModel = new Model_Group();
            $where = array('name'=>$typeData['name'], 'status' => 'enable');
            $whereWithout = array();
            $gid = $this->getRequest()->get('gid');
            if($gid){
                $whereWithout = array('id' => $gid);
            }
			$row = $upgradeModel->getCategoryRowByField(array('id'), $where, $whereWithout);
			if($row) {
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('resource_category_exists'));
			}
            if($gid){
                // print_r($gid);exit;
                $upgradeModel->updateCategory($typeData, array('`id` = "'.$gid.'"'));
                
            }else{
                $upgradeModel->insertCategory($typeData);
            }
			
			return Common_Protocols::generate_json_response();
		}
        $gid = $typeData = $this->getRequest()->get('gid');
        $info = array(
            'name' => '',
            'description' => ''
        );
        if($gid){
           $this->view->gid = $gid;
           $upgradeModel = new Model_Group();
           $info = $upgradeModel->getCategoryRowByField(array('name', 'description'), array('id' => $gid));
        }
        $this->view->info = $info;
	}
    
    
	/**
     * 删除权限组状态
     * @return type
     */
	public function deleteAction() {
		// post方法提交，则保存数据 
		if($this->getRequest()->isPost()) {
            $typeId = $this->getRequest()->getPost('gid');
            if(is_integer($typeId) && $typeId) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
			$upgradeModel = new Model_Group();
			try {
                $upgradeModel->begin_transaction();
                //删除之前要判断是否该类型下存在相关的类型
                $categoryInfo = $upgradeModel->getMapList(0, 10, array('user_group_id = "'.$typeId.'"'));
                // print_r($categoryInfo);exit;
                if(isset($categoryInfo['total']) && $categoryInfo['total'] > 0 ){
                    return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('user_group_remove_notice'));
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
    
	/**
     * 更新权限组状态
     * @return type
     */
	public function updateAction() {
		// post方法提交，则保存数据 
		if($this->getRequest()->isPost()) {
            $typeId = $this->getRequest()->get('gid');
            if(is_integer($typeId) && $typeId) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
			$upgradeModel = new Model_Group();
			try {
                $upgradeModel->begin_transaction();
                $status = $this->getRequest()->get('status');
                $categoryInfo = $upgradeModel->getMapList(0, 10, array('user_group_id = "'.$typeId.'"'));
                //print_r($categoryInfo);exit;
                if($status == 'deleted'){
                     //删除之前要判断是否该类型下存在相关的类型
                     if(isset($categoryInfo['total']) && $categoryInfo['total'] > 0 ){
                     	// 删除该组的时该组内成员会移动到默认组
                     	$targetId = $upgradeModel->getDefaultGroupId();
                     	if(!empty($targetId)){
                     		$upgradeModel->changeUserGroup($typeId,$targetId);
                     	}
                     	//return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('user_group_remove_notice'));
                     }
                }
                
                // 用户组禁用和启用涉及的组成员操作
                if($status == 'disable' || $status == 'enable'){
                	if(isset($categoryInfo['total']) && $categoryInfo['total'] > 0 ){
                		$newStatus = $status;
                		// 拼接 SQL如： id IN ('1','2','3')
                		$idLists = '(';
                		foreach($categoryInfo['rows'] as $v){
                			$idLists .= "'{$v['user_id']}',";
                		}
                		$idLists = rtrim($idLists,',').')';
                		$newStatus = $status; // 更改为的最新状态
                		$userModel = new Model_User();
                		$userModel->changeGroupUser($newStatus,$idLists);
                	}
                }

				$upgradeModel->updateCategory(array('status' => $status), 'id="'.$typeId.'"');
				$upgradeModel->commit();
			} catch(Exception $e) {
				$upgradeModel->rollback();
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::ERROR, $e->getMessage());
			}
			return Common_Protocols::generate_json_response();
		}
        return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
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