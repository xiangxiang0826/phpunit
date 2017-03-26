<?php
/**
 * Bootsrap扩展
 * @author lvyz@wondershare
 *
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	/**
	 * 一些常量的定义
	 */
	protected function _initDefine()
	{
		if(!defined('DS'))   define('DS', DIRECTORY_SEPARATOR);
	}
	
	/**
	 * 增加Model类自动加载处理
	 */
	protected function _initAutoLoader()
	{
		$resourceLoader = new Zend_Loader_Autoloader_Resource(array(
				'basePath'  => APPLICATION_PATH,
				'namespace' => '',
		));
		$resourceLoader->addResourceType('model', 'models/', 'Model');
		
		new Cms_Loader(array(
				'basePath'  => APPLICATION_PATH,
				'namespace' => 'Hooks_',
		));			//assume 1264B
		
		//注册模块命名空间
		Cms_Module_Loader::getInstance()->registerModulesNamespace();
	}
	
	/**
	 * 增加控制器助手
	 */
	protected function _initActionHelper()
	{
		Zend_Controller_Action_HelperBroker::addHelper(new ZendX_Controller_Action_Helper_Auth());
		Zend_Controller_Action_HelperBroker::addHelper(new ZendX_Controller_Action_Helper_View());
	}
	
	protected function _initSession()
	{
		$user_session = new Zend_Session_Namespace('UserInfo');
		Zend_Registry::set('user_session', $user_session);
	}
	
	protected function _initQuote()
	{
		if(get_magic_quotes_gpc())
		{
			$_POST = Cms_Func::stripslashes($_POST);
			$_GET = Cms_Func::stripslashes($_GET);
		}
	}
	
}