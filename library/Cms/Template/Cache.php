<?php

/**
 * 模版缓存类
 * 
 * @author 刘通
 */
class Cms_Template_Cache
{
	static $_instance = null;
	private $_cache_path = null;
	
	static function getInstance()
	{
		if(null === self::$_instance)
		{
			self::$_instance = new self();
		}
		
		return self::$_instance;
	}
	
	/**
	 * 把编译后的PHP代码写入PHP文件
	 * 
	 * @param string $tpl
	 * @param string $content
	 */
	function write($tpl, $content)
	{
		$file = $this->getCachePath() . $tpl;
		Cms_Func::writeFile($file, $content);
	}
	
	/**
	 * 获取模版编译后的PHP文件
	 * 
	 * @param array $tpl_info
	 */
	function getTplFile($tpl_info)
	{
		$tpl_file = Cms_Template::cacheName($tpl_info);
		$file = $this->getCachePath() . $tpl_file;
		if(!file_exists($file))
		{
			//因为缓存中没有存conetnt内容，而这种情况很少，所以这里重新取一次
			$tpl_model = new Template_Models_Template();
			$content = $tpl_model->getContentById($tpl_info['tpl_id']);
			Cms_Template_Parse::compile($content, $tpl_file);
		}
		
		return $file;
	}
	
	/**
	 * 获取块的缓存文件
	 * 
	 * @param array $blk_info
	 */
	function getBlkFile($blk_info)
	{
		$blk_file = Cms_Block::cacheName($blk_info);
		$file = $this->getCachePath() . $blk_file;
		
		if(!file_exists($file))
		{
			//因为缓存中没有存conetnt内容，而这种情况很少，所以这里重新取一次
			$blk_model = new Template_Models_Block();
			$blk_info = $blk_model->getInfoById($blk_info['blk_id']);
			Cms_Template_Parse::compile($blk_info['content'], $blk_file);
		}
		
		return $file;
	}
	
	/**
	 * 获取PHP文件的缓存路径
	 */
	function getCachePath()
	{
		return $this->_cache_path;
	}
	
	private function __construct()
	{
		$this->_cache_path = APPLICATION_PATH . '/cache/templates/';
	}
	
	private function __clone()
	{
		
	}
}