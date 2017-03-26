<?php
/**
 * 打包客户端
 * @author zhouxh
 *
 */
class ZendX_GearmanClient {
	protected $host;
	protected $port;
	protected $timeout = 0;
	protected $gearman = NULL;
	
	public function __construct($servers=null) {
		if(empty($servers)){
			$servers =ZendX_Config::get('intra', 'gearman_server');
		}
		if(!class_exists('GearmanClient')) {
			throw new Zend_Exception('class GearmanClient Not ');
		}
		$this->gearman = new GearmanClient();
		foreach ($servers as $server){
			$this->gearman->addServer($server['host'], $server['port']);
		}
	}
	
	/**
	 *发送请求到Gearman服务器
	 *
	 *@param string $service 请求服务名
	 *@param string $func 请求的方法名
	 *@param array  $params 请求的参数
	 *@param boolean $async true为异步、false为同步
	 *@return array 响应数据包
	 */
	public function call($service, $func, $params=array(), $async=true){
		$response = array('result' => null);
		if(empty($service) || empty($func)){
			$response['status'] = Common_Protocols::VALIDATE_FAILED;
			$response['msg'] = '500_error';
			return $response;
		}
		try{
			$request = array('service' => $service,'func'=>$func, 'params' => $params);
			$request = json_encode($request);
			if($async){
				$this->gearman->doBackground('back_run', $request);  //异步 doBackground
				$response['status'] = Common_Protocols::SUCCESS;
				$response['msg'] = '';
				return $response;
			}else{
				$result = $this->gearman->doNormal('back_run', $request);  //同步doNormal
				return json_decode($result, true);
			}
		}catch(Exception $e){
			ZendX_Tool::log('info',"调用gearman服务器错误:" . $e->getMessage());
			$response['status'] = Common_Protocols::ERROR;
			$response['msg'] = '500_error';
			return $response;
		}
	}
}