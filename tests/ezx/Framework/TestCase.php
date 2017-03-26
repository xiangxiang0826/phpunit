<?php
/**
 * 控制器层测试基类
 * @author zhouxh
 *
 */
class EZX_Framework_TestCase extends Zend_Test_PHPUnit_ControllerTestCase {
	
	public function setUp(){
		$this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		parent::setUp();
	}
	
	/** 
	 * 模拟用户登录
	 */
	public function mockUserLogin(){
		$userInfo =  array(
				'id' => '36',
				'user_name' => 'root',
				'real_name' => '超级用户',
				'password' => '36bf3ec8db36b47890bb0223abcbe502',
				'department' => '遥控e族云端平台组',
				'phone' => '15989562826',
				'email' => 'wangyn@wondershare.cn',
				'cuser' => '1',
				'ctime' => '2014-08-11 16:16:17',
				'last_login_time' => '2014-12-11 14:13:00',
				'status' => 'enable',
				'user_id' => '36',
				'group_ids' => array(1)
		);
		$session = new Zend_Session_Namespace('UserInfo');
		$session->user_info = $userInfo;
	}
	
	/**
	 * 根据类名创建mock对象
	 * @param string $class
	 * @return object
	 */
	public function createMock($class) {
		$mock_obj = $this->getMock($class);
		return $mock_obj;
	}
	
	/**
	 * 设置mock的返回值
	 * @param object $mockObject
	 * @param array $array
	 */
	public function assignMockReturns($mock_object, $array){
		foreach ($array as $method => $return) {
			$mock_object->expects($this->any())
			->method($method)
			->will($this->returnValue($return));
		}
	}
	
	/**
	 * 异步调用接口获取Json值结果
	 * @param Zend_Controller $obj 控制器名字
	 * @param string $action 控制器方法
	 * @param boolean $resultIsArr 返回结果是否是数组默认为true
	 * @return array|string
	 */
	public function getAsynJsonData($obj, $action, $resultIsArr = true){
		ob_start();
		EZX_TestReflection::invoke($obj, $action);
		$response = ob_get_contents();
		if($resultIsArr) {
			$response = json_decode($response, true);
		}
		ob_end_clean();
		return $response;
	}
	
	/**
	 * 创建模型被mock的控制器
	 * @param string $className
	 * @param Zend_Controller_Request_HttpTestCase $request
	 * @param Zend_Controller_Response_HttpTestCase $response
	 * @throws Exception
	 * @return ZendX_Controller_Action 
	 */
	public function createMockController($className, $request, $response){
		if(!class_exists($className)) {
			throw new Exception($className . '类名不存在');
		}
		$controller = new $className($this->request, $this->response);
		$ref = new ReflectionClass($controller);
		//查询所有Protected|Private属性的后缀名是Model的标识要注入的方法
		$props = $ref->getProperties(ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PRIVATE);
		$mustMockProps = array();
		foreach ($props as $prop) {
			$propName = $prop->getName();
			if(stripos($propName, 'model') !== false) {
				$prop->setAccessible(true);
 				$mustMockProps[$propName] = get_class($prop->getValue($controller));
			}
		}
		foreach ($mustMockProps as $p => $class) {
			$this->{$p} = $this->createMock($class);
			EZX_TestReflection::setValue($controller, $p, $this->{$p});
		}
		return $controller;
	}
	
	/**
	 * 通过map的方式设置返回值
	 * example:
	 *  $map = array(
	 array('a', 'b', 'c', 'd'),
	 array('e', 'f', 'g', 'h')
	 );
	 *  assignMockReturnMap($mock_object, 'dosomething', $map);
	 *  $mock_object->dosomething('a','b', 'c')
	 *  result value is: d
	 * @param object $mock_object
	 * @param string $method
	 * @param array $map
	 */
	public function assignMockReturnMap($mock_object, $method, $map){
		$mock_object->expects($this->any())
		->method($method)->will($this->returnValueMap($map));
	}
	
	
}