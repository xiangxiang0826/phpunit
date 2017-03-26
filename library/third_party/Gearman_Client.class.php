<?php
/** 
*Gearman 客户端
*  $gearman = new Gearman_Client();
*  $gearman->call('sendmsg','send');
* @author liu.jinde<liujd@wondershare.cn>
* @version 1.0  create_time:2014-06-03 17:46:19 
* @copyright  2014 @Wondershare.com
*/ 
interface start_job {
	
	public function call($service,$func, $params = array(),$async = true);
	
}

class Gearman_Client implements start_job {
    
    protected $host;
    protected $port;
    protected $timeout = 0;
    public $gearman = NULL;
    
    public function __construct($host=null,$port=null) {
    	$arr_api_cfg = Common_Func::getConfig('application', 'gearman_server');
		$gearman_server_cfg = $arr_api_cfg->gearman_server->params->toArray();
		$server_host = $gearman_server_cfg['gearman_host'];
		$server_port = $gearman_server_cfg['gearman_port'];
		$this->host = !empty($host) ? $host : $server_host;
        $this->port = !empty($port) ? $port : $server_port;
        if($this->gearman) {
        	return $this->gearman;
        }
        $this->gearman = new GearmanClient();
        $this->gearman->addServer($this->host, $this->port);
    }
    
    
    
    
    /**
    *发送请求到Gearman服务器
    *
    *@param string $service 请求服务名
    *@param string $func 请求的方法名
    *@param array  $params 请求的参数
    *@param boolean $async true为异步、false为同步
    *@return boolean 成功返回true，失败返回false
    */
    public function call($service,$func, $params = array(),$async = true){
        if(empty($service) || empty($func)) return false;
        $request = array('service'=>$service,'func'=>$func,'params'=>$params);
        $request = json_encode($request);
        if($async) return $this->gearman->doBackground('run', $request);  //异步 doBackground
        return $this->gearman->doNormal('run', $request);  //同步doNormal
    }
}


?>