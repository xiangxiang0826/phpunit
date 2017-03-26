<?php
/**
 * 厂商管理控制器
 * @author zhouxh
 *
 */
class EnterPrise_IndexController extends ZendX_Controller_Action
{
    /**
     * 厂商列表
     */
	public function indexAction()
	{		
		$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$rows = isset($_GET['rows']) ? intval($_GET['rows']) : $this->page_size;
		$offset = ($page-1)*$rows;
		$query = "";
		$arrQuery = array();
		$search = $this->_request->getParam("search");
		
		if(!empty($search['status'])){
			$queryString = "t.status = '". $search['status'] ."'";
			array_push($arrQuery, $queryString);
		}
		if(!empty($search['company_name'])){
			$queryString = "t.company_name LIKE '%". $search['company_name'] ."%'";
			array_push($arrQuery, $queryString);
		}
		$queryString = "(t.status  IN ( 'enable', 'locked', 'deleted')  )";
		array_push($arrQuery, $queryString);
		if(!empty($arrQuery)){
			$query = implode(" AND ", $arrQuery);
		}
		$result = array();
		$enterpriseModelsAccount = new Model_Enterprise();
		$row = $enterpriseModelsAccount->getTotal($query);
		$result["total"] = $row;
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($result["total"]));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($rows);
		$items = $enterpriseModelsAccount->getList($query, $offset, $rows);
		foreach($items as $key => $value) {
			$items[$key]['product_num'] = $enterpriseModelsAccount->getProductNumber($value['id']);
			$items[$key]['is_ezapp'] = $enterpriseModelsAccount->isHasSelfApp($value['id']);
		}
		$result['rows'] = $items;
		$this->view->results =  $result;
		$this->view->pagenation = $pagenation;
		$this->view->search = $search;
	}
	/**
	 * 待审核厂商列表
	 */
	public function pendingAction(){
		$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$rows = isset($_GET['rows']) ? intval($_GET['rows']) : $this->page_size;
		$offset = ($page-1)*$rows;
		$query = "";
		$arrQuery = array();
		$queryParams = $this->_request->getParam("queryParams");
        
		$queryString = "(t.status  IN ( 'pending', 'unactivated', 'audit_failed')  )";
		array_push($arrQuery, $queryString);
		if(!empty($arrQuery)){
			$query = implode(" AND ", $arrQuery);
		}
		$result = array();
		$enterpriseModelsAccount = new Model_Enterprise();
		$row = $enterpriseModelsAccount->getTotal($query);
		$result["total"] = $row;
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($result["total"]));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($rows);
		$items = $enterpriseModelsAccount->getList($query, $offset, $rows);
		$result['rows'] = $items;
		$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('images');
		$this->view->upload = $uploadConfig;
		$this->view->results =  $result;
		$this->view->pagenation = $pagenation;
	}
	/**
	 * 厂商详情
	 */
	public function detailAction(){
		$id = $this->_request->getParam('id');
		$id = intval($id);
		if($id) {
			$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('images');
			$enterprise = new Model_Enterprise();
			$info = $enterprise->getDetail($id);
			$this->view->upload = $uploadConfig;
			$this->view->info =  $info;
		} else {
			throw new Zend_Exception('参数错误');
		}
	}
	/**
	 * 审核页面
	 */
	public function checkAction(){
		$post = ZendX_Validate::factory($_POST);
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout()->disableLayout();
		$post->rules(
						'pass',
						array(
										array('not_empty')
						)
		);
		$post->rules(
						'check_opinion',
						array(
										array('max_length',array(':value',500))
						)
		);
		$post->rules(
						'label',
						array(
										array('max_length',array(':value',32)),
										array('not_empty'),
										array('regex', array(':value', '/^[a-zA-Z]+$/' ))
						)
		);
		$enterprise = new Model_Enterprise();
		if($post->check() && !$enterprise->labelExists($post['label'])) {
			if($_POST['pass'] == '1') {
				$status = 'unactivated';
			} else {
				$status = 'audit_failed';
			}
            $statusEmail = $statusSms = 0;
            if(isset($_POST['message'])  && !empty($_POST['message'])){
                foreach ($_POST['message'] as $v) {
                    if($v == 'email') {
                        $statusEmail = 1;
                    } else if($v == 'msg') {
                        $statusSms = 2;
                    }
                }
            }
            $informStatus = $statusEmail + $statusSms;
			$data = array(
                'status' => $status,
                'check_opinion' => $_POST['check_opinion'],
                'label' => $_POST['label'],
                'audit_time' => date('Y-m-d H:i:s'),
                'inform_status' => $informStatus
			);		
			$db = $enterprise->get_db();
			$db->beginTransaction();
			try {
				$where = $enterprise->get_db()->quoteInto("id=?", $_POST['enterprise_id']);
				$enterprise->update($data, $where);
				$enterpriseInfo = $enterprise->getFieldsById(array('name', 'mobile', 'email', 'label', 'id', 'user_type'), $_POST['enterprise_id']);
				//审核通过
				if($status == 'unactivated') {
                    $this->createConfigFile($_POST['label'], $enterpriseInfo['id']);
					$grantModel = new Model_EnterpriseGrant();
					$permissionMapModel = new Model_ApiPermissionMap();
                    $apiId = $grantModel->createGrant($_POST['enterprise_id'], $enterpriseInfo['name']);
					if($apiId) {
						$permissionMapModel->addEnterprisePer($enterpriseInfo['user_type'], $apiId);
					}
				}
				//发送短信或者邮件
				if(!empty($_POST['message'])) {
					if($status == 'unactivated') {
						//审核成功
						$info = $this->view->getHelper('t')->t('check_success_info');
					} else if($status == 'audit_failed') {
						//审核失败
						$info = $this->view->getHelper('t')->t('check_failed_info');
					}
                    $resultEmail = $resultSms = 0;
					foreach ($_POST['message'] as $v) {
						if($v == 'email') {
							if(!empty($enterpriseInfo['email'])) {
								$to_address =  array(array('email' => $enterpriseInfo['email'], 'name' => $enterpriseInfo['name']));
								$result = ZendX_Tool::sendEmail($info, $to_address, '遥控e族企业云平台通知');
                                if($result == TRUE){
                                     $resultEmail = 1;
                                 }
							}
						} else if($v == 'msg') {
							if(!empty($enterpriseInfo['mobile'])) {
								$result = ZendX_Tool::sendSms($enterpriseInfo['mobile'], $info);
                                if($result == TRUE){
                                     $resultSms = 2;
                                 }
							}
						}
					}
                    $informResult = $resultEmail + $resultSms;
                    $data = array(
                        'inform_result' => $informResult
                    );
                    $enterprise->update($data, $where);
				}
				$db->commit();
				return Common_Protocols::generate_json_response();
			} catch ( Exception $e) {
				ZendX_Tool::log('info',$e->getMessage());
				$db->rollback();
				return Common_Protocols::generate_json_response(null, Common_Protocols::ERROR);
			}
		} else {
		    return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED);	
		}
	}
	/**
	 * 管理页面
	 */
	public function managerAction(){
		$post = ZendX_Validate::factory($_POST);
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout()->disableLayout();
		$post->rules(
				'pass',
				array(
						array('not_empty')
				)
		);
		$post->rules(
				'remark',
				array(
						array('max_length',array(':value',200))
				)
		);
		if($post->check()) {
			if($_POST['pass'] == '1') {
				$status = 'locked';
			} else {
				$status = 'enable';
			}
            $statusEmail = $statusSms = 0;
            if(isset($_POST['message'])  && !empty($_POST['message'])){
                foreach ($_POST['message'] as $v) {
                    if($v == 'email') {
                        $statusEmail = 1;
                    } else if($v == 'msg') {
                        $statusSms = 2;
                    }
                }
            }
            $informStatus = $statusEmail+$statusSms;
			$data = array(
					'status' => $status,
					'remark' => $_POST['remark'],
                    'inform_status' => $informStatus
			);
			$enterprise = new Model_Enterprise();
			$where = $enterprise->get_db()->quoteInto("id=?", $_POST['enterprise_id']);
			if($enterprise->update($data, $where)) {
				//发送短信或者邮件
				if(!empty($_POST['message'])) {
					$enterpriseInfo = $enterprise->getFieldsById(array('name', 'mobile', 'email'), $_POST['enterprise_id']);
					if($status == 'locked') {
						$info = $this->view->getHelper('t')->t('lock_info');
					} else if($status == 'enable') {
						$info = $this->view->getHelper('t')->t('enable_info');
					}
                    $resultEmail = $resultSms = 0;
					try {
						foreach ($_POST['message'] as $v) {
							if($v == 'email') {
								if(!empty($enterpriseInfo['email'])) {
									$to_address =  array(array('email' => $enterpriseInfo['email'], 'name' => $enterpriseInfo['name']));
									$result = ZendX_Tool::sendEmail($info, $to_address, '遥控e族企业云平台通知');
                                    if($result == TRUE){
                                        $resultEmail = 1;
                                    }
								}
							} else if($v == 'msg') {
								if(!empty($enterpriseInfo['mobile'])) {
									$result = ZendX_Tool::sendSms($enterpriseInfo['mobile'], $info);
                                    if($result == TRUE){
                                        $resultSms = 2;
                                    }
								}
							}
						}
                        $informResult = $resultEmail + $resultSms;
                        $data = array(
                            'inform_result' => $informResult
                        );
                        $enterprise->update($data, $where);
					} catch (Exception $e) {
						throw new Zend_Exception('发送信息失败');
					}
				}
			}
		}
		header("location:" . $_SERVER['HTTP_REFERER']);
		exit;
	}
	/**
	 * 需要审核的数目
	 */
	public function needcheckAction(){
		$result = 0;
		$enterprise = new Model_Enterprise();
		$number = $enterprise->getNumberBystatus('pending');
		$result = $number;
		return Common_Protocols::generate_json_response($result);
	}
	/**
	 * 不通过的信息
	 */
	public function nopassinfoAction(){
		$enterpriseId =$this->_request->getParam('id');
		$enterpriseId = intval($enterpriseId);
		if($enterpriseId) {
			$enterprise = new Model_Enterprise();
			$info = $enterprise->getDetail($enterpriseId);
			return Common_Protocols::generate_json_response($info);
		} else {
			return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED);
		}
	}
	/**
	 * 是否有自己的APP
	 */
	public function hasselfappAction(){
		$enterpriseId =$this->_request->getParam('id');
		$enterpriseId = intval($enterpriseId);
		if($enterpriseId) {
			$enterprise = new Model_Enterprise();
			$info = $enterprise->isHasSelfApp($enterpriseId);
			return Common_Protocols::generate_json_response($info);
		} else {
			return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED);
		}
	}
	/**
	 * 企业标识是否存在
	 */
	public function labelexistsAction(){
		$label = $this->_request->getParam('label');
		if(!empty($label)) {
			$enterprise = new Model_Enterprise();
			if ($enterprise->labelExists($label)) {
				echo 'false';
			} else {
				echo 'true';
			}
		} else {
			echo 'true';
		}
			
	}
	
	/* 根据厂商ID获取产品列表 */
	public function getproductsAction() {
		if($this->getRequest()->isPost()) {
			$enterpriseId = $this->getRequest()->getPost('enterprise_id');
			$product_model  = new Model_Product();
			$products = $product_model->GetList(array('status'=>Model_Product::STATUS_ENABLE,'enterprise_id'=>$enterpriseId, 'product_id'=>0), 0, 100);
			return Common_Protocols::generate_json_response($products);
		}
	}
	
	/*厂商 添加 新建厂商*/
    public function addAction(){
    	$settings = ZendX_Config::get('application', 'settings');
    	$this->view->download_domain = "http://{$settings['download_domain']}";
        if($this->getRequest()->isPost()) {
            $data = $_POST['enterprise'];
            $data['salt'] = ZendX_Tool::getRandom();
            $data['password'] = md5(md5($data['password']).$data['salt']);
            $data['status'] = 'enable';
            $data['audit_time'] = $data['reg_time'] = $data['active_time'] = date('Y-m-d H:i:s');
            $enterpriseModel = new Model_Enterprise();
            $tr = $enterpriseModel->begin_transaction();
            try {
            	$enterpriseId = $enterpriseModel->insert($data);
            	$this->createConfigFile($data['label'], $enterpriseId);
            	$grantModel = new Model_EnterpriseGrant();
            	$permissionMapModel = new Model_ApiPermissionMap();
            	$apiId = $grantModel->createGrant($enterpriseId, $data['company_name']);
            	if($apiId) {
            		$permissionMapModel->addEnterprisePer($data['user_type'], $apiId);
            	}
            	$tr->commit();
            	return Common_Protocols::generate_json_response();
            } catch (Exception $e) {
            	ZendX_Tool::log('info',$e->getMessage());
            	$tr->rollback();
            	return Common_Protocols::generate_json_response(null, Common_Protocols::ERROR);
            }
        }
    }
        
    /*验证email是否唯一*/
    public function checkemailAction(){
    	$email = $_POST['enterprise']['email'];
    	if(!empty($email)) {
    		$enterprise = new Model_Enterprise();
    		if ($enterprise->emailExists($email)) {
    			echo 'false';
    		} else {
    			echo 'true';
    		}
    	} else {
    		echo 'true';
    	}
    }
    
    /**
     * 验证手机
     */
    public function checkmobileAction(){
    	$mobile = $_POST['enterprise']['mobile'];
    	if(!empty($mobile)) {
    		$enterprise = new Model_Enterprise();
    		if ($enterprise->mobileExists($mobile)) {
    			echo 'false';
    		} else {
    			echo 'true';
    		}
    	} else {
    		echo 'true';
    	}
    }
	    
    /**
     * 创建企业APP的配置文件
     * @param string $label 企业标识
     * @param int $enterpriseId 企业ID
     */
    private function createConfigFile($label, $enterpriseId){
    	//properties文件修改
    	$settings = ZendX_Config::get('application', 'settings');
    	$demoContent = file_get_contents(APPLICATION_PATH . DS . 'configs' . DS . 'enterprise_demo.properties');
    	$platforms = array('android', 'ios');
    	$search = array('{upgrade_type}', '{enterprise_id}', '{label}');
    	$sendPath = array();
    	foreach ($platforms as $platform) {
    		$upgradeType = $label . '_app_' . $platform;
    		$replace = array($upgradeType, $enterpriseId, $label);
    		$putContent= str_replace($search, $replace, $demoContent);
    		$filename = $settings['path'] . DS . $label . '_' . $platform . '_' . 'config.properties';
    		$sendPath [] = $filename;
    		file_put_contents($filename, $putContent);
    	}	
    	//ios的下载和plist文件
    	Common_Func::updateIosPage($label);
    	//发布到外网
    	$ret = Cms_Task::getInstance()->send($sendPath, 'edit', $settings['domain'], true);
    }
	
}