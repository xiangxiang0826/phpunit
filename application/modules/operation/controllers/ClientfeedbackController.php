<?php

class Operation_ClientFeedbackController extends ZendX_Controller_Action{
	const UID = 1;
	const LABEL = 'ezu';
	private $_debug;
	private $_onlineFeedbackModel, $_onlineFeedbackMessageModel, $_feedbackTagMapModel, $_onlineFeedbackTagModel, $_productModel, $_deviceModel, $_enterpriseAppModel, $_userModel, $_userDeviceModel, $_deviceSnModel, $_enterpriseModel, $productCateModel;	
	public function init() {
		$this->_onlineFeedbackModel = new Model_OnlineFeedback();
		$this->_productModel = new Model_Product(); 
		$this->_onlineFeedbackTagModel = new Model_OnlineFeedbackTag();
		$this->_onlineFeedbackMessageModel = new Model_OnlineFeedbackMessage();
		$this->_deviceModel = new Model_Device();
		$this->_userModel = new Model_Member();
		$this->_enterpriseAppModel = new Model_EnterpriseApp();
		$this->_userDeviceModel = new Model_UserDeviceMap();
		$this->_feedbackTagMapModel = new Model_FeedbackTagMap(); 
		$this->_deviceSnModel = new Model_DeviceSn();
		$this->_enterpriseModel = new Model_Enterprise();
		$this->_productCateModel = new Model_ProductCate();
		$this->_debug = 0;
		parent::init();
				
	}
	
	/**
	 * 在线反馈列表
	 */
	public function indexAction(){
		$page = $this->_request->getParam('page', 1);
		$rows = $this->_request->getParam('rows', $this->page_size);
		$offset = ($page-1)*$rows;
		
		//todo get data from db 
		$query = " AND of.label != '" .self::LABEL ."' ";
		$subQuery = "";
		$search = $this->_request->getParam('search');
		$search['status'] = isset($search['status']) ? $search['status'] : 'toreply';
		if(!empty($search['enterprise'])){
			$query .= " AND e.id =".$search['enterprise'];
		}
		
		if(!empty($search['product'])){
			$query .= " AND p.id =".$search['product'];
		}
		
		if(!empty($search['tag'])){
			$subQuery .= " WHERE  tag_id = ".$search['tag'];
		}
		
		if(!empty($search['mobile'])){
			$query .= " AND u.phone LIKE '%".$search['mobile']."%'";
		}
		
		if(!empty($search['email'])){
			$query .= " AND u.email LIKE '%".$search['email']."%'";
		}
		
		if(!empty($search['status'])){
			$query .= " AND of.status = '".$search['status']."'";
		}
		$tagEnterpriseId = isset($search['enterprise']) ? $search['enterprise'] : 0;
		$tagQuery = $tagEnterpriseId ? 'enterprise_id='.$tagEnterpriseId : 'enterprise_id != 1';
		$tags = $this->_onlineFeedbackTagModel->getList($tagQuery);
		
		$queryProductArray = array('status'=>Model_Product::STATUS_ENABLE,'product_id'=>0);
		if($tagEnterpriseId) $queryProductArray['enterprise_id'] = $tagEnterpriseId;
		$products = $this->_productModel->GetList($queryProductArray, 0, 100);
		$product_list = array();
		foreach($products['list'] as $product) {
			$product_list[$product['id']] = $product;
			$product_list[$product['id']]['name'] = mb_strimwidth($product['name'], 0, 16, '...');
		}
		
		$ItemArray = $this->_onlineFeedbackModel->getList($query, $subQuery, $offset, $rows);
		$itemCount = $this->_onlineFeedbackModel->getCount($query, $subQuery);
		$paginator = new Zend_Paginator(new Zend_Paginator_Adapter_Null($itemCount));
		$paginator->setCurrentPageNumber($page)->setItemCountPerPage($rows);
		$enterpriseDropdown = $this->_enterpriseModel->droupDownData();
		unset($enterpriseDropdown['1']);
		$this->view->enterprises = $enterpriseDropdown;
		$this->view->product = $product_list;
		$this->view->search = $search;
		$this->view->paginator = $paginator;
		$this->view->tags = $tags;
		$this->view->items = $ItemArray['item'];
	}
	
	/**
	 * 在线反馈详情页
	 */
	public function detailAction(){
		$user = Common_Auth::getUserInfo();
		$this->view->loginUser = $user['real_name'];
		$id = $this->_request->getParam('id');
		if(empty($id)){
			throw new Zend_Exception('反馈ID异常！');
		} 
		$feedbackMain = $this->_onlineFeedbackModel->getFeedbackById($id);
		$feedbackMessage = $this->_onlineFeedbackMessageModel->getList($id);		
		$enterpriseId = $this->_enterpriseModel->getByLabel($feedbackMain['label']); 
		$tagsAll = $this->_onlineFeedbackTagModel->getList('enterprise_id='.$enterpriseId['id']);
		$tagsAllMap = array();
		$tagMessageArr = array();
		
		if(!empty($tagsAll)){
			foreach($tagsAll as $v){
				$tagsAllMap[$v['id']] = $v['name'];
			}
			$tagMessage = $this->_feedbackTagMapModel->getList($id);
			
			if(!empty($tagMessage)){
				foreach($tagMessage as $v){
					$tagMessageArr[$v['tag_id']]['name'] = $tagsAllMap[$v['tag_id']];
				}
			}
		}
		$product = array();
		if($feedbackMain['product_id']){
			$product = $this->_productModel->FindByIds(array($feedbackMain['product_id']));
			$product = $product[0];
		}
		$deviceArr = array();
		if($feedbackMain['device_id']){
			$deviceArr = $this->_deviceModel->getDetailById($feedbackMain['device_id']);
		}		
		if($deviceArr){
			$deviceArr['sn'] = $this->_deviceSnModel->getDeviceById($feedbackMain['device_id']);
		}
		
		$appArr = $this->_enterpriseAppModel->getByLabel($feedbackMain['label'],$feedbackMain['platform']);
		// test data
		//$feedbackMain['app_id'] = 'pfe43efdddc9f0d98d4e44b68c5d55f19';
		
		$userArr = $this->_userModel->getByAppID($feedbackMain['app_id']);
		$changeFlag = 0;
		//compare userid to see anychange happens
		$intriArr = array(
				'key' => 'appid',
				'value' => $feedbackMain['app_id']
		);
		if($feedbackMessage){
			$reverseFeedbackMessage = array_reverse($feedbackMessage);
			foreach($reverseFeedbackMessage as $v){
				if($v['type'] == 'feedback' && $v['user_id'] ){
					$intriArr['key'] = 'user_id';
					$intriArr['value'] = $v['user_id'];
					if($userArr['id'] != $v['user_id']){
						$userArr['id'] = $v['user_id'];
						$changeFlag = 1;
					}
					break;
				}
			}
		}
		if($changeFlag)
			$userArr = $this->_userModel->getInfoByUid($userArr['id']);
		$userDeviceArr = array();
		if($userArr['id']){
			$userDeviceArr = $this->_userDeviceModel->getDeviceList($userArr['id']);
		}
		if($userArr){
			$userArr['device_info'] = $userDeviceArr;
		}
		$this->view->id = $id;
		$this->view->enterprise = $enterpriseId;
		$this->view->device = $deviceArr;
		$this->view->app = $appArr;
		$this->view->user = $userArr;
		$this->view->main = $feedbackMain;
		$this->view->product = $product;
		$this->view->message = $feedbackMessage;
		$this->view->tags = $tagsAll;
		$this->view->tagMessage = $tagMessageArr;
		$this->view->intri = $intriArr;
	}
	
	/**
	 * 回复
	 */
	public function replyAction(){
		$user = Common_Auth::getUserInfo();
		$data['content'] = $this->_request->getParam('content');
		$data['feedback_id'] = $this->_request->getParam('id');
		$data['content_type'] = 'text';
		$data['type'] = 'reply';
		$data['ctime'] = date('Y-m-d H:i:s');
		$data['reply_user'] = $user['real_name'];
		$intri_key = $this->_request->getParam('intri_key');
		$intri_value = $this->_request->getParam('intri_value');
		$update['reply_count'] = (int)$this->_request->getParam('reply_count') + 1;
		$update['reply_time'] = date('Y-m-d H:i:s');
		$update['status'] = 'replied';
		$db = $this->_onlineFeedbackMessageModel->get_db();
		$db->beginTransaction();
		try{
			$insertID = $this->_onlineFeedbackMessageModel->insert($data);
			if(!$this->_onlineFeedbackModel->update($update, "id='" .$data['feedback_id']."'")){
				throw new Zend_Exception('更新FEEDBACK表出错');
			}
			$pushRuleJson = array(
                    		array(
                    			'key'		=>	$intri_key,
                    			'condition' =>	'contain',
                    			'value' 	=>	$intri_value
                    				)
                    		);
			$intraData = array(
                    'message_type' => 'notice',
                    'push_type' => 'accurate',
                    'push_rule' => $pushRuleJson,					
                    'is_appbox' => 0,
                    'is_accountbox' => 0,
                    'send_type' => 'real_time',
                    'start_time' => date('Y-m-d H:i:s'),
                    'expire_time' => '',
                    'is_offline' => 1,
                    'is_test' => 0,
                    'content' => array(
                    		'title'=>'',
                    		'text'=>$data['content'],
                    		'go'=>'activity',
                    		"sound"=>"default",
                    		"badge" => 1,
                    		"style_id" => 0,                    		
                    		'extend'=>array(
                    				array('id'=>$insertID),
                    				array('activity_android'=>'123'),
                    				array('activity_ios'=>'')
                    				)
                    		)
                );
			$resJson = $this->_sendApiRequest('/push/send/',$intraData);
			$db->commit();
		}catch( Exception $e ){
			$db->rollback();
			return Common_Protocols::generate_json_response('',Common_Protocols::EXISTS_ERROR,$e->getMessage());
		}
		return Common_Protocols::generate_json_response($data,Common_Protocols::SUCCESS,'');
	}
	
	/**
	 * 完成按钮
	 */
	public function remarkAction(){
		$id = $this->_request->getParam('id');
		$tags = $this->_request->getParam('tags');
		$tagsChecked = $this->_request->getParam('tags_checked');
		$tagsCheckedOld= $this->_request->getParam('tags_checked_old');
		$enterpriseId = $this->_request->getParam('enterprise_id');
		$tagsNew = $this->_request->getParam('tags_new');
		
		$tagsArr = explode(',',$tags);
		$tagsCheckedArr = explode(',',$tagsChecked);
		$tagsCheckedOldArr = explode(',',$tagsCheckedOld);
		$tagsNewArr = explode(',',$tagsNew);
		$delArr = array_filter(array_diff($tagsCheckedOldArr,$tagsCheckedArr));
		$addArr = array_filter(array_diff($tagsCheckedArr, $tagsCheckedOldArr));
		
		if(empty($id) || empty($enterpriseId)){
			return Common_Protocols::generate_json_response('',Common_Protocols::EXISTS_ERROR,'数据异常');
		}
		$data['remark'] = $this->_request->getParam('remark');
		$db = $this->_onlineFeedbackModel->get_db();
		$db->beginTransaction();
		try{
			$this->_onlineFeedbackModel->update($data,"id='$id'");
			//删除旧的关系数据
			if(!empty($delArr)){
				foreach($delArr as $v){
					$this->_feedbackTagMapModel->get_db()->delete('EZ_FEEDBACK_TAG_MAP',"tag_id=$v and feedback_id='$id'");
				}
			}
			
			//添加新的关系
			if(!empty($addArr)){
				foreach($addArr as $v){
					$tmpTagOld = array(
							'tag_id' => $v,
							'feedback_id' => $id
							);
					$this->_feedbackTagMapModel->insert($tmpTagOld);					
				}
			}
			 
			//插入新的tag标签 并插入新的关系数据
			if(!empty($tagsNewArr)){
				foreach($tagsNewArr as $v){
					if(empty($v)){
						continue;
					}
					$tmpTag = array(
							"name" 			=> $v,
							"enterprise_id"	=> $enterpriseId,
							"ctime"			=> date('Y-m-d H:i:s')
							);
					$mapId = $this->_onlineFeedbackTagModel->insert($tmpTag);
					if($mapId){
						$tmpTagMap = array(
								"feedback_id" 	=> $id,
								"tag_id"		=> $mapId
						);
						$this->_feedbackTagMapModel->insert($tmpTagMap);
					}					
				}
			}			
			$db->commit();
		}catch( Exception $e ){
			$db->rollback();
			return Common_Protocols::generate_json_response('',Common_Protocols::EXISTS_ERROR,$e->getMessage());					
		}
		return Common_Protocols::generate_json_response($enterpriseId,Common_Protocols::SUCCESS,'');
	}
	
	/**
	 * 反馈的问题详情
	 */
	public function gettagAction(){
		$enterpriseId = $this->_request->getParam('enterprise_id');
		$tagList = array();
		$query = $enterpriseId ? 'enterprise_id='.$enterpriseId : '';
		$tagList = $this->_onlineFeedbackTagModel->getList($query);
		if(!$enterpriseId) array_unshift($tagList,array('id'=>0,'name'=>'所有标签'));
		return Common_Protocols::generate_json_response($tagList);
	}	
	
	
	/**
     * 调用api.1719.com的接口数据
     * 
     * @param string $url 请求的URL地址
     * @param array $postData 发送的post数据
     * @return mixed boolean|string 请求的结果
     */
    private function _sendApiRequest($url, $postData){
    	if($this->_debug)	return 1;
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
            //var_dump($post);exit;
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
     * 反馈待回复条数
     */
    public function needchecknumAction(){
    	$where = " AND label != 'ezu' AND status = 'toreply'";
    	$info = $this->_onlineFeedbackModel->getTotal($where);
    	return Common_Protocols::generate_json_response($info);
    
    }
    
   
    
    /* 根据厂商ID获取产品列表 */
    public function getproductsAction() {
    	if($this->getRequest()->isPost()) {
    		$enterpriseId = $this->getRequest()->getPost('enterprise_id');
    		$queryArray = array('status'=>Model_Product::STATUS_ENABLE);
    		if($enterpriseId) $queryArray['enterprise_id'] = $enterpriseId;  
    		$products = $this->_productModel->GetList($queryArray, 0, 100);
    		if(isset($products['list'])){
	    		foreach($products['list'] as $k => $v){
	    			$products['list'][$k]['name'] = mb_strimwidth($v['name'], 0, 16, '...');
	    		}
    		}
    		if(!$enterpriseId)
    			array_unshift($products['list'],array('id'=>0,'name'=>'所有产品'));
    		return Common_Protocols::generate_json_response($products);
    	}
    }
	
}