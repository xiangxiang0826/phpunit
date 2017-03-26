<?php
/**
 * 企业E族控制器
 * @author zhouxh
 *
 */
class Product_EnappController extends ZendX_Controller_Action
{
	
	/**
	 * 列表查询
	 */
	public function indexAction(){
		$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$rows = isset($_GET['rows']) ? intval($_GET['rows']) : 10;
		$offset = ($page-1)*$rows;
		$query = "";
		$search = $this->_request->getParam("search");
		if(empty($search)) {
			$search['platform'] = Model_EnterpriseApp::PLANTFORM_ANDROID;
		}
		$enappModel = new Model_EnterpriseApp();
		$arrQuery = array();
		if(!empty($search['platform'])) {
		   $queryString = $enappModel->get_db()->quoteInto("a.platform =?", $search['platform']);
			array_push($arrQuery, $queryString);
		}
		if(!empty($arrQuery)){
			$query = implode(" AND ", $arrQuery);
		}
		$cfg = ZendX_Config::get("application","production");
		$baseUrl = $cfg['images']['baseUrl']; 
		$dataList = $enappModel->getList($query, $offset, $rows);
		$result = array();
		$result["total"] = $enappModel->getTotal($query);
		$result['rows'] = $dataList;
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($result["total"]));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($rows);
		$this->view->pagenation = $pagenation;
		$this->view->search = $search;
		$this->view->baseUrl = $baseUrl;
		$this->view->results =  $result;
	}
	
	/**
	 * 查看详情
	 */
	public function detailAction(){
		$appId = $this->_request->getParam('appid');
		$lang = $this->_request->getParam('lang', 'zh-cn');
		if($appId) {
		    $uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
		    $imagesConfig = $this->getInvokeArg('bootstrap')->getOption('images');
			$appModel = new Model_EnterpriseApp();
			$appInfo = $appModel->getAppInfoByid($appId, $lang);
			//查询最新版本信息
			if($appInfo['upgrade_type_id']) {
				$version = $appModel->findLatestVersion($appInfo['upgrade_type_id']);		
				if($version) {
				   $appInfo = $appInfo + $version;
				}
			}
			$this->view->appinfo = $appInfo;
			$this->view->uploadServer = $uploadConfig;
			$this->view->imagesServer = $imagesConfig;
			if($appInfo['type'] == 'develop') {
				$this->getHelper('viewRenderer')->setNoRender();
				echo $this->view->render('enapp_detail_dev.phtml');
			} else {
				// 编辑模式
				$langs = $appModel->getLangList($appId);
				$this->view->langs = $langs;
				$this->view->currentLang = $lang;
			}
			
		}
	}
	/**
	 * 下载最新版本
	 */
	public function downloadAction(){
		$this->getHelper('viewRenderer')->setNoRender();
		$upgradeId = $this->getRequest()->getParam('upgrade_type_id');
		$upgradeId = intval($upgradeId);
		if($upgradeId) {
			$enappModel = new Model_EnterpriseApp();
			$version = $enappModel->findLatestVersion($upgradeId);
			if(!empty($version['file_path'])) {
				$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
				try {
					$filePath = $uploadConfig['baseUrl'] . $version['file_path'];
					return header("Location: {$filePath}");
				} catch (Exception $e) {
					throw new Zend_Exception($e->getMessage());
				}
			}
		}
	}
	/**
	 * 查询产品数
	 */
	public function getproductsAction(){
		$enterpriseId = $this->getRequest()->getParam('enterprise_id');
		if($enterpriseId) {
			$model = new Model_Enterprise();
			$number = $model->getProductNumber($enterpriseId);
			Common_Protocols::generate_json_response($number);
		} else {
			Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED);
		} 
		exit;
	}
	
	
}