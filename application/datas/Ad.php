<?php
/**
 * 广告 模块的数据提取类
 * @author tjx
 * @time 2013-07-27
 */
class Datas_Ad extends Cms_Data
{
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * 获取广告信息(一维数组)
	 * {ad::info ad_pos_name="art_side" foreign_id="254" /}
	 * 页面参数   ad_pos_name     广告位名称
	 * 页面参数   foreign_id      外部ID
	 * @param array $params
	 */
	public function info($params)
	{
	    extract($params);
	    
	    $row = array();
	    if(!empty($ad_id))
	    {
	        $ad_id = intval($ad_id);
    	    $sql = "SELECT * FROM `ad`
            	    WHERE  `ad_id` = {$ad_id}
            	    LIMIT 1";
    	    $row = $this->fetchRow($sql);
    	    return $row;
	    }
	    elseif(!empty($name))
	    {
	       $name = trim($name); 
	       $sql = "SELECT * FROM `ad`
        	       WHERE  `name` = '{$name}' AND `site_id` = {$site_id} AND `state` = 'able' 
        	       LIMIT 1";
	       $row = $this->fetchRow($sql);
	       return $row;
	    }
	    elseif(!empty($ad_pos_name))
	    {
    	    $foreign_id =  isset($foreign_id) ? intval($foreign_id) : 0;
    	    $ad_pos_name = trim($ad_pos_name);
    	    
    	    $sql = "SELECT `ad_pos_id`, `ad_pos_type`, `width`, `high`
    	            FROM `ad_position`   
    	            WHERE `name` = '{$ad_pos_name}' AND `site_id` = {$site_id} AND `state` = 'able' 
    	            LIMIT  1";
    	    $row = $this->fetchRow($sql);
    	    if(empty($row))
    	    {
    	        return array();
    	    }
    	    
    	    extract($row);
    	    
    	    $sql = "SELECT `ad_id` FROM `ad_ad_position`
    	            WHERE `ad_pos_id` = '{$ad_pos_id}' AND `site_id` = {$site_id} AND `foreign_id` = {$foreign_id} AND `ad_pos_type` = '{$ad_pos_type}'
    	            LIMIT 1";
    	    $row = $this->fetchRow($sql);
    	    
    	    if(empty($row))
    	    {
        	    return array();
    	    }
    	    $sql = "SELECT * FROM `ad` WHERE `ad_id` = {$row['ad_id']}";
    	    $row = $this->fetchRow($sql);
    	    $row['width'] = $width;
    	    $row['high'] = $high;
	    }
	    return $row;
	}
    
	/**
	 * 获取文字广告信息(一维数组)
	 * 		{ad::text pro_id="112" /}
	 * 		{=$r['content']}
	 * 
	 * 页面参数   pro_id     产品ID
	 * @param array $params
	 */
	public function text($params)
	{
	    extract($params);
	    if(empty($pro_id))
	    {
	        echo '没有发现 产品ID';
	        return array();
	    }
	     
	    $pro_id = intval($pro_id);
	     
	    $sql = "SELECT `ad_text_id` FROM `ad_text_product`
        	    WHERE `site_id` = {$site_id} AND `pro_id` = {$pro_id}
        	    LIMIT 1";
	    $row = $this->fetchRow($sql);
	     
	    if(empty($row))
	    {
	        return array();
    	}
    	$sql = "SELECT * FROM `ad_text` WHERE `ad_text_id` = {$row['ad_text_id']}";
    	$row = $this->fetchRow($sql);
    	return $row;
	}
    
	/**
	 * 获取弹层广告信息(一维数组)
	 * 		{ad::layer art_id="112" /}
	 * 		{=$r['content']}
	 *
	 * 页面参数   art_id     文章ID
	 * @param array $params
	 */
	public function layer($params)
	{
	    extract($params);
	    if(empty($art_id))
	    {
	        echo '没有发现 文章ID';
	        return array();
	    }
	
	    $art_id = intval($art_id);
	
	    $sql = "SELECT `ad_layer_id` FROM `ad_layer_article`
        	    WHERE `site_id` = {$site_id} AND `art_id` = {$art_id}
        	    LIMIT 1";
	    $row = $this->fetchRow($sql);
	
	    if(empty($row))
	    {
	        return array();
	    }
    	$sql = "SELECT * FROM `ad_layer` WHERE `ad_layer_id` = {$row['ad_layer_id']}";
    	$row = $this->fetchRow($sql);
        return $row;
	}
}