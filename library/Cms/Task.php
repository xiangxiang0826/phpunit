<?php

/**
 * 任务发布
 */
class Cms_Task
{
	private  $api_url = '';
	private  $secure_code = '';
	private  $publish = false;
	static $instance = NULL;
	
	static function getInstance(){
        if(self::$instance == NULL){
            self::$instance = new Cms_Task();
        }
       	return self::$instance;
    }
	
    private function __construct() {
    	$task_config = ZendX_Config::get('task','task');
    	$this->api_url = $task_config['api_url'];
    	$this->secure_code = $task_config['secure_code'];
    	$this->publish  = $task_config['publish'];
    	$this->site_id  = $task_config['site_id'];
    	$this->domain  = $task_config['domain'];
    }
    
	
	public function getUrl()
	{
		return $this->api_url;
	}
	
	
	public function getCode()
	{
		return $this->secure_code;
	}
	
	public function getLoginUrl()
	{
		$user_info = Common_Auth::getUserInfo();
		return self::getUrl().'/login?code='.self::getCode().'&user_name='.$user_info['user_name'].'&password='.$user_info['password'];
	}

	/**
	 * 向发布系统添加任务
	 * @param $auto boolean true 自动发布
	 * */
	public function send($url_arr, $type, $domain='', $auto_publish = false) {
		if(!$this->publish) return true;
		$tpl_id = (int) Zend_Controller_Front::getInstance()->getRequest()->getParam('tpl_id');
		$blk_id = (int) Zend_Controller_Front::getInstance()->getRequest()->getParam('blk_id');
		if(!empty($blk_id)){  // 如果有block_id ，则找block对应的域名
			$block_model = new Template_Models_Block();
			$block_info = $block_model->getInfoById($blk_id);
			if($block_info['host']) {
				$tpl_info['host'] = $block_info['host'];
			}
		}

		//验证参数有效性
		if(!$url_arr)
		{
			return false;
		}
		
		$user_info = Common_Auth::getUserInfo();
		
		if( $tpl_id )
		{
			//获取模版信息
			$tpl_model = new Template_Models_Template();
			$tpl_info = $tpl_model->getInfoById($tpl_id);
			$tpl_level = isset($tpl_info['level']) ? $tpl_info['level'] : 'B';
		}
		else
		{
			//来自文件管理的上传
			$level = Zend_Controller_Front::getInstance()->getRequest()->getParam('manage_type');	
			$tpl_level = $level == 'www' ? 'A' : 'B';
		}
		
		$data = array(
			'file_array'	=> json_encode($url_arr),
			'action_type'	=> $type,
			'template_type'	=> $tpl_level,
			'user_name'		=> $user_info['user_name'] ? $user_info['user_name'] : 'admin'
		);
        if(!empty($domain)){
            $data['host'] = $domain;
        } else if(!empty($tpl_info['host'])){
        	// 补http
        	$tpl_info['host'] = strpos($tpl_info['host'],'http://')===false ? "http://{$tpl_info['host']}" : $tpl_info['host'];
        	$url_info = parse_url($tpl_info['host']);
        	$data['host'] = $url_info['host'];
		} else { // 否则直接读配置
			$data['host'] = $this->domain;
		}
		$data['auto'] = $auto_publish;
		$result = Cms_Func::curl( $this->api_url.'/recordurl?code='.$this->secure_code, 'post', $data );
		$arr = json_decode($result, true);
		return $arr['state'] == 'ok' ? true : false;
	}
	
	/**
	 * 取得发布系统消息
	 * @return array
	 * */
	public function getSubmitTask()
	{
		$user_info = Common_Auth::getUserInfo();
		
		$result = Cms_Func::curl( $this->api_url.'/getsubmittask?code='.$this->secure_code.'&user_name='.$user_info['user_name'] );
		return json_decode($result, true);
	}
	
	
	/**
	 * 向发布系统新增用户
	 * @return array
	 * */
	public function addTaskUser( array $arr )
	{
		$data = array(
				'user_array' => serialize(
					array(	'user_name'		=> $arr['user_name'],
							'password'		=> $arr['password'],
							'real_name'		=> $arr['real_name']
					)
				),
				'action_type' => 'add');
		
		$result = Cms_Func::curl( $this->api_url.'/synuser?code='.$this->secure_code, 'post', $data );
		return json_decode($result, true);
	}
	
	
	/**
	 * 更新发布系统用户信息
	 * @return array
	 * */
	public function updateTaskUser( array $arr )
	{
		$data = array(
				'user_array' => serialize(
					array(	'user_name'		=> $arr['user_name'],
							'password'		=> $arr['password']
					)
				),
				'action_type' => 'update');
	
		$result = Cms_Func::curl( $this->api_url.'/synuser?code='.$this->secure_code, 'post', $data );
		return json_decode($result, true);
	}
	
	
	/**
	 * 启用或禁用发布用户
	 * @return array
	 * */
	public function ableTaskUser( array $arr )
	{
		$data = array(
				'user_array' => serialize(
					array('user_name' => $arr['user_name'])
				),
				'action_type' => $arr['type']);
		
		$result = Cms_Func::curl( $this->api_url.'/synuser?code='.$this->secure_code, 'post', $data );
		return json_decode($result, true);
	}
	
}
