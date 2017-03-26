<?php
require_once APPLICATION_PATH.'/modules/enterprise/controllers/AnnounceController.php';
class dbMock{
	public function fetchAll($str){
		return array(array('company_name'=>'test','id'=>1));
	}
	public function quoteInto($str){
		return 'test';
	}
	public function delete($str){
		return 1;
	}
}
class EnterPrise_AnnounceControllerTest extends EZX_Framework_TestCase {
	public $controller;
	public function setUp(){
		parent::setUp();
		$this->controller = $this->createMockController('EnterPrise_AnnounceController', $this->request, $this->response);
	}
	public function tearDown(){
		$this->controller = NULL;
	}
   
    public function testIndexAction()
    {
    	$this->request->setMethod('GET')
    	->setParams(array(
    			'search' => array('enterprise_id' => '12')
    	));
    	//$controller = $this->createMockController('EnterPrise_AnnounceController', $this->request, $this->response);
    	$enterpriseMockMethods = array(
    			'droupDownData' => array()
    	);
    	$this->assignMockReturns($this->enterpriseModel, $enterpriseMockMethods);
    	$announceModelMethods = array(
    			'getTotal' => 20,
    			'getList' => array(),
    			'getStatusMap' => array()
        );
    	$this->assignMockReturns($this->announceModel, $announceModelMethods);
        $this->controller->indexAction();
        $this->assertSame(array(), $this->controller->view->status);
    }
	
    public function testAuditAction(){
    	$this->request->setMethod('GET')
    	->setParam('id',1);
    	//$controller = $this->createMockController('EnterPrise_AnnounceController', $this->request, $this->response);
    	/*				审核页面展示					*/
    	$announceMockMethods = array(
    			'getFieldsById'=>array('enterprise_ids'=>1),
    			'getEnterpriseName'=>array(array('company_name'=>'test'))
    	);
    	$this->assignMockReturns($this->announceModel, $announceMockMethods);
    	$this->controller->auditAction();    	
    	$this->assertSame($this->controller->view->info['enterprise_name'],'test');
    	
    	/*				post审核					*/
    	$this->request->setMethod('Post')
    	->setPost(array(
    			'status'=>'',
    			'pass'=>'',
    			'remark'=>'',
    			'actual_time'=>'',
    			'id'=>''));
    	
    	$announceMockUpdateMethods = array(
    			'update'=>1
    			); 
    	$this->assignMockReturns($this->announceModel, $announceMockUpdateMethods);
    	$res = $this->getAsynJsonData($this->controller,'auditAction');
    	//print_r($res);
    	$this->assertSame(200,$res['status']); 
    	/*				post审核   END					*/
    	
    }
    
    public function testViewAction(){
    	$this->request->setMethod('GET')
    	->setParam('id',1);
    	//$controller = $this->createMockController('EnterPrise_AnnounceController', $this->request, $this->response);
    	$announceMockMethods = array(
    			'getFieldsById'=>array('enterprise_ids'=>1),
    			'getEnterpriseName'=>array(array('company_name'=>'test'))
    	);
    	$this->assignMockReturns($this->announceModel, $announceMockMethods);
    	$this->controller->viewAction();
    	$this->assertSame($this->controller->view->info['enterprise_name'],'test');    	
    }
    
    public function	testAddAction(){    	
    	$this->controller->addAction();
    	$this->assertSame(null,$this->controller->view->announceId);
    	$this->request->setMethod('GET')
    	->setParam('id',1);
    	$this->request->setMethod('Post')
    	->setPost(array(
    			'announce'=>array(
    					'content'=>'content',
    					'title'=>'title',
    					),
    			'send_type'=>'all',    			
    			'id'=>''));
    	$announceMockMethods = array(
    			'insert'=>1
    	);
    	$validateMockMethod = array(
    			'validateMessage'=>true
    			);

    	$this->assignMockReturns($this->announceModel, $announceMockMethods);
    	
    	$res = $this->getAsynJsonData($this->controller,'addAction');
    	$this->assertSame(200,$res['status']);
    }
    
    public function testEditAction(){   
    	/*			编辑页面			*/	    	
    	$db = new dbMock();
    	$announceMockMethods = array(
    			'getFieldsById'=>array('enterprise_ids'=>1),
    			'get_db'=>$db
    	);
    	$this->assignMockReturns($this->announceModel, $announceMockMethods);
    	$this->controller->editAction(); 
    	$this->assertSame(null,$this->controller->view->announceId);
    	$this->assertSame($this->controller->view->info['enterprise_name'],'test');
    	
    	/*			编辑添加			*/
    	$this->request->setMethod('Post')
    	->setPost(array(
    			'announce'=>array(
    					'content'=>'content',
    					'title'=>'title',
    			),
    			'send_type'=>'all',
    			'id'=>''));
    	$announceMockMethods = array(
    			'update'=>1
    	);
    	$this->assignMockReturns($this->announceModel, $announceMockMethods);
    	$res = $this->getAsynJsonData($this->controller,'editAction');
    	$this->assertSame(200,$res['status']);
    }
    
    public function testEnterpriseAction(){
    	
    }
   
    public function testDeleteAction(){
    	$this->request->setMethod('GET')
    	->setParam('id',1);
    	$db = new dbMock();
    	$announceMockMethods = array(
    			'getFieldsById'=>array('enterprise_ids'=>1),
    			'get_db'=>$db
    	);
    	$this->assignMockReturns($this->announceModel, $announceMockMethods);
    	$res = $this->getAsynJsonData($this->controller,'deleteAction');
    	$this->assertSame(200,$res['status']);
    }
    
    public function testNeedchecknumAction(){
    	$announceMockMethods = array(
    			'getTotal'=>array('total'=>20)
    	);
    	$this->assignMockReturns($this->announceModel, $announceMockMethods);
    	$res = $this->getAsynJsonData($this->controller,'needchecknumAction');
    	$this->assertSame(20,$res['result']['result']);
    	
    }

}







