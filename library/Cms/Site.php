<?php

/**
 * 获取site信息，给其它模块提供的访问site信息的接口
 */
class Cms_Site
{
	static function getInfo() {
		$task_config = ZendX_Config::get('task','task');
		$site_info[$task_config['site_id']] = array(
			'site_id'=>$task_config['site_id'],
			'type'=>'seo',
			'host_js'=>'',
			'host_css'=>'',
			'host_image'=>'',
			'payment_mode'=>'',
			'host'=> $task_config['domain'],
			'html_path'=>$task_config['html_path'],
				
		);
		return $site_info;
	}
	
	static function getInfoById($site_id)
	{
		$site_info = self::getInfo();
		return isset($site_info[$site_id]) ? $site_info[$site_id] : array();
	}
	
	/**
	 * 获取站点级别的静态生成路径
	 * 		现在是在块生成静态网页时用到
	 * 
	 * @param integer	$site_id
	 * @deprecated
	 */
	static function getHtmlPath($site_id)
	{
		$site_info = self::getInfoById($site_id);
		$create_cfg = Cms_Func::getConfig('create', 'root')->toArray();
		
		return $create_cfg['root'] . $site_info['html_path'];
	}
}