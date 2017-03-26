<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * cms系统的模块命名空间
 *
 * @author 刘通
 */
class Cms_Module_Loader {
	/**
	 * @var Cms_Module_Loader
	 */
	private static $_instance;
	
	/**
	 * @var array
	 */
	private $_moduleNames;
	
	/**
	 * 系统模块
	 * @var array
	 */
	private $_sysModules = array(
								'default', 
								'template', 
								'system', 
								'file', 
								'log', 
								'public', 
								'search',
								'ad',
								'ajax',
								'crontab',
								'editor',
								'website',
							);
	
	/**
	 * @return Cms_Module_Loader
	 */
	static function getInstance() 
	{
		if (null == self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;		
	}
	
	private function __construct() 
	{
		$this->_moduleNames = $this->_getModules();
	}
	
	/**
	 * 获取扩展的模块
	 */
	function getExtModuleNames()
	{
		$module_names = $this->getModuleNames();
		return array_diff($module_names, $this->_sysModules);
	}
	
	/**
	 * 获取所有的模块名
	 */
	function getModuleNames() 
	{
		return $this->_moduleNames;
	}
	
	/**
	 * @return array
	 */
	private function _getModules() 
	{
		return Cms_Utility_File::getSubDir(APPLICATION_PATH . DS . 'modules');
	}
    
	/**
     * 注册模块的命名空间，暂时不用
     */
	function registerModulesNamespace()
	{
		$modules = $this->getModuleNames();
		foreach($modules as $module)
		{
			$this->registerModuleNamespace($module);
		}
	}
    
	/**
     * 注册指定模块的命名空间
     * @param string $module
     */
	function registerModuleNamespace($module)
	{
		new Cms_Loader(array(
			'basePath'  => APPLICATION_PATH . DS . 'modules' . DS . $module,
			'namespace' => ucfirst($module) . '_',
		));
	}
}

?>
