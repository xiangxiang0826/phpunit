<?php
/** 
*内部系统调用API基类
*
* @author lin.qianhui<linqh@wondershare.cn>
* @version 1.0  create_time:2013-11-19 20:15:19 
* @copyright  2013 @Wondershare.com
*/ 

require_once 'Snoopy.class.php';

class Intranet_Base{
    
    protected $host;
    protected $key;
    protected $salt;
    protected $version;
    protected $timeout=0;
    
    public function __construct($host=null){
    	$arr_api_cfg = ZendX_Config::get('intra', 'intra_api');
		$intra_api_cfg = $arr_api_cfg['params'];
		$intra_api_host = $intra_api_cfg['intra_api_host'];
		$this->host = !empty($host) ? $host : $intra_api_host;
        $this->key = $intra_api_cfg['intra_api_app_key'];
        $this->salt = $intra_api_cfg['intra_api_salt'];
        $this->version = '1.0.0.0';
    }
    
    
    /**
    *设置请求超时时间，用于快速请求断开连接
    *例如发送邮件服务，模拟多线程处理
    *
    *@param int $timeout 超时时间,以秒为单位
    *@return return_type return_desc
    */
    protected function set_timeout($timeout){
        $this->timeout = $timeout;
    }
    
    /**
    *发送请求到内部API服务器
    *
    *@param string $uri 请求服务前缀
    *@param array 请求数据
    *@return boolean 成功返回true，失败返回false
    */
    public function send($uri, $data=array()){
        $post = json_encode($data);
        $api_auth_manager = APIAuthManager::get_instance();
        $vc = $api_auth_manager->generate_vc($this->key, $this->version, $this->salt, $post);
        $url = Http_Client::add_paras($this->host . $uri, array('app_id' => $this->key, 'ver' => $this->version, 'vc' => $vc));
        $snoopy = new Snoopy();
        if(!empty($this->timeout)){
            $snoopy->read_timeout = $this->timeout;
        }
        if($snoopy->submit_data($url, $post)){
            $result_json = json_decode($snoopy->results, true);
    		if(!is_array($result_json) && $result_json == null){
    			Logger::error("内部API系统返回数据转换JSON对象失败！数据：" . $snoopy->results);
    			throw new API_Exception("内部API系统返回数据转换JSON对象失败！");
    		}
    		return $result_json;
        }else{
            Logger::error("内部API系统访问错误，url:" . $url . " postdata:" . $post);
            throw new API_Exception($snoopy->error);
        }
    }    
    
    
}

/**
 * 内部API系统异常类
 * @author lin.qianhui<linqh@wondershare.cn>
 *  */
class API_Exception extends Exception{

}
