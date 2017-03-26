<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 代替函数的全局类
 *
 * @author liujd@wondershare.cn
 */
class Common_Func
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
		if(APPLICATION_ENV != 'testing') {
			exit();
		}
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
	
	/* 
	 *  根据以维数组生成树形结构的数组
	 *  */
	public static function arrayToTree($nodes) {
		foreach($nodes as &$node) {
			foreach($node as &$n)
			{
				if(isset($nodes[$n['id']]))
				{
					$n['children'] = &$nodes[$n['id']];
				}
			}
		}
		return isset($nodes[0]) ? $nodes[0] : array();
	}

	/*  生成厂家app标记
	 * @params $label string 企业标记
	 * */
	public static function CreateAppLabel($label) {
		return $label ? "{$label}_mobile_app" : false;
	}
	
	/*  生成固件升级标识
	 * @params $label string 通讯固件标识
	* */
	public static function CreateFirmwareLabel($label) {
		return $label ? "{$label}_firmware_comm" : false;
	}
	
	/* 计算时间差 */
	public static function timediff($begin_time,$end_time) {
		$begin_time = strtotime($begin_time);
		$end_time = strtotime($end_time);
		if($begin_time < $end_time){
			$starttime = $begin_time;
			$endtime = $end_time;
		} else {
			$starttime = $end_time;
			$endtime = $begin_time;
		}
		$timediff = $endtime-$starttime;
		$days = intval($timediff/86400);
		$remain = $timediff%86400;
		$hours = intval($remain/3600);
		$remain = $remain%3600;
		$mins = intval($remain/60);
		$secs = $remain%60;
		$res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
		return $res;
	}
	
	/* @param $label 企业标识
	 * @param $version 最新版本 
	 * 此函数专门针对ios上传版本后的更新
	 * */
	public static function updateIosPage($label, $version = '0.0.0.1') {  // 更新ios下载页面,因为在提交、更新ezapp的时候，都需要更新下载页面，这里提取出公共方法
		$model_upgrade = new Model_Upgrade();
		$upgrade_label = "{$label}_app_ios";
		$version_info = $model_upgrade->GetLatestByLabel($upgrade_label);
		$version = $version_info ? $version_info['version'] : $version;
		$settings = ZendX_Config::get('application', 'settings');
		$download_url = $version_info ? "https://{$settings['app_download_domain']}{$version_info['file_path']}" : '';
		//ios的下载和plist文件
		$plist_send_url = array();
		$download_page = file_get_contents(APPLICATION_PATH . DS . 'configs' . DS . 'template.download');
		$plist_page = file_get_contents(APPLICATION_PATH . DS . 'configs' . DS . 'template.plist');
		$download_page = str_replace(array('{ENTERPRISE_LABEL}'), array($label), $download_page);
		$plist_page = str_replace(array('{DOWNLOAD_URL}','{ENTERPRISE_LABEL}','{VERSION}'), array($download_url,$label,$version), $plist_page);
		$plist_send_url[] = $plist_save_path = "{$settings['plist_save_path']}{$label}.plist";
		$plist_send_url[] = $download_save_path = "{$settings['plist_save_path']}{$label}.html";
		file_put_contents($download_save_path, $download_page);
		file_put_contents($plist_save_path, $plist_page);
		Cms_Task::getInstance()->send($plist_send_url, 'edit', $settings['root_domain'], true); // 发布ios配置的静态文件
		return true;
	}
	
}