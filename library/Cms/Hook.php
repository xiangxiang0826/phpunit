<?php

/**
 * 钩子的基类
 * 
 * @author 刘通
 */
abstract class Cms_Hook
{
	protected $site_id;
	protected $tpl_id;
	
	function __construct(array $options)
	{
	    if(!empty($options))
	    {
    		foreach ($options as $key => $opt)
    		{
    			$this->{$key} = $opt;
    		}
	    }
	}
	
	/**
	 * 添加前动作
	 */
	function beforeAdd($data)
	{
		return true;
	}
	
	/**
	 * 添加后动作
	 * @param array $data
	 */
	abstract function afterAdd($data);
	
	/**
	 * 修改前动作
	 * @param array $old_data	原有数据
	 * @param array $data	新数据
	 */
	abstract function beforeEdit($old_data, $data);
	
	/**
	 * 修改后动作
	 * @param array $data
	 */
	abstract function afterEdit($data);
	
	/**
	 * 删除前动作
	 * @param array $data
	 */
	abstract function beforeDelete($data);
	
	/**
	 * 删除后动作
	 * @param array $data
	 */
	abstract function afterDelete($data);
	
	/**
	 * 更新全站搜索表
	 */
	protected function updateSitePage($data, $action='update')
	{
		//更新全站搜索表site_pages
		$site_page_model = new Search_Models_Site_Pages();
		$data['site_id'] = $this->site_id;
		$data['tpl_id'] = $this->tpl_id;
		$data['keyword'] = isset($data['keyword']) ? preg_replace('/,[ ]+/', ',', $data['keyword']) : '';			//为了便于查询把逗号后的空格都去掉
		$tpl_info = Cms_Template::getInfoById($this->tpl_id);
		$data['module'] = $tpl_info['module'];
		$site_page_model->$action($data);
		
		return true;
	}
}