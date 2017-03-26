<?php
/**
 * 产品管理
 * @author zhouxh
 *
 */
class Product_IndexController extends ZendX_Controller_Action
{
	/**
	 * 产品概况
	 */
	public function indexAction() {
		// /product/overview/index
        $this->redirect('/product/overview/index');
	}
    
	/**
	 * 已经发布产品列表
	 */
	public function listAction() {
		$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$rows = isset($_GET['rows']) ? intval($_GET['rows']) : 10;
		$offset = ($page-1)*$rows;
		$query = "1=1 ";
		//查询已审核的
        // 去掉了审核失败的产品：audit_failed
		$products  = new Model_Product();
		$productCate = new Model_ProductCate();
		$search = $this->_request->getParam("search");
		$arrQuery = array();
		if(!empty($search['product_name'])){
			$queryString = "t.name LIKE  '%".  $search['product_name'] ."%'";
			array_push($arrQuery, $queryString);
		}
		if(!empty($search['enterprise_id'])) {
			$queryString = "t.enterprise_id =  ". $products->quote($search['enterprise_id'], Zend_Db::INT_TYPE) ."";
			array_push($arrQuery, $queryString);
		}
		//类别查询
		if(!empty($search['category'])) {
			$categoryIds = $productCate->getAllChildrenById($search['category']);
			if($categoryIds) {
				$categoryIds[] = $search['category'];
			} else {
				$categoryIds = array($search['category']);
			}
			$queryString = "t.category_id IN({$products->quote($categoryIds)})";
			array_push($arrQuery, $queryString);
		}
		//状态
	    if(isset($search['into_ezapp']) && $search['into_ezapp'] !== '') {
	    	$queryString = "t.into_ezapp ='{$search['into_ezapp']}' AND t.status = '". Model_Product::STATUS_ENABLE ."'" ;
			array_push($arrQuery, $queryString);
		}
		if(empty($search['status'])) {
			$search['status'] = Model_Product::STATUS_ENABLE;
        } 
        if($search['status'] !== 'all') {
        	$queryString = "t.status ='{$search['status']}'";
        	array_push($arrQuery, $queryString);
        }
		if(!empty($arrQuery)){
			$query = implode(" AND ", $arrQuery);
		}
		$query .= "AND t.status <> 'deleted'";
		$result = array();
		$result["total"] = $products->getTotal($query);
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($result["total"]));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($rows);
		$items = $products->getPublishList($query, $offset, $rows);
		$result['rows'] = $items;
		$cfg = ZendX_Config::get("application","production");
		$baseUrl = $cfg['images']['baseUrl']; 
	    $idArr = array();
		foreach($items as $key => $value){
			$idArr[] = $value['id'];
		}
		$makeProductModel = new Model_MakeProduct();
		//激活数量查询
		if(!empty($idArr)) {
		    $activeNumberMap = $makeProductModel->queryActiveNumber($idArr);
		    $this->view->actives = $activeNumberMap;
		}
		//查询列表数据
        $enterprise = new Model_Enterprise();
		$this->view->enterprises =  $enterprise->droupDownData();
		$this->view->category = $productCate->dumpTree();
		$this->view->results =  $result;
		$this->view->baseUrl = $baseUrl;
		$this->view->pagenation = $pagenation;
		$this->view->search = $search;
	}
	/**
	 * 待发布产品列表
	 */
	public function pendingAction(){
		$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$rows = isset($_GET['rows']) ? intval($_GET['rows']) : $this->page_size;
		$offset = ($page-1)*$rows;
		$query = "";

		$products  = new Model_MakeProduct();
        // $arrQuery = array();
        $autitStatus = array(Model_MakeProduct::STATUS_PUBLISH_FAILED, Model_MakeProduct::STATUS_PENDING);
        $autitStatus = array_map(function ($v) { return "'{$v}'";}, $autitStatus);
        $arrQuery = array("t.status IN(". implode(',',$autitStatus)  . ")");
        
		$productCate = new Model_ProductCate();
		$search = $this->_request->getParam("search");
		if(!empty($search['product_name'])){
			$queryString = "t.name LIKE  '%".  $search['product_name'] ."%'";
			array_push($arrQuery, $queryString);
		}
		//厂商查询
		if(!empty($search['enterprise_id'])) {
			$queryString = "t.enterprise_id =  ". $products->quote($search['enterprise_id'], Zend_Db::INT_TYPE) ."";
			array_push($arrQuery, $queryString);
		}
        
		//厂商查询
		if(!empty($search['enterprise_id'])) {
			$queryString = "t.enterprise_id =  ". $products->quote($search['enterprise_id'], Zend_Db::INT_TYPE) ."";
			array_push($arrQuery, $queryString);
		}
		
		//类别查询
		if(!empty($search['category'])) {
			$categoryIds = $productCate->getAllChildrenById($search['category']);
			if($categoryIds) {
				$categoryIds[] = $search['category'];
			} else {
				$categoryIds = array($search['category']);
			}
			$queryString = "t.category_id IN({$products->quote($categoryIds)})";
			array_push($arrQuery, $queryString);
		}
        
		//产品状态查询
		if(!empty($search['status'])) {
			$queryString = "t.status IN({$products->quote($search['status'])})";
			array_push($arrQuery, $queryString);
        }else{
            // $queryString = "t.status ='pending'";
            // 去掉了审核失败的产品：audit_failed

        }
        
		//是否有查询条件
		if(!empty($arrQuery)){
			$query = implode(" AND ", $arrQuery);
		}
		$result = array();
		//审核状态查询,
		if(!empty($search['check_type']) && $search['check_type'] !== 'all') {
			// SELECT p.id AS is_exist,t.* FROM EZ_MAKE_PRODUCT AS t LEFT JOIN EZ_PRODUCT AS p ON t.id=p.id WHERE p.id != ''
            if($search['check_type'] == 'new'){
                $whereAdd = 'p.id IS NULL';
            }else{
                $whereAdd = 'p.id IS NOT NULL';
            }
            $query = ($query)?($query.' AND '.$whereAdd):$whereAdd;
            $makeProductModel= new Model_MakeProduct();
            $select = $makeProductModel->get_db()->select()->from(array('t'=>'EZ_MAKE_PRODUCT'), "count(*) as total");
            $select->joinLeft(array('p'=>'EZ_PRODUCT'), 't.id=p.id ', array(''));
            $select->where($query);
            $productTotal = $makeProductModel->get_db()->fetchRow($select);
            $total = 0;
            if(is_array($productTotal) && isset($productTotal['total'])){
                $total = $productTotal['total'];
            }
            unset($makeProductModel);
            $makeProductModel= new Model_MakeProduct();
            $select = $makeProductModel->get_db()->select()->from(array('t'=>'EZ_MAKE_PRODUCT'), "t.*");
            $select->joinLeft(array('p'=>'EZ_PRODUCT'), 't.id=p.id ', array('p.ctime AS p_time'));
            $select->joinLeft(array('c'=>'EZ_PRODUCT_CATEGORY'), 't.category_id=c.id ', array('c.name AS c_name'));
            $select->joinLeft(array('e'=>'EZ_ENTERPRISE'), 't.enterprise_id=e.id ', array('e.company_name AS e_name'));
            $select->where($query);
            $select->order('t.ctime DESC');
            $select->limit($rows, $offset);
            $productInfo = $makeProductModel->get_db()->fetchAll($select);
            $pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($total));
            $pagenation->setCurrentPageNumber($page)->setItemCountPerPage($rows);
            $items = $productInfo;
            /*
            SELECT distinct t.*,d.id as is_post, c.name AS c_name,e.company_name AS e_name,d.status AS p_status,d.ctime AS p_time FROM `{$this->_table}` AS t LEFT JOIN EZ_PRODUCT_CATEGORY c ON t.category_id=c.id  LEFT JOIN EZ_ENTERPRISE e ON t.enterprise_id=e.id
            */
        }else{
            $result["total"] = $products->getTotal($query);
            $pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($result["total"]));
            $pagenation->setCurrentPageNumber($page)->setItemCountPerPage($rows);
            $items = $products->getList($query, $offset, $rows);
        }
		$result['rows'] = $items;
		
		//查询列表数据
		$enterprise = new Model_Enterprise();
		$this->view->enterprises =  $enterprise->droupDownData();;
		$this->view->category = $productCate->dumpTree();
		$this->view->results =  $result;
		$this->view->pagenation = $pagenation;
		$this->view->search = $search;
	}
    
	/**
	 * 产品详情审查
	 */
	public function recheckAction(){
        $productId =$this->_request->getParam('id');
		$productId = intval($productId);
		if($productId) {
			$product = new Model_MakeProduct();
			$info = $product->queryProductInfo($productId);
			if(!empty($info['langs'])) {
				$langsArr = explode(',', $info['langs']);
				$newLangArr =  array();
				foreach ($langsArr as $lang) {
					$newLangArr[] = $this->view->getHelper('t')->t($lang);
				}
				$info['langs'] = implode(',', $newLangArr);
			}
			$apiInfo = $this->getInvokeArg('bootstrap')->getOption('api');
            $this->view->pid  = $productId;
			$this->view->api  = $apiInfo;
			$this->view->info = $info;
		}
	}
    
	/**
	 * 查询产品信息
	 */
	public function showinfoAction(){
		$productId =$this->_request->getParam('id');
		$productId = intval($productId);
		if($productId) {
			$product = new Model_MakeProduct();
			$info = $product->queryProductInfo($productId);
			if(!empty($info['langs'])) {
				$langsArr = explode(',', $info['langs']);
				$newLangArr =  array();
				foreach ($langsArr as $lang) {
					$newLangArr[] = $this->view->getHelper('t')->t($lang);
				}
				$info['langs'] = implode(',', $newLangArr);
			}
			$apiInfo = $this->getInvokeArg('bootstrap')->getOption('api');
			$this->view->api  = $apiInfo;
			$this->view->info = $info;
		}
	}
	/**
	 * 管理产品
	 */
	public function managerAction(){
		$post = ZendX_Validate::factory($_POST);
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout()->disableLayout();
		$post->rules(
				'is_ez_app',
				array(
						array('not_empty')
				)
		);
		$post->rules(
				'control',
				array(
						array('not_empty')
				)
		);
		$post->rules(
				'remark',
				array(
						array('max_length',array(':value',100))
				)
		);
		if($post->check()) {
			//禁用，启用
			if($_POST['control'] == '1') {
				$status = Model_Product::STATUS_ENABLE;
			} else {
				$status = Model_Product::STATUS_DISABLE;
			}
            $statusEmail = $statusSms = 0;
            if(isset($_POST['message']) && !empty($_POST['message'])){
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
					'into_ezapp' => $_POST['is_ez_app'],
                    'inform_status' => $informStatus
			);
			try {
				$publicProduct = new Model_Product();
				$publicProduct->begin_transaction();
				$where = $publicProduct->get_db()->quoteInto("id=?", $_POST['product_id']);
				$publicProduct->update($data, $where);
				$makeProduct = new Model_MakeProduct();
				$makeProduct->update(array('into_ezapp' => $_POST['is_ez_app']), $where);
				// $publicProduct->commit();
				if(!empty($_POST['message'])) {
					$select = $publicProduct->select()->from('EZ_PRODUCT', 'enterprise_id')->where('id=?', $_POST['product_id']);
					$enterpriseId = $publicProduct->get_db()->fetchOne($select);
					if($enterpriseId) {
						$enterprise = new Model_Enterprise();
						$enterpriseInfo = $enterprise->getFieldsById(array('name', 'mobile', 'email'), $enterpriseId);
					}
					if($status == Model_Product::STATUS_ENABLE) {
						//审核成功
						$info = $this->view->getHelper('t')->t('check_success_info');
					} else if($status == Model_Product::STATUS_DISABLE) {
						//审核失败
						$info = $this->view->getHelper('t')->t('check_failed_info');
					}
                    $resultEmail = $resultSms = 0;
					foreach ($_POST['message'] as $v) {
						if($v == 'email') {
							if(!empty($enterpriseInfo['email'])) {
								$to_address =  array(array('email' => $enterpriseInfo['email'], 'name' => $enterpriseInfo['name']));
								$result = ZendX_Tool::sendEmail($info, $to_address, '遥控e族审核信息');
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
                    $publicProduct->update($data, $where);
				}
                $publicProduct->commit();
                
			} catch (Exception $e) {
			    throw new Zend_Exception('操作产品状态失败');	
			}
        }
		header("location:" . $_SERVER['HTTP_REFERER']);
		exit;
	}
	
	/**
	 * 查询产品信息
	 */
	public function getinfoAction(){
		$productId =$this->_request->getParam('id');
		$productId = intval($productId);
		if($productId) {
			$product = new Model_MakeProduct();
			$info = $product->queryProductInfo($productId);
			Common_Protocols::generate_json_response($info);
		} else {
			Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED);
		}
		exit;
	}
    
	/**
	 * 审核
	 */
	public function checkAction(){
		$post = ZendX_Validate::factory($_POST);
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout()->disableLayout();
		$post->rules(
				'product_id',
				array(
						array('not_empty')
				)
		);
		$post->rules(
				'pass',
				array(
						array('not_empty')
				)
		);
		$post->rules(
				'remark',
				array(
						array('max_length',array(':value',120))
				)
		);
		if($post->check()) {
			$user_info = Common_Auth::getUserInfo();
			//禁用，启用
			if($_POST['pass'] == '1') {
				$status = Model_MakeProduct::STATUS_AUDIT_SUCESS;
			} else {
				$status = Model_MakeProduct::STATUS_AUDIT_FAILED;
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
					'audit_user' => $user_info['user_name'],
					'status' => $status,
					'remark' => $this->_request->getPost('remark',''),
					'into_ezapp' => 1,
					'audit_time'=>date('Y-m-d H:i:s'),
                    'inform_status' => $informStatus
			);
           
            // 需要增加into_app的判断，如果是新增则为1，如果已存在则保持不变
            $publicProduct = new Model_Product();
            $info = $publicProduct->getFieldsById('into_ezapp', $_POST['product_id']);
            if(is_array($info) && isset($info['into_ezapp'])){
                $data['into_ezapp'] = $info['into_ezapp'];
            }
            
			$publicProduct = new Model_MakeProduct();
			try {
				$publicProduct->begin_transaction();
				//更新记录
				$where = $publicProduct->get_db()->quoteInto("id=?", $_POST['product_id']);
				$publicProduct->update($data, $where);
                
                $resultEmail = $resultSms = 0;
				//发送消息
				if(!empty($_POST['message'])) {
					$select = $publicProduct->select()->from('EZ_MAKE_PRODUCT', 'enterprise_id')->where('id=?', $_POST['product_id']);
					$enterpriseId = $publicProduct->get_db()->fetchOne($select);
					if($enterpriseId) {
						$enterprise = new Model_Enterprise();
						$enterpriseInfo = $enterprise->getFieldsById(array('name', 'mobile', 'email'), $enterpriseId);
					}
					if($status == Model_MakeProduct::STATUS_AUDIT_SUCESS) {
						//审核成功
						$info = $this->view->getHelper('t')->t('check_product_success_info');
					} else if($status == Model_MakeProduct::STATUS_AUDIT_FAILED) {
						//审核失败
						$info = $this->view->getHelper('t')->t('check_product_failed_info');
					}
					$info = $this->_request->getPost('remark',$info);
					foreach ($_POST['message'] as $v) {
						if($v == 'email') {
							if(!empty($enterpriseInfo['email'])) {
								$to_address =  array(array('email' => $enterpriseInfo['email'], 'name' => $enterpriseInfo['name']));
								$result = ZendX_Tool::sendEmail($info, $to_address, '遥控e族审核信息');
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
                    $informResult = $resultEmail+$resultSms;
                    $newData = array(
                        'inform_result' => $informResult
                    );
                    $publicProduct->update($newData, $where);
				}
				$publicProduct->commit();
				$res = array('status'=>200,'msg'=>'');
				if($status == Model_MakeProduct::STATUS_AUDIT_SUCESS) {
					//调用打包机
					$client = new ZendX_GearmanClient();
					$res = $client->call('Product', 'publish', array('product_id' =>$_POST['product_id']));
				}
				
				if($res['status'] == 200) {
					return Common_Protocols::generate_json_response();
				}
				return Common_Protocols::generate_json_response(null,Common_Protocols::ERROR, $res['msg']);
			} catch (Exception $e) {
				$publicProduct->rollback();
				return Common_Protocols::generate_json_response(null,Common_Protocols::ERROR);
			}
		}
		return Common_Protocols::generate_json_response(null,Common_Protocols::VALIDATE_FAILED);
	}
	/**
	 * 查询待审核的产品数目
	 */
	public function needchecknumAction(){
		$product = new Model_MakeProduct();
		$info = $product->needCheckProduct();
		return Common_Protocols::generate_json_response($info);
	}
	/**
	 * 重新发布
	 */
	public function republishAction(){
		$productId =$this->_request->getParam('id');
		$productId = intval($productId);
		if(empty($productId)) {
			return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED);
		}
		$product = new Model_MakeProduct();
		 try {
		 	$client = new ZendX_GearmanClient();
		 	$res = $client->call('Product', 'publish', array('product_id' =>$productId));
		 	if($res['status'] == 200) {
		 		$where = $product->get_db()->quoteInto('id=?', $productId);
		 		$product->update(array('status' =>'audit_success'), $where);
		 		return Common_Protocols::generate_json_response();
		 	} else {
		 		return Common_Protocols::generate_json_response(null, Common_Protocols::ERROR, $client['msg']);
		 	} 
		 } catch (Exception $e) {
		 	return Common_Protocols::generate_json_response(null, Common_Protocols::ERROR, '内部错误');
		 }
	}
	/**
	 * 下载二维码
	 */
	public function downloadqrcodeAction(){
		$productId =$this->_request->getParam('id');
		$productId = intval($productId);
		if($productId) {
			$apiInfo = $this->getInvokeArg('bootstrap')->getOption('api');
			$this->view->api  = $apiInfo;
			$fileName = $apiInfo['server'] . '/intranet/generate_qr_code/?width=100&height=100&content=' . $productId;
			$imageInfo = getimagesize($fileName);
			$imgtype = 'jpg';
			switch ($imageInfo[2]) {
				case 1:
					$imgtype = "GIF";
					break;
				case 2:
					$imgtype = "JPG";
					break;
				case 3:
					$imgtype = "PNG";
					break;
			}
			Header("Content-type: application/octet-stream");
			Header("Accept-Ranges: bytes");
			Header("Content-Disposition: attachment; filename=". time() . ".$imgtype");
			echo file_get_contents($fileName);
			exit;
		}
	}

	
	
}