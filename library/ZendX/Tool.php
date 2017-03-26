<?php


class ZendX_Tool
{
	public static function get_client_ip()
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
	 * 发送邮箱
	 * @param string $body
	 * @param array $address
	 * @param string $subject
	 * @param bolean $isHtml
	 * @return Ambigous <boolean, mixed>
	 */
	public static function sendEmail($body, $address, $subject, $isHtml = true) {
		$arr_api_cfg = ZendX_Config::get('intra', 'intra_api');
		$intra_api_cfg = $arr_api_cfg['params'];
		$obj = self::_getSendObj();
		$data = array('subject' =>$subject, 'body' => $body, 'sender' => $intra_api_cfg['sender'], 'addresses' => $address,'is_html' => $isHtml);
		$result = $obj->send('/intranet/send_email/',$data);
		ZendX_Tool::log('error',json_encode($result));
		ZendX_Tool::log('debug',json_encode($data));
		return $result;
	}
	
	protected static function _getSendObj() {
		require_once "third_party".DS."Http_Client.class.php";
		require_once "third_party".DS."Intranet_Base.class.php";
		require_once "third_party".DS."Snoopy.class.php";
		require_once "third_party".DS."APIAuthManager.class.php";
		return new Intranet_Base();
	}
	/**
	 *  发送短信接口
	 * @param int $mobil
	 * @param string $message
	 * @return unknown
	 */
	public static function sendSms($mobil, $message){
		$obj = self::_getSendObj();
		$data = array('phone' => $mobil, 'message' => $message);
		$result = $obj->send('/intranet/send_sms/',$data);
		ZendX_Tool::log('error',json_encode($result));
		ZendX_Tool::log('debug',json_encode($data));
		return $result;
	}
	
	/* 记录文本日志 
	 * @level string 日志级别  debug、info、error,
	 * @msg   string   日志内容 
	 * 例子：ZendX_Tool::Log('debug','hello world!!!');
	 * */
	public static function Log ($level,$msg,$name = 'root') {
		$log_config = ZendX_Config::get('application','log');
		require_once "third_party".DS."Logger.class.php";
		$level_map = array('debug'=>'debug','error'=>'error','warn'=>'warn','info'=>'info','fatal'=>'fatal');// 映射到具体的内部函数名
		$call_func = isset($level_map[$level]) ? $level_map[$level] : 'info';
		$log_instance = Logger::getInstance();
		$log_instance->setLogPath($log_config['log_path']);
		$log_instance->setLogLevel($log_config['level']);
		return $log_instance->$call_func($msg); // logger内部维护了单例，此处直接放心使用
	}
	/**
	 * 文件是否存在
	 * @param string $url
	 * @return boolean
	 */
	public static function url_exists($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($http_code == 200) {
        	return true;
        }
        return false;
	}
	
	/**
	 * 对接任务调度系统
	 * @param string $service
	 * @param string $func
	 * @param array $params
	 * @param boolen $async
	 */
	public static function SendSchedulerTask($service, $func, $params = array(), $async = true){
		require_once "third_party".DS."Http_Client.class.php";
		require_once "third_party".DS."Intranet_Base.class.php";
		require_once "third_party".DS."Snoopy.class.php";
		require_once "third_party".DS."APIAuthManager.class.php";
		$data['service'] = $service;
		$data['func'] = $func;
		$data['params'] = $params;
		$data['async'] = $async;
		$intranet_base = new Intranet_Base();
		$result = $intranet_base->send('/intranet/send_scheduler_task/',$data);
		return $result;
	}

    /**
     * 返回随机字符数字组合
     * @param int $bit
     * @return string
     */
    public static function getRandom($bit = 6){
        $bit = (int)$bit;
        $randStr = str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
        return substr($randStr, 0, $bit);
    }
    
    /* post上传文件到静态资源服务器
     * $local_file  本地文件  请填本地服务器上传成功后的文件全路径
    * $mixed_path  需要保存到静态资源服务器的目录前缀或者全路径，如果执行全路径，则服务器将按照指定的文件名保存，否则只保存在以mixed_path
    * 为前缀的目录下。
    * eg: $mixed_path = test/firemware/2014/a.jpg  服务器将返回  test/firemware/2014/a.jpg
    * $mixed_path = test     服务器将返回  test/2014/12/26/a1224da214x.jpg
    * $delete  是否删除本地文件
    * */
    public static function upload($local_file, $mixed_path, $delete = true) {
    	require_once "third_party".DS."PostUpload.class.php";
    	$post_upload = new PostUpload();
    	return $post_upload->upload($local_file, $mixed_path, $delete);
    }
    
    /* 带重试机制的mongodb连接 */
    
    public static function getMongoClient($connections = '', $options = array(), $retry = 3) {
    	require_once "third_party".DS."MongoConnector.class.php";
    	return MongoConnector::get_mongo_client($connections,$options, $retry);
    }
    
    /**
     * 创建类实例
     * 如果该实例存在不会在创建
     * @param string $className 类名
     * @return mixed
     */
    public static function getInstance($className){
    	$registry = Zend_Registry::getInstance();
    	if(!isset($registry[$className])) {
    		$registry->set($className, new $className);
    	}
    	return $registry->get($className);
    }
    
    
}