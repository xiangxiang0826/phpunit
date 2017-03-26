<?php

/**
 * 切换页的钩子
 * 
 * @author 刘通
 */
class Hooks_Product_Switch extends Cms_Hook
{
	function afterAdd($data)
	{
		
	}
	
	/**
	 * 编辑前动作
	 * 		修改页面状态
	 * 
	 * @see Cms_Hook::beforeEdit()
	 */
	function beforeEdit($old_data, $data)
	{
		//修改状态
		if($old_data['tpl_id'] > 0)
		{
			Cms_Page::updateState($old_data['tpl_id'], array($old_data['page_id']));
		}
		
		return true;
	}
	
	function afterEdit($data)
	{
		
	}
	
	function beforeDelete($data)
	{
		//删除页面
		if($data['tpl_id'] > 0)
		{
			Cms_Page::deletePage($data['tpl_id'], array($data['page_id']));
		}
		
		return true;
	}
	
	function afterDelete($data)
	{
		
	}
}