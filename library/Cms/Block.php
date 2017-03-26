<?php

/**
 * 块的封装
 * 
 * @author 刘通
 */
class Cms_Block
{
	static private $_folder = null;
	
	static function cacheName($data)
	{
		return "block/cache-{$data['site_id']}-{$data['blk_id']}.php";
	}
	
	static function getFolder()
	{
		if(null === self::$_folder)
		{
			$cfg = Cms_Func::getConfig('create', 'block');
			self::$_folder = $cfg->folder;
		}
		
		return self::$_folder;
	}
	
	/**
	 * 获取静态生成路径
	 * 
	 * @param array $blk_info
	 */
	static function getFilePath($blk_info)
	{
		$create_cfg = Cms_Func::getConfig('create', 'root')->toArray();
		$block_folder = Cms_Block::getFolder();
		return "{$create_cfg['root']}{$blk_info['html_path']}/{$block_folder}/{$blk_info['file_path']}";
	}
	
	/**
	 * 替换SSI指令为文本内容
	 * 
	 * @param string $content
	 */
	static function replace($content, $html_path)
	{
		$content = preg_replace_callback('/<!--#include virtual="([^"]+)"-->/', 
										function($matches) use($html_path){
											if($matches[1]{0} == '.') {
												$matches[1] = preg_replace('/(\.\.\/)+/', '', $matches[1]);
											}
											$page_file = $html_path . '/' . $matches[1];
											if(file_exists($page_file)) {
												return file_get_contents($page_file);
											}
											return '块文件还没生成';
										}, $content);
		return $content;
	}
}