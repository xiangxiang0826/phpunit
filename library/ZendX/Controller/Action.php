<?php
class ZendX_Controller_Action extends Zend_Controller_Action {
	//do something 
	public $page_size = 10;
	public function init() {
		parent::init();
		if(isset($_SERVER['QUERY_STRING'])) {
			parse_str($_SERVER['QUERY_STRING'],$query);
			$query['rnd'] = rand(1000,9999);
			unset($query['page']);
			$this->view->base_url = "?".http_build_query($query);
		}
	}
	
	public function postDispatch() {
		$this->triger_hooks();
		
	}
	
	
	private function triger_hooks() {
		// do somthing
		
	}
	
	/**
	 * 获取类的实例
	 * @param string $className 类名
	 * @return mixed
	 */
	public function getModel($className) {
		$registry = Zend_Registry::getInstance();
		if(!isset($registry[$className])) {
			$registry->set($className, new $className);
		}
		return $registry->get($className);
	}
}