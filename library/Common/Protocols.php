<?php
/** 
*通信协议类
*定义与系统通信所使用的消息格式
*
* @author lin.qianhui<linqh@wondershare.cn>
* @version 1.0  create_time:2012-11-16 15:09:59 
* @copyright  2012 @Wondershare.com
*/ 

class Common_Protocols {
	
	const SUCCESS = 200; //成功
	const ERROR = 500;//服务器发生错误
	const NOT_LOGIN = 401; //未登录错误
	const VALIDATE_FAILED = 403; //验证错误
	const DATA_NOT_FOUND = 405; //数据未找到
	const PAGE_NOT_FOUND = 404; //页面或处理器未找到
	const EXISTS_ERROR = 406;   //数据已存在错误
	const NO_ACTION = 201; //无动作状态，例如不需要升级
	const HAS_PROCESSED_ERROR = 501; //已被处理错误，例如已被激活，不允许激活了
	const USER_VERIFY_FAILED = 505; //用户身份验证错误
	
	/**
	*生成json返回内容
	*
	*@param array $result 返回结果数据
	*@param int $status 返回状态码，根据协议类中定义
	*@param string $message 返回消息
	*/
	static function generate_json_response($result=null, $status=self::SUCCESS, $message=''){
		$response = array();
		$response['result'] = $result;
		$response['status'] = $status;
		$response['msg'] = $message;
		$json_str = json_encode($response);
		echo $json_str;
	}
	
	
	/**
	 *生成jsonp返回内容
	 *callback函数的参数名为jsonp_callback
	 *
	 *@param array $result 返回结果数据
	 *@param int $status 返回状态码，根据协议类中定义
	 *@param string $message 返回消息
	 */
	static function generate_jsonp_response($result=null, $status=self::SUCCESS, $message=''){
		$response = array();
		$response['result'] = $result;
		$response['status'] = $status;
		$response['msg'] = $message;
		$json_str = json_encode($response);
		$callback = $_GET['jsonp_callback'];
		echo "$callback($json_str);";
	}
	
	
	/**
	*生成重定向响应
	*用于在静态页面中显示状态信息，静态页面通过js获取这些参数
	*
	*@param string $url 重定向链接地址
	*@param array $paras 链接地址追加参数
	*@param int $status 返回状态码，根据协议类中定义
	*@param string $message 返回消息
	*/
	static function generate_redirect_response($url=null, $paras=array(), $status=self::SUCCESS, $message=''){
		$paras['status'] = $status;
		$paras['msg'] = $message;
		$url = Http_Client::add_paras($url, $paras);
		header('Location: ' . $url);
	}
	
	
	/**
	 *生成设备访问接口的json返回内容
	 *包含数据签名串的header头
	 *
	 *@param array $result 返回结果数据
	 *@param int $status 返回状态码，根据协议类中定义
	 *@param string $message 返回消息
	 */
	static function generate_device_json_response($result=null, $status=self::SUCCESS, $message=''){
	    $response = array();
	    $response['result'] = $result;
	    $response['status'] = $status;
	    $response['msg'] = $message;
	    $json_str = json_encode($response);
	    $request  = $_POST['request_data'];
	    $encry_factor = isset($_SESSION['encry_factor']) ? $_SESSION['encry_factor'] : 
	                    func::get_cfg('factory_activate_salt'); //出厂激活时需要用默认加密因子
	    $api_auth_manager = APIAuthManager::get_instance();
	    $vc = $api_auth_manager->generate_vc($request['device_id'], $request['mac'], $request['version'], $encry_factor , $json_str);
	    header('sm_vc:' . $vc);
	    echo $json_str;
	}
	
	
	
}

?>