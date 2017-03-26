<?php
/**
 * Newsroom 模块的数据提取类
 * @author tjx
 * @time 2013-07-27
 */
class Datas_Newsroom extends Cms_Data
{
	public function __construct()
	{
		parent::__construct();
	}

	
	/**
	 * 获取公司文章信息(一维数组)
	 * {newsroom::article_info art_id="1" /}
	 * 页面参数   art_id         文章ID
	 * @param array $params
	 */
	public function article_info($params)
	{
	    if(empty($params['art_id']))
	    {
	        echo '没有发现 文章ID';
	        return array();
	    }
	
	    $art_id = intval($params['art_id']);
	    $sql = "SELECT n_a.*, n_a.page_url AS url 
	            FROM `newsroom_articles` n_a  
	            WHERE `id` = {$art_id}  LIMIT  1";
	    return $this->fetchRow($sql);
	}
    
	/**
	 * 获取年份列表(二维数组)
	 * 例：
	 * {newsroom::year_list sort="" order="" num="5"}
	 *   <li>{=$r['year']}</li>
	 * {/newsroom}
	 *
	 * @author liutong
	 * @update author tjx
	 */
	public function year_list($params)
	{
	    //条件
	    $condition = "`state` = 'able'";
	    $tpl_info = Cms_Template::getListByModuleType($params['site_id'], 'newsroom', 'list_year');
	    
		if(empty($params['sort']))
		{
		    $params['sort'] = 'year';
		}
		if(empty($params['order']) OR strtolower($params['order']) != 'asc')
		{
		    $params['order'] = 'DESC';
		}
		$condition = "WHERE {$condition} ORDER BY `{$params['sort']}` {$params['order']}";
		
		if(isset($params['num']))
		{
		    $limit = intval($params['num']);
		    $limit > 0 && $condition .= " LIMIT {$limit}";
		}
		
	    $sql = "SELECT * FROM `{$tpl_info[0]['page_table']}`  {$condition}";
	    $rows = $this->fetchAll($sql);
	    return $rows;
	}
	
	/**
	 * 获取公司文章信息列表(二维数组)
	 * 例：
	 * {newsroom::article_list year="2013" art_ids="12,12" sort="" order="" gt="" lt="" num="5"}
	 *   <li>{=$r['title']}</li>
	 * {/newsroom}
	 * 
	 * @author liutong 
	 * @update author tjx
	 * 页面参数  gt           id大于多少
	 * 页面参数  lt           id小于多少
	 * 页面参数  year         年份
	 * 页面参数  art_ids      文章ids
	 * 页面参数  sort         排序字段
	 * 页面参数  order        排序
	 * 页面参数  num          取的条数   
	 */
	public function article_list($params)
	{
	    //条件
	    $condition = "`tpl_id` > 0 AND `state` = 'able'";
	    if(!empty($params['art_ids']))
	    {
	        $art_ids = Cms_Func::strToArr($params['art_ids']);
	        if(!empty($art_ids))
	        {
	            $condition .= $this->quoteInto(" AND `id` IN (?) ", $art_ids);
	        }
	    }
	    if(!empty($params['year']))
	    {
	        $params['year'] = intval($params['year']);
	        $start_date = "{$params['year']}-01-01 00:00:00";
	        $params['year']++;
	        $end_date = "{$params['year']}-01-01 00:00:00";
	        $condition .= " AND `publish_time` > '{$start_date}' AND `publish_time` < '{$end_date}'";
	    }
		if(!empty($params['gt']))
		{
		    $params['gt'] = intval($params['gt']);
		    $condition .= " AND `id` > {$params['gt']}";
		}
		if(!empty($params['lt']))
		{
		    $params['lt'] = intval($params['lt']);
		    $condition .= " AND `id` < {$params['lt']}";
		}
		if(empty($params['sort']))
		{
		    $params['sort'] = 'publish_time';
		}
	    if(empty($params['order']) OR strtolower($params['order']) != 'asc')
	    {
	        $params['order'] = 'DESC';
	    }
		
		$condition = "WHERE `site_id`= {$params['site_id']} AND {$condition}
		                ORDER BY `{$params['sort']}` {$params['order']}, `id` DESC";
        if(isset($params['num']))
        {
                $limit = intval($params['num']);
                $limit > 0 && $condition .= " LIMIT {$limit}";
		}
		
		$sql = "SELECT n_a.*, n_a.`page_url` AS `url` 
				FROM `newsroom_articles` n_a 
				{$condition}";
		$data = $this->fetchAll($sql);
		return $data;
	}

	/**
	 * 获取公司文章分页列表(二维数组)
	 * 例：
	 * {newsroom::page_article_list year="2012" sort="" order="" page="$page" pagesize="15"}
	 *   <li>{=$r['title']}</li>
	 * {/newsroom}
	 *
	 * 页面参数  year             年份
	 * 页面参数  sort             排序字段
	 * 页面参数  order            排序
	 */
	public function page_article_list($params)
	{
	    //条件
	    $condition = "`tpl_id` > 0 AND `state` = 'able'";
	    if(!empty($params['year']))
	    {
	        $params['year'] = intval($params['year']);
	        $start_date = "{$params['year']}-01-01 00:00:00";
	        $params['year']++;
	        $end_date = "{$params['year']}-01-01 00:00:00";
	        $condition .= " AND `publish_time` > '{$start_date}' AND `publish_time` < '{$end_date}'";
	    }
		
		if(empty($params['sort']))
		{
		    $params['sort'] = 'publish_time';
		}
	    if(empty($params['order']) OR strtolower($params['order']) != 'asc')
	    {
	        $params['order'] = 'DESC';
	    }
		
		$condition = "WHERE `site_id`={$params['site_id']} AND {$condition}
                    ORDER BY `{$params['sort']}` {$params['order']}, `id` DESC";
		
		$params['sql'] = "SELECT n_a.*, n_a.`page_url` AS `url` 
        				FROM `newsroom_articles` n_a 
        				{$condition}";
        return $this->pageQuery($params);
	}
}