<?php
/**
 * cms2.1-Public 同步Support钩子
 * @author tjx
 * @time 2013-06-25
 */
class Hooks_Support_Syn extends Cms_Hook
{
    private $_template_table_prefix = 'template_page_';
	public function beforeEdit($old_data, $data)
	{
		  
	}
	
	/**
	 * 同步Support修改后处理动作
	 * @param array $data 数组
	 */
    public function afterEdit($data)
	{
	    
	}
	
	/**
	 * 同步Support修改后处理动作
	 * @param array $data 数组
	 */
	public function afterAdd($data)
	{
	    
	}
	
	public function beforeDelete($data)
	{
	
	}
	
	public function afterDelete($data)
	{
	
	}
	
	/**
	 * 同步Support添加页面后处理动作
	 * @param array $data 数组
	 */
	public function afterAddPage($data)
	{
	    static $page_table, $hooks_page;
	    if(empty($page_table[$this->tpl_id] ))
	    {
	        $page_table[$this->tpl_id] = $this->tpl_id;
	        $hooks_page = new Hooks_Page(array('site_id' => $this->site_id, 'tpl_id' => $this->tpl_id));//钩子
	    }
	    $data['state'] = 'able';
	    
	    //调用页面钩子
	    $hooks_page->afterAdd($data);
	    return true;
	}
	
	/**
	 * 同步Support修改页面后处理动作
	 * @param array $data 数组
	 */
	public function afterUpdatePage($data)
	{
	    static $table, $table_info;
	
	    if(empty($table[$this->tpl_id]))
	    {
	        $table_info = Cms_Template_Config::getSynTableByTplId($this->tpl_id);
	        $table[$this->tpl_id] = $this->tpl_id;
	    }
	
	    if(!empty($table_info['syn_table']) )
	    {
	        //同步实体表信息
	        $set = array('page_state' => 'modified');
	
	        $db = Cms_Db::getConnection();
	        $set['page_url'] = $data['url'];
	        $set['page_keyword'] = $data['keyword'];
	        $set['tpl_id'] = $this->tpl_id;
	        $set['page_id'] = $data['page_id'];
	        $db->update($table_info['syn_table'], $set, "`{$table_info['entity_id']}` = {$data['entity_id']}");
	    }
	
	    //更新全站搜索表
	    $data['state'] = 'able';
	    $this->updateSitePage($data);
	    return true;
	}
	
	/**
	 * 同步Support修改页面后处理动作
	 * @param string  $where_str 条件
	 */
	public function afterDeletePage($where_str)
	{
	    static $table, $file_path_prefix, $tpl_info, $site_page_model, $db;
	    
	    if(empty($table[$this->tpl_id]))
	    {
	        $file_path_prefix = Cms_Template::getHtmlPath($this->tpl_id);
	        $tpl_info = Cms_Template::getInfoById($this->tpl_id);
	        $site_page_model = new Search_Models_Site_Pages();
	        $db = Cms_Db::getConnection();
	        $table[$this->tpl_id] = $this->tpl_id;
	    }
	    
	    $sql  = "SELECT `url`, `page_id`  FROM `{$this->_template_table_prefix}{$this->tpl_id}` WHERE {$where_str}";
	    $rows = $db->fetchAll($sql);
	    $urls = array();
	    foreach($rows AS $row)
	    {
	        $urls[] =  array($row['url'], $row['page_id']);
	    }
	    
	    //删除文件 以及记录发布系统
	    if(!empty($urls))
	    {
	        foreach($urls AS $value)
	        {
	            Cms_Utility_File::deleteNodeBackTrace($file_path_prefix.$value[0], $tpl_info['is_list']);   
           }
	    }
	    
	    //更新全站搜索里面的记录
	    $site_page_model->updatePages($tpl_info);
	    return true;
	}
}