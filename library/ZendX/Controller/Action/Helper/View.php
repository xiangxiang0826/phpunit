<?php
class ZendX_Controller_Action_Helper_View extends Zend_Controller_Action_Helper_Abstract
{
	public function preDispatch()
	{
		$request = $this->getRequest();

		$moduleName = $request->getModuleName();
		$controllerName = $request->getControllerName();
		$actionName = $request->getActionName();

		$controller = $this->getActionController();
		$viewRenderer = $controller->getHelper('viewRenderer');
		$bootstrap = $controller->getInvokeArg('bootstrap');
		$layout = $bootstrap->getResource('Layout');
		$layout->setLayout('default');
		if($moduleName == 'template') {
			$layout->setLayout('cms');
		}
		//Ajax请求禁用Layout和view自动渲染
		if($request->isXmlHttpRequest()) { 
			$viewRenderer->setNoRender();
			if($layout) {
				$layout->disableLayout();
			}
		}
		//重定义原有view路径
		$viewRenderer->view->setScriptPath(APPLICATION_PATH . '/modules/'.$moduleName.'/views/');
		$viewRenderer->view->addScriptPath(APPLICATION_PATH . '/views/');
		$viewRenderer->setViewScriptPathSpec(':controller_:action.:suffix');

		$controller->view->moduleName = $moduleName;
		$controller->view->controllerName = $controllerName;
		$controller->view->actionName = $actionName;
		
		//多语言处理
		$config = $bootstrap->getOption('translate');
		$translate = new Zend_Translate($config['adapter'], $config['path'] .'/'.$config['default'], $config['default']);
		foreach($config['locale'] as $v) {
			$translate->addTranslation($config['path'] .'/'.$v, $v);
		}
		//@todo 设置默认语言
		$translate->setLocale($config['default']);
		
		Zend_Registry::set('Zend_Translate', $translate);
		
	}

}