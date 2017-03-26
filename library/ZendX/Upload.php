<?php 
/**
 * 文件上传类
 * @author lvyz@wondershare
 */

class ZendX_Upload
{
	protected $_options = array(
		'basePath' => '/', 				//文件上传保存的根路径
		'baseUrl' => '/',				//文件访问URL的根路径
		'allowExt' => 'jpg,png,gif', 	//允许的扩展名
		'maxSize' => 2097152, 			//单个文件最大2M
		'path' => null, 				//文件上传后存储的路径，相对于basePath
	);
	
	protected $_errors = null;
	
	protected $_errorsConfig = array(
		'fail' => '上传失败！',
		'no_file' => '没有文件被上传！',
		'error_file' => '非法上传文件！',
		'error_file_type' => '不允许的文件类型！',
		'error_file_size' => '文件大小超过了限制！',		
	);
	
	public function __construct($options)
	{
		foreach($this->_options as $k=>$v)
		{
			if(isset($options[$k])) {
				$this->_options[$k] = $options[$k];
			}
		}

		if(isset($this->_options['path'])) {
			$fullPath = $this->_options['basePath'] . DS . $this->_options['path'];
		} else {
			$path = date('Y') . DS . date('m');
			$fullPath = $this->_options['basePath'] . DS . $path;
			$this->_options['path'] = $path;
		}
		if(!is_dir($fullPath)) {
			mkdir($fullPath, 0755, true);
		}
		
	}
	
	/**
	 * 上传单个文件
	 * @param string $field  上传文件表单域名
	 * @param boolean $keepName 是否保持原文件名
	 * @param string $suffix 文件名追加的后缀
	 * @return boolean|string
	 */
	public function upload($field = NULL, $keepName = FALSE, $suffix=NULL)
	{
		if(empty($_FILES)) {
			$this->_setErrors('no_file');
			return false;
		}
		if($field && !isset($_FILES[$field])) {
			$this->_setErrors('no_file');
			return false;
		}
		if($field) {
			$file = $_FILES[$field];
		} else {
			$file = array_shift($_FILES);
		}
		
		if(!is_uploaded_file($file['tmp_name']))
		{
			$this->_setErrors('error_file');
		}
		
		if(!$this->checkFileExt($file['name']))
		{
			$this->_setErrors('error_file_type');
			return false;
		}
		
		if(!$this->checkFileSize($file['tmp_name']))
		{
			$this->_setErrors('error_file_size');
			return false;
		}
		
		if(!$keepName) {
			$filename = $this->makeFilename() . '.' . $this->getFileExt($file['name']);
		} else {
			$filename = $file['name'];
		}
		if($suffix) {
			$filename = pathinfo($filename, PATHINFO_FILENAME) . '_' . $suffix . '.' .  $this->getFileExt($filename);
		}
		
		$destination = $this->getFullPath() . DS . $filename;
		if (!move_uploaded_file($file['tmp_name'], $destination)) {
			$this->_setErrors('fail');
			return false;
		}
		
		return '/'.$this->_options['path'] . '/' . $filename;
	}
	
	/**
	 * 生成随机文件名
	 * @return string
	 */
	public function makeFilename()
	{
		return date('Ymd_His') . substr(md5(uniqid(mt_rand(), true)), -15);
	}
	
	/**
	 * 自定义错误提示内容
	 * @param array $errorConfig
	 */
	public function setErrorsConfig($errorConfig) {
		$this->_errorsConfig = array_merge($this->_errorsConfig, $errorConfig);
	}
	
	/**
	 * 错误配置内容
	 */
	public function getErrorsConfig()
	{
		return $this->_errorsConfig;
	}
	
	/**
	 * 检查文件扩展名
	 * @param string $filename
	 * @return boolean
	 */
	public function checkFileExt($filename)
	{
		$allowExt = explode(',', $this->_options['allowExt']);
		$ext = $this->getFileExt($filename);
		return in_array($ext, $allowExt);
	}
	
	/**
	 * 检查文件大小
	 * @param unknown_type $filename
	 * @return boolean
	 */
	public function checkFileSize($filename) {
		$filesize = filesize($filename);
		return $filesize < $this->_options['maxSize'];
	}
	
	/**
	 * 获取完整的上传路径，不包括文件名
	 * @return string
	 */
	public function getFullPath()
	{
		return $this->_options['basePath'] . DS . $this->_options['path'];
	}
	
	/**
	 * 获取完整的URL访问路径，不包括文件名部分
	 * @return string
	 */
	public function getFullUrl()
	{
		return $this->_options['baseUrl'] . '/'. $this->_options['path'];
	}
	
	/**
	 * 获取所有的设置项值
	 * @return array
	 */
	public function getOptions()
	{
		return $this->_options;
	}
	
	/**
	 * 错误信息
	 * @return string
	 */
	public function getErrors()
	{
		return $this->_errors;
	}

	
	/**
	 * 获取文件扩展名
	 * @param unknown_type $filename
	 * @return mixed
	 */
	public function getFileExt($filename)
	{
		$info = pathinfo($filename);
		return strtolower($info['extension']);
	}
	
	/**
	 * 设置错误信息
	 * @param string $error
	 */
	protected function _setErrors($error)
	{
		if(isset($this->_errorsConfig[$error])) {
			$this->_errors[$error] = $this->_errorsConfig[$error];
		} else {
			$this->_errors[$error] = $this->_errorsConfig['fail'];
		}
		
	}
	
}