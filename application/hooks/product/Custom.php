<?php

/**
 * 站点产品的钩子
 * 
 * @author 刘通
 */
class Hooks_Product_Custom extends Cms_Hook
{
	/**
	 * 添加后动作
	 * 		1. 根据是否评论选项，决定评论页面的操作
	 * 			选择是，自动添加评论页面
	 * 
	 * @see Cms_Hook::afterAdd()
	 */
	function afterAdd($data)
	{
		if($data['is_review'] == '1')
		{
			$set = array(
				'title'		=> $data['name'],
				'keyword'	=> '',
				'url'		=> '/reviews/' . Cms_Page::string2url($data['name']) . '/index.html',
				'entity_id' => $data['pro_id'],
				'entity_name' => $data['name'],
			);			
			Cms_Page::autoAdd($this->site_id, 'product', 'list_review', $set);
		}
	}
	
	/**
	 * 编辑前动作
	 * 		1. 判断是否评论选项是否修改，如果修改则进行处理评论页面，具体策略见 afterAdd
	 * 		2. 根据tpl_id和page_id来处理overview页面的状态
	 * 
	 * @see Cms_Hook::beforeEdit()
	 */
	function beforeEdit($old_data, $data)
	{
		//修改评论页面信息
		if($data['is_review'] != $old_data['is_review'])
		{
			if($data['is_review'] == '1')
			{
				$set = array(
					'title'		=> $data['name'] . ' - reviews',
					'keyword'	=> '',
					'url'		=> '/reviews/' . Cms_Page::string2url($data['name']) . '/index.html',
					'entity_id' => $data['pro_id'],
					'entity_name' => $data['name'],
				);
				
				Cms_Page::autoAdd($this->site_id, 'product', 'list_review', $set);
			}
			else 
			{
				Cms_Page::autoDelete($this->site_id, 'product', 'list_review', $data['pro_id']);
			}
		}
		
		//修改状态
		if($old_data['tpl_id'] > 0)
		{
			Cms_Page::updateState($old_data['tpl_id'], array($old_data['page_id']));
		}
	}
	
	/**
	 * 编辑后动作
	 * 
	 * @see Cms_Hook::afterEdit()
	 */
	function afterEdit($data)
	{
		
	}
	
	/**
	 * 删除前动作
	 * 		1. 根据is_review来处理评论页面
	 * 		2. 根据tpl_id和page_id来处理overview页面的状态
	 * 		
	 * @see Cms_Hook::beforeDelete()
	 */
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