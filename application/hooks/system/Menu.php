<?php

/**
 * 站点管理的钩子
 * 
 * @author 刘通
 */
class Hooks_System_Menu extends Cms_Hook
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
	
	/**
	 * 删除前动作
	 * 		先删除分给用户组的菜单信息
	 * 
	 * @see Cms_Hook::beforeDelete()
	 */
	function beforeDelete($data)
	{
		$groupmenu_model = new System_Models_Groupmenu();
		$groupmenu_model->delete("`menu_id`={$data['menu_id']}");
		
		return true;
	}
	
	function afterDelete($data)
	{
		
	}
}