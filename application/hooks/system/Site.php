<?php

/**
 * 站点管理的钩子
 * 
 * @author 刘通
 */
class Hooks_System_Site extends Cms_Hook
{
	function afterAdd($data)
	{
		
	}
	
	/**
	 * 修改前动作
	 * 		从开发状态改变到正常状态，判断页面数据是否完整（因为导入的数据，可能不完整，要编辑整好才能上线）
	 */
	function beforeEdit($old_data, $data)
	{
		if($data['state'] == 'able' && $old_data['state'] != $data['state'])
		{
			$db = Cms_Db::getConnection();
			$sql = "SELECT count(*) FROM `search_site_pages` WHERE `site_id`='{$data['site_id']}' AND `state`='able' AND `index_id`=0 AND `title`=''";
			$cnt = $db->fetchOne($sql);
			if($cnt)
			{
				Cms_Func::response("有{$cnt}篇页面没有title，不允许上线");
			}
		}
	}
	
	function afterEdit($data)
	{
		
	}
	
	/**
	 * 删除前动作
	 * 		先删除分给用户组的站点信息
	 * 		再删除站点信息
	 * 
	 * @see Cms_Hook::beforeDelete()
	 */
	function beforeDelete($data)
	{
		$groupsite_model = new System_Models_Groupsite();
		$groupsite_model->delete("`site_id`='{$data['site_id']}'");
		
		Cms_Cache::getInstance()->delete('site_info');
		
		return true;
	}
	
	function afterDelete($data)
	{
		
	}
}