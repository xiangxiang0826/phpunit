<?php
/**
 * cms2.1-文章钩子
 * @author tjx
 * @time 2013-05-30
 */
class Hooks_Article_Article extends Cms_Hook
{
    /**
     * 文章修改前处理动作
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
	 * 文章修改后处理动作
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
	 * 文章删除后处理动作
	 * @param array $data 数组
	 */
	public function afterDelete($data)
	{
	    Cms_Page::deletePage($data['tpl_id'], array($data['page_id']));
	}
}