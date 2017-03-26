<?php

/**
 * 登录控制器
 * 
 * @author 刘金德
 * modify by liujinde
 * 2014-07-04
 */
class Login_IndexController extends ZendX_Controller_Action {

	function indexAction() {
		$this->_helper->layout()->disableLayout();
		$session = Zend_Registry::get('user_session');
		if(isset($session->user_info)) {
			$this->_redirect('/');
		}
	}
	/*
	 *  用户登录接口 
	 *  登录成功后根据用户组设置其所有可以访问的模块、菜单、url、用户信息、用户组到用户的session中。
	 *  如果是超级管理员，则直接忽略。
	 *  如果是在公司账号系统可以登录，但是在oss内部没有此用户，则提示用户组不正确。
	*/
	function loginAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$data = $this->_request->getPost();
		if(!$data) {
			return Common_Func::jsonResponse($this->view->getHelper('t')->t('need_post_method'),1);
		}
		//--fetch user info
		$group_model = $this->getGroupModel();
		$user_model = $this->getUserModel();
		$user_info = $user_model->getInfoByName($data['user_name']);
		//check username
		if(!$user_info || (isset($user_info['password']) && $user_info['password'] !== md5($data['password']))) { //用户不存在，或者密码不正确
			/* 以下调用公司账号系统，登录验证 */
			$login_data = array('user_name'=>$data['user_name'],'password'=>md5($data['password']));
			$wondershare_ret = Common_Auth::authFromWondershare($login_data);
			if(!$wondershare_ret['return']) { // 都没有登录成功，才返回用户不存在
				return Common_Func::jsonResponse($this->view->getHelper('t')->t('username_pwd_error'),'user_name');
			}
			$login_cfg = ZendX_Config::get('application','user_auth');
			if(!$user_info) { // 不在oss系统中，则插入用户
				$db = $user_model->get_db();
				$db->beginTransaction();
				try {
					$new_user = array('user_name'=>$data['user_name'],'real_name'=>$data['user_name'],
							'password'=>md5($data['password']),'cuser'=>$login_cfg['admin_uid'],
							'last_login_time'=>date('Y-m-d H:i:s')); // 保存登录密码，第二次则直接使用oss账号系统
					$user_info['id'] = $user_model->insert($new_user);
					$group_model->addUserGroup(array('user_id'=>$user_info['id'],'user_group_id'=>$login_cfg['default_gid']));
					$db->commit();
				} catch ( Exception $e) {
					$db->rollback();
					return Common_Protocols::generate_json_response(null, Common_Protocols::ERROR, $this->view->getHelper('t')->t('500_error'));
				}
				$user_info['user_id'] = $user_info['id'];
				$user_info['user_name'] = $data['user_name'];
				$user_info['status'] = Model_User::STATUS_ENABLE;
				$user_info['real_name'] = $data['user_name'];
			}
			$user_info['password'] = md5($data['password']);
		}
		//check password
		if($user_info['password'] !== md5($data['password'])) {
			return Common_Func::jsonResponse($this->view->getHelper('t')->t('username_pwd_error'),'password');
		}
		// 判断状态
		if($user_info['status'] != Model_User::STATUS_ENABLE) {
			return Common_Func::jsonResponse($this->view->getHelper('t')->t('status_error'),'user_name');
		}
		
		$groups = $group_model->GetGroupByUid($user_info['id']); // 有，则使用默认用户组
		if(empty($groups)) {
			return Common_Func::jsonResponse($this->view->getHelper('t')->t('group_error'),'user_name');
		}
		foreach($groups as $group) {
			$user_info['group_ids'][] = $group['id'];
		}
		$session = new Zend_Session_Namespace('UserInfo');
		$session->user_info = $user_info;
		//fetch prev
		if(!Common_Auth::isAdmin($user_info['group_ids'])) {
			$group_module_model = $this->getGroupModuleModel();
			$group_permission_model = $this->getGroupPermissionModel();
			$modules = $group_module_model->getModulesByGroupIds($user_info['group_ids']);
			$permissions = $group_permission_model->getPermsByGroupIds($user_info['group_ids']);
			$perm_menu = array();
            $perm_action = array();
			foreach($permissions as $perm) {
				$perm_action[] = $perm['url']; // 所有可访问的url
				if($perm['type'] == Model_GroupPermission::PERM_TYPE_MENU) { // 组合可见的菜单
					$perm_menu[$perm['module_id']][$perm['parent_id']][] = $perm;
				}
			}
			$session->modules = $modules;
			$session->perm_menu = $perm_menu;								//用于显示菜单
			$session->perm_action = array_unique($perm_action);			    //用于每个url的验证
		}
		$ip = Common_Func::getIp();
		$user_model->logLogin($ip);
		//以下调用接口到发布系统增加用户
		$ret = Cms_Task::getInstance()->addTaskUser(array('user_name'=>$data['user_name'],'password'=>$data['password'],'real_name'=>$data['user_name']));
		return Common_Func::jsonResponse($this->view->getHelper('t')->t('login_sucess'),0);
	}
	
	function logoutAction() {
		//禁用模版
		$this->_helper->viewRenderer->setNoRender();
		$session = Zend_Registry::get('user_session');
		$session->__unset('user_info');
		$this->_redirect('login');
	}
	
	/**
	 * 用户组
	 * @return mixed
	 */
	protected function getGroupModel(){
		return $this->getModel('Model_Group');
	}
	
	/**
	 * 获取用户模型
	 * @return mixed
	 */
	protected function getUserModel(){
		return $this->getModel('Model_User');
	}
	
	/**
	 * 获取用户组模型
	 * @return mixed
	 */
	protected function getGroupModuleModel(){
		return $this->getModel('Model_GroupModule');
	}
	
	/**
	 * 获取权限模型
	 */
	protected function getGroupPermissionModel(){
		return $this->getModel('Model_GroupPermission');
	}
}