<?php

/**
 * 模版类，用来外部调用模版类信息
 * 
 * @author 刘通
 */
class Cms_Template
{
	/**
	 * 获取模版编译缓存文件名
	 * @param array $row	模版记录
	 * @return string
	 */
	static function cacheName($row)
	{
		return $row['site_id'] . '/' . implode('-', array($row['tpl_id'], $row['module'], $row['type'])) . '.php';
	}
	
	/**
	 * 加载模块提取数据对象
	 * @param string $module
	 * @return object
	 */
	static function loadDataObject($module)
	{
		static $objects = null;
		if(empty($objects[$module]))
		{
			$class_name = 'Datas_' . ucfirst($module);
			$objects[$module] = new $class_name();
		}
		
		return $objects[$module];
	}
	
	/**
	 * 获取模版信息
	 */
	static function getInfo($site_id)
	{
		$cache_key = 'template_info_' . $site_id;
		$cache_obj = Cms_Cache::getInstance();
		$tpl_info = $cache_obj->get($cache_key);
		
		if(!$tpl_info)
		{
			$tpl_model = new Template_Models_Template();
			$info_arr = $tpl_model->getInfoAll($site_id);
			$tpl_info = array();
			foreach($info_arr as $tpl)
			{
				$tpl_info[$tpl['tpl_id']] = $tpl;
			}
			
			$cache_obj->set($cache_key, $tpl_info);
		}
		
		return $tpl_info;
	}
	
	/**
	 * 根据模版ID获取模版信息
	 */
	static function getInfoById($tpl_id)
	{
		$site_id = self::getSiteIdByTplId($tpl_id);
		$tpl_info = self::getInfo($site_id);
		return isset($tpl_info[$tpl_id]) ? $tpl_info[$tpl_id] : array();
	}
	
	/**
	 * 根据站点获取获取模版信息
	 * 
	 * @param integer $site_id
	 * @return 二维数组
	 */
	static function getListBySiteId($site_id)
	{
		$tpl_info = self::getInfo($site_id);
		return $tpl_info;
	}
	
	/**
	 * 根据模块和站点获取对应的模版列表
	 * 
	 * @param integer $site_id
	 * @param string $module
	 * @author tjx
	 */
	static function getListByModule($site_id, $module)
	{
		$tpl_list = self::getListBySiteId($site_id);
		
		$return = array();
		foreach($tpl_list as $tpl)
		{
			if($tpl['module'] == $module)
			{
				$return[] = $tpl;
			}
		}
		
		return $return;
	}
	
	/**
	 * 根据模块和类型获取对应的模版列表
	 * 		该方法在好多模块列表都有调用
	 *
	 * @param integer $site_id
	 * @param string $module
	 * @param string $type
	 */
	static function getListByModuleType($site_id, $module, $type)
	{
	    $tpl_list = self::getListBySiteId($site_id);
	
	    $return = array();
	    foreach($tpl_list as $tpl)
	    {
	        if($tpl['module'] == $module && $tpl['type'] == $type)
	        {
	            $return[] = $tpl;
	        }
	    }
	
	    return $return;
	}
	
	/**
	 * 判断一个模版是不是某种类型
	 */
	static function is($tpl_id, $module, $type)
	{
		$tpl_info = self::getInfoById($tpl_id);
		if($tpl_info['module'] == $module && $tpl_info['type'] == $type)
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * 获取静态生成路径
	 * 
	 * @param integer $tpl_id
	 */
	static function getHtmlPath($tpl_id)
	{
		$tpl_info = self::getInfoById($tpl_id);
		
		if(!$tpl_info)
		{
			return false;
		}
		
		$create_cfg = Cms_Func::getConfig('create', 'root')->toArray();
		
		return $create_cfg['root'] . $tpl_info['html_path'];
	}
	
	/**
	 * 获取预览地址
	 * 
	 * @param integer $tpl_id
	 */
	static function getCheckHost($tpl_id)
	{
		$tpl_info = self::getInfoById($tpl_id);
		$create_cfg = Cms_Func::getConfig('create', 'host')->toArray();
		
		return $create_cfg['check'] . $tpl_info['html_path'];
	}
	
	/**
	 * 获取默认模版
	 */
	static function getDefaultTpl($site_id, $module, $type)
	{
		$tpl_list = Cms_Template::getListByModuleType($site_id, $module, $type);
		if(!$tpl_list)			//没有发现该类模版
		{
			return false;
		}
		
		$tpl_info = null;
		foreach($tpl_list as $tpl)
		{
			if($tpl['is_default'] == 'Y')
			{
				$tpl_info = $tpl;
				break;
			}
		}
		
		if(null == $tpl_info && count($tpl_list) == 1)
		{
			$tpl_info = $tpl_list[0];
		}
		
		return $tpl_info;
	}
	
	/**
	 * 获取根据tpl_id获取site_id
	 * 
	 * @param integer	$tpl_id
	 * @return integer	$site_id
	 */
	static function getSiteIdByTplId($tpl_id)
	{
		$cache_key = 'tpl_site_id_maps';
		$cache_obj = Cms_Cache::getInstance();
		$map_info = $cache_obj->get($cache_key);
		
		if(!$map_info)
		{
			$tpl_model = new Template_Models_Template();
			$map_info = $tpl_model->getTplSiteIdMap();
			
			$cache_obj->set($cache_key, $map_info);
		}
		
		return $map_info[$tpl_id];
	}
}