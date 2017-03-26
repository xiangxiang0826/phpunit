<?php
/**
 * 问题类别管理
 * @author zhouxh
 *
 */
class Operation_MessageController extends ZendX_Controller_Action
{
	/**
	 * 已经审核消息列表
	 */
	public function indexAction(){
		//查询版本列表
		$page = $this->getRequest()->getParam('page', 1);
		$search = $this->_request->getParam("search");
		$condition = array();
		if(!empty($search['enterprise_id'])) {
			$condition['t.enterprise_id'] = $search['enterprise_id'];
		}
		if(!empty($search['id'])) {
			$condition['t.id'] = $search['id'];
		}
		if(!empty($search['status'])) {
			$condition['t.status'] = $search['status'];
		}
		if(!empty($search['product'])) {
			$condition['product'] = $search['product'];
		}
		if(!empty($search['start']) && !empty($search['end'])) {
			$condition['time'] = "(t.send_time >='{$search['start']} 00:00:00' AND t.send_time <= '{$search['end']} 23:59:59')";
		} else if(!empty($search['start'])) {
			$condition['time'] = "(t.send_time >='{$search['start']} 00:00:00')";
		} else if(!empty($search['end'])) {
			$condition['time'] = "(t.send_time <='{$search['end']} 23:59:59' )";
		}
		$mesageModel = new Model_Message();
		$products = new Model_Product();
		//品类查询
		if(!empty($search['category'])) {
			$categoryIds = $productCate->getAllChildrenById($search['category']);
			if($categoryIds) {
				$categoryIds[] = $search['category'];
			} else {
				$categoryIds = array($search['category']);
			}
			$condition['category_id'] = $search['category'];
		}
		$status = $mesageModel->getStatusMap();
		$select = $mesageModel->createCondition($condition, 0);
		$paginator = Zend_Paginator::factory($select);
		$paginator->setItemCountPerPage($this->page_size);// 测试分页 =1
		$paginator->setCurrentPageNumber($page);
		$enterprise = new Model_Enterprise();
		unset($status[Model_Message::STATUS_NEED_CHECK]);
		$productData= $products->getDroupdownData();
		$this->view->enterprises =  $enterprise->droupDownData();
		$this->view->paginator = $paginator;
		$this->view->search = $search;
		$this->view->status = $status;
		$this->view->products = $productData;
	}
	/**
	 * 待审核消息列表
	 */
	public function pendingAction(){
		//查询版本列表
		$page = $this->getRequest()->getParam('page', 1);
		$search = $this->_request->getParam("search");
		$condition = array();
		if(!empty($search['enterprise_id'])) {
			$condition['t.enterprise_id'] = $search['enterprise_id'];
		}
		if(!empty($search['id'])) {
			$condition['t.id'] = $search['id'];
		}
		if(!empty($search['product'])) {
			$condition['product'] = $search['product'];
		}
		if(!empty($search['start']) && !empty($search['end'])) {
			$condition['time'] = "(t.send_time >='{$search['start']} 00:00:00' AND t.send_time <= '{$search['end']} 23:59:59')";
		} else if(!empty($search['start'])) {
			$condition['time'] = "(t.send_time >='{$search['start']} 00:00:00' )";
		} else if(!empty($search['end'])) {
			$condition['time'] = "(t.send_time <='{$search['end']} 23:59:59' )";
		}
		$products = new Model_Product();
		$productData= $products->getDroupdownData();
		$enterprise = new Model_Enterprise();
		$mesageModel = new Model_Message();
		$status = $mesageModel->getStatusMap();
		$select = $mesageModel->createCondition($condition);
		$paginator = Zend_Paginator::factory($select);
		$paginator->setItemCountPerPage($this->page_size);// 测试分页 =1
		$paginator->setCurrentPageNumber($page);
		$this->view->paginator = $paginator;
		$this->view->enterprises =  $enterprise->droupDownData();
		$this->view->search = $search;
		$this->view->status = $status;
		$this->view->products = $productData;
	}
	/**
	 * 审核消息
	 */
	public function checkAction(){
		$messageId  = $this->getRequest()->getParam('id');
		if($messageId) {
			$messageModel = new Model_Message();
			//添加审核信息
			if($this->getRequest()->isPost()) {
				$data = $_POST['opinion'];
				if($data['pass'] == '1') {
					$status = Model_Message::STATUS_PENDING;
				} else if($data['pass'] == '0') {
					$status = Model_Message::STATUS_CHECK_FAILED;
				}
				$comments = $data['comments'];
				if($messageModel->update(array('status' => $status, 'comments' => $comments), "id=$messageId")) {
					return Common_Protocols::generate_json_response();
				} else {
					return Common_Protocols::generate_json_response(null ,Common_Protocols::ERROR);
				}
			} else {
				$info = $messageModel->getFieldsById('*', $messageId);
				if (!empty($info['products'])) {
					$sql = 'SELECT name FROM EZ_PRODUCT WHERE id IN (' . $info['products'] .')';
					$product = $messageModel->get_db()->fetchCol($sql);
					$info['product_name'] = $product;
				}
				$this->view->info = $info;
			}
		} else {
			throw new Zend_Exception('参数错误');
		}
	}
	/**
	 * 查看消息
	 */
	public function viewAction(){
		$messageId  = $this->getRequest()->getParam('id');
		if($messageId) {
			$messageModel = new Model_Message();
			$info = $messageModel->getFieldsById('*', $messageId);
			if (!empty($info['products'])) {
				$sql = 'SELECT name FROM EZ_PRODUCT WHERE id IN (' . $info['products'] .')';
				$product = $messageModel->get_db()->fetchCol($sql);
				$info['product_name'] = $product;
			}
			$this->view->info = $info;
		} else {
			throw new Zend_Exception('参数错误');
		}
	}
	/**
	 * 添加或者修改记录
	 */
	public function editAction(){
		$messageId  = $this->getRequest()->getParam('id');
        $this->view->messageId = $messageId;
		$messageModel = new Model_Message();
		if($this->getRequest()->isPost()) {
			$data = $_POST['message'];
			$validate = $this->validateMessage($data);
			if($validate === true) {
				if($messageId) {
					$messageModel->update($data, "id = '{$messageId}'");
					return  Common_Protocols::generate_json_response();
				} else {
					$data['status'] = Model_Message::STATUS_NEED_CHECK;
					$data['sender'] = $_SESSION['UserInfo']['user_info']['user_name'];
					if(empty($data['send_time'])) {
						$data['send_time'] = date('Y-m-d H:i:s', time());
					}
                    // 这里需要指定遥控E族的默认enterprise_id
                    $data['enterprise_id'] = Model_Enterprise::DEFAULT_EZ_ID;
					if($messageModel->insert($data)) {
						return Common_Protocols::generate_json_response();
					}
				}
				return Common_Protocols::generate_json_response(null, Common_Protocols::ERROR);
			} else {
				return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED, '验证失败');
			}
		} else {
			if($messageId) {
				$info = $messageModel->getFieldsById('*', $messageId);
				if (!empty($info['products'])) {
					$sql = 'SELECT name FROM EZ_PRODUCT WHERE id IN (' . $info['products'] .')';
					$product = $messageModel->get_db()->fetchCol($sql);
					$info['product_name'] = $product;
					$sql = 'SELECT id,name FROM EZ_PRODUCT WHERE id IN (' . $info['products'] .')';
					$productInfo = $messageModel->get_db()->fetchPairs($sql);
					$info['product_str_info'] = json_encode($productInfo);
				}
				$this->view->info = $info;
			}
		}
	}
	/**
	 * 删除消息
	 */
	public function deleteAction(){
		$messageId  = $this->getRequest()->getParam('id');
		if($messageId) {
			$messageModel = new Model_Message();
			$result = $messageModel->getFieldsById(array('enterprise_id', 'status'), $messageId);
			if(($result['status'] == Model_Message::STATUS_PENDING ) && $result['enterprise_id'] == Model_Enterprise::DEFAULT_EZ_ID) {
				$sql = $messageModel->get_db()->quoteInto('id = ?',$messageId);
				if($messageModel->get_db()->delete('EZ_MANAGER_MESSAGE', $sql)) {
					return Common_Protocols::generate_json_response();	
				} else {
					return Common_Protocols::generate_json_response(null, Common_Protocols::ERROR);
				}
			} else {
				return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED);
			}
		}
	}
	/**
	 * 消息添加验证
	 * @param array $data
	 * @return boolean|Ambigous <multitype:, multitype:string >
	 */
	protected  function validateMessage($data){
		$post = ZendX_Validate::factory($data)->labels(array(
						'title' => 'title',
						'content' => 'content',
						'type' => 'type',
						'send_time' => 'send_time',
						// 	  					'receive_type' => 'receive_type',
						'url' => 'url'
		));
		$post->rules(
						'title',
						array(
										array('not_empty'),
										array('max_length',array(':value',20))
						)
		);
		$post->rules(
						'content',
						array(
										array('not_empty'),
										array('max_length',array(':value',200))
						)
		);
		$post->rules('url', array(
						array('url')
		));
		$post->rules('type', array(
						array('digit')
		));
		if($post->check()){
			return true;
		} else {
			return $post->errors('validate');
		}
	}
	/**
	 * 查询产品列表
	 */
	public function productsAction() {
		//查询版本列表
		$page = $this->getRequest()->getParam('page', 1);
        $offset = $this->getRequest()->getParam('offset', 10);
        $selectModel = $this->getRequest()->getParam('selectmodel', 'checkbox');
		$search = $this->_request->getParam("search");
		$condition = array();
		$model = new Model_Product();
		$select = $model->get_db()->select();
		$select->from(array('t'=>'EZ_PRODUCT', array('name','id')));
		$select->joinLeft( array('e' => 'EZ_ENTERPRISE'),"t.enterprise_id=e.id", array('company_name'));
		$select->joinLeft( array('c' => 'EZ_PRODUCT_CATEGORY'),"t.category_id=c.id", array('c_name'=>'name'));
		if(!empty($search['product_name'])){
			$select->where("t.name LIKE  '%".  $search['product_name'] ."%'");
		}
		if(!empty($search['enterprise_id'])) {
		    $select->where('t.enterprise_id =?', $search['enterprise_id']);
		}
		$productCate = new Model_ProductCate();
		//类别查询
		if(!empty($search['category'])) {
			$categoryIds = $productCate->getAllChildrenById($search['category']);
			if($categoryIds) {
				$categoryIds[] = $search['category'];
			} else {
				$categoryIds = array($search['category']);
			}
			$select->where("t.category_id IN(" . implode(',', $categoryIds) . ")");
		}
		$select->where("t.status = 'enable'");
		$select->order('t.ctime DESC');
		$paginator = Zend_Paginator::factory($select);
		$paginator->setItemCountPerPage($offset);// 测试分页 =1
		$paginator->setCurrentPageNumber($page);
		$this->view->paginator = $paginator;
		$enterprise = new Model_Enterprise();
		$this->view->enterprises =  $enterprise->droupDownData();
		$this->view->category = $productCate->dumpTree();
		$this->view->search = $search;
        $this->view->base_url .= (!strpos($this->view->base_url, 'selectmodel='))?('&selectmodel='.$selectModel):'';
        //print_r($this->view->base_url);exit;
        $this->view->select_model = $selectModel;
		echo $this->view->render('message_products.phtml'); 
	}
	
	/**
	 * 检查测试发送的账号和用户名是否正确
	 */
	public function checktestuserAction(){
		if($this->getRequest()->isPost()) {
			$username = $this->getRequest()->getPost('user_name');
			$password = $this->getRequest()->getPost('password');
			if($username == '' || $password == ''){
				return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED, '请输入用户名/密码');
			}
			$modelUser = new Model_Member();
			if(strpos($username, "@") === false){ //手机登录
				$userInfo = $modelUser->getRowByField(array('id', 'salt', 'password'), array('phone' => $username));
			}else{ //邮箱登录
				$userInfo = $modelUser->getRowByField(array('id', 'salt', 'password'), array('email' => $username));
			}
			if(!empty($userInfo)){
				$password = md5(md5($password).$userInfo['salt']);
				if($password == $userInfo['password']){
					$_SESSION['test_transfor_uid'] = $userInfo['id'];
					return Common_Protocols::generate_json_response();
				} else {
					return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED, '密码输入错误' );
				}
			} else {
				return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED, '用户名不存在' );
			}
		}
	}
	
	/**
	 * 处理测试发送
	 */
	public function dotesttransAction(){	
		if($this->getRequest()->isPost()) {
			$title = $this->getRequest()->getPost('title');
			$content = $this->getRequest()->getPost('content');
			$linkUrl = $this->getRequest()->getPost('linkurl');
			$type = $this->getRequest()->getPost('type');	
			if($content == '' || $type == ''){
				return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED, '消息验证错误');
			}
			$userId = $_SESSION['test_transfor_uid'];	
			if($userId){
				$data = array(
								'content' => $content,
								'url' => $linkUrl,
								'status' => 'unread',
								'ctime' => date('Y-m-d H:i:s'),
								'type' => $type,
								'user_id' => $userId,
								'title' => $title
				);
				$model = new Model_Message();
				$flag = $model->get_db()->insert('EZ_USER_MESSAGE', $data);
				if($flag){
					    unset($_SESSION['test_transfor_uid']);
						return Common_Protocols::generate_json_response();
				} else {
					return Common_Protocols::generate_json_response(null, Common_Protocols::ERROR, '发送失败');
				}
			} else {
				return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED, '登录失败');
			}
		}
	}
	/**
	 * 需要审核的消息
	 */
	public function needcheckAction(){
		$message = new Model_Message();
		$number = $message->getNumberBystatus(Model_Message::STATUS_NEED_CHECK);
		if($number) {
			return Common_Protocols::generate_json_response($number);
		} else {
			return Common_Protocols::generate_json_response(0, Common_Protocols::ERROR);
		}
	}
	 
}