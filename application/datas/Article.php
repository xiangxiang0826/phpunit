<?php
/**
 * 文章模块的数据提取类
 * @author liutong
 * @update author tjx
 * @time 2013-07-13
 */
class Datas_Article extends Cms_Data
{
	private $_end_condition = " `tpl_id` > 0 AND `state` = 'able'";
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * 获取文章评论
	 * 例：
	 * {article::review_list art_id="12" num="10" /}
	 *
	 * @author hcb
	 *
	 * 页面参数  $art_id          文章ID
	 * 页面参数  $num          	显示条数
	 * 
	 */
	public function review_list($params)
	{
		$condition = '';
		
		//条件
		if(isset($params['art_id']))
		{
			$art_id = intval($params['art_id']);
			$art_id > 0 &&  $condition = "`art_id` =  {$art_id} AND ";
		}
		
		if(isset($params['num']))
		{
			$num = intval($params['num']);
		}else{
			$num = 10;
		}
	
		$sql = "SELECT `author`, `message` FROM `article_reviews` WHERE {$condition} `state` = 'able' ORDER BY `id` DESC LIMIT {$num}";
		$rows = $this->fetchAll($sql);
		foreach($rows as &$row)
		{
			$row['message'] = stripslashes($row['message']);
		}
		
		return  $rows;
	}
	
	/**
	 * 获取文章评论数
	 * 例：
	 * {article::review_num art_id="12" /}
	 *
	 * 页面参数  $art_id          文章ID
	 */
	public function review_num($params)
	{
	    //条件
	    $condition = "`state` = 'able'";
	    if(isset($params['art_id']))
	    {
	        $art_id = intval($params['art_id']);
	        $art_id > 0 &&  $condition .= " AND `art_id` =  {$art_id}";
	    }

	    $sql = "SELECT count(*) as num FROM `article_reviews` WHERE {$condition}";
	    $row = $this->fetchRow($sql);
	    return  $row;
	}
	
	/**
	 * 获取标签信息
	 * {article::tag_info tag_id="1" /}
	 * 页面参数   id         标签ID
	 * @param array $params
	 */
	public function tag_info($params)
	{
	    if(empty($params['tag_id']))
	    {
	        echo '没有发现 标签ID';
	        return array();
	    }
	
	    $tag_id = intval($params['tag_id']);
	    $sql = "SELECT a_t.*, a_t.page_url AS url FROM `article_tags` a_t  WHERE `tag_id` = {$tag_id}  LIMIT  1";
	    $row = $this->fetchRow($sql);
	    
	    if($row)
	    {
	    	$row['url'] = $row['page_url'] = $this->_formatURL($row['url']);
	    }
	    
	    return $row;
	}
	
	/**
	 * 获取分类信息
	 * {article::category_info cat_id="1" /}
	 * 页面参数   id         分类ID
	 * @param array $params
	 */
	public function category_info($params)
	{
	    if(empty($params['cat_id']))
	    {
	        echo '没有发现分类ID';
	        return array();
	    }
	    
	    $cat_id = intval($params['cat_id']);
	
	    if( !empty( $params['get_child_ids'] ) && $params['get_child_ids'] == 1 ){//取得某分类下所有子分类ID by hcb
	    	$sql = "SELECT GROUP_CONCAT(`cat_id`) AS child_ids, '' AS url FROM `article_categorys` a_c WHERE `parent_id` = {$cat_id} LIMIT 1";
	    }else{
	    	$sql = "SELECT a_c.*, a_c.page_url AS url FROM `article_categorys` a_c WHERE `cat_id` = {$cat_id} LIMIT 1";
	    }	    

	    $row = $this->fetchRow($sql);
	    
	    if($row)
	    {
	    	$row['url'] = $row['page_url'] = $this->_formatURL($row['url']);
	    }

	    return $row;
	}
	
	
	/**
	 * 根据作者ID获取作者信息
	 * {article::author_info author_id="33" /}
	 * 
	 * 页面参数   id         作者ID
	 * 页面参数   cat_id     分类ID
	 * @param array $params
	 */
	public function author_info($params)
	{
	    if(isset($params['cat_id']))//传入分类获取文章数最多的作者
	    {
	        $cat_id = intval($params['cat_id']);
	        $sql = "SELECT COUNT(*) `nums`, `author_id` 
	                FROM `articles` 
	                WHERE `cat_id` = {$cat_id} 
	                GROUP BY `author_id`
	                ORDER BY `nums` DESC 
	                LIMIT 1";
	        $row = $this->fetchRow($sql);
	        $author_id = empty($row) ? 0 : $row['author_id'];
	    }
	    else 
	    {
	        $author_id = intval($params['author_id']);
	    }
	    
	    $sql = "SELECT * FROM `article_authors` WHERE `author_id` = {$author_id} LIMIT 1";
	    return $this->fetchRow($sql);
	}
	
	
	/**
	 * 获取多个作者
	 * {article::author_list num="3"}
	 *
	 * @param array $params
	 */
	public function author_list($params)
	{
		$num = isset( $params['num'] ) ? (int)$params['num'] : 5;
		$sql = "SELECT * FROM `article_authors` WHERE `site_id`={$params['site_id']} AND `state` = 'able' ORDER BY `sort` ASC LIMIT {$num}";
		return $this->fetchAll($sql);
	}
	
	/**
	 * 根据作者ID获取文章信息
	 * {article::info art_id="33" /}
	 * 
	 * 页面参数  id         文章ID
	 * @param array $params
	 */
	public function info($params)
	{
		if(empty($params['art_id']))
		{
		    echo '没有发现文章ID';
			return array();
		}
		$art_id = intval($params['art_id']);
		$sql = "SELECT *, page_url AS url FROM `articles` WHERE `art_id`={$art_id}";
		$data = $this->fetchRow($sql);
		return $data;
	}
	
	/**
	 * 获取文章分类的子分类IDs包括自己
	 * 例：
	 * {article::child_cat_ids cat_id="1" }
	 *   <li>{=$r['cat_ids']}</li>
	 * {/article}
	 *
	 * 页面参数 cat_id  分类ID		
	 */
	public function child_cat_ids($params)
	{
	    if(empty($params['cat_id']))
	    {
	        echo '没有发现分类ID';
	        return array();
	    }
	    
        $cat_id = intval($params['cat_id']);
        $row['cat_ids'] = '';
        if($cat_id > 0)
        {
            $row['cat_ids'] = $this->_getChildAndSelfCatIds($params['site_id'], $cat_id);
            $row['cat_ids'] = implode(',', $row['cat_ids']);
        }
        return $row;
	}
	
	/**
	 * 获取文章分类信息列表(二维数组)
	 * 例：
	 * {article::category_list is_recom="1" is_hot="1" num="5"}
	 *   <li>{=$r['title']}</li>
	 * {/article}
	 * 
	 * @author liutong 
	 * @update author tjx
	 * 
	 * 页面参数 cat_ids			获取多个分类的信息列表
	 * 页面参数 parent_id			获取该ID的子分类	//201308091120 刘通添加该参数
	 * 页面参数  is_recom         是否推荐
	 * 页面参数  is_hot           是否热门
	 * 页面参数  sort             排序字段
	 * 页面参数  order            排序
	 * 页面参数  num            取的条数   
	 */
	public function category_list($params)
	{
	    //条件
	    $condition = '';
	    
	    if ( isset( $params['author_id'] ) )//取得某个作者负责文章的所有分类, by HCB
	    {
	    	$author_id = intval($params['author_id']);
	    	$sql = "SELECT GROUP_CONCAT(DISTINCT(`cat_id`)) AS cat_ids FROM `articles` WHERE author_id = $author_id";
	    	$ret = $this->fetchRow( $sql );

	    	if(!empty( $ret['cat_ids'])) 
	    	{
	    	    $params['cat_ids'] = $ret['cat_ids'];
	    	}
	    }	    
	    

	    if(!empty($params['cat_ids']))
	    {

	        $cat_ids = Cms_Func::strToArr($params['cat_ids']);
	        if(!empty($cat_ids))
	        {
	            $condition .= $this->quoteInto(" AND `cat_id` IN (?) ", $cat_ids);
	        }
	    }
	    
	    if(!empty($params['parent_id']))		//刘通添加，获取一个分类下的子分类信息
	    {
	    	$parent_id = (int) $params['parent_id'];
	    	$condition .= " AND `parent_id`={$parent_id}";
	    }
	    
	    if(isset($params['layer']))
	    {
	        $layer = intval($params['layer']);
	        $layer > 0 &&  $condition .= " AND `layer` =  {$layer}";
	    }
	    if(isset($params['is_recom']) && in_array($params['is_recom'], array('0', '1')))
		{
		    $condition .= " AND `is_recom` =  '{$params['is_recom']}'";
		}
		if(isset($params['is_hot']) && in_array($params['is_hot'], array('0', '1')))
		{
		    $condition .= " AND `is_hot` =  '{$params['is_hot']}'";
		}
		if(empty($params['sort']))
		{
		    $params['sort'] = 'sort';
		}
	    if(empty($params['order']) OR strtolower($params['order']) != 'asc')
	    {
	        $params['order'] = 'DESC';
	    }
		
		$condition = "WHERE `site_id`={$params['site_id']} {$condition} AND {$this->_end_condition}
                    ORDER BY `{$params['sort']}` {$params['order']}, `cat_id` DESC";
        if(isset($params['num']))
        {
			$limit = intval($params['num']);
			$limit > 0 && $condition .= " LIMIT {$limit}";
		}
		
		$sql = "SELECT ac.*, `page_url` AS `url` 
				FROM article_categorys ac
				{$condition}";
		$rows = $this->fetchAll($sql);
		foreach($rows as &$row)
		{
			$row['url'] = $row['page_url'] = $this->_formatURL($row['url']);
		}
		
		return $rows;
	}
	
	/**
	 * 获取文章信息列表(二维数组)
	 * 例：
	 * {article::tag_list is_recom="1" is_hot="1" num="5"}
	 *   <li>{=$r['title']}</li>
	 * {/article}
	 *
	 * @author tjx
	 * 
	 * 页面参数  is_hot           是否热门
	 * 页面参数  sort             排序字段
	 * 页面参数  order            排序
	 * 页面参数  num              取的条数
	 */
	public function tag_list($params)
	{
	    //条件
	    $condition = '';
	    if(isset($params['is_hot']) && in_array($params['is_hot'], array('0', '1')))
	    {
	        $condition .= " AND `is_hot` =  '{$params['is_hot']}'";
	    }
	     
	    if(empty($params['sort']))
	    {
	        $params['sort'] = 'sort';
	    }
	    if(empty($params['order']) OR strtolower($params['order']) != 'asc')
	    {
	        $params['order'] = 'DESC';
	    }
	     
	    $condition = "WHERE `site_id`={$params['site_id']} {$condition} AND {$this->_end_condition}
	                ORDER BY `{$params['sort']}` {$params['order']}, `tag_id` DESC";
	    if(isset($params['num']))
	    {
	        $limit = intval($params['num']);
	        $limit > 0 && $condition .= " LIMIT {$limit}";
	    }
	
       $sql = "SELECT `tag_id`, `name`, `page_url` AS `url`
                FROM `article_tags`
                {$condition}";
        $rows = $this->fetchAll($sql);
        
        foreach($rows as &$row)
        {
        	$row['url'] = $row['page_url'] = $this->_formatURL($row['url']);
        }
        
        return $rows;
	}
		
	/**
	 * 获取文章信息列表(二维数组)
	 * 例：
	 * {article::lists is_recom="1" is_hot="1" num="5"}
	 *   <li>{=$r['title']}</li>
	 * {/article}
	 * 
	 * @author liutong 
	 * @update author tjx
	 * 页面参数  cat_ids          分类IDs
	 * 页面参数  art_ids          文章IDs
	 * 页面参数  is_recom         是否推荐
	 * 页面参数  is_hot           是否热门
	 * 页面参数  sort             排序字段
	 * 页面参数  order            排序
	 * 页面参数  num              取的条数   
	 * 页面参数  is_rand          是否随机
	 * 
	 */
	public function lists($params)
	{
	    //条件
	    $condition = '';
	    if(!empty($params['cat_id']))
	    {
	        $cat_id = intval($params['cat_id']);
	        if($cat_id > 0)
	        {
	            $cat_ids = $this->_getChildAndSelfCatIds($params['site_id'], $cat_id);
	        
	            if(empty($params['is_cat_id_v']))
	            {
	                $condition .= $this->quoteInto(" AND `cat_id` IN (?) ", $cat_ids);
	            }
	            elseif(in_array($params['is_cat_id_v'], array(1)) )
	            {
	                $condition .= $this->quoteInto("AND (`cat_id` IN (?) OR FIND_IN_SET({$cat_id}, `cat_id_v`) )", $cat_ids);
	            }
	        }
	    }
	    elseif(!empty($params['cat_ids']))
	    {
	        $cat_ids = Cms_Func::strToArr($params['cat_ids']);
	        if(!empty($cat_ids))
	        {
	            $condition .= $this->quoteInto(" AND `cat_id` IN (?) ", $cat_ids);
	        }
	    }
	    if(isset($params['tag_id']))
	    {
	        $tag_id = intval($params['tag_id']);
	        $tag_id > 0 && $condition .= " AND FIND_IN_SET({$tag_id}, `tag_ids`) ";
	    }
	    if(isset($params['author_id']))
	    {
	    	$condition .= " AND `author_id` =  '{$params['author_id']}'";
	    }
	    if(isset($params['is_recom']) && in_array($params['is_recom'], array('0', '1')))

	    {    
	        $condition .= " AND `is_recom` =  '{$params['is_recom']}'";
	    }
	    if(isset($params['is_hot']) && in_array($params['is_hot'], array('0', '1')))
	    {
	        $condition .= " AND `is_hot` =  '{$params['is_hot']}'";
	    }
        
	    $limit = 0;
	    $where_limit = '';
	    if(isset($params['num']))
	    {
	        $limit = intval($params['num']);
	        $limit > 0 && $where_limit = " LIMIT {$limit}";
	    }
	    
	    if(isset($params['is_rand']) && in_array($params['is_rand'], array('1')))
	    {
	        $sql = "SELECT `art_id` FROM `articles` 
	                WHERE `site_id`={$params['site_id']} {$condition} AND {$this->_end_condition}";
	        $rows = $this->fetchAll($sql);
	        $tem_array = array();
	        foreach($rows AS $value)
	        {
	            $tem_array[$value['art_id']] = $value['art_id'];
	        }
	        unset($rows);
	        
            $limit > count($tem_array) && $limit = count($tem_array);
	        if($limit > 0)
	        {
	            $tem_array =  $limit == 1 ? $tem_array :  array_rand($tem_array, $limit);
	            
	            $params['art_ids'] = implode(',', $tem_array);
	        }
	        
	    }
	    
        if(empty($params['sort']))
        {
            $params['sort'] = 'art_id';
        }
        if(empty($params['order']) OR strtolower($params['order']) != 'asc')
        {
            $params['order'] = 'DESC';
        }
	    
        
	    if(!empty($params['art_ids']))
	    {
	        $art_ids = Cms_Func::strToArr($params['art_ids']);
	        if(!empty($art_ids))
	        {
	            $condition .= $this->quoteInto(" AND `art_id` IN (?) ", $art_ids);
	        }
	    }
	    
	    $condition = "WHERE `site_id`={$params['site_id']} {$condition} AND {$this->_end_condition}
	                 ORDER BY `{$params['sort']}` {$params['order']} 
	                {$where_limit}";
		$sql = "SELECT `art_id`, `title`, `subtitle`, `summary`, `author_id`, `page_url` AS `url`, `image_url`, `edit_time`
				FROM `articles` 
				{$condition}";
		$data = $this->fetchAll($sql);
		return $data;
	}
	
	
	/**
	 * 获取文章分页列表(二维数组)
	 * 例：
	 * {article::page_list cat_id="12" sort="" order="" page="$page" pagesize="15" is_cat_id_v="1"}
	 *   <li>{=$r['title']}</li>
	 * {/article}
	 *
	 * 页面参数  cat_id           分类ID
	 * 页面参数  is_cat_id_v      是否要副分类
	 * 页面参数  tag_id           标签ID
	 * 页面参数  sort             排序字段
	 * 页面参数  order            排序
	 */
	public function page_list($params)
	{
	    //条件
	    $condition = '';
	  
	    if(!empty($params['cat_id']))
	    {
	        $cat_id = intval($params['cat_id']);
	        if($cat_id > 0)
	        {
        	    $cat_ids = $this->_getChildAndSelfCatIds($params['site_id'], $cat_id);
        
        	    if(empty($params['is_cat_id_v']))
        	    {
        	        $condition .= $this->quoteInto(" AND `cat_id` IN (?) ", $cat_ids);
        	    }
        	    elseif(in_array($params['is_cat_id_v'], array(1)) )
        	    {   
        	       $condition .= $this->quoteInto("AND (`cat_id` IN (?) OR FIND_IN_SET({$cat_id}, `cat_id_v`) )", $cat_ids);
        	    }
	        }
	    }
	    
	    if(!empty($params['tag_id']))
	    {
	        $tag_id = intval($params['tag_id']);
	        $tag_id > 0 && $condition .= " AND FIND_IN_SET({$tag_id}, `tag_ids`) ";
	    }
	    
	    if(isset($params['is_hot']) && in_array($params['is_hot'], array('0', '1')))
	    {
	        $condition .= " AND `is_hot` = '1' ";
	    }
	        
	    if(empty($params['sort']))
	    {
	        $params['sort'] = 'art_id';
	    }
	    if(empty($params['order']) OR strtolower($params['order']) != 'asc')
	    {
	        $params['order'] = 'DESC';
	    }
	     
	    $condition = "WHERE `site_id`={$params['site_id']} {$condition} AND {$this->_end_condition}
	                ORDER BY `{$params['sort']}` {$params['order']}";
		$params['sql'] = "SELECT `art_id`, `title`, `subtitle`, `summary`, `page_url` AS `url`
                         FROM `articles` {$condition}";
        return $this->pageQuery($params);
	}
	
	/**
	 * 获取一个分类的子分类和自身
	 */
	private function _getChildAndSelfCatIds($site_id, $cat_id)
	{
		$cat_model = new Article_Models_Category();
		$cat_ids = $cat_model->getChildrenId($site_id, $cat_id);
		$cat_ids[] = $cat_id;
		
		return $cat_ids;
	}
}