<?php
/** 
*API系统鉴权管理类
*
* @author lin.qianhui<linqh@wondershare.cn>
* @version 1.0  create_time:2013-11-19 18:41:51 
* @copyright  2013 @Wondershare.com
*/ 

class APIAuthManager{

    private static $instance;
    
    private function __construct(){
    	
    }
    
    /**
    *获取授权管理类对象
    *
    *@return 授权管理类对象
    */
    public static function get_instance(){
        if(!isset(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    
    /**
     *生成内部系统请求验证串
     *
     *@param string $data 请求数据字符串
     *@return string 验证串
     */
    public function generate_intra_vc($data){
    	$arr_api_cfg = ZendX_Config::get('intra', 'intra_api');
		$intra_api_cfg = $arr_api_cfg['params'];
		
		$intra_api_app_key = $intra_api_cfg['intra_api_app_key'];
		$intra_api_salt = $intra_api_cfg['intra_api_salt'];
		
        return md5($intra_api_app_key . $data . $intra_api_salt);
    }
    
    /**
     *生成设备接入验证串
     *
     *@param string $id 设备ID或者应用ID
     *@param string $version 接口请求版本号
     *@param string $encry_factor 设备加密因子
     *@param string $json_data 请求中的json业务数据字符串
     *@return string 验证串
     */
    public function generate_vc($id, $version, $encry_factor, $json_data){
    	return md5($id . $encry_factor . $version . $json_data);
    }
    
    
}



?>