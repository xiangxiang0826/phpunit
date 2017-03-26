<?php
/**
 * 遥控E族控制器
 * @author zhouxh
 *
 */
class Product_EzappController extends ZendX_Controller_Action {
	
	/**
	 * 遥控E族列表
	 */
	public function indexAction(){
		//查询IOS和android版本信息
		$enappModel = new Model_EnterpriseApp();
		$androidApp = $enappModel->getInfoByCondition(array('is_ezapp=1', "platform='android'"));
		$iosApp = $enappModel->getInfoByCondition(array('is_ezapp=1', "platform='ios'"));
		$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
        $user_info = Common_Auth::getUserInfo();
        $this->view->androidApp = $androidApp;
        $this->view->iosApp = $iosApp;
        $this->view->upload = $uploadConfig;
        //查询版本列表
        $page = $this->getRequest()->getParam('page', 1);
        $search = $this->_request->getParam("search");
        if(empty($search)) {
        	$search['platform'] = Model_EnterpriseApp::PLANTFORM_ANDROID;
        }
        $condition = array();
        if(!empty($search['platform'])) {
        	$condition['platform'] = $search['platform']; 
        }
        $ezuIcon = 'http://'.$_SERVER['SERVER_NAME'].$this->view->static.'/images/sh_logo.jpg';
        $cfg = ZendX_Config::get("application","production");
        $showUrl =  $cfg['api']['server'].'/download/app/?label=ezu';
        $select = $enappModel->createCondition($condition);
        $paginator = Zend_Paginator::factory($select);
        $paginator->setItemCountPerPage($this->page_size);// 测试分页 =1
        $paginator->setCurrentPageNumber($page);
        $this->view->showUrl = $showUrl;
        $this->view->paginator = $paginator;
        $this->view->ezuIcon = $ezuIcon;
        $this->view->ezulabel = 'ezu';
        $this->view->loginUid = $user_info['id'];
        $this->view->search = $search;
		
	}
	/**
	 * 查看版本信息
	 */
	public function versioninfoAction(){
		$versionId = $this->getRequest()->getParam('version_id');
		if($versionId) {
			$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
			$appModel = new Model_EnterpriseApp();
			$select = $appModel->get_db()->select()->from(array('V'=>'EZ_UPGRADE_VERSION'));
			$select->join(array('A'=>'EZ_ENTERPRISE_APP'), 'V.upgrade_type_id=A.upgrade_type_id', array('A.name', 'A.platform'));
			$select->where('V.id=?', $versionId);
			$appInfo = $appModel->get_db()->fetchRow($select);
			$this->view->appinfo = $appInfo;
			$this->view->uploadServer = $uploadConfig;
		}
	}
	/**
	 * 查看基本信息
	 */
	public function viewbasicAction(){
		$appId = $this->getRequest()->getParam('appid');
		if($appId) {
			$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
			$appModel = new Model_EnterpriseApp();
			$appInfo = $appModel->getAppInfoByid($appId);
			//查询最新版本信息
			if($appInfo['upgrade_type_id']) {
				$model_upgrade = new Model_Upgrade();
				$version = $model_upgrade->GetLatestVersion($appInfo['upgrade_type_id']);
				$version['v_description'] = $version['description'];
				unset($version['description']);
				if($version) {
				   $appInfo = array_merge($appInfo, $version);
				}
			}
			$this->view->appinfo = $appInfo;
			$this->view->uploadServer = $uploadConfig;
		}
	}
	/**
	 * 编辑基本信息
	 */
	public function editbasicAction(){
		$appId = $this->getRequest()->getParam('appid');
		$appModel = new Model_EnterpriseApp();
		if($appId) {
	        $appInfo = $appModel->getAppInfoByid($appId);
		}
		$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');	
		if($this->getRequest()->isPost()) {
		     $data = $_POST['app'];
		     $post = ZendX_Validate::factory($data)->labels(array(
		     		'name' => 'APP名称',
		     		'description' => '应用简介',
		     		'icon' => '应用icon',
		     ));
		     $post->rules(
		     		'name',
		     		array(
		     				array('not_empty'),
		     				array('min_length',array(':value', 2)),
		     				array('max_length',array(':value',64))
		     		)
		     );
		     $post->rules(
		     		'description',
		     		array(
		     				array('max_length',array(':value',512))
		     		)
		     );
		     $post->rules(
		     		'icon',
		     		array(
		     				array('max_length',array(':value',512))
		     		)
		     );
 		    if($check = $post->check()) {
 		    	    try {
 		    	    	$appModel->begin_transaction();
 		    	    	$appId = intval($appId);
 		    	    	$appModel->update(array('name' => $data['name'], 'description' => $data['description']), "id='{$appId}'");
 		    	    	//图标是否上传
 		    	    	if(!empty($data['icon'])) {
 		    	             $appModel->get_db()->update('EZ_ENTERPRISE_APP_MATERIAL', array('icon' => $data['icon']), "app_id='{$appId}'");
 		    	    	}
 		    	    	if(empty($data['images'])) {
 		    	    		$appModel->updateImage(array(), $appId);
 		    	    	} else {
 		    	    	    $appModel->updateImage($data['images'], $appId);
 		    	    	}
 		    	    	$appModel->commit();
 		    	    	return Common_Protocols::generate_json_response();
 		    	    } catch (Exception $e) {
 		    	    	$appModel->rollback();
 		    	    	return Common_Protocols::generate_json_response($errors, Common_Protocols::ERROR);
 		    	    }
 		    } else {
 		    	$errors = $post->errors('validate');
 		    	return Common_Protocols::generate_json_response($errors, Common_Protocols::VALIDATE_FAILED, '验证失败');
 		    }
		}
		$this->view->appinfo = $appInfo;
		$this->view->uploadServer = $uploadConfig;
	}
	/**
	 * 添加新版本
	 */
	public function addversionAction(){
		$versionId = $this->getRequest()->getParam('version_id');
        $this->view->versionId = $versionId;
        // print_r($versionId);exit;
        $settings = ZendX_Config::get('application', 'settings');
        $this->view->static_domain = "http://{$settings['download_domain']}";
		if($versionId) {
			//查看版本信息
			$appModel = new Model_EnterpriseApp();
			$select = $appModel->get_db()->select()->from(array('V'=>'EZ_UPGRADE_VERSION'));
			$select->join(array('A'=>'EZ_ENTERPRISE_APP'), 'V.upgrade_type_id=A.upgrade_type_id', array('A.name', 'A.platform'));
			$select->where('V.id=?', $versionId);
			$appInfo = $appModel->get_db()->fetchRow($select);
			$this->view->appinfo = $appInfo;
		}
		if($this->getRequest()->isPost()) {
			$data = $_POST['app'];
			$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
			$validate = $this->validateVersion($data);
			if($validate === true) {
				$model = new Model_EnterpriseApp();
				$result = $model->getRowByField(array('upgrade_type_id','id'), array('platform' => $data['platform'], 'is_ezapp' => 1));
				$user = Common_Auth::getUserInfo();
				if($result) {
					$curentTime = date('Y-m-d H:i:s', time());
					$versionData = array(
							                   'upgrade_type_id' => $result['upgrade_type_id'],
							                   'version' => $data['version'],
							                   'description' => $data['description'],
							                   'file_path' => $data['file_path'],
							                   'is_force' => $data['is_force'],
							                   'check_sum' => $data['check_sum'],
							                   'status' => $data['status'],
							                   'publish_status' => Model_Upgrade::PUBLISHED_STATUS,
							                   'exec_type' => 'all',
							                   'name' => 'ezapp',
							                   'cuser' => $user['user_name'],
							                   'mtime' =>$curentTime,
									           'device_type' => $data['device_type']
							               );
					try {
						$model->begin_transaction();
						// 只要是正式发布，并且是enable状态，则将其他的版本全部变成disable
						if($data['device_type'] == Model_Upgrade::DEVICE_TYPE_FORMAL && $data['status'] == Model_Upgrade::STATUS_ENABLE) {
							//正式版本停用
							$sql = "UPDATE EZ_UPGRADE_VERSION SET status='disable' WHERE upgrade_type_id='{$result['upgrade_type_id']}' AND device_type='formal'";
							$model->get_db()->query($sql);
						}
						if($versionId) {
							//测试版本切换到正式版本
							$where = $model->get_db()->quoteInto('id=?', $versionId);
							$model->get_db()->update('EZ_UPGRADE_VERSION', $versionData, $where);
						} else {
							$model->get_db()->insert('EZ_UPGRADE_VERSION', $versionData);
						}
						
						if($data['platform'] == 'ios') { // 是ios，则更新下载页面
							Common_Func::updateIosPage('ezu');
						}
						$model->commit();
						// 放在事务提交之后，因为需要在更新或添加之后才查找数据库
						$model_upgrade = new Model_Upgrade();
						$latest_version = $model_upgrade->GetLatestVersion($result['upgrade_type_id']);
						if($latest_version) {
							$model->get_db()->query("UPDATE EZ_ENTERPRISE_APP SET mtime='{$curentTime}',version='{$latest_version['version']}' WHERE id={$result['id']}");
						}
						return Common_Protocols::generate_json_response();
					} catch (Exception $e) {
						$model->rollback();
						return Common_Protocols::generate_json_response(null, Common_Protocols::ERROR);
					}
				}
			} else {
				return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED ,'验证失败 ');
			}		
		} 
	}
	/**
	 * 添加版本验证
	 * @param array $data
	 * @return boolean|Ambigous <multitype:, multitype:string >
	 */
	protected function validateVersion($data){
		$post = ZendX_Validate::factory($data)->labels(array(
				'platform' => 'platform',
				'description' => 'description',
				'file_path' => 'file path',
				'version' => 'version',
				'is_force' => 'is force'
		));
		$post->rules(
				'platform',
				array(
						array('not_empty')
				)
		);
		$post->rules(
				'file_path',
				array(
						array('not_empty'),
						array('max_length',array(':value',512))
				)
		);
		$post->rules(
				'version',
				array(
						array('max_length', array(':value',32))
				)
		);
		$post->rules(
						'status',
						array(
										array('not_empty')
						)
		);
		$post->rules(
						'device_type',
						array(
										array('not_empty')
						)
		);
		$post->rules('is_force', array(
				array('not_empty'),
				array('digit'),
				array('max_length',array(':value',1))
		));
		$post->rules('description', array(
				array('max_length',array(':value',1024))
				));
		if($post->check()){
			return true;
		} else {
		    return $post->errors('validate');	
		}
	}
	/**
	 * 删除版本
	 */
	public function delversionAction(){
		$versionId = $this->getRequest()->getParam('id');
		$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
		if($versionId) {
			$model = new Model_EnterpriseApp();
			if($model->delApp($versionId, $uploadConfig['basePath'])) {
				return Common_Protocols::generate_json_response('1');
			}
			return Common_Protocols::generate_json_response('0');
		}
	}

    /**
     * 二维码下载
     */
    public function downqcodeAction(){
        $width = $this->_request->getParam('width','258');
        $logo = $this->_request->getParam('logo');
        $label = $this->_request->getParam('label');
        $ez_pro_id = $this->_request->getParam('ez_pro_id');
        if(empty($label) && empty($ez_pro_id)){
            throw new Zend_Exception('参数错误');
        }
        $cfg = ZendX_Config::get("application","production");
        $baseUrl = $cfg['api']['server'].'/intranet/generate_qr_code/?';
        if(empty($label)){
        	$contUrl = $cfg['api']['server'].'/download/app/?ez_pro_id='.$ez_pro_id;
        	$downloadName = $ez_pro_id;
        }else{
        	$contUrl = $cfg['api']['server'].'/download/app/?label='.$label;
        	$downloadName = $label;
        }
        if($logo && ( $logo == 'default' || file_get_contents($logo,0,null,0,1)) ){
            $file_name = $baseUrl."width={$width}&height={$width}&logo={$logo}&content=".urlencode($contUrl);
        }else{
            $file_name = $baseUrl."width={$width}&height={$width}&content=".urlencode($contUrl);
        }
        $mime = 'application/force-download';
        header('Pragma: public'); // required
        header('Expires: 0'); // no cache
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private',false);
        header('Content-Type: '.$mime);
        header('Content-Disposition: attachment; filename="'.$downloadName.'_'.$width.'.jpg"');
        header('Content-Transfer-Encoding: binary');
        header('Connection: close');
        readfile($file_name); // push it out

        echo "<script type='text/javascript'>window.close();</script>";
        exit;
    }
	
}