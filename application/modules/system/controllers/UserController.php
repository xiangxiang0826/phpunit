<?php
/* 
 * 用户模块
 *  */
class System_UserController extends ZendX_Controller_Action {
	
	public function indexAction() {
		echo "system";
	}
	
	/* 账户信息 
	 * */
	public function accountAction() {
		$user_info = Common_Auth::getUserInfo();
		$user_model = new Model_User();
		$account = $user_model->getInfoByUid($user_info['id']);
		$this->view->account_info = $account;
	}

	public function modifyaccountAction() {
		if($this->getRequest()->isPost()) {
			foreach($_POST as &$v) {
				$v = trim($v);
			}
			$user_info = Common_Auth::getUserInfo();
			$password = $this->getRequest()->getPost('password');
			$post = ZendX_Validate::factory($_POST)->labels(array(
					'real_name' => 'real_name',
					'department' => 'department',
					'email' => 'Email',
					'phone' => 'Phone',
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
			
			if(!$post->check()) {
				return Common_Protocols::generate_json_response($post->errors('validation'),Common_Protocols::VALIDATE_FAILED,$post->errors('validation'));
			}
			$user_model = new Model_User();
			$user_model->update($_POST,array("id='{$user_info['id']}'"));
			Common_Auth::setUserInfo('real_name',$_POST['real_name']);
			return Common_Protocols::generate_json_response();
		}
		return Common_Protocols::generate_json_response(NULL,Common_Protocols::VALIDATE_FAILED);
	}
	
	public function modifypwdAction() {
		if($this->getRequest()->isPost()) {
			$user_info = Common_Auth::getUserInfo();
			$password = $this->getRequest()->getPost('password');
			$post = ZendX_Validate::factory($_POST)->labels(array(
					'old_password' => 'Old Password',
					'password' => 'Password',
			));
			$post->rules(
					'old_password',
					array(
							array('not_empty'),
							array('min_length',array(':value', 6)),
							array('max_length',array(':value',16))
					)
			);
			$post->rules(
					'password',
					array(
							array('not_empty'),
							array('min_length',array(':value', 6)),
							array('max_length',array(':value',16))
					)
			);
			
			if(!$post->check()) {
				return Common_Protocols::generate_json_response($post->errors('validation'),Common_Protocols::VALIDATE_FAILED,$post->errors('validation'));
			}
			$user_model = new Model_User();
			$user_model->update(array('password'=>md5($password)),array("id='{$user_info['id']}'"));
			return Common_Protocols::generate_json_response();
		}
	}
	
	public function verifypwdAction() {
		$password = $this->_request->getParam("old_password");
		$user_info = Common_Auth::getUserInfo();
		$user_model = new Model_User();
		$user_info = $user_model->getInfoByName($user_info['user_name']);
		//check password
		if($user_info['password'] !== md5($password)) {
			echo 'false';
			return ;
		}
		echo 'true';
	}
}