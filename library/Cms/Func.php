<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 代替函数的全局类
 *
 * @author 刘通
 */
class Cms_Func
{
	/**
	 * 获取配置信息
	 * 
	 * @param string $config	配置文件名
	 * @param string $section	配置文件中的节名
	 * @author 刘通
	 */
	static function getConfig($config, $section)
	{
		static $cfg = null;
		if(empty($cfg[$config][$section]))
		{
			$config_file = APPLICATION_PATH . DS . 'configs' . DS . $config . '.ini';
			$cfg[$config][$section] = new Zend_Config_Ini($config_file, $section);
		}
		
		return $cfg[$config][$section];
	}
	
	/**
	 * 写文件
	 * 
	 * @param string $file		文件名
	 * @param string $content	文件内容
	 * @author 刘通
	 */
	static function writeFile($file, $content)
	{
		$dir = dirname($file);
		if(!is_dir($dir))
		{
			mkdir($dir, 0777, true);
			@chmod($dir, 0777);
		}
		
		file_put_contents($file, $content);
	}
	
	/**
	 * 获取Ip
	 * 
	 * @return string
	 * @author 刘通
	 */
	static function getIp()
	{
		$ip = '';
		if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown'))
		{
			$ip = getenv('HTTP_CLIENT_IP');
		}
		elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown'))
		{
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		}
		elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown'))
		{
			$ip = getenv('REMOTE_ADDR');
		}
		elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown'))
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
	
	/**
	 * 根据请求类型，返回不同的数据格式
	 * 
	 * @param string $msg
	 * @author 刘通
	 */
	static function response($msg)
	{
		$request = Zend_Controller_Front::getInstance()->getRequest();
		if($request->isXmlHttpRequest())
		{
			self::jsonResponse($msg);
		}
		else 
		{
			header('content-type:text/html;charset=utf-8');
			exit($msg);
		}
	}
	
	/**
	 * json格式响应
	 * 
	 * @param string $msg
	 * @param string $state
	 * @param array $append_arr
	 * @author 刘通
	 */
	static function jsonResponse($msg, $state='error', $append_arr=null)
	{
		$arr = array('state'=>$state, 'msg'=>$msg);
		if(is_array($append_arr))
		{
			$arr = array_merge($arr, $append_arr);
		}
		echo json_encode($arr);
		exit();
	}
	
	/**
	 * 转义
	 * 
	 * @param mixed $data
	 * @author 刘通
	 */
	static function stripslashes($data)
	{
		if(is_array($data))
		{
			foreach($data as $key => $val)
			{
				$data[$key] = self::stripslashes($val);
			}
		}
		else 
		{
			$data = stripslashes($data);
		}
		
		return $data;
	}
	
	/**
	 * 转换以逗号分隔的数字为数组
	 * 
	 * @param string	$str
	 * @param string	$sep
	 * @return array
	 * @author 刘通
	 */
	static function strToArr($str, $sep=',')
	{
		$arr = explode($sep, $str);
		$arr = array_map('intval', $arr);
		$arr = array_filter($arr);
		$arr = array_unique($arr);
		
		return $arr;
	}
	
	/**
	 * http请求
	 * @param string    $url  请求的url
	 * @author tjx
	 */
	static function httpRequest($url)
	{
	    $response_data = '';
	    try
	    {
	        $http = new Zend_Http_Client($url);
	        $http->setConfig(array('timeout' => 1000));
	        $response = $http->request(Zend_Http_Client::GET);
	         
	        if($response->isSuccessful())
	        {
	            $response_data = $response->getBody();
	        }
	        else
	        {
	            return false;
	        }
	    }
	    catch(Zend_Http_Client_Exception $e)
	    {
	        return false;
	    }
	    return $response_data;
	}
	
	/**
	 * 字符串过滤非法字符
	 * @param string    $str 
	 * @param string    $separator  分割符
	 * @author tjx
	 */
	static function strFilterIllegal($str, $separator = '-')
	{
	    $str = preg_replace('/-+/', $separator, preg_replace('/\W/is', $separator, $str));
	    if(substr($str, 0, 1) == $separator)
	    {
	        $str = substr($str, 1, strlen($str) - 1);
	    }
	    
	    if(substr($str, -1, 1) == $separator)
	    {
	        $str = substr($str, 0, strlen($str) - 1);
	    }
	    return $str;
	}
	
	
	/**
	 * CURL请求
	 * @param string $url 请求地址
	 * @param string 请求形式(get或post)
	 * @param array 请求参数
	 * @author hcb
	 * */
	static function curl($url, $method='get', $param=array())
	{
	    $url = $url . ($method == 'get' ? '&'.http_build_query($param) : '');
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    if($method == 'post')
	    {
	        curl_setopt($ch, CURLOPT_POST, true);
	        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
	    }
	
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    $exec = curl_exec( $ch );
	    curl_close( $ch );
	    return $exec;
	}
	
	
	/**
	 * 导出数据到CSV文件
	 * @param	array	$data		数据
	 * @param	array	$title_arr	标题
	 * @param	string	$file_name	CSV文件名
	 */
	static function exportCsv(&$data, $title_arr, $file_name = '') {
	    ini_set('max_execution_time', 3600);
	
	    $csv_data = '';
	
	    /** 标题 */
	    $nums = count($title_arr);
	    for ($i = 0; $i < $nums - 1; ++$i) {
	        $csv_data .= '"' . $title_arr[$i] . '",';
	    }
	
	    if ($nums > 0) {
	        $csv_data .= '"' . $title_arr[$nums - 1] . "\"\r\n";
	    }
	
	    foreach ($data as $k => $row) {
	        for ($i = 0; $i < $nums - 1; ++$i) {
	            $row[$i] = str_replace("\"", "\"\"", $row[$i]);
	            $csv_data .= '"' . $row[$i] . '",';
	        }
	        $csv_data .= '"' . $row[$nums - 1] . "\"\r\n";
	        unset($data[$k]);
	    }
	
	    $csv_data = mb_convert_encoding($csv_data, 'cp936', 'UTF-8');
	
	    $file_name = empty($file_name) ? date('Y-m-d-H-i-s', time()) : $file_name;
	
	    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')) {
	        $file_name = urlencode($file_name);
	        $file_name = str_replace('+', '%20', $file_name);
	    }
	
	    $file_name = $file_name . '.csv';
	
	    header('Content-type:text/csv;');
	    header('Content-Disposition:attachment;filename=' . $file_name);
	    header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
	    header('Expires:0');
	    header('Pragma:public');
	    echo $csv_data;
	    exit;
	}

}