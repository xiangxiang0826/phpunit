<?php
/**
 * 设备报修单元测试
 * @author zhouxh
 *
 */
class RepairControllerTest extends EZX_Framework_TestCase {
	
	public function setUp(){
		parent::setUp();
		$this->mockUserLogin();
	}
	
	public function testIndexAction1(){
		$_GET['search']['number'] = '123456';
		$this->mockListData();
		$this->dispatch('/operation/repair/');
		$this->assertResponseCode(200);
		$this->assertNotRedirect();
		$this->assertQueryCount('.baoxiu_table tbody tr', 1);
	}
	
	public function testIndexAction2(){
		$_GET['search']['sn'] = 'sn123456';
		$this->mockListData();
		$this->dispatch('/operation/repair/');
		$this->assertResponseCode(200);
		$this->assertNotRedirect();
		$this->assertQueryCount('.baoxiu_table tbody tr', 2);
	}
	
	public function testDetail(){
		$_GET['id'] = 1;
		$this->mockDetailData();
		$this->dispatch('/operation/repair/detail');
		$this->assertResponseCode(200);
		$this->assertNotRedirect();
		$this->assertQueryCount('.trouble_table', 2);
		$this->assertQueryCount('.handle_table .one_line', 1);
		$this->assertNotQuery('.user_detail');
	}
	
	public function testDetail1(){
		$_GET['id'] = 2;
		$this->mockDetailData();
		$this->dispatch('/operation/repair/detail');
		$this->assertResponseCode(200);
		$this->assertNotRedirect();
		$this->assertQueryCount('.trouble_table', 2);
		$this->assertNotQuery('.handle_table .one_line');
		$this->assertQuery('.user_detail');
	}
	
	/**
	 * 
	 */
	public function testDetail2(){
		$this->dispatch('/operation/repair/detail');
		$this->assertResponseCode(500);
	}
	
	/**
	 * @dataProvider postDealData
	 * @param unknown_type $data
	 * @param unknown_type $status
	 */
	public function testDeal($data, $status){
		$this->request->setMethod('POST')->setPost($data);
		$this->getRequest()->setHeader('x-requested-with','XMLHttpRequest');
		$this->mockDealData();
		$this->dispatch('/operation/repair/deal');
		$this->assertResponseCode(200);
		$body = $this->getResponse()->getBody();
		$result = json_decode($body, true);
		$this->assertEquals($status, $result['status']);
	}
	
	/**
	 * @expectedException Zend_Exception
	 */
	public function testHistory1(){
		$_GET['id'] = '1';
		$this->mockHistoryData();
		$this->getRequest()->setHeader('x-requested-with','XMLHttpRequest');
		$this->dispatch('/operation/repair/history');
	}
	
	/**
	 *
	 */
	public function testHistory2(){
		$_GET['id'] = '2';
		$this->mockHistoryData();
		$this->getRequest()->setHeader('x-requested-with','XMLHttpRequest');
		$this->dispatch('/operation/repair/history');
		$this->assertResponseCode(200);
		$body = $this->getResponse()->getBody();
		$result = json_decode($body, true);
		$this->assertEquals(200, $result['status']);
	}
	
	public function mockHistoryData(){
		$mockDeviceReapir = $this->createMock('Model_DeviceRepair');
		$map = array(
				    array('device_id','1', ''),
				    array('device_id','2', array('device_id' => 1)),
				);
		$this->assignMockReturnMap($mockDeviceReapir, 'getFieldsById', $map);
		$this->assignMockReturns($mockDeviceReapir, array('getHistoryList' => array('id' => 2)));
		Zend_Registry::set('Model_DeviceRepair', $mockDeviceReapir);
	}
	
	public function postDealData(){
		$data = array(
				array(
						array(
								'content' => '内容',
								'status' => '123456',
								'repair_id' => '123456',
						),
						403
				),
				array(
						array(
								'content' => '内容',
								'status' => 'new',
						),
						403
				),
				array(
						array(
								'content' => '内容',
								'status' => 'new',
								'repair_id' => '123456',
						),
						403
				),
				array(
						array(
								'content' => 'content',
								'status' => 'process',
								'repair_id' => '1',
						),
						200
				),
				array(
						array(
								'content' => 'content',
								'status' => 'new',
								'repair_id' => '1',
						),
						500
				),
		);
		return $data;
	}
	
	public function mockDealData(){
		$mockDeviceReapir = $this->createMock('Model_DeviceRepair');
		$map = array(
				    array('status', '123456', array('status' =>'cancel')),
				    array('status', '1', array('status'=>'new')),
				);
		$this->assignMockReturnMap($mockDeviceReapir, 'getFieldsById', $map);
		Zend_Registry::set('Model_DeviceRepair', $mockDeviceReapir);
		$mockReaprRecord = $this->createMock('Model_DeviceRepairRecord');
		$map = array(
				array('content','1', 'process','超级用户', 1),
		);
		$this->assignMockReturnMap($mockReaprRecord, 'addRecord', $map);
		Zend_Registry::set('Model_DeviceRepairRecord', $mockReaprRecord);
	}
	
	public function mockDetailData(){
		$mockDeviceReapir = $this->createMock('Model_DeviceRepair');
		$result1 = array(
				      'id' => 1,
				      'number' => '123456',
				       'p_name' => '产品名',
				       'device_id' => 'asdfasdasd',
				       'content' => 'content',
				       'status' => 'new',
				       'user_id' => '',
				       'sn' => '',
				       'ctime' =>'2014-12-15 18:18:18',
				       'buy_date' => '',
				       'attachment' => array(),
				   );
		$result2 = array(
				'id' => 2,
				'number' => '123456',
				'p_name' => '产品名',
				'device_id' => 'asdfasdasd',
				'content' => 'content',
				'status' => 'new',
				'user_id' => '1',
				'sn' => '',
				'ctime' =>'2014-12-15 18:18:18',
				'buy_date' => '',
				'attachment' => array(),
		);
		$map = array(
				array(1,$result1),
				array(2, $result2)
		);
		$this->assignMockReturnMap($mockDeviceReapir, 'getDetailInfo', $map);
		$array = array('statusDroupDownData' => array('new' =>'新的') );
		$this->assignMockReturns($mockDeviceReapir, $array);
		$mockReaprRecord = $this->createMock('Model_DeviceRepairRecord');
		$map = array(
				array(1,array(0 => array('name' =>'name', 'ctime' => '2015-01-30 18:00:00', 'status' => 'new', 'content' => 'content'))),
				array(2,array())
		);
		$this->assignMockReturnMap($mockReaprRecord, 'getRecordList', $map);
		Zend_Registry::set('Model_DeviceRepair', $mockDeviceReapir);
		Zend_Registry::set('Model_DeviceRepairRecord', $mockReaprRecord);
		$mockUser = $this->createMock('Model_Member');
		$array = array(
				     'getFieldsById' => array('id' => '', 'email'=>'', 'name' => '', 'phone' => '', 'reg_time'=>''),
				 );
		$this->assignMockReturns($mockUser, $array);
		Zend_Registry::set('Model_Member', $mockUser);
	}
				
	public function mockListData(){
		$mockDeviceReapir = $this->createMock('Model_DeviceRepair');
		$result1 = array(
				0 => array(
						'id' => 1,
						'number' => '123456',
						'content' => 'content',
						'ctime' => '2015-01-30 18:00:00',
						'mtime' => '0000-00-00 00:00:00',
						'status' => 'new'
				)
		);
		$result2 = array(
				0 => array(
						'id' => 1,
						'number' => 'sn123456',
						'content' => 'content',
						'ctime' => '2015-01-30 18:00:00',
						'mtime' => '0000-00-00 00:00:00',
						'status' => 'new'
				),
				1 => array(
						'id' => 1,
						'number' => 'sn123456',
						'content' => 'content',
						'ctime' => '2015-01-30 18:00:00',
						'mtime' => '0000-00-00 00:00:00',
						'status' => 'new'
				)
		);
		$map = array(
			   array(array('number'=>'123456'), 'pending', $result1),
			   array(array('sn'=>'sn123456'), 'pending', $result2),
		);
		$this->assignMockReturnMap($mockDeviceReapir, 'createSelect', $map);
		$map = array(
				array('pending', array('new' => '新的', 'process' => '正在处理')),
				array('all', array('new' => '新的', 'process' => '正在处理', 'closed' => '已关闭')),
		);
		$this->assignMockReturnMap($mockDeviceReapir, 'statusDroupDownData', $map);
		Zend_Registry::set('Model_DeviceRepair', $mockDeviceReapir);
		//模拟企业列表
		$mockEnterprise = $this->createMock('Model_Enterprise');
		$array = array(
				'droupDownData' => array(1=> '企业一', 2 => '企业二')
		);
		$this->assignMockReturns($mockEnterprise, $array);
		Zend_Registry::set('Model_Enterprise', $mockEnterprise);
		//模拟产品
		$mockProduct = $this->createMock('Model_Product');
		$array = array(
				'getDroupdownData' => array(1=> '产品一', 2 => '产品二')
		);
		$this->assignMockReturns($mockProduct, $array);
		Zend_Registry::set('Model_Product', $mockProduct);
		//模拟产品类别
		$mockProductCate = $this->createMock('Model_ProductCate');
		$array = array(
				'dumpTree' => array(
						0 => array('id' => 1, 'levelstr'=>'-', 'name' => '类别一'),
						1 => array('id' => 2, 'levelstr'=>'-', 'name' => '类别二')
				)
		);
		$this->assignMockReturns($mockProductCate, $array);
		Zend_Registry::set('Model_ProductCate', $mockProductCate);
	}
	
}
