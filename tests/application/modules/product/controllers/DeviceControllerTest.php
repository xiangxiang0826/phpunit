<?php
require_once APPLICATION_PATH.'/modules/product/controllers/DeviceController.php';
class dbMockDevice{
	public function quoteInto(){
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
class Product_DeviceControllerTest extends EZX_Framework_TestCase {
	public $controller;
	public function setUp(){
		parent::setUp();
		$this->controller = $this->createMockController('Product_DeviceController', $this->request, $this->response);
	}
	public function tearDown(){
		$this->controller = NULL;
	}
   
    public function testIndexAction(){
    	//input params
    	$this->request->setMethod('GET')
    	->setParams(array(
    			'search' => array(
    					'device_id' => '1',
    					'name'=>'test',
    					'status'=>'new',
    					'device_type'=>'test',
    					'enterprise_id'=>'1',
    					'category'=>'1'
    					)
    	));
    	
    	//mock part
    	$categoryMockMethods = array(
    			'getAllChildrenById' => array(1),
    			'dumpTree' => array(1,2,3)
    	);
    	$deviceMockMethods = array(
    			'getCondition' => 2,
    			'getStatusMap' => array('test')
    	);
    	$enterpriseMockMethods = array(
    			'droupDownData' => array('test_enterprise')
    	);
    	$this->assignMockReturns($this->_categoryModel, $categoryMockMethods);
    	$this->assignMockReturns($this->_deviceModel, $deviceMockMethods);
    	$this->assignMockReturns($this->_enterpriseModel, $enterpriseMockMethods);
    	
    	//exec action
    	$this->controller->indexAction();
    	
    	//assert
    	$this->assertSame(array('device_id' => '1',	'name'=>'test',	'status'=>'new', 'device_type'=>'test',	'enterprise_id'=>'1', 'category'=>'1'),$this->controller->view->search);
    	$this->assertSame(array(1,2,3),$this->controller->view->category);
    }
    
    public function testDetailAction(){
    	//input params
    	$this->request->setMethod('GET')
    	->setParams(array(
    			'id' => 2
    	));
    	
    	//mock part
    	$deviceMockMethods = array(
    			'getDetailById' => array('id'=>'2','status'=>'new'),
    			'getBinderById' => array(array('name'=>'xiangxiang')),
    			'getStatusMap' => array('new'=>'新的')
    	);
    	$this->assignMockReturns($this->_deviceModel, $deviceMockMethods);
    	
    	//exec action
    	$this->controller->detailAction();
    	
    	//assert
    	$this->assertSame($this->controller->view->device, array('id'=>'2','status'=>'new','bind_user'=>array(array('name'=>'xiangxiang'))));
    }
    
    public function testSaveAction(){
    	//input params
    	$this->request->setMethod('GET')
    	->setParams(array(
    			'id' => 2
    	));
    	$_POST = array(
    			'type' => 'test',
    			'status' => 'new'
    	);
    	
    	//mock part
    	$db = new dbMockDevice();
    	$deviceMockMethods = array(
    			'get_db' => $db,
    			'update' => 1,
    			'getStatusMap' => array('new'=>'新的')
    	);
    	$this->assignMockReturns($this->_deviceModel, $deviceMockMethods);
    	
    	//exec action
    	$res = $this->getAsynJsonData($this->controller,'saveAction');
    	$this->assertSame($res['status'],200);
    	
    	
    }
    
    
}








