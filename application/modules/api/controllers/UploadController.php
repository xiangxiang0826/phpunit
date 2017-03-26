<?php
/* Created By liujd@wondershare.cn
 * 上传模块
 *  */
class Api_UploadController extends ZendX_Controller_Action {
	/* 公共上传接口 */
	public function indexAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
		//修改 kindeditor 上传图片
		$type = $this->_request->getPost("type");
		//是否保持原文件名
		$keepName = $this->_request->getPost("keep_name");
		//文件名增加的后缀，固件上传文件名需求增加参数
		$suffix = $this->_request->getPost("suffix");
		$config = $this->getConfigByType($type);
		$uploadConfig['allowExt'] = $config['allowext'];
		$uploadConfig['path'] = "{$config['prefix']}/".date('Y').'/'.date('m');
		$uploadConfig['maxSize'] = $config['maxsize'];
		$upload = new ZendX_Upload($uploadConfig);
		if(($filePath = $upload->upload('Filedata', $keepName, $suffix)) == false) {
			$error = $upload->getErrors();
			foreach($error as $k=>$v) {
				ZendX_Tool::log('error',"{$k}:{$v}");
				return Common_Protocols::generate_json_response($v,Common_Protocols::VALIDATE_FAILED);
			}
		}
		$settings = ZendX_Config::get('application', 'settings');
		$md5 = md5_file("{$uploadConfig['basePath']}{$filePath}"); //计算md5
		if($this->_request->getPost("task") && ($upload->getFileExt($filePath) == 'ipa')) { //post了task,并且是ipa文件,则调用发布系统
			Cms_Task::getInstance()->send("{$uploadConfig['basePath']}{$filePath}", 'edit', $settings['app_download_domain'], true); // 发布ios配置的静态文件
		}
		// 上传文件到静态服务器
		if($keepName) {
			$type = ltrim($filePath, '/');
		}
		$upload_ret = ZendX_Tool::upload("{$uploadConfig['basePath']}{$filePath}", $type, true);
		$upload_ret = json_decode($upload_ret,true);
		$full_path = "http://{$settings['download_domain']}{$upload_ret['result']['access_path']}";
		$data = array('url'=>$full_path, 'path'=>$upload_ret['result']['access_path'],'file_name'=>basename($filePath),'md5'=>$md5);
		return Common_Protocols::generate_json_response($data,Common_Protocols::SUCCESS);
	}
	
	private function getConfigByType($type) {
		$uploadConfig = ZendX_Config::get('application','upload');
		return isset($uploadConfig[$type]) ? $uploadConfig[$type] : array('allowext'=>'gif,jpg,png,bin,apk,ipa,rar,zip,dex,doc,txt,docx','prefix'=>'common','maxsize'=>1024*1024*10);
	}
}