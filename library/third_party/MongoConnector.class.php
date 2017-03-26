<?php
/* MongoDB连接类
 * 主要针对MongoClient使用长连接后，由于服务端主动关闭连接，导致连接失败的问题增加重试机制。来自Mongodb的官网建议 
 * created by liujd@wondershare.cn
 * 2015-01-06
 * 
 * eg:
 * $connections = 'mongodb://'.$host.':'.$port;
 * $conn = MongoConnector::get_mongo_client($connections);
 * $conn->ez_statistics_459->device_info->find();
 * */
class MongoConnector {
	
	public static $_mongo = NULL;
	public static $_instance = NULL;
	public function __construct() {
		
	}
	
	/* 单例返回mongo client对象 */
	public static function get_mongo_client($connections = "", $options = array(), $retry = 3) {
		if(!self::$_instance) { //单例
			self::$_instance = new MongoConnector();
		}
		if(!self::$_mongo) { //只保留一个mongo client句柄
			self::$_mongo = self::$_instance->__get_mongo_client($connections, $options, $retry);
		}
		return self::$_mongo;
	}
	
	/* 连接mongo,如果失败，则重试
	 * $connections  连接字符串   mongodb://{$host}:{$port}
	 * $options  array('persist'=>false)
	 * $retry  重试次数
	 *  */
	private function __get_mongo_client($connections = "", $options = array(), $retry = 3) {
	    try {
	    	return new MongoClient($connections, $options);
	    } catch(Exception $e) {
	        /* Log the exception so we can look into why mongod failed later */
	        ZendX_Tool::log('error', "连接mongodb失败：" . $e->getMessage());
	    }
	    if ($retry > 0) {
	        return $this->__get_mongo_client($connections, $options, --$retry);
	    }
	    throw new Exception("重连mongodb {$retry} 次 后仍然连不上，请检查mongodb server是否在线.");
	}
	
	public function __destruct() { //析构函数，每次用完，关闭连接以保证服务端不至于维护较多的连接
		self::$_mongo->close();
		self::$_mongo = NULL;
	}
}