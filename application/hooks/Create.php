<?php
/**
 * 生成时用到的Hook
 * 
 * @author 刘通
 */
class Hooks_Create extends Cms_Hook
{
	function afterAdd($data)
	{
		
	}
	
	function beforeEdit($old_data, $data)
	{
		
	}
	
	function afterEdit($data)
	{
		
	}
	
	function beforeDelete($data)
	{
		
	}
	
	function afterDelete($data)
	{
		
	}
	
	/**
	 * 生成前处理
	 * 
	 * @param array $page_urls
	 */
	function beforeCreate($page_urls)
	{
		$url_arr = array();
		$html_path = Cms_Template::getHtmlPath($this->tpl_id);
		
		foreach($page_urls as $page_url)
		{
			$page_file = $html_path . $page_url;
			if(!file_exists($page_file))
			{
				continue;
			}
			
			$dirname = dirname($page_file);
			$url_arr = array_merge($url_arr, Cms_Utility_File::getPageFiles($dirname, true));
		}
		
		if($url_arr)
		{
			Cms_Task::getInstance()->send($url_arr, 'delete');
		}
		
		return true;
	}
	
	/**
	 * 生成后处理
	 * 
	 * @param array $url_arr
	 * @param mixed $condition
	 */
	function afterCreate($url_arr, $page_ids=null)
	{
		$html_path = Cms_Template::getHtmlPath($this->tpl_id);
		$tpl_info = Cms_Template::getInfoById($this->tpl_id);
		
		if($tpl_info['is_list'] == 'Y')
		{
			$tmp = array();
			foreach($url_arr as $url)
			{
				$tmp[] = $file = $html_path . $url;
				$dirname = dirname($file);
				$page_urls = Cms_Utility_File::getPageFiles($dirname);
				$tmp = array_merge($tmp, $page_urls);
			}
			
			$url_arr = $tmp;
		}
		else 
		{
			foreach($url_arr as &$url)
			{
				$url = $html_path . $url;
			}
		}
		Cms_Task::getInstance()->send($url_arr, 'edit');
		
		//修改实体表状态
		$table_info = Cms_Template_Config::getSynTableByTplId($this->tpl_id);
		if($table_info)
		{
			$db = Cms_Db::getConnection();
			
			if(is_array($page_ids))
			{
				$page_ids = explode(',', $page_ids);
			}
			
			if($page_ids)
			{
				$condition = " AND `page_id` IN ({$page_ids})";
			}
			else 
			{
				$condition = '';
			}
			
			$db->update($table_info['syn_table'], array('page_state'=>'created'), "`tpl_id`={$this->tpl_id}{$condition}");
		}
		
		return true;
	}
}