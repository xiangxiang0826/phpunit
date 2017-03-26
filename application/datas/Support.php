<?php
/**
 * Support模块的数据提取类
 * @author tjx
 * @time 2013-07-13
 */
class Datas_Support extends Cms_Data
{
    public $sales_cat_ids = '2,4,7,8,48';
     
    /**
     * 获取首页信息(一维数组)
     * {support::index /}
     * {=$r['rec_s_cat_ids']}
     *
     * @param array $params
     */
    public function index($params)
    {
        $main_site_id = self::_getMainSiteId($params['site_id']);
        $sql = "SELECT * FROM `support_index` WHERE site_id ={$main_site_id} LIMIT 1";
        return $this->fetchRow($sql);
    }
    
    /**
     * 获取文章信息(一维数组)
     * {support::article /}
     * {=$r['s_art_id']}
     *
     * @param array $params
     */
    public function article($params)
    {
        if(empty($params['s_art_id']))
        {
            return array();
        }
        $s_art_id = intval($params['s_art_id']);
        
        $sql = "SELECT * FROM `support_articles` WHERE s_art_id ={$s_art_id} LIMIT 1";
        return $this->fetchRow($sql);
    }
    
    /**
     * 获取分类信息(一维数组)
     * {support::category_info s_cat_id="2" /}
     * <li>{=$r['name']}</li>
     * @param array $params
     */
    public function category($params)
    {
        //条件
        $condition = '';
        if(empty($params['s_cat_id']))
        {
            return array();
        }
    
        $s_cat_id = intval($params['s_cat_id']);
        $sql = "SELECT * FROM `support_categorys` WHERE `s_cat_id` = {$s_cat_id} LIMIT 1";
        return $this->fetchRow($sql);
    }
    
    /**
     * 获取分类列表(二维数组)
     * {support::category_list is_sales="1" }
     * <li>{=$r['name']}</li>
     * {/support}
     * @param array $params
     */
    public function category_list($params)
    {
        //条件
        $condition = '';
        if(isset($params['is_sales']) && in_array($params['is_sales'], array(1)))
		{
		    $condition .= "`s_cat_id` IN({$this->sales_cat_ids})";
		}
		else 
		{
		    $condition .= "`s_cat_id` NOT IN({$this->sales_cat_ids})";
	    }
    
        $sql = "SELECT * FROM `support_categorys` WHERE {$condition}";
        return $this->fetchAll($sql);
    }
    
	/**
     * 获取support文章信息列表(二维数组)
     * 例如：
     * {support::article_list pro_id="12" num="5" is_sales="1"}
	 *   <li>{=$r['title']}</li>
	 * {/support}
	 * 页面参数  s_art_ids        文章IDs
	 * 页面参数  s_cat_id         分类ID
	 * 页面参数  pro_id           产品ID(定制表ID)
	 * 页面参数  is_important     是否重要  
	 * 页面参数  is_recom_index   是否推荐到首页 
	 * 页面参数  is_sales         是否搜索营销文章 (文章分营销文章（分类ID:2,4,7,8,48），技术文章（其它分类ID）二种文章)
	 * 页面参数  sort             排序字段
	 * 页面参数  order            排序
	 * 页面参数  num            取的条数   
	 * @param array $params
	 */
	public function article_list($params)
	{
	    $main_site_id = self::_getMainSiteId($params['site_id']);
	    //条件
	    $condition = '';
		if(!empty($params['s_art_ids']))
		{
			$s_art_ids = Cms_Func::strToArr($params['s_art_ids']);
			$condition .= $this->quoteInto(" AND `s_art_id` IN (?) ", $s_art_ids);
		}
		if(!empty($params['s_cat_id']))
		{
		    $s_cat_id = intval($params['s_cat_id']);
		    $s_cat_id > 0 && $condition .= " AND `s_cat_id` =  {$s_cat_id} ";
		}
		if(!empty($params['version_ids']))
		{
		    $version_ids = explode(',', $params['version_ids']);
		    $version_condition = $s = '';
		    foreach ($version_ids AS $version_id)
		    {
		        $version_condition .= $s."FIND_IN_SET({$version_id}, version_id)";
		        $s = ' OR ';
		    }
		    if(empty($version_condition))
		    {
		        return array();
		    }
		     
		    $condition .= " AND ({$version_condition}) ";
		    
		    if(!empty($params['s_art_id']))
		    {
		        $s_art_id = intval($params['s_art_id']);
		        $condition .= "AND `s_art_id` !=  {$s_art_id}";
		    }
		    
		}
		if(!empty($params['pro_id']))
		{
		    $pro_id = intval($params['pro_id']);
	        $pro_versions = self::_getProductVersionsByProId($pro_id);
	        $pro_condition = $s = '';
	        foreach($pro_versions AS $key => $value)
	        {
	            $pro_condition .= $s."FIND_IN_SET({$value['version_id']}, version_id)";
	            $s = ' OR ';
	        }
	        if(empty($pro_condition))
	        {
	            return array();
	        }
	            
		    $condition .= " AND ({$pro_condition}) ";
		}
		if(isset($params['is_important']) && in_array($params['is_important'], array(1)))
		{
		    $condition .= " AND `is_important` =  '{$params['is_important']}'";
		}
		if(isset($params['is_recom_index']) && in_array($params['is_recom_index'], array(1)))
		{
		    $condition .= " AND `is_recom_index` =  '{$params['is_recom_index']}'";
		}
		if(empty($params['s_cat_id']))
		{
    		if(isset($params['is_sales']) && in_array($params['is_sales'], array(1)))
    		{
    		    $condition .= " AND `s_cat_id` IN({$this->sales_cat_ids})";
    		}
    		else 
    		{
    		    $condition .= " AND `s_cat_id` NOT IN({$this->sales_cat_ids})";
		    }
		}
		if(empty($params['sort']))
		{
		    $params['sort'] = 'is_important';
		}
		if(empty($params['order']))
		{
		    $params['order'] = 'DESC';
		}
		
	    $condition = "  WHERE `site_id` = {$main_site_id} {$condition} 
                        ORDER BY `{$params['sort']}` {$params['order']}, `s_art_id` DESC";
		if(isset($params['num']))
		{
		    $num = intval($params['num']);
		    $num > 0 && $condition .= " LIMIT {$num}";
		}
		
		//查询
		$sql = "SELECT *, `page_url` url FROM `support_articles` {$condition}";
		$rows = $this->fetchAll($sql);
		return $rows;
	}
	
	/**
	 * 获取support文章分页列表(二维数组)
	 * 例如：
	 * <!--{support::page_article_list page="$page" return="art_info" }-->
	 *   <li>{=$r['title']}</li>
	 * {/support::article_list}
	 * 页面参数  s_art_ids        文章IDs
	 * 页面参数  s_cat_id         分类ID
	 * 页面参数  pro_id           产品ID(定制表ID)
	 * 页面参数  is_important     是否重要
	 * 页面参数  is_recom_index   是否推荐到首页
	 * 页面参数  is_sales         是否搜索营销文章 (文章分营销文章（分类ID:2,4,7,8），技术文章（其它分类ID）二种文章)
	 * 页面参数  sort             排序字段
	 * 页面参数  order            排序
	 * 页面参数  num            取的条数
	 * @param array $params
	 */
	public function page_article_list($params)
	{
	    $main_site_id = self::_getMainSiteId($params['site_id']);
	    //条件
	    $condition = '';
	    if(!empty($params['s_art_ids']))
	    {
	        $s_art_ids = Cms_Func::strToArr($params['s_art_ids']);
	        $condition .= $this->quoteInto(" AND `s_art_id` IN (?) ", $s_art_ids);
	    }
	    if(isset($params['s_cat_id']))
	    {
	        $s_cat_id = intval($params['s_cat_id']);
	        $s_cat_id > 0 && $condition .= " AND `s_cat_id` =  {$s_cat_id} ";
	    }
	    if(!empty($params['version_ids']))
	    {
	        $version_ids = explode(',', $params['version_ids']);
	        $version_condition = $s = '';
	        foreach ($version_ids AS $version_id)
	        {
	            $version_condition .= $s."FIND_IN_SET({$version_id}, version_id)";
	            $s = ' OR ';
	        }
	        if(empty($version_condition))
	        {
	            return array();
	        }
	         
	        $condition .= " AND ({$version_condition}) ";
	    
	    }
	    if(!empty($params['pro_id']))
	    {
	        $pro_id = intval($params['pro_id']);
	        $pro_versions = self::_getProductVersionsByProId($pro_id);
	        $pro_condition = $s = '';
	        foreach($pro_versions AS $key => $value)
	        {
	            $pro_condition .= $s."FIND_IN_SET({$value['version_id']}, version_id)";
	            $s = ' OR ';
	        }
	        if(empty($pro_condition))
	        {
	            return array();
	        }
	         
	        $condition .= " AND ({$pro_condition}) ";
	    }
	    if(isset($params['is_important']) && in_array($params['is_important'], array(1)))
	    {
	        $condition .= " AND `is_important` =  '{$params['is_important']}'";
	    }
	    if(isset($params['is_recom_index']) && in_array($params['is_recom_index'], array(1)))
	    {
	        $condition .= " AND `is_recom_index` =  '{$params['is_recom_index']}'";
	    }
	    if(isset($params['is_sales']) && in_array($params['is_sales'], array(1)))
	    {
	        $condition .= " AND `s_cat_id` IN({$this->sales_cat_ids})";
	    }
	    else
	    {
	        $condition .= " AND `s_cat_id` NOT IN({$this->sales_cat_ids})";
	    }
	    if(empty($params['sort']))
	    {
	        $params['sort'] = 'is_important';
	    }
	    if(empty($params['order']))
	    {
	        $params['order'] = 'DESC';
	    }
	
	    $condition = "  WHERE `site_id` = {$main_site_id} {$condition}
	    ORDER BY `{$params['sort']}` {$params['order']}, `s_art_id` DESC";
	    if(isset($params['num']))
	    {
    	    $num = intval($params['num']);
    	    $num > 0 && $condition .= " LIMIT {$num}";
	    }
	
	    //查询
	    $params['sql'] = "SELECT *, `page_url` url FROM `support_articles` {$condition}";
	    return $this->pageQuery($params);
	}
	
	/**
	 * 获取在线帮助信息(二维数组)
	 * {support::onhelp_list h_art_ids="2"}
	 *     <li>{=$r['title']}</li>
	 * {/support}
	 * 页面参数  h_art_ids        文章IDs
	 * 页面参数  pro_id           产品ID(定制表ID)
	 * 页面参数  sort             排序字段
	 * 页面参数  order            排序
	 * 页面参数  limit            取的条数   
	 * @param array $params
	 */
	public function onhelp_list($params)
	{
	    $main_site_id = self::_getMainSiteId($params['site_id']);
	    
	    //条件
	    $condition = '';
	    if(!empty($params['h_art_ids']))
	    {
	        $h_art_ids = Cms_Func::strToArr($params['h_art_ids']);
	        $condition .= $this->quoteInto(" AND `h_art_id` IN (?) ", $h_art_ids);
	    }
	    if(!empty($params['pro_id']))
	    {
	        $pro_id = intval($params['pro_id']);
	        $pro_versions = self::_getProductVersionsByProId($pro_id);
	        $pro_condition = $s = '';
	        foreach($pro_versions AS $key => $value)
	        {
	            $pro_condition .= $s."FIND_IN_SET({$value['version_id']}, version_id)";
	            $s = ' OR ';
	        }
	        if(empty($pro_condition))
	        {
	            $pro_condition = '0';
	        }
	        $condition .= " AND ({$pro_condition}) ";
	    }
	    if(empty($params['sort']))
	    {
	        $params['sort'] = 'hits';
	    }
	    if(empty($params['order']))
	    {
	        $params['order'] = 'DESC';
	    }
	    $condition = "  WHERE `site_id` = {$main_site_id} {$condition}
	                    ORDER BY `{$params['sort']}` {$params['order']}, `h_art_id` DESC";
	    if(isset($params['num']))
		{
		    $num = intval($params['num']);
		    $num > 0 && $condition .= " LIMIT {$num}";
		}
		
		//查询
        $sql = "SELECT * FROM `support_onhelps` {$condition}";
	    return $this->fetchAll($sql);
	}
	
	/**
	 * 获取多个页面信息(用于获取主站的页面信息)
	 * <!--{support::main_urls module="product" type="switch" entity_id="$pro_list[$j]['pro_id']" key="switch_name" return="switch_urls"/}--> 
	 * @param array $params
	 */
	public function main_urls($params)
	{
	    //转化为主站点
	    $params['site_id'] = self::_getMainSiteId($params['site_id']);
	    $data_page = new Datas_Page();
	    return $data_page->urls($params);
	}
	
	/**
	 * 获取产品信息列表（调用产品接口的list）
	 * 		{support::product_lists pro_ids="2,4,7" cat_id="3" rank="1" label="Top Seller" product_os="Win,Mac" show_os="Mac" brand="Wondershare"}
	 * 		<li>{=$r['name']}</li>
	 * 		{/support::product_lists}
	 * 
	 * 参数说明：
	 * 		pro_ids		产品ID，用逗号分隔开，如果cat_id不为空，则此值被忽略
	 * 		cat_id		分类ID，获取该分类以及子分类的产品
	 * 		rank		是否重要产品，1为重要
	 * 		label		标签，可选值 New | Free | Upgrade | Top Seller | Top Downloader
	 * 		is_free		是否免费
	 * 		free_or_try	免费或试用的
	 * 		product_os	产品平台，1. 如果为Win,Mac，则显示Win和Mac分开，根据show_os来组织数据 2.如果为空，这两者皆有
	 * 		version		产品版本
	 * 		high_pro_id	对应低版本
	 * 		brand		品牌，如果此值不为空，把产品名去品牌
	 */
	public function product_lists($params)
	{
	    //转化为主站点
	    $params['site_id'] = self::_getMainSiteId($params['site_id']);
	    if(!empty($params['version_ids']))
	    {
	        if(is_string($params['version_ids']))
	        {
	            $params['version_ids'] = Cms_Func::strToArr($params['version_ids']);
	        }

            $sql = $this->quoteInto("SELECT `pro_id` FROM `product_versions` WHERE version_id IN(?) GROUP BY `pro_id`", $params['version_ids']);
            $rows = $this->fetchAll($sql);
            if(empty($rows))
            {
                return array();
            }
            
            $pro_ids = array();
            foreach ($rows AS $row)
            {
                $pro_ids[] = $row['pro_id'];
            }
            $params['pro_ids'] = implode(',', $pro_ids);
            
	    }
	    $data_pro = new Datas_Product();
	    return $data_pro->lists($params);
	}
	
	/**
	 * 获取一级分类和二级分类列表
	 * 		{support::product_category_list layer="1" parent_id="103" no_link="0"}
	 * 		{=$r['name']}
	 * 		{/support::product_category_list}
	 */
	public function product_category_list($params)
	{
	    //转化为主站点
	    $params['site_id'] = self::_getMainSiteId($params['site_id']);
	    $data_pro = new Datas_Product();
	    return $data_pro->category_list($params);
	}
	
	/**
	 * 获取产品信息
	 * 		{support::product_info pro_id="$entity_id" high_pro_id="" brand="Wondershare" /}
	 * 
	 * 
	 * 参数说明：
	 * 		pro_id		产品ID
	 * 		high_pro_id	高板本是该ID的产品
	 * 		brand		如果不为空，则去品牌名
	 */
	public function product_info($params)
	{
	    //转化为主站点
	    $params['site_id'] = self::_getMainSiteId($params['site_id']);
	    $data_pro = new Datas_Product();
	    return $data_pro->info($params);
	}
	
	/**
     * 获取主站点id
     * 
     * @param int $site_id 站点ID
     */
    private  function _getMainSiteId($site_id)
    {
        $site_info = Cms_Site::getInfoById($site_id);
        return $site_info['main_site_id'];
    }
    
    /**
     * 通过定制表产品ID获取产品版本信息
     *
     * @param int $pro_id 产品id(定制表)
     */
    private  function _getProductVersionsByProId($pro_id)
    {
        $pro_id = intval($pro_id);
        $pro_row = self::_getProductCustomInfo($pro_id);//获取公共产品信息
        $pro_versions  = self::_getProductVersions($pro_id);//获取产品版本信息
        if(empty($pro_versions))
        {
            return array();
        }
        return $pro_versions;
    }
    
    /**
     * 获取产品定制信息
     *
     * @param int $pro_id 产品id(定制表)
     */
    private  function _getProductCustomInfo($pro_id)
    {
        $pro_id = intval($pro_id);
        $sql = "SELECT * FROM `product_customs` WHERE `pro_id` = {$pro_id} LIMIT 1";
	    return $this->fetchRow($sql);
    }
    
    /**
     * 获取产品版本信息
     *
     * @param int $pro_id 产品id
     */
    private  function _getProductVersions($pro_id)
    {
        $pro_id = intval($pro_id);
        $sql = "SELECT * FROM `product_versions` WHERE `pro_id` = {$pro_id}";
        $rows = $this->fetchAll($sql);
        return $rows;
    }
    
    
    /**
     * 获取cms123的support信息
     * 		因为suport和cms用的不是同一个库
     * 		{support::faqs cbs_id="50" num="10" /}
     * 
     * @author 刘通
     */
    function get_faqs($params)
    {
    	extract($params);
    	if(empty($cbs_id))
    	{
    		return array();
    	}
    	
    	$cbs_id = (int) $cbs_id;
    	
    	$db = Cms_Db::getConnection('support');
    	$sql = "SELECT `version_id` FROM `version` WHERE `product_cbs_id`={$cbs_id}";
    	$rows = $db->fetchAll($sql);
    	
    	$sql = array();
    	foreach($rows as $row)
    	{
    		$version_id = (int) $row['version_id'];
    		$sql[] = "SELECT `id`, `title`, `sub_title` AS `subtitle` FROM `faq_info_new` WHERE `web_type`={$site_id} AND `type_id` NOT IN(2, 4, 7, 8, 48) AND FIND_IN_SET('{$version_id}', `version_id`)";
    	}
    	
    	if(!$sql)
    	{
    		return array();
    	}
    	
    	$sql = implode(' UNION ', $sql);
    	$sql = "SELECT faq.*, IF(o_types.is_important IS NULL , 0, o_types.is_important) is_important FROM ({$sql}) faq
    	        LEFT JOIN (
				    SELECT is_important, id 
				    FROM faq_operation_types 
				    WHERE web_type = {$site_id}
				)  AS o_types ON o_types.id = faq.id
				ORDER BY is_important DESC ";
    	$rows = $db->fetchAll($sql);
    	$faq_ids = $faq_list = array();
    	foreach ($rows as $row)
    	{
    		$faq_ids[] = $row['id'];
    		$faq_list[$row['id']] = $row;
    	}
    	
    	if(!$faq_ids)
    	{
    		return array();
    	}
    	
    	if(empty($num))
    	{
    		$num = 5;
    	}

    	$table_cfg = array(
    		'1' => array(			//wondershare.com
    			'table' => 'Table_92',
    			'entity_id' => 'Field_20948',
    			'search' => '/support.wondershare.com/httpdocs/',
    			'replace' => 'http://support.wondershare.com/'
    		),
	        '2'	=> array(			//aimersoft.com
	                'table' => 'Table_227',
	                'entity_id' => 'Field_22276',
	                'search' => '/support.aimersoft.com/httpdocs/',
	                'replace' => 'http://support.aimersoft.com/'
	        ),
    		'8'	=> array(			//iskysoft.com
    			'table' => 'Table_159',
    			'entity_id' => 'Field_21782',
    			'search' => '/support.iskysoft.com/httpdocs/',
    			'replace' => 'http://support.iskysoft.com/'
    		),
    	        
    	);
    	
    	if(empty($table_cfg[$site_id]))
    	{
    		return array();
    	}
    	
    	extract($table_cfg[$site_id]);
    	$sql = $db->quoteInto("SELECT `url`, `{$entity_id}` AS `id` FROM `{$table}` WHERE `{$entity_id}` IN (?) LIMIT {$num}", $faq_ids);
    	$rows = $db->fetchAll($sql);
    	
    	$return = array();
    	foreach($rows as $row)
    	{
    		$faq_list[$row['id']]['url'] = str_replace($search, $replace, $row['url']);
    		$return[] = $faq_list[$row['id']];
    	}
    	
    	return $return;
	}
}