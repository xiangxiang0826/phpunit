<?php
/**
 * Copyscape类
 * Copyscape是一款专用搜寻引擎，你只要输入文章网址，
 * Copyscape会试图找出与该文章最接近的网站，若真有垃圾网页抄袭，Copyscape将会把他们一一揪出 
 * @author: tjx
 */
class Cms_Copyscape
{
	const  COPYSCAPE_USERNAME = 'lily';
	const  COPYSCAPE_API_KEY = '1u9p3094xa3il67y';
	const  COPYSCAPE_API_URL = 'http://www.copyscape.com/api/';
	
	/**
	 * 初始化
	 */
	public function __construct()
	{
	}
	
	/**
	 * 调用api主函数
	 * @param string    $url  请求的 url
	 * @param string    $res_format  响应格式
	 *
	 */
	static public function apiCall($url, $response_format = 'array')
	{
	    $response = false;
	    $url = self::COPYSCAPE_API_URL.'?u='.urlencode(self::COPYSCAPE_USERNAME).
	                                    '&k='.urlencode(self::COPYSCAPE_API_KEY).'&o=csearch&q='.urlencode($url).'&f=';
	    if($response_format == 'array')
	    {
	        $url .= 'xml';
	        $copyscape_xml =  Cms_Func::httpRequest($url);
	        if($copyscape_xml)
	        {
	            $response = self::xmlToArray($copyscape_xml);
	        }
	    }
	    elseif($response_format == 'string')
	    {
	        $url .= 'html';
	        $response = Cms_Func::httpRequest($url);
	    }
	   
	    return $response;
	}
	
	/**
	 * 把Copyscape返回的xml转化为数组
	 * @param string    $copyscape_xml  xml
	 *
	 */
	static public function xmlToArray($copyscape_xml)
	{
	    $copyscape_array = array();
	    if(strstr($copyscape_xml, '<?xml')) 
	    {
    	    //转化为一个数组
    	    $tem_array = (array) simplexml_load_string($copyscape_xml);
    	    if(!empty($tem_array['result']))
    	    {
        	    $copyscape_array = (array) $tem_array['result'];
        	    if(!empty($copyscape_array))
        	    {
        	        if(isset($copyscape_array['index']))//表示为一维数组
        	        {
        	            $copyscape_array = array(0 => $copyscape_array);//转化为二维数组
        	        }
        	        else //表示为二维数组
        	        {
        	            foreach($copyscape_array AS &$value)
        	            {
        	                $value = (array) $value;
        	            } 
        	        }
        	    }
    	    }
    	    unset($tem_array);
	    }
	    return $copyscape_array;
	}
}