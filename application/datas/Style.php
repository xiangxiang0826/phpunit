<?php
/**
 * 样式数据管理
 * 
 * @author leimc<leimc@wondershare.cn>
 * @date 2013-02-24
 */
class Datas_Style extends Cms_Data
{
	/**
	 * 获取产品信息
	 * 		{style::css domain="my.spotmau.cn" mod="index" /}
	 * 		{style::css domain="$domain" mod="$mode" /}
	 * 
	 * 参数说明：
	 * 		domain		对应域名
	 * 		mod			对应模块
	 */
	public function css($params)
	{
		extract($params);
		
		if(empty($domain) || empty($mod)){
			return;
		}else{
			$arr_version_cfg = Cms_Func::getConfig('template/version', $domain);
			$version_cfg = $arr_version_cfg->$mod->toArray();
			return $version_cfg['css'];
		}
	}
	
	/**
	 * 获取产品信息
	 * 		{style::css domain="my.spotmau.cn" mod="index" /}
	 * 		{style::css domain="$domain" mod="$mode" /}
	 * 
	 * 参数说明：
	 * 		domain		对应域名
	 * 		mod			对应模块
	 */
	public function js($params)
	{
		extract($params);
		
		if(empty($domain) || empty($mod)){
			return;
		}else{
			$arr_version_cfg = Cms_Func::getConfig('template/version', $domain);
			$version_cfg = $arr_version_cfg->$mod->toArray();
			return $version_cfg['js'];
		}
	}

}