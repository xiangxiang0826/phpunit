<?php
/* 
 * 用户管理模块
 * Created By liujd@wondershare.cn
 *  */
class System_UsermanageController extends ZendX_Controller_Action {
	/* 初始化 */
	public function init() {
		parent::init();
		$this->status_map = array(
				Model_TemplateType::STATUS_ENABLE=>'启用',
				Model_TemplateType::STATUS_DISABLE=>'停用',
		);
	}
	/* 用户列表 */
	public function indexAction() {
		$search_data = $this->getRequest()->get('search');
		$page = $this->_request->get("page");
		$page = !empty($page) ? $page : 1;
		$offset = ($page-1) * $this->page_size;
		$user_model = new Model_User();
		$group_model = new Model_Group();
		$groups = $group_model->getList(0,100);//取所有用户组
		$users = $user_model->getList($offset, $this->page_size, $search_data);
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($users['total']));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($this->page_size);
		
		$user_ary = array();
		foreach($users['rows'] as $row) {
			$user_ary[$row['id']]['id'] = $row['id'];
		}
		$group_map = array();
		$user_group = $group_model->GetGroupByUids(array_keys($user_ary));
		if($user_group) {
			foreach($user_group as $gp) {
				$group_map[$gp['id']] = $gp;
				$user_ary[$gp['user_id']]['groups'][] = $gp['id'];
			}
		}
		$this->view->pagenation = $pagenation;
		$this->view->groups = $groups['rows'];
		$this->view->users = $users['rows'];
		$this->view->group_map = $group_map;
		$this->view->user_group = $user_ary;
		$this->view->search = $search_data;
	}
	
	/* 设置用户状态 */
	public function setstatusAction() {
		if($this->getRequest()->isPost()) {
			$uid = $this->getRequest()->getPost('uid');
			$status = $this->getRequest()->getPost('status');
			if($status == Model_User::STATUS_ENABLE) {
				$status = Model_User::STATUS_DISABLE;
			} else {
				$status = Model_User::STATUS_ENABLE;
			}
			$data = array('status'=>$status);
			$user_model = new Model_User();
			$user_model->update($data, array("id = '{$uid}'"));
			return Common_Protocols::generate_json_response();
		}
	}
	
	
	/* 查看账户信息 
	 * */
	public function accountAction() {
		$uid = $this->getRequest()->get('uid');
		$grp_map = array();
		$user_model = new Model_User();
		$account = $user_model->getInfoByUid($uid);
		$account['groups'] = array();
		$group_model = new Model_Group();
		$user_group = $group_model->GetGroupByUids(array($uid));
		$groups = $group_model->getList(0,100);//取所有用户组
		if($user_group) {
			foreach($user_group as $grp) {
				$account['groups'][$grp['id']] = $grp;
			}
		}
		$this->view->account_info = $account;
		$this->view->groups = $groups['rows'];
		$this->view->status_map = $this->status_map;
	}
	// 修改用户信息
	public function modifyaccountAction() {
		if($this->getRequest()->isPost()) {
			foreach($_POST as &$v) {
				$v = trim($v);
			}
			$post = ZendX_Validate::factory($_POST)->labels(array(
					'real_name' => 'real_name',
					'department' => 'department',
					'email' => 'Email',
					'phone' => 'Phone',
					'password' => 'Password',
					'group_id' =>'Group ID'
			));
			$post->rules(
					'real_name',
					array(
							array('not_empty'),
							array('min_length',array(':value', 2)),
							array('max_length',array(':value',20))
					)
			);
			$post->rules(
					'department',
					array(
							array('not_empty'),
							array('min_length',array(':value', 2)),
							array('max_length',array(':value',20))
					)
			);
			$post->rules(
					'email',
					array(
						array('not_empty'),
						array('email'),	
						array('min_length',	array(':value',	6)),
						array('max_length',array(':value',64))
					)
			);
			$post->rules(
				    'phone',
					array(
						array('not_empty'),
						array('min_length',	array(':value',	6)),
						array('max_length',array(':value',20))
					)
			);
			$post->rules(
					'password',
					array(
							array('min_length',	array(':value',	6)),
							array('max_length',array(':value',20))
					)
			);
			$post->rules(
					'group_id',
					array(
							array('not_empty'),
							array('digit')
					)
			);
			if(!$post->check()) {
				return Common_Protocols::generate_json_response($post->errors('validation'),Common_Protocols::VALIDATE_FAILED,$post->errors('validation'));
			}
			$grp_model = new Model_Group();
			$db = $grp_model->get_db();
			$db->beginTransaction();
			try {
				$grp_model->updateUserGroup(array($_POST['group_id']), $_POST['id']); // 更新用户组
				if(empty($_POST['password'])) unset($_POST['password']);
				else $_POST['password'] = md5($_POST['password']);
				unset($_POST['group_id']);
				$user_model = new Model_User();
				$user_model->update($_POST,array("id='{$_POST['id']}'"));
				$db->commit();
				return Common_Protocols::generate_json_response();
			} catch ( Exception $e) {
				$db->rollback();
				return Common_Protocols::generate_json_response(null, Common_Protocols::ERROR);
			}
		}
		return Common_Protocols::generate_json_response(NULL,Common_Protocols::VALIDATE_FAILED);
	}
	/* 增加用户 */
	public function adduserAction() {
		if($this->getRequest()->isPost()) {
			$post = ZendX_Validate::factory($_POST)->labels(array(
					'user_name' => 'User Name',
					'group_id' => 'Group Id',
			));
			$post->rules(
					'user_name',
					array(
							array('not_empty'),
							array('min_length',array(':value', 2)),
							array('max_length',array(':value',20))
					)
			);
			$post->rules(
					'group_id',
					array(
							array('not_empty'),
							array('digit')
					)
			);
			
			if(!$post->check()) {
				return Common_Protocols::generate_json_response($post->errors('validation'),Common_Protocols::VALIDATE_FAILED, $post->errors('validation'));
			}
			$user_name = $this->_request->getPost("user_name");
			$group_id = $this->_request->getPost("group_id");
			$user_info = Common_Auth::getUserInfo();
			$user_model = new Model_User();
			$user_exists = $user_model->getInfoByName($user_name);
			if($user_exists) {
				return Common_Protocols::generate_json_response('',Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('user_name_exists'));
			}
			$db = $user_model->get_db();
			$db->beginTransaction();
			try {
				$data = array('user_name'=>$user_name,'real_name'=>$user_name,'password'=>md5($user_name),'cuser'=>$user_info['id']);
				$user_id = $user_model->insert($data);
				$grp_model = new Model_Group();
				$grp_model->addUserGroup(array('user_id'=>$user_id,'user_group_id'=>$group_id));
				$db->commit();
				return Common_Protocols::generate_json_response();
			} catch ( Exception $e) {
				$db->rollback();
				return Common_Protocols::generate_json_response(null, Common_Protocols::ERROR, $this->view->getHelper('t')->t('500_error'));
			}
		}
		return Common_Protocols::generate_json_response(NULL,Common_Protocols::VALIDATE_FAILED);
	}
}