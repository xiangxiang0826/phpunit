<?php
/**
 * 页面模块的数据提取类
 * @author tjx
 * @time 2013-07-22
 */
class Datas_Page extends Cms_Data
{
    /**
     * 获取单个页面信息
     * 		{page::info module="article" type="index" id="$entity_id" field="contrast_table" value="" /}
     * 		{=$r['url']}
     *
     *
     * @param array $params
     * 		1. 添加field和value属性
     * 		2. 修改返回数据类型为数组
     */
    public function info($params)
    {
        extract($params);
         
        $tpl_info = Cms_Template::getListByModuleType($site_id, $module, $type);
        if(!empty($tpl_info) && Cms_Template_Config::getEntityId($tpl_info[0]) && empty($id))
        {
            return array();
        }
    
        $condition = "state='able'";
        if(isset($id))
        {
            $id = intval($id);
            $id > 0 && $condition .= " AND `entity_id` ={$id}";
        }
    
        if(!empty($field) && !empty($value))
        {
            $condition .= $this->quoteInto(" AND `{$field}`=?", $value);
        }
    
        foreach ($tpl_info AS $value)
        {
            $sql = "SELECT * FROM `{$value['page_table']}` WHERE {$condition} ORDER BY `is_default` ASC LIMIT 1";
            $row = $this->fetchRow($sql);
            if($row)
            {
                $row['url'] = $this->_formatURL($row['url']);
                return  $row;
            }
        }
        return array();
    }
    
    /**
	 * 获取单个url
	 * 		{page::url module="article" type="index" id="$entity_id" field="contrast_table" value="" /}
	 * 		{=$r['url']}
	 * 
	 * 
	 * @param array $params
	 * 修改者：刘通
	 * 		1. 添加field和value属性
	 * 		2. 修改返回数据类型为数组
	 */
	public function url($params)
	{
	    extract($params);
	    
        $tpl_info = Cms_Template::getListByModuleType($site_id, $module, $type);
        if(!empty($tpl_info) && Cms_Template_Config::getEntityId($tpl_info[0]) && empty($id))
        {
            return array();
        }
        
        $condition = "state='able'";
        if(isset($id))
        {
            $id = intval($id);
            $id > 0 && $condition .= " AND `entity_id` ={$id}";
        }
        
        if(!empty($field) && !empty($value))
        {
        	$condition .= $this->quoteInto(" AND `{$field}`=?", $value);
        }
        
        foreach ($tpl_info AS $value)
        {
            $sql = "SELECT `page_id`, `title`, `url` FROM `{$value['page_table']}` WHERE {$condition} ORDER BY `is_default` ASC LIMIT 1";
            $row = $this->fetchRow($sql);
            if($row)
            {
                $row['url'] = $this->_formatURL($row['url']);
                return  $row;
            }
        }
	    return array();
	}
	
	/**
	 * 获取多个页面信息
	 * {page::urls module="store" type="index"  key="switch_name" /}
	 * @param array $params
	 */
	public function urls($params)
	{
	    extract($params);
	    $tpl_info = Cms_Template::getListByModuleType($site_id, $module, $type);
	    if(empty($tpl_info))
	    {
	        return '模块或类型错误';
	    }
	    
	    $condition = "state='able'";
	    if(isset($id))
	    {
	        $id = intval($id);
	        $id > 0 && $condition .= " AND `entity_id` ={$id}";
	    }

        $sql = "SELECT * FROM `{$tpl_info[0]['page_table']}` WHERE {$condition}";
        $rows = $this->fetchAll($sql);
       
        if(!empty($rows) && !empty($key) && !empty($rows[0][$key]))
        {
            $tem_array = array();
            foreach($rows AS $row)
            {
                $row['url'] = $this->_formatURL($row['url']);
                $tem_array[$row[$key]] = $row;
            } 
            return $tem_array;
        }
        else 
        {
            foreach($rows AS &$row)
            {
                $row['url'] = $this->_formatURL($row['url']);
            }
        }
       
	    return $rows;
	}
}