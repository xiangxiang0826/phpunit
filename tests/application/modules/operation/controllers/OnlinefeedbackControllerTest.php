<?php
require_once APPLICATION_PATH.'/modules/operation/controllers/OnlinefeedbackController.php';
class dbMockOnline{
	public function beginTransaction(){
		return 1;
	}
	public function commit(){
		return 2;
	}
	public function rollback(){
		return 3;
	}
	public function delete(){
		return 1;
	}
}
class Operation_OnlineFeedbackControllerTest extends EZX_Framework_TestCase {
	public $controller;
	public function setUp(){
		parent::setUp();
		$this->controller = $this->createMockController('Operation_OnlineFeedbackController', $this->request, $this->response);
	}
	public function tearDown(){
		$this->controller = NULL;
	}
   
    public function testIndexAction(){
    	//input params
    	$this->request->setMethod('GET')
    	->setParams(array(
    			'search' => array(
    					'product' => '12',
    					'tag'=>'12',
    					'mobile'=>'132',
    					'email'=>'xiangxiang',
    					'status'=>'replied')
    	));
    	
    	//mock part
    	$onlineFeedbackTagMockMethods = array(
    			'getList' => array()
    	);
    	$onlineFeedbackMockMethods = array(
    			'getList' => array('item'=>array()),
    			'getCount' => 10
    	);
    	$productMockMethods = array(
    			'GetList' => array('list'=>array(array('id'=>1)))
    	);
    	$this->assignMockReturns($this->_onlineFeedbackTagModel, $onlineFeedbackTagMockMethods);
    	$this->assignMockReturns($this->_onlineFeedbackModel, $onlineFeedbackMockMethods);
    	$this->assignMockReturns($this->_productModel, $productMockMethods);
    	
    	//exec action
    	$this->controller->indexAction();
    	
    	//assert
    	$this->assertSame(array(1=>array('id'=>1)),$this->controller->view->product);    	
    	$this->assertSame(array('product' => '12','tag'=>'12','mobile'=>'132','email'=>'xiangxiang','status'=>'replied'),$this->controller->view->search);
    	$this->assertSame(array(),$this->controller->view->tags);
    }
    
    public function testDetailAction(){
    	//input params
    	$this->request->setMethod('GET')
    	->setParams(array(
    			'id'=>1
    	));
    	
    	//mock part
    	$onlineFeedbackMockMethods = array(
    			'getFeedbackById' => array('product_id'=>1, 'device_id'=>1, 'platform'=>'ios', 'app_id'=>1)
    	);
    	$onlineFeedbackMessageMockMethods = array(
    			'getList' => array(
    					0	=>	array(
    							'user_id'	=>	null,
    							'type'		=>	'feedback',
    							),
    					1	=>	array(
    							'user_id'	=>	3,
    							'type'		=>	'feedback',
    					),
    					)
    	);
    	$onlineFeedbackTagMockMethods = array(
    			'getList' => array()
    	);
    	$onlineFeedbackTagMockMethods = array(
    			'getList' => array()
    	);
    	$feedbackTagMapMockMethods = array(
    			'getList' => array()
    	);
    	$productMockMethods = array(
    			'FindByIds' => array(array())
    	);
    	$deviceMockMethods = array(
    			'getDetailById' => array('id'=>1)
    	);
    	$deviceSnMockMethods = array(
    			'getDeviceById' => array('sn'=>1)
    	);
    	$enterpriseMockMethods = array(
    			'getByPlatform' => array()
    	);
    	$userMockMethods = array(
    			'getByAppID' => array('id'=>1),
    			'getInfoByUid' => array('id'=>3)
    	);
    	$userDeviceMockMethods = array(
    			'getDeviceList' => array('device'=>1)
    	);
    	$this->assignMockReturns($this->_onlineFeedbackModel, $onlineFeedbackMockMethods);
    	$this->assignMockReturns($this->_onlineFeedbackMessageModel, $onlineFeedbackMessageMockMethods);
    	$this->assignMockReturns($this->_onlineFeedbackTagModel, $onlineFeedbackTagMockMethods);
    	$this->assignMockReturns($this->_feedbackTagMapModel, $feedbackTagMapMockMethods);
    	$this->assignMockReturns($this->_productModel, $productMockMethods);
    	$this->assignMockReturns($this->_deviceModel, $deviceMockMethods);
    	$this->assignMockReturns($this->_deviceSnModel, $deviceSnMockMethods);
    	$this->assignMockReturns($this->_enterpriseAppModel, $enterpriseMockMethods);
    	$this->assignMockReturns($this->_userModel, $userMockMethods);
    	$this->assignMockReturns($this->_userDeviceModel, $userDeviceMockMethods);
    	
    	//exec action
    	$this->controller->detailAction();
    	
    	//assert
    	$this->assertSame($this->controller->view->id,1);
    	$this->assertSame($this->controller->view->device,array('id'=>1,'sn'=>array('sn'=>1)));
    	$this->assertSame($this->controller->view->user['id'],3);
    }
    
    public function testReplyAction(){
    	//input params
    	$this->request->setMethod('POST')
    	->setParams(array(
    			'content'			=>	'CONTENT',
    			'1'					=>	1,
    			'intri_key'			=>	array('key'=>'appid','value'=>1),
    			'reply_count'		=>	2
    	));
    	
    	//mock
    	$db = new dbMockOnline();
    	$onlineFeedbackMessageMockMethods = array(
    			'insert' => 1,
    			'get_db' => $db
    	);
    	$onlineFeedbackMockMethods = array(
    			'update' => 1
    	);
    	//$this->controller->_sendApiRequest = function(){return 1;};
    	$this->assignMockReturns($this->_onlineFeedbackMessageModel, $onlineFeedbackMessageMockMethods);
    	$this->assignMockReturns($this->_onlineFeedbackModel, $onlineFeedbackMockMethods);
    	//reflaction
    	$ref = new ReflectionClass($this->controller);
        $privateName = $ref->getProperty ('_debug');
        $privateName->setAccessible(true);
        EZX_TestReflection::setValue($this->controller, '_debug', 1);
    	//exec
    	$res = $this->getAsynJsonData($this->controller,'replyAction');
    	$this->assertSame($res['status'],200);
    }
    
    public function testRemarkAction(){
    	//input params
    	$this->request->setMethod('POST')
    	->setParams(array(
    			'id'					=>	'1',
    			'tags'					=>	'1,2,3',
    			'tags_checked'			=>	'2',
    			'tags_checked_old'		=>	'',
    			'tags_new'				=>	'test',
    			'remark'				=>	'remark'
    	));
    	
    	//mock
    	$db = new dbMockOnline();
    	$onlineFeedbackMockMethods = array(
    			'update' => 1,
    			'get_db' => $db
    	);
    	$feedbackTagMapkMockMethods = array(
    			'delete' => 1,
    			'get_db' => $db,
    			'insert' => 1
    	);
    	$onlineFeedbackTagkMockMethods = array(
    			'insert' => 1
    	);
    	$this->assignMockReturns($this->_onlineFeedbackModel, $onlineFeedbackMockMethods);
    	$this->assignMockReturns($this->_feedbackTagMapModel, $feedbackTagMapkMockMethods);
    	$this->assignMockReturns($this->_onlineFeedbackTagModel, $onlineFeedbackTagkMockMethods);
    	
    	//exec
    	$res = $this->getAsynJsonData($this->controller, 'remarkAction');
    	$this->assertSame($res['status'],200);
    }
}








