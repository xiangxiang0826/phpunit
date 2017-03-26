<?php

/**
 * 语言类
 * 		example:	echo Cms_L::_('key', array('var'=>$val));
 * 
 * @author 刘通
 */
class Cms_L
{
	static $lang = null;
	static $module = null;
	
	/**
	 * 获取语言项
	 * 
	 * @param string $key
	 * @param array $vars
	 * @example
	 * 		array(
	 * 			'product_name'	=> '产品名称',
	 * 			'max_tip'		=> '最大值{x}',
	 * 		);
	 * 		1. Cms_L::_('product_name')				=> '产品名称';
	 * 		2. Cms_L::_('max_tip', array('x'=>30))	=> '最大值100';
	 */
	static function _($key, array $vars=array())
	{
		$translater = new ZendX_View_Helper_T();
		return $translater->t($key);
	}
	
	static function getModuleName()
	{
		if(null === self::$module)
		{
			$request = Zend_Controller_Front::getInstance()->getRequest();
			self::$module = $request->getModuleName();
		}
		
		return self::$module;
	}
	
	static function getLanguage()
	{
		if(isset($_COOKIE['ck_cms_lang']) && $_COOKIE['ck_cms_lang']){
			$lang = $_COOKIE['ck_cms_lang'];
		}else{
			$lang = 'cn';
		}
		
		return $lang;
	}
}