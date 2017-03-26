<?php
/**
 * cms2.1-项目钩子
 * @author hcb
 * @time 2013-09-23
 */
class Hooks_Lab_lab extends Cms_Hook
{
    /**
     * 项目修改前处理动作
     * @param array $old_data 数组
     * @param array $data 数组
     */
	public function beforeEdit($old_data, $data)
	{
	    if($old_data['tpl_id'] > 0)
	    {
	        Cms_Page::updateState($old_data['tpl_id'], array($old_data['page_id']));
	    }
	    unset($old_data, $data);
	    return true;
	}
	
	/**
	 * 项目修改后处理动作
	 * @param array $data 数组
	 */
    public function afterEdit($data)
	{
	  
	}
	
	public function afterAdd($data)
	{
		
	}
	
	public function beforeDelete($data)
	{
	
	}
	
	/**
	 * 项目删除后处理动作
	 * @param array $data 数组
	 */
	public function afterDelete($data)
	{
	    Cms_Page::deletePage($data['tpl_id'], array($data['page_id']));
	}
}