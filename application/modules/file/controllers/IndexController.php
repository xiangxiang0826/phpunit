<?php
/**
 * cms2.1-文件管理模块-文件控制器
 * @author hcb
 * @time 2013-05-29
 */

class File_IndexController extends ZendX_Cms_Controller
{
	private $type;
	private $file_arr = array();

	/**
	 * 初始化
	 */
	public function init()
	{
		parent::init();
	}

	/**
	 * www服务器管理窗口
	 */
	public function wwwAction()
	{
		$request = $this->getRequest(); 
		$domain = $request->getParam('domain'); // 通过菜单的传值，获取域名。
		$cfg = Cms_Func::getConfig('create', 'domain')->toArray();
		$key = 'www';
		$this->view->type = $key;
		$this->view->domain = $domain;
	}
	
	/**
	 * images服务器管理窗口
	 */
	public function imagesAction()
	{
		$cfg = Cms_Func::getConfig('create', 'domain')->toArray();
		$key = 'images';
		$this->view->type = $key;
		$this->view->domain = $cfg[$key];
		$this->render('index');
	}
	
	
	/**
	 * 沙盒项目文件管理窗口
	 * */
	public function labAction()
	{
		$this->view->type = 'lab';
		$this->render('index');
	}
	
	
	/**
	 * 取得管理服务器的信息
	 * */
	private function _getRootInfo($domain = 'spotmau.cn') {
		$domain = str_replace('http://','',$domain);
		$cfg_key = str_replace('.','_',$domain);  //配置文件里的key是以_代替.
		$cfg = Cms_Func::getConfig('create', 'root')->toArray();
		if( in_array( $this->type, array('www', 'lab') ) ){
			$dir['dir'] = $cfg[$cfg_key];
			$dir['url'] = "http://{$domain}";
		}elseif( $this->type == 'images' ){ 
			$dir['dir'] = $cfg['images']; // 如果是图片类型，则直接返回images的配置项
			$dir['url'] = "http://{$domain}";
		}
		return $dir;
	}
	
	
	/**
	 * 遍历文件夹
	 * */
	private function _mapFile( $dir, &$file_arr, $range_str = 'a-g', $is_top_dir = true ){
		try {
		if( is_dir( $dir ) ){
			if( substr( $dir, -1, 1) == '/' ) $dir = substr( $dir, 0, -1);
			$arr = glob( $dir . '/*');
			
			//是否是第一层目录
			if( $range_str && $is_top_dir ){
				$range_arr = explode('-', $range_str);
				$range = range( $range_arr[0], $range_arr[1]);
				foreach( $arr as $k=>$v ){
					if( !is_dir( $v ) ) continue;
					$v_ary = explode('/', $v);
					$dirname = end($v_ary);
					if( !in_array( strtolower( $dirname[0] ), $range, true ) ){
						unset( $arr[$k] );
					}
				}
			}
			
			foreach( $arr as $v ){
				$this->_mapFile( $v, $file_arr, $range_str, false );
			}
		}else{
			$file_arr[] = $dir;
		}
		} catch(Exception $e) {
			ZendX_Tool::log('debug',$v);
		}
	}
	
	
	
	/**
	 * 连接CKFinder
	 * */
	public function connectorAction()
	{

		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout()->disableLayout();
		
		$this->type = $_GET['manage_type'];
		$this->domain = $_GET['domain'];
		
		$host_arr = $this->_getRootInfo($this->domain);

		$host_root_dir = $host_arr['dir'];//在CKFinder的配置文件中用到
		$host_root_url = $host_arr['url'];//在CKFinder的配置文件中用到
		$GLOBALS['file_obj'] = $this;
		
		include_once dirname(APPLICATION_PATH).'/public/static/js/libs/ckfinder/core/connector/php/connector.php';
	}
	
	
	/**
	 * CKFinder上传文件成功后调用
	 * @param string $filePath 上传的文件在服务器上的绝对路径
	 * */
	public function uploadFileAction( $filePath ,$domain = ''){
		Cms_Task::getInstance()->send( array( $filePath ), 'edit', $domain);
	}
	
	
	/**
	 * CKFinder删除文件成功后调用
	 * @param array $arr 二维数组形式 array( array('folder'=>'/images/about/', 'name'=>'123456.png', 'type'=>'images.wondershare.com') )
	 * */
	public function deleteFileAction( $arr ,$domain = ''){
		$files_arr = array();
		$host_arr = $this->_getRootInfo($domain);
		
		foreach( $arr as $v ){
			$files_arr[] = $host_arr['dir'] . $v['folder'] . $v['name'];
		}
		
		Cms_Task::getInstance()->send( $files_arr, 'delete', $domain);
	} 
	
	
	/**
	 * CKFinder编辑文件成功后调用
	 * @param string $folder 编辑文件所在的文件夹
	 * @param string $filename 编辑文件的文件名
	 * */
	public function editFileAction( $folder, $filename ,$domain = ''){
		$host_arr = $this->_getRootInfo($domain);
		Cms_Task::getInstance()->send( array( $host_arr['dir'] . $folder . $filename ), 'edit', $domain);
	}
	
	
	/**
	 * CKFinder重命名文件成功后调用
	 * @param string $folder 重命名文件所在的文件夹
	 * @param string $filename 重命名文件的文件名
	 * @param string $newfilename 命名后的文件名
	 * */
	public function renameFileAction( $folder, $filename, $newfilename ,$domain = ''){
		$host_arr = $this->_getRootInfo($domain);
		
		if( !isset($_GET['no_del']) ){//在替换重命名文件时，该文件还不在外网，不要发布系统删除重命名文件
			Cms_Task::getInstance()->send( array( $host_arr['dir'] . $folder . $filename ), 'delete', $domain);
		}
		Cms_Task::getInstance()->send( array( $host_arr['dir'] . $folder . $newfilename ), 'edit', $domain);
	}
	
	/**
	 * CKFinder删除文件夹成功后调用
	 * @param string $dir 要删除的文件夹，格式：/images/
	 * */
	public function deleteDirAction( $dir ,$domain = ''){
		$host_arr = $this->_getRootInfo($domain);
		
		$files_arr = array();
		$this->_mapFile( $host_arr['dir'] . $dir, $files_arr);

		Cms_Task::getInstance()->send( $files_arr, 'delete', $domain);
	}
	
	
	/**
	 * CKFinder取得所有文件列表
	 * */
	public function getFilesAction($domain = 'spotmau.cn'){
		$host_arr = $this->_getRootInfo($domain);
		$files_arr = array();
		$this->_mapFile( $host_arr['dir'], $files_arr, $_GET['show_char_dir']);
		$str = '<?xml version="1.0" encoding="utf-8"?>
				<Connector resourceType="All files list">
				<Error number="0" />
				<CurrentFolder path="/" url="'.$host_arr['url'].'" acl="251" />
				<Files>';
		foreach( $files_arr as $k=>$v ){
			$file = preg_replace('/.*\/httpdocs\/(.*)/', '/$1', $v);
			if( substr_count( $file, '&') > 0 ){
				$file = '/1这个URL中的XXX处有特殊符号：'.str_replace('&', 'XXX', $file);
			}
			$str .= '<File name="'.$file .'" date="'.date('YmdHi',filemtime($v)).'" size="'.ceil(filesize( $v )/1000).'" />';		
		}
		
		$str .= '</Files></Connector>';
		
		return $str;
	}

	
}