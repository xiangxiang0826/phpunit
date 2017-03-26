<?php
/*
----------------------------------------------------------------------------------------------------------------
HTTP Client

Author: Zhang Junlei (zhangjunlei26@gmail.com)
----------------------------------------------------------------------------------------------------------------
Useage:

----------------------------------------------------------------------------------------------------------------
*/
class Http_Client
{
    /**
     * POST数据
     * @param string $url
     * @param array $data
     * @param array $headers
     */
    static function post ($url, $data, $headers = array(), $SSL_VERIFYPEER = FALSE)
    {
        $info = parse_url($url);
        $nums = count($data);
        if (is_array($data))
            $data = http_build_query($data, '_');
        $data = trim($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        if ($headers) {
            $headers = (array) $headers;
            foreach ($headers as $header)
                curl_setopt($ch, CURLOPT_HEADER, $header);
        } else {
            curl_setopt($ch, CURLOPT_HEADER, 0);
        }
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $SSL_VERIFYPEER);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    /**
     * post并跳转到指定地址
     * @param string $url
     * @param array $post
     */
    static function post_to ($url, $post,$target="_self")
    {
        echo <<<HERE
<html><head></head>
<body onLoad="document.getElementById('form1').submit();">
<form id="form1" method="post" action="{$url}" target="{$target}">\n
HERE;
        foreach ($post as $k => $v)
            echo '<input type="hidden" name="', $k, '" value="', addslashes($v), '" />',"\n";
        echo "</form></body></html>";
        exit();
    }
    
    
    /**
     *为url增加参数
     *
     *@param string $url URL字符串
     *@param array $paras 参数关联数组
     *@return string 增加完参数的url
     *
     */
    static public function add_paras($url, $paras){
    	$paras_str = http_build_query($paras);
    	if(strpos($url, '?') == false){
    		return $url . '?' . $paras_str;
    	}else{
    		return $url . '&' . $paras_str;
    	}
    }
    
    
    
    /**
    *解析url地址中的query参数部分，转换成参数数组
    *参数不进行urldecode
    *
    *@param string $url url地址
    *@return array 参数数组
    */
    static public function parse_url_query($url){
        $params = array();
        $query = parse_url($url, PHP_URL_QUERY);
        if(!empty($query)){
            $query_parts = explode('&', $query);
            foreach ($query_parts as $param)
            {
                $item = explode('=', $param);
                $params[$item[0]] = $item[1];
            }
        }
        return $params;
    }
}