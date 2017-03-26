<?php
/**
 * 资讯频道单元测试
 * @author zhouxh
 *
 */
class ChannelControllerTest extends EZX_Framework_TestCase {
	
	public function setUp(){
		parent::setUp();
		$this->mockUserLogin();
	}
	
	public function testIndex(){
		$this->mockListData();
		$this->dispatch('/operation/channel/index');
		$this->assertResponseCode(200);
		$this->assertNotRedirect();
		$this->assertQueryCount('.news tr', 2);
		$this->assertAction('index');
	}
	
	public function testAdd1(){
		$this->dispatch('/operation/channel/add');
		$this->assertAction('add');
		$this->assertNotRedirect();
		$this->assertResponseCode(200);
		$this->assertQueryCount('#message_form', 1);
	}
	/**
	 * @dataProvider postData
	 * @param unknown_type $data
	 * @param unknown_type $status
	 */
	public function testAdd2($data, $status){
		$this->request->setMethod('POST')->setPost($data);
		$this->mockAddData();
		$this->getRequest()->setHeader('x-requested-with','XMLHttpRequest');
		$this->dispatch('/operation/channel/add');
		$this->assertResponseCode(200);
		$body = $this->getResponse()->getBody();
		$result = json_decode($body, true);
		$this->assertEquals($status, $result['status']);
	}
	
	public function testEdit1(){
		$this->dispatch('/operation/channel/edit');
		$this->assertResponseCode(500);
	}
	
	public function testEdit2(){
		$_GET['id'] = 1;
		$this->mockEditData();
		$this->dispatch('/operation/channel/edit');
		$this->assertResponseCode(200);
		$this->assertQueryCount('#message_form', 1);
	}
	
	/**
	 * @dataProvider postData
	 * @param unknown_type $data
	 * @param unknown_type $status
	 */
	public function testEdit3($data, $status){
		$this->request->setMethod('POST')->setPost($data);
		$this->mockEditData();
		$this->getRequest()->setHeader('x-requested-with','XMLHttpRequest');
		$this->dispatch('/operation/channel/edit/id/1');
		$this->assertResponseCode(200);
		$body = $this->getResponse()->getBody();
		$result = json_decode($body, true);
		if($status == 500) {
			$status = 200;
		}
		$this->assertEquals($status, $result['status']);
	}
	/**
	 *  @dataProvider publishData
	 */
	public function testPublish($id, $status){
		$this->request->setMethod('POST')->setParam('id', $id);
		$this->mockPublishData();
		$this->getRequest()->setHeader('x-requested-with','XMLHttpRequest');
		$this->dispatch('/operation/channel/publish');
		$this->assertResponseCode(200);
		$body = $this->getResponse()->getBody();
		$result = json_decode($body, true);
		$this->assertEquals($status, $result['status']);
	}
	/**
	 *  @dataProvider publishData
	 */
	public function testDel($id, $status){
	    $this->request->setMethod('POST')->setParam('id', $id);
	    $this->mockDelData();
	    $this->getRequest()->setHeader('x-requested-with','XMLHttpRequest');
	    $this->dispatch('/operation/channel/del');
	    $this->assertResponseCode(200);
	    $body = $this->getResponse()->getBody();
	    $result = json_decode($body, true);
	    $this->assertEquals($status, $result['status']);
	}
	
	public function publishData(){
		$data = array(
				array(1,200),
				array(2,500),
				array('',500),
		);
		return $data;
	}
	
	protected function mockDelData(){
		$mockChannelContnet = $this->createMock('Model_ChannelContent');
		$map = array(
				array(1, 1),
				array(2, 0),
		);
		$this->assignMockReturnMap($mockChannelContnet, 'deleteInfo', $map);
		Zend_Registry::set('Model_ChannelContent', $mockChannelContnet);
	}
	
	protected function mockPublishData(){
		$mockChannelContnet = $this->createMock('Model_ChannelContent');
		$map = array(
				array( array('status'=>'enable'), array('id =?' => 1), 1),
				array( array('status'=>'enable'), array('id =?' => 2), 0),
		);
		$this->assignMockReturnMap($mockChannelContnet, 'update', $map);
		Zend_Registry::set('Model_ChannelContent', $mockChannelContnet);
	}
	
	
	public function postData(){
		$data = array(
				array(
						array(
								'content' => '内容',
								'title' => '1234567891012345678910',
								'source' => '123456',
								'url' => '123456',
								'image' => '123456',
						),
						403
				),
				array(
						array(
							    'content' => '',
								'title' => '123456',
								'source' => '来源',
								'url' => 'http://www.baidu.com',
								'image' => '123456',
						),
						403
				),
				array(
						array(
								'content' => '内容',
								'title' => '123456',
								'source' => '123456',
								'url' => '123456',
								'image' => '123456',
						),
						403
				),
				array(
						array(
								'content' => 'content1',
								'title' => 'title1',
								'source' => 'source',
								'url' => 'http://www.baidu.com',
								'image' => 'images',
						),
						200
				),
				array(
						array(
								'content' => 'content1',
								'title' => 'title1',
								'source' => 'source',
								'url' => 'http://www.sina.com',
								'image' => 'images1',
						),
						500
				),
		);
		return $data;
	}
	
	protected function mockEditData(){
		$mockChannelContnet = $this->createMock('Model_ChannelContent');
		$result = array(
				    'title' => 'title',
				    'id' => '1',
				    'content' => 'content',
				    'url' => 'http://www.baidu.com',
				    'image' => 'http://www.baidu.com/1.jpg',
				    'source' => 'source',
				);
		$map = array(
				array('title1', 'content1', 'source', 'http://www.baidu.com', 'images','1', 1),
				array('title1', 'content1', 'source', 'http://www.sina.com', 'images1','1', 0),
		);
		$this->assignMockReturnMap($mockChannelContnet, 'editInfo', $map);
		$this->assignMockReturns($mockChannelContnet, array('getFieldsById' => $result));
		Zend_Registry::set('Model_ChannelContent', $mockChannelContnet);
	}
	
	protected function mockAddData(){
		$mockChannelContnet = $this->createMock('Model_ChannelContent');
		$map = array(
				  array('title1', 'content1', 'source', 'http://www.baidu.com', 'images', 1),
				  array('title1', 'content1', 'source', 'http://www.sina.com', 'images1', 0),
			   );
		$this->assignMockReturnMap($mockChannelContnet, 'addInfo', $map);
		Zend_Registry::set('Model_ChannelContent', $mockChannelContnet);
	}
	
	protected function mockListData(){
		$mockChannelContnet = $this->createMock('Model_ChannelContent');
		$result = array(
				0 => array(
						'id' => 1,
						'title' => 'sn123456',
						'content' => 'content',
						'ctime' => '2015-01-30 18:00:00',
						'mtime' => '0000-00-00 00:00:00',
						'source' => '网易',
						'publish_source' => 'oss',
						'url' =>'http://www.baidu.com',
						'image' => 'images.1719.com/1.jpg',
						'if_notice' => 'no',
						'status' => 'enable'
				),
				1 => array(
						'id' => 2,
						'title' => 'sn123456',
						'content' => 'content',
						'ctime' => '2015-01-30 18:00:00',
						'mtime' => '0000-00-00 00:00:00',
						'source' => '网易',
						'publish_source' => 'oss',
						'url' =>'http://www.baidu.com',
						'image' => 'images.1719.com/1.jpg',
						'if_notice' => 'no',
						'status' => 'disable'
				)

		);
		$array = array(
				'createSelect' => $result,
				'findAllFavNumber' => array('1' => '5', '2' => '1'),
		);
		$this->assignMockReturns($mockChannelContnet, $array);
		Zend_Registry::set('Model_ChannelContent', $mockChannelContnet);
	}
	
	
}
