<?php

/**
 * 产品分类的钩子
 * 
 * @author 刘通
 */
class Hooks_Product_Category extends Cms_Hook
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
		
		return true;
	}
	
	function afterEdit($data)
	{
		
	}
	
	/**
	 * 删除前动作
	 * 		判断是否有子分类，有则不允许删除
	 * 		查看是否有产品属于这个分类，有则不允许删除
	 * 		如果有页面，则删除页面
	 * 
	 * @see Cms_Hook::beforeDelete()
	 */
	function beforeDelete($data)
	{
		$cat_model = new Product_Models_Category();

		//判断有无子分类
		$children = $cat_model->getChildrenId($this->site_id, $data['cat_id']);
		if($children)
		{
			Cms_Func::response(Cms_L::_('has_child_del'));
		}
		
		//判断有无产品属于该分类
		$custom_model = new Product_Models_Custom();
		$count = $custom_model->getCount("`site_id`={$this->site_id} AND `cat_id`={$data['cat_id']}");
		if($count > 0)
		{
			Cms_Func::response(Cms_L::_('has_product_del'));
		}
		
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