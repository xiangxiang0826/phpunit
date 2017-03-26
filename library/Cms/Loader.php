<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Loader
 *
 * @author 刘通
 */
class Cms_Loader extends Zend_Loader_Autoloader_Resource 
{
	function __construct($options) 
	{
		parent::__construct($options);
	}

	function autoload($class) 
	{
		$prefix = APPLICATION_PATH . DS;
		$paths  = explode('_', $class);
		switch (strtolower($paths[0])) 
		{
			case 'plugins':
			case 'hooks':
			case 'datas':
				$prefix .= '';
				break;
			default:
				$prefix .= 'modules' . DS;
				break;
		}

		$className = array_pop($paths);
		$classFile = implode(DS, $paths);
		$classFile = $prefix . strtolower($classFile) . DS . $className . '.php';

		if (Zend_Loader::isReadable($classFile)) 
		{
			return include $classFile;
		}

		return false;
	}
}

?>
