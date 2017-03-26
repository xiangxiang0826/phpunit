<?php

/**
 * 处理template.ini的配置信息
 */
class Cms_Template_Config
{
	/**
	 * 获取所有配置信息
	 */
	static function getCfgAll()
	{
		$reqest = Zend_Controller_Front::getInstance()->getRequest();
		$site_id = $reqest->getParam('site_id');
		$site_info = Cms_Site::getInfoById($site_id);
		
		if(empty($site_info['tpl_cfg_file']))
		{
			$site_info['tpl_cfg_file'] = 'template';
		}
		
		return Cms_Func::getConfig("template/{$site_info['tpl_cfg_file']}", 'type')->toArray();
	}
	
	/**
	 * 根据模版类型，获取配置信息
	 */
	static function getCfgInfo($tpl_info)
	{
		static $tpl_cfg = null;
		
		if(null === $tpl_cfg)
		{
			$tpl_cfg = self::getCfgAll();
		}
		
		$module = $tpl_info['module'];
		$type = $tpl_info['type'];
				
		if(isset($tpl_cfg[$module][$type]))
		{
			return $tpl_cfg[$module][$type];
		}
		
		return false;
	}
	
	/**
	 * 根据配置判断模版是否需要实体ID
	 */
	static function getEntityId($tpl_info)
	{
		$tpl_cfg = self::getCfgInfo($tpl_info);
		
		if(empty($tpl_cfg['entity_id']))
		{
			return false;
		}
		
		return $tpl_cfg['entity_id'];
	}
	
	/**
	 * 根据module，获取对应的类型
	 */
	static function getTypeByModule($module)
	{
		$tpl_cfg = self::getCfgAll();
		return isset($tpl_cfg[$module]) ? $tpl_cfg[$module] : array();
	}
	
	/**
	 * 获取所有模块
	 */
	static function getModuleAll()
	{
		$tpl_cfg = self::getCfgAll();
		return array_keys($tpl_cfg);
	}
	
	/**
	 * 根据模版ID获取模版对应实体表信息，如果没有实体ID和syn_table信息，返回false
	 */
	static function getSynTableByTplId($tpl_id)
	{
		$tpl_info = Cms_Template::getInfoById($tpl_id);
		$table_info = self::getCfgInfo($tpl_info);
		
		if(!empty($table_info['entity_id']) && !empty($table_info['syn_table']))
		{
			return $table_info;
		}
		
		return false;
	}
	
	/**
	 * 根据实体ID获取对应实体表中的模版信息
	 * 
	 * @param string	$module
	 * @param string	$type
	 * @param array		$entity_ids
	 */
	static function getTplInfoByEntityId($module, $type, array $entity_ids)
	{
		$tpl_cfg = self::getCfgAll();
		if(!isset($tpl_cfg[$module][$type]))
		{
			return false;
		}
		
		$table_info = $tpl_cfg[$module][$type];
		if(empty($table_info['entity_id']) OR empty($table_info['syn_table']))
		{
			return false;
		}
		
		$db = Cms_Db::getConnection();
		$condition = $db->quoteInto("`{$table_info['entity_id']}` IN (?) AND `tpl_id`>0 AND `state`='able'", $entity_ids);
		$sql = "SELECT `tpl_id`, `page_id`, `page_url` 
				FROM `{$table_info['syn_table']}`
				WHERE {$condition}";
		
		return $db->fetchAll($sql);
	}
}