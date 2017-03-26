<?php
/**
 * 用户登录控制器测试
 * @author zhouxh
 *
 */
class UserControllerTest extends EZX_Framework_TestCase {
	
	/**
	 * 测试访问登录页面
	 */
	 public function testIndexActionShouldContainLoginForm()
	{
		$this->dispatch('/login');
		$this->assertAction('index');
		$this->assertQueryCount('form#login_form', 1);
	}
	
	/**
	 * 测试用户登录
	 * @dataProvider userLoginData 
	 */
	 public function testMockUserLogin($postData, $res){
		$this->request->setMethod('POST')->setPost($postData);
		$this->getRequest()->setHeader('x-requested-with','XMLHttpRequest');
		$this->mockLoginData();
		$this->dispatch('/login/index/login');
		$controller = $this->getFrontController();
		$this->assertModule('login');
		$this->assertController('index');
		$this->assertAction('login');
		$this->assertSame(200, $this->getResponse()->getHttpResponseCode());
		$obj = json_decode($this->getResponse()->getBody(), true);
		$this->assertEquals($res, $obj['state']);
	} 
	
	 public function userLoginData(){
		$data = array(
				array(
						array(
								'user_name' => 'root',
								'password' => '123456'
						),
						0
				),
				array(
						array(
								'user_name' => '12',
								'password' => '456789'
						),
						'user_name'
				)
		);
		return $data;
	}

	public function mockLoginData(){
		$mockGroupModel = $this->createMock('Model_Group');
		$mockUserModel = $this->createMock('Model_User');
		$mockModulGroup = $this->createMock('Model_GroupModule');
		$mockGroupPerm = $this->createMock('Model_GroupPermission');
		$modeGroupMethod = array(
				'addUserGroup'=> '',
				'GetGroupByUid' => array(array('id' => 1)),
		);
		$userModelMethod = array(
				'getInfoByName' => array('password' => md5('123456'), 'user_name' => 'root', 'status' => 'enable' ,'group_ids' => array(), 'id' => 1),
				'logLogin' => '',
				'insert' => '',
				'get_db' => '',
		);
		$this->assignMockReturns($mockGroupModel, $modeGroupMethod);
		$this->assignMockReturns($mockUserModel, $userModelMethod);
		Zend_Registry::set('Model_Group', $mockGroupModel);
		Zend_Registry::set('Model_User', $mockUserModel);
		Zend_Registry::set('Model_GroupModule', $mockModulGroup);
		Zend_Registry::set('Model_GroupPermission', $mockGroupPerm);
	}
	
	
}