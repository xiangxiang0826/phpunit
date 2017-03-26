<?php
/* Created By liujd@wondershare.cn 
 * 2014-12-25
 * 用于向静态资源服务器上传文件。
 * 先将客户端文件上传到本地，将本地保存的文件通过此类post到静态资源服务器
 * 依赖Snoopy库
 **/
require_once 'Snoopy.class.php';
class PostUpload {
	private $access_key = '84d9ee44e457ddef7f2c4f25dc8fa865'; //访问的秘钥，需要和静态服务器保持一致
	private $access_salt = '57daf7b99568cc6ecb592cbec196e075';
	private $snoopy = '';
	private $file_key  = 'file'; //文件域的名称
	private $api_url = 'http://static.1719.com/';
	public function __construct() {
		$this->snoopy = new Snoopy();
		$this->snoopy->_submit_type = 'multipart/form-data'; //post文件
		$this->snoopy->_fp_timeout = 10;
	}
	
	public function set_api_url($api_url) { //设置api地址
		$this->api_url = $api_url;
	}
	
	public function set_time_out($time_out) { //超时
		$this->snoopy->_fp_timeout = $time_out;
	}
	
	public function set_access_key($access_key) {
		$this->access_key = $access_key;
	}
	
	public function set_access_salt($access_salt) {
		$this->access_salt = $access_salt;
	}
	
	public function set_file_key($file_key) { //文件域
		$this->file_key = $file_key;
	}
	
	/* 
	 * $local_file     本地文件完整路径
	 * $mixed_path     如果为一个完整的路径（包含扩展名），则将调用file/upload_assing_path接口,否则将调用upload_assing_path接口
	 * 否则，将调用/file/upload_by_type/
	 * $delete      是否删除本地路径，默认删除
	 *  */
	public function upload($local_file, $mixed_path = '' ,$delete = true) {
		if(empty($local_file) || !file_exists($local_file)) {
			return $this->response(403,'file not exists',null);
		}
		$path_info = pathinfo($mixed_path);
		try	{
			if(isset($path_info['extension'])) { // 为全路径
				$this->upload_assing_path($local_file, $mixed_path);
			} else { //类型
				$this->upload_by_type($local_file, $mixed_path);
			}
			if($delete) { //如果删除文件，则将本地文件删除
				@unlink($local_file);
			}
			if($this->snoopy->status == 404) { // 404
				return $this->response(404, 'page not found');
			}
			if($this->snoopy->status == 500) { // 500
				return $this->response(500, 'remote server error');
			}
			return $this->snoopy->results;
		} catch (Exception $e) {
			return $this->response(500, 'server busy');
		}
	}
	
	/*  
	 * 根据类型上传文件
	 * $mixed_path  可以为路径格式 如:/upload/app
	 * $local_file  本地文件
	 * */
	private function upload_by_type($local_file, $mixed_path) {
		$file_name = basename($local_file);
		$vc = md5($this->access_key . $file_name . $mixed_path . $this->access_salt);
		$action = "{$this->api_url}file/upload_by_type/?vc={$vc}&access_key={$this->access_key}";
		$formvars['type'] = $mixed_path;
		$postfiles[$this->file_key] = $local_file;
		return $this->snoopy->submit($action, $formvars, $postfiles);
	}
	
	/* 
	 * 调用静态服务器的根据路径上传文件的接口
	 * $local_file 本地文件路径
	 * $mixed_path  需要原样的路径
	 *  */
	private function upload_assing_path($local_file, $mixed_path) {
		$vc = md5($this->access_key . $mixed_path . $this->access_salt);
		$action = "{$this->api_url}file/upload_assing_path/?vc={$vc}&access_key={$this->access_key}";
		$formvars['file_path'] = $mixed_path;
		$postfiles[$this->file_key] = $local_file;
		return $this->snoopy->submit($action, $formvars, $postfiles);
	}

	private function response($status, $msg, $result = null) {
		return json_encode(array('status'=>$status,'msg'=>$msg,'result'=>$result));
	}
}