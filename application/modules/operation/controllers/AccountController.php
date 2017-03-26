<?php
/**
 * 账号消息管理
 * 
 * @author etong <zhoufeng@wondershare.cn>
 * @version $v1 2014/11/22 11:40$
 */
class Operation_AccountController extends ZendX_Controller_Action
{
	/**
	 * 官方推送消息列表
	 */
	public function indexAction(){
		//查询版本列表
		$page = $this->getRequest()->getParam('page', 1);
		$search = $this->_request->getParam("search");
		$condition = array();
		if(!empty($search['title'])) {
			$condition['t.title'] = $search['title'];
		}
		if(!empty($search['status'])) {
			$condition['t.status'] = $search['status'];
		}
		$mesageModel = new Model_PushMessage();
		$status = $mesageModel->getStatusMap();
        $listStatus = $mesageModel->getListStatusMap();
        $publishedStatus = $mesageModel->getSendingStatusMap();
		$select = $mesageModel->createCondition($condition, $type='account');
		$paginator = Zend_Paginator::factory($select);
		$paginator->setItemCountPerPage($this->page_size);// 测试分页 =1
		$paginator->setCurrentPageNumber($page);
        
        $checkIds = array();
        foreach($paginator->getItemsByPage($page) as $item){
            if($item['status'] == Model_PushMessage::STATUS_PUBLISHED){
                $checkIds[] = $item['message_id'];
            }
        }
        $postData = array('message_id' => $checkIds);
        $result = $this->_sendApiRequest('/push/status/', $postData);
        $publishedInfo = isset($result['result'])?$result['result']:array();
		// $enterprise = new Model_Enterprise();
		$this->view->paginator = $paginator;
		$this->view->search = $search;
		$this->view->status = $status;
        $this->view->listStatus = $listStatus;
        $this->view->publishedInfo = $publishedInfo;
        $this->view->publishedStatus = $publishedStatus;
	}
    
	/**
	 * 厂商推送消息列表
	 */
	public function enterpriseAction(){
		//查询版本列表
		$page = $this->getRequest()->getParam('page', 1);
		$search = $this->_request->getParam("search");
		$condition = array();
		if(!empty($search['title'])) {
			$condition['t.title'] = $search['title'];
		}
		if(!empty($search['status'])) {
			$condition['t.status'] = $search['status'];
		}
		$mesageModel = new Model_PushMessage();
		$status = $mesageModel->getStatusMap();
        $listStatus = $mesageModel->getListStatusMap();
        $publishedStatus = $mesageModel->getSendingStatusMap();
		$select = $mesageModel->createConditionForEnterprise($condition, $type='account');
		$paginator = Zend_Paginator::factory($select);
		$paginator->setItemCountPerPage($this->page_size);// 测试分页 =1
		$paginator->setCurrentPageNumber($page);
        
        $checkIds = array();
        foreach($paginator->getItemsByPage($page) as $item){
            if($item['status'] == Model_PushMessage::STATUS_PUBLISHED){
                $checkIds[] = $item['message_id'];
            }
        }
        $postData = array('message_id' => $checkIds);
        $result = $this->_sendApiRequest('/push/status/', $postData);
        $publishedInfo = isset($result['result'])?$result['result']:array();
		// $enterprise = new Model_Enterprise();
		$this->view->paginator = $paginator;
		$this->view->search = $search;
		$this->view->status = $status;
        $this->view->listStatus = $listStatus;
        $this->view->publishedInfo = $publishedInfo;
        $this->view->publishedStatus = $publishedStatus;
	}
    
    /**
     * 重新推送消息
     */
    public function pushAction(){
        $messageId  = $this->getRequest()->getParam('id');
        if($messageId) {
			$messageModel = new Model_PushMessage();
            $pushMessageId = '';
            $errorCode = '';
            $errorContent = '';
			//添加审核信
            $status = Model_PushMessage::STATUS_AUDIT_SUCCESS;
            $info = $messageModel->getFieldsById('*', $messageId);
            $content = json_decode($info['original_message'], TRUE);
            
            if(isset($content['ex'])){
                $content['ex'][] = array('id' => $messageId);
                $content['extend'] = $content['ex'];
                unset($content['ex']);
            }else{
                $content['extend'] = array( array('id' => $messageId) );
            }
            if($info['user_type'] == 'all'){
                $pushRule = array(
                    array(
                        'key' => 'app',
                        'condition' => 'contain',
                        'value' => 'all'
                   )
                );
                $info['push_rule'] = json_encode($pushRule);
            }
            //添加异常消息的处理
            if($info['expire_time'] == '0000-00-00 00:00:00'){
                $info['expire_time'] = '';
            }
             if($info['start_time'] == '0000-00-00 00:00:00'){
                $info['start_time'] = '';
            }
            $postData = array(
                'message_type' => $info['message_type'],
                'push_type' => $info['push_type'],
                'push_rule' => json_decode($info['push_rule'], TRUE),
                'is_appbox' => (int)$info['is_appbox'],
                'is_accountbox' => (int)$info['is_accountbox'],
                'send_type' => $info['send_type'],
                'start_time' => $info['start_time'],
                'expire_time' => $info['expire_time'],
                'is_offline' => (int)$info['is_offline'],
                'is_test' => (int)$info['is_test'],
                'content' => $content
            );
             //发送api通知
            $result = $this->_sendApiRequest('/push/send/', $postData);
            if($result){
                if($result['status'] == 200){
                    if($result['result']['message_id']){
                        $pushMessageId = $result['result']['message_id'];
                        $status = Model_PushMessage::STATUS_PUBLISHED;
                    }
                }else{
                    $errorCode = $result['status'];
                    $errorContent = $result['msg'];
                    $status = Model_PushMessage::STATUS_PUBLISH_FAILED;
                }
            }
            $updateData = array(
                'status' => $status,
                'message_id' => $pushMessageId,
                'error_code' => $errorCode,
                'error_content' => $errorContent
             );
            $messageModel->update($updateData, "id=$messageId");
            return Common_Protocols::generate_json_response();
		} else {
			throw new Zend_Exception('参数错误');
		}
    }
    
	/**
	 * 审核消息
	 */
	public function checkAction(){
		$messageId  = $this->getRequest()->getParam('id');
        $dimensionArr = array(
            'product' => '产品'
        ); 
        $conditionArr = array(
            'contain' => '包含',
            'exclude' => '不包含'
        );
        $unitArr = array(
            'day' => '天',
            'hour' => '小时'
        ); 
		if($messageId) {
			$messageModel = new Model_PushMessage();
			//添加审核信息
			if($this->getRequest()->isPost()) {
				$data = $_POST['opinion'];
                $pushMessageId = 0;
                $errorCode = '';
                $errorContent = '';
				if($data['pass'] == '1') {
					$status = Model_PushMessage::STATUS_AUDIT_SUCCESS;
                    $info = $messageModel->getFieldsById('*', $messageId);
                    // 需要判断时间参数等等
                    $dateNow = date('Y-m-d H:i:s');
                    if($info['start_time'] && $info['start_time'] != '0000-00-00 00:00:00' && $info['start_time'] <= $dateNow){
                        return Common_Protocols::generate_json_response(null ,Common_Protocols::VALIDATE_FAILED, '定时发送时间必须大于当前时间');
                    }
                    $content = json_decode($info['original_message'], TRUE);
                    if(isset($content['ex'])){
                        $content['ex'][] = array('id' => $messageId);
                        $content['extend'] = $content['ex'];
                        unset($content['ex']);
                    }else{
                        $content['extend'] = array( array('id' => $messageId) );
                    }
                    if($info['enterprise_id'] != Model_Enterprise::DEFAULT_EZ_ID){
                        $modelEnterprise = new Model_Enterprise();
                        $enterpriseInfo = $modelEnterprise->getFieldsById('label', $info['enterprise_id']);
                        $label = $enterpriseInfo['label'];
                        $addPush = array(
                            'key' => 'app_label',
                            'condition' => 'contain',
                            'value' => $label
                        );
                        if($info['user_type'] == 'all'){
                            $pushRule = array();
                        }else{
                            $pushRule = json_decode($info['push_rule'], TRUE);
                        }
                        $pushRule = array_merge($pushRule, array($addPush));
                        $info['push_rule'] = json_encode($pushRule);
                    }else{
                        if($info['user_type'] == 'all'){
                            $pushRule = array(
                                array(
                                    'key' => 'app',
                                    'condition' => 'contain',
                                    'value' => 'all'
                               )
                            );
                            $info['push_rule'] = json_encode($pushRule);
                        }
                    }

                    
                    //添加异常消息的处理
                    if($info['expire_time'] == '0000-00-00 00:00:00'){
                        $info['expire_time'] = '';
                    }
                     if($info['start_time'] == '0000-00-00 00:00:00'){
                        $info['start_time'] = '';
                    }
                    
                    $postData = array(
                        'message_type' => $info['message_type'],
                        'push_type' => $info['push_type'],
                        'push_rule' => json_decode($info['push_rule'], TRUE),
                        'is_appbox' => (int)$info['is_appbox'],
                        'is_accountbox' => (int)$info['is_accountbox'],
                        'send_type' => $info['send_type'],
                        'start_time' => $info['start_time'],
                        'expire_time' => $info['expire_time'],
                        'is_offline' => (int)$info['is_offline'],
                        'is_test' => (int)$info['is_test'],
                        'content' => $content
                    );
                    // 
                    //发送api通知
                    $result = $this->_sendApiRequest('/push/send/', $postData);
                    if($result){
                        if($result['status'] == 200){
                            if($result['result']['message_id']){
                                $pushMessageId = $result['result']['message_id'];
                                $status = Model_PushMessage::STATUS_PUBLISHED;
                            }
                        }else{
                            $errorCode = $result['status'];
                            $errorContent = $result['msg'];
                            $status = Model_PushMessage::STATUS_PUBLISH_FAILED;
                        }
                    }
				} else if($data['pass'] == '0') {
					$status = Model_PushMessage::STATUS_AUDIT_FAILED;
				}
				$comments = $data['comments'];
                $updateData = array(
                    'status' => $status, 
                    'comments' => $comments,
                    'message_id' => $pushMessageId,
                    'error_code' => $errorCode,
                    'error_content' => $errorContent
                 );
				$messageModel->update($updateData, "id=$messageId");
                return Common_Protocols::generate_json_response();

			} else {
				$info = $messageModel->getFieldsById('*', $messageId);
				if (!empty($info['products'])) {
					$sql = 'SELECT name FROM EZ_PRODUCT WHERE id IN (' . $info['products'] .')';
					$product = $messageModel->get_db()->fetchCol($sql);
					$info['product_name'] = $product;
				}
                if($info['push_rule']){
                    $pushRule = json_decode($info['push_rule'], TRUE);
                    if(count($pushRule)){
                        for($i =0, $count = count($pushRule); $i<$count; $i++){
                            $item  = $pushRule[$i];
                            $showvalue = $this->_getShowvalue($item['key'], $item['value']);
                            $item['value'] = $showvalue;
                            $pushRule[$i] = $item;
                        }
                        $info['push_rule'] = json_encode($pushRule);
                    }
                }
                // print_r($info['push_rule']);exit;
                $this->view->dimensions = $dimensionArr;
                $this->view->conditions = $conditionArr;
                $this->view->units = $unitArr;
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
        $dimensionArr = array(
            'product' => '产品'
        );
        $conditionArr = array(
            'contain' => '包含',
            'exclude' => '不包含'
        );
        $unitArr = array(
            'day' => '天',
            'hour' => '小时'
        );
		if($messageId) {
			$messageModel = new Model_PushMessage();
			$info = $messageModel->getFieldsById('*', $messageId);
			if (!empty($info['products'])) {
				$sql = 'SELECT name FROM EZ_PRODUCT WHERE id IN (' . $info['products'] .')';
				$product = $messageModel->get_db()->fetchCol($sql);
				$info['product_name'] = $product;
			}
			if($info['push_rule']){
				$pushRule = json_decode($info['push_rule'], TRUE);
				if(count($pushRule)){
					for($i =0, $count = count($pushRule); $i<$count; $i++){
						$item  = $pushRule[$i];
						$showvalue = $this->_getShowvalue($item['key'], $item['value']);
						$item['value'] = $showvalue;
						$pushRule[$i] = $item;
					}
					$info['push_rule'] = json_encode($pushRule);
				}
			}
            $this->view->dimensions = $dimensionArr;
            $this->view->conditions = $conditionArr;
            $this->view->units = $unitArr;
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
        $dimensionArr = array(
            'product' => '产品'
        ); 
        $conditionArr = array(
            'contain' => '包含',
            'exclude' => '不包含'
        ); 
		$messageModel = new Model_PushMessage();
		if($this->getRequest()->isPost()) {
			$data = $_POST['message'];
			$validate = $this->validateMessage($data);
            $validateOriginal = $this->validateOriginalMessage($data['original_message']);
            $validateOriginalEx = $this->validateOriginalMessageEx($data['original_message']['url']);
			if($validate === true && $validateOriginal === true && $validateOriginalEx === true) {
                $pushTypeArr = array(
                    'all' => 'batch',
                    'special' => 'batch'
                );
                $pushRule = array();
                if($data['user_type'] == 'special'){
                    $check = 1;
                    $keys = array('key', 'condition', 'value');
                    if(isset($data['push_rule']) && is_array($data['push_rule']) && !empty($data['push_rule'])){
                        $len = count($data['push_rule']);
                        for($i=0; $i<$len; $i++){
                            $item = $data['push_rule'][$i];
                            if(is_array($item)){
                                foreach($keys as $key){
                                    if(isset($item[$key])){
                                        $pushRule[$i][$key] = $item[$key];
                                    }else{
                                        $check = $check & 0;
                                    }
                                }
                            }else{
                                $check = $check & 0;
                            }
                        }
                    }else{
                        $check = $check & 0;
                    }
                    if(!$check){
                        return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED, 'PushRule验证失败');
                    }
                    if(empty($pushRule)){
                        return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED, 'PushRule不能为空');
                    }
                    // 检查非空
                    foreach($pushRule as $item){
                        foreach($keys as $key){
                            if($item[$key] == ''){
                                $check = $check & 0;
                            }
                        }
                    }
                    if(!$check){
                        return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED, 'PushRule验证失败');
                    }
                }
                                // 处理扩展字段的代码
                $ex = array();
                if(isset($data['original_message']['extend'])){
                    $len = count($data['original_message']['extend']);
                    $extends = $data['original_message']['extend'];
                    $check = 1;
                    // 需要过滤的键值
                    $filterArr = array('activity_android', 'activity_ios', 'app', 'url', 'custom_go');
                    $keys = array('key', 'value');
                    if($len == 1){
                        if(($extends[0]['key'] && !$extends[0]['value']) || (!$extends[0]['key'] && $extends[0]['value'])){
                            $check = $check & 0;
                        }else{
                            if($extends[0]['key'] && $extends[0]['value']){
                                if(in_array($extends[0]['key'], $filterArr)){
                                    $check = $check & 0;
                                }else{
                                    $ex[] = array(
                                        $extends[0]['key'] => $extends[0]['value']
                                     );
                                }
                            }else{
                                $check = $check & 1;
                            }
                        }
                    }else{
                        for($i=0; $i<$len; $i++){
                            $item = $extends[$i];
                            $exItem = array();
                            if(is_array($item)){
                                if($item['key'] && $item['value']){
                                    if(in_array($item['key'], $filterArr)){
                                        $check = $check & 0;
                                    }else{
                                        $ex[] = array(
                                            $item['key'] => $item['value']
                                         );
                                    }
                                }else{
                                    $check = $check & 0;
                                }
                            }else{
                                $check = $check & 0;
                            }
                        }
                    }
                    if(!$check){
                        return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED, 'Extend验证失败');
                    }
                }
                $startTime = '';
                if($data['send_type'] == 'regular_time'){
                    $startTime = $data['start_time'];
                }
                $expireTime = '';
                $dateNow = date('Y-m-d H:i:s');
                // $ex = array();
                $data['original_message']['go'] = 'none';
                if($data['original_message']['url']){
                    $data['original_message']['go'] = 'url';
                    $ex[] = array(
                            'url' => $data['original_message']['url']
                        );
                }
                $originalMessage = array(
                    'go' => $data['original_message']['go'],
                    'title' => $data['original_message']['title'],
                    'text' => $data['original_message']['text'],
                    'ex' => $ex
                );
                $postData = array(
                    'enterprise_id' => Model_Enterprise::DEFAULT_EZ_ID,
                    'message_type' => 'account',
                    'push_type' => $pushTypeArr[$data['user_type']],
                    'user_type' => $data['user_type'],
                    'push_rule' => json_encode($pushRule),
                    'is_appbox' => 0,
                    'is_accountbox' => 1,
                    'send_type' => $data['send_type'],
                    'start_time' => $startTime,
                    'expire_time' => $expireTime,
                    'is_offline' => 0,
                    'ctime' => $dateNow,
                    'mtime' => $dateNow,
                    'is_test' => 0,
                    'status' => Model_PushMessage::STATUS_PENDING,
                    'title' => $data['original_message']['title'],
                    'text' => $data['original_message']['text'],
                    'original_message' => json_encode($originalMessage)
                );
				if($messageId) {
                    unset($postData['ctime']);
					$messageModel->update($postData, "id = '{$messageId}'");
					return  Common_Protocols::generate_json_response();
				} else {
					if($messageModel->insert($postData)) {
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
                if($info['push_rule']){
                    $pushRule = json_decode($info['push_rule'], TRUE);
                    if(count($pushRule)){
                        for($i =0, $count = count($pushRule); $i<$count; $i++){
                            $item  = $pushRule[$i];
                            $showvalue = $this->_getShowvalue($item['key'], $item['value']);
                            $item['showvalue'] = $showvalue;
                            $pushRule[$i] = $item;
                        }
                        $info['push_rule'] = json_encode($pushRule);
                    }
                }
				$this->view->info = $info;
			}
            $this->view->dimensions = $dimensionArr;
            $this->view->conditions = $conditionArr;
		}
	}
    
	/**
	 * 删除消息
	 */
	public function deleteAction(){
		$messageId  = $this->getRequest()->getParam('id');
		if($messageId) {
			$messageModel = new Model_PushMessage();
			$result = $messageModel->getFieldsById(array('enterprise_id', 'status'), $messageId);
			if($result['enterprise_id']) {
                //删除状态监测的条件
				$sql = $messageModel->get_db()->quoteInto('id = ?',$messageId);
				if($messageModel->get_db()->delete('EZ_PUSH_MESSAGE', $sql)) {
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
						'user_type' => 'user_type',
						'send_type' => 'send_type'
		));
		$post->rules(
						'user_type',
						array(
                               array('in_array', 
                                        array(
                                            ':value', 
                                            array('all','special')
                                        )
                               )
						)
		);
		$post->rules(
                    'send_type', 
						array(
                               array('in_array', 
                                        array(
                                            ':value', 
                                            array('real_time','regular_time')
                                        )
                               )
						)
		);
		if($post->check()){
			return true;
		} else {
			return $post->errors('validate');
		}
	}
    
	/**
	 * 消息添加验证
	 * @param array $data
	 * @return boolean|Ambigous <multitype:, multitype:string >
	 */
	protected  function validateOriginalMessage($data){
		$post = ZendX_Validate::factory($data)->labels(array(
						'title' => 'title',
						'text' => 'text'
		));
		$post->rules(
						'title',
						array(
										array('not_empty'),
										array('max_length',array(':value',20))
						)
		);
		$post->rules(
						'text',
						array(
										array('not_empty'),
										array('max_length',array(':value',50))
						)
		);
		if($post->check()){
			return true;
		} else {
			return $post->errors('validate');
		}
	}
    
	/**
	 * 消息添加验证
	 * @param array $data
	 * @return boolean|Ambigous <multitype:, multitype:string >
	 */
	protected  function validateOriginalMessageEx($url){
        return true;
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
            $userId = $_SESSION['test_transfor_uid'];
            if(!$userId){
                return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED, '登录失败');
            }
            $data = $_POST['message'];
			$validate = $this->validateMessage($data);
            $validateOriginal = $this->validateOriginalMessage($data['original_message']);
            $validateOriginalEx = $this->validateOriginalMessageEx($data['original_message']['url']);
			if($validate === true && $validateOriginal === true && $validateOriginalEx === true) {
                $pushTypeArr = array(
                    'all' => 'batch',
                    'special' => 'batch'
                );
                $pushRule = array();
                if($data['user_type'] == 'special'){
                    $check = 1;
                    $keys = array('key', 'condition', 'value');
                    if(isset($data['push_rule']) && is_array($data['push_rule']) && !empty($data['push_rule'])){
                        $len = count($data['push_rule']);
                        for($i=0; $i<$len; $i++){
                            $item = $data['push_rule'][$i];
                            if(is_array($item)){
                                foreach($keys as $key){
                                    if(isset($item[$key])){
                                        $pushRule[$i][$key] = $item[$key];
                                    }else{
                                        $check = $check & 0;
                                    }
                                }
                            }else{
                                $check = $check & 0;
                            }
                        }
                    }else{
                        $check = $check & 0;
                    }
                    if(!$check){
                        return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED, 'PushRule验证失败');
                    }
                    if(empty($pushRule)){
                        return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED, 'PushRule不能为空');
                    }
                    // 检查非空
                    foreach($pushRule as $item){
                        foreach($keys as $key){
                            if($item[$key] == ''){
                                $check = $check & 0;
                            }
                        }
                    }
                    if(!$check){
                        return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED, 'PushRule验证失败');
                    }
                }
                // 处理扩展字段的代码
                $ex = array();
                if(isset($data['original_message']['extend'])){
                    $len = count($data['original_message']['extend']);
                    $extends = $data['original_message']['extend'];
                    $check = 1;
                    // 需要过滤的键值
                    $filterArr = array('activity_android', 'activity_ios', 'app', 'url', 'custom_go');
                    $keys = array('key', 'value');
                    if($len == 1){
                        if(($extends[0]['key'] && !$extends[0]['value']) || (!$extends[0]['key'] && $extends[0]['value'])){
                            $check = $check & 0;
                        }else{
                            if($extends[0]['key'] && $extends[0]['value']){
                                if(in_array($extends[0]['key'], $filterArr)){
                                    $check = $check & 0;
                                }else{
                                    $ex[] = array(
                                        $extends[0]['key'] => $extends[0]['value']
                                     );
                                }
                            }else{
                                $check = $check & 1;
                            }
                        }
                    }else{
                        for($i=0; $i<$len; $i++){
                            $item = $extends[$i];
                            $exItem = array();
                            if(is_array($item)){
                                if($item['key'] && $item['value']){
                                    if(in_array($item['key'], $filterArr)){
                                        $check = $check & 0;
                                    }else{
                                        $ex[] = array(
                                            $item['key'] => $item['value']
                                         );
                                    }
                                }else{
                                    $check = $check & 0;
                                }
                            }else{
                                $check = $check & 0;
                            }
                        }
                    }
                    if(!$check){
                        return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED, 'Extend验证失败');
                    }
                }
                $startTime = '';
                if($data['send_type'] == 'regular_time'){
                    $startTime = $data['start_time'];
                }
                $expireTime = '';
                $dateNow = date('Y-m-d H:i:s');
                // $ex = array();
                $data['original_message']['go'] = 'none';
                if($data['original_message']['url']){
                    $data['original_message']['go'] = 'url';
                    $ex[] = array(
                        'url' => $data['original_message']['url']
                    );
                }
                $originalMessage = array(
                    'go' => $data['original_message']['go'],
                    'title' => $data['original_message']['title'],
                    'text' => $data['original_message']['text'],
                    'ex' => $ex
                );
                $insertData = array(
                    'enterprise_id' => Model_Enterprise::DEFAULT_EZ_ID,
                    'message_type' => 'account',
                    'push_type' => $pushTypeArr[$data['user_type']],
                    'user_type' => $data['user_type'],
                    'push_rule' => json_encode($pushRule),
                    'is_appbox' => 0,
                    'is_accountbox' => 1,
                    'send_type' => $data['send_type'],
                    'start_time' => $startTime,
                    'expire_time' => $expireTime,
                    'is_offline' => 0,
                    'ctime' => $dateNow,
                    'mtime' => $dateNow,
                    'is_test' => 0,
                    'title' => $data['original_message']['title'],
                    'text' => $data['original_message']['text'],
                    'status' => Model_PushMessage::STATUS_PENDING,
                    'original_message' => json_encode($originalMessage)
                );
                $content = $originalMessage;
                if(isset($content['ex'])){
                    $content['ex'][] = array('id' => 0);
                    $content['extend'] = $content['ex'];
                    unset($content['ex']);
                }else{
                    $content['extend'] = array( array('id' => 0) );
                }
                //  测试时，条件直接重置为uid相关
                $pushRule = array(
                    array(
                        'key' => 'user_id',
                        'condition' => 'contain',
                        'value' => $userId
                   )
                );
                //添加异常消息的处理
                if($startTime == '0000-00-00 00:00:00'){
                    $startTime = '';
                }
                 if($expireTime == '0000-00-00 00:00:00'){
                    $expireTime = '';
                }
                $postData = array(
                    'message_type' => 'account',
                    'push_type' => $pushTypeArr[$data['user_type']],
                    'push_rule' => $pushRule,
                    'is_appbox' => 0,
                    'is_accountbox' => 1,
                    'send_type' => $data['send_type'],
                    'start_time' => $startTime,
                    'expire_time' => $expireTime,
                    'is_offline' => 0,
                    'is_test' => 1,
                    'content' => $content
                );
                
                $errorCode = '';
                $errorContent = '';
                $pushMessageId = '';
                $status = Model_PushMessage::STATUS_PENDING;
                // print_r($postData);exit;
                 //发送api通知
                // print_r(json_encode($postData));exit;
                $result = $this->_sendApiRequest('/push/send/', $postData);
                if($result){
                    if($result['status'] == 200){
                        if($result['result']['message_id']){
                            $pushMessageId = $result['result']['message_id'];
                            $status = Model_PushMessage::STATUS_PUBLISHED;
                        }
                    }else{
                        $errorCode = $result['status'];
                        $errorContent = $result['msg'];
                        $status = Model_PushMessage::STATUS_PUBLISH_FAILED;
                    }
                }
                
                $insertData['status'] = $status;
                $insertData['message_id'] = $pushMessageId;
                $insertData['error_code'] = $errorCode;
                $insertData['error_content'] = $errorContent;
                
                // 记录测试发送的数据到数据表
                /*
                $messageTestModel = new Model_PushMessageTest();
                if($messageTestModel->insert()) {
                    return Common_Protocols::generate_json_response();
                }
                 */
                if($pushMessageId){
                    return Common_Protocols::generate_json_response();
                }
                if($errorCode || $errorContent){
                    $errorCode = '('.$errorCode.')';
                    return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED, '推送服务器返回错误：'.$errorCode.$errorContent);
                }
				return Common_Protocols::generate_json_response(null, Common_Protocols::ERROR);
			} else {
				return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED, '验证失败');
			}
		}
	}
    
    /**
     * 调用api.1719.com的接口数据
     * 
     * @param string $url 请求的URL地址
     * @param array $postData 发送的post数据
     * @return mixed boolean|string 请求的结果
     */
    private function _sendApiRequest($url, $postData){
        $apiCfg = ZendX_Config::get('intra', 'intra_api');
        $version = '1.0.0';
        $post = json_encode($postData);
        include_once 'third_party/APIAuthManager.class.php';
        include_once 'third_party/Snoopy.class.php';
        $apiAuth = APIAuthManager::get_instance();
        $vc = $apiAuth->generate_vc($apiCfg['params']['intra_api_app_key'], $version, $apiCfg['params']['intra_api_salt'], $post);
        $apiUri = $apiCfg['params']['intra_api_host'].$url;
        $apiUri .= '?app_id='.$apiCfg['params']['intra_api_app_key'].'&ver='.$version.'&vc='.$vc;
        $snoopy = new Snoopy();
        $snoopy->read_timeout=30;
        try{
            $snoopy->submit_data($apiUri, $post);
            if($snoopy->results && !$snoopy->timed_out){
                $result = json_decode($snoopy->results, TRUE);
                return $result;
            }
        }catch(Exception $e){
            ZendX_Tool::log('error', $e->getMessage());
        }
        return FALSE;
    }
    
	/**
	 * 需要审核的消息
	 */
	public function needcheckAction(){
		$message = new Model_PushMessage();
		$number = $message->getNumberBystatus(Model_PushMessage::STATUS_PENDING, 'account', 0);
		if($number) {
			return Common_Protocols::generate_json_response($number);
		} else {
			return Common_Protocols::generate_json_response(0, Common_Protocols::ERROR);
		}
	}
	
   /**
     * 获取showvalue的值
     * 
     * @param string $key 键值
     * @param string $value 值
     * @return string 返回查询到的结果
     */
    private function _getShowvalue($key, $value){
        switch($key){
            case 'product':{
                return $this->_getShowvalueByProducts($value);
                break;
            }
            case 'app_label':{
                return $this->_getShowvalueByApps($value);
                break;
            }
            case 'app_platform':{
                return $this->_getShowvalueByPlatforms($value);
                break;
            }
            default:{
                break;
            }
        }
        return '';
    }
	
	/**
	 * 查询产品列表
	 */
	private function _getShowvalueByProducts($pids) {
		//查询版本列表
		$model = new Model_Product();
        $select = $model->get_db()->select();
		$select->from(array('t'=>'EZ_PRODUCT', array('name','id')));
		$select->where('t.id in ('.$pids.')');
        $paginator = Zend_Paginator::factory($select);
        $info = $paginator->getItemsByPage(1);
        $result = array();
        foreach($info as $item){
            $result[$item['id']] = $item['name'];
        }
        $pidsArr = explode(',', $pids);
        $value = '';
        for($i=0,$count=count($pidsArr); $i < $count; $i++){
            $valueItem = isset($result[$pidsArr[$i]])?$result[$pidsArr[$i]]:'';
            $value .= $valueItem.',';
        }
		return $value?substr($value, 0, -1):'';
	}
    
	/**
	 * 查询产品列表
	 */
	private function _getShowvalueByApps($labels) {
		//查询版本列表
        $where = str_replace(',', '" OR t.label="', $labels);
        $where = 't.label="'.$where.'"';
		$model = new Model_Enterprise();
        $select = $model->get_db()->select();
		$select->from(array('t'=>'EZ_ENTERPRISE', array('id','name','label')));
		$select->where($where);
        $paginator = Zend_Paginator::factory($select);
        $info = $paginator->getItemsByPage(1);
        $result = array();
        foreach($info as $item){
            $result[$item['label']] = $item['name'];
        }
        $labelsArr = explode(',', $labels);
        $value = '';
        for($i=0,$count=count($labelsArr); $i < $count; $i++){
            $valueItem = isset($result[$labelsArr[$i]])?$result[$labelsArr[$i]]:'';
            $value .= $valueItem.',';
        }
		return $value?substr($value, 0, -1):'';
	}
    
	/**
	 * 查询平台列表
	 */
	private function _getShowvalueByPlatforms($platforms) {
		//查询版本列表
        $all = array(
            'android' => '安卓平台',
            'ios' => '苹果平台'
        );
        $platformsArr = explode(',', $platforms);
        $value = '';
        for($i=0,$count=count($platformsArr); $i < $count; $i++){
            $valueItem = isset($all[$platformsArr[$i]])?$all[$platformsArr[$i]]:'';
            $value .= $valueItem.',';
        }
        return $value?substr($value, 0, -1):'';
	}
}