<?php

/* 
 * 系统模块
 * 
 */
class Enterprise_ServiceController extends ZendX_Controller_Action{
    
	public function indexAction() {
		$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$rows = isset($_GET['rows']) ? intval($_GET['rows']) : $this->page_size;
		$offset = ($page-1)*$rows;
		$query = 'status != "deleted"';
		$result = array();
        $modelApiEnterpriseExtendGrant = new Model_ApiEnterpriseExtendGrant();
		$result["total"] = $modelApiEnterpriseExtendGrant->getTotal($query);
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($result["total"]));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($rows);
		$items = $modelApiEnterpriseExtendGrant->getList($query, $offset, $rows);
		$result['rows'] = $items;
		$this->view->results =  $result;
		$this->view->pagenation = $pagenation;
	}
    
	/**  
	 * 增加配置
         */
	public function addAction() {
            // post方法提交，则保存数据
	    if($this->getRequest()->isPost()) {
                if(!$this->_checkPost()) {
                        return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
                }
                $appKey = $this->getRequest()->getParam('app_key');
                $productId = $this->getRequest()->getParam('product_id');
                $callbackUrl = $this->getRequest()->getParam('callback_url');
                $status = $this->getRequest()->getParam('status');
                $modelApiEnterpriseExtendGrant = new Model_ApiEnterpriseExtendGrant();
                $rowProductId = $modelApiEnterpriseExtendGrant->getRowByField(array('id'), array('product_id'=>$productId));
                if(!$rowProductId) {
                    $insert = array(
                        'app_key' => $appKey,
                        'product_id' => $productId,
                        'callback_url' => $callbackUrl,
                        'status' => $status
                    );
                    $modelApiEnterpriseExtendGrant->insert($insert);
                    return Common_Protocols::generate_json_response();
                }else{
                    if($rowProductId){
                        return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('api_service_is_exists'));
                    }
                    return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('service_exists'));
                }
	      }
	}
    
/**  
	 * 编辑权限
     */
	public function editAction() {
		// post方法提交，则保存数据
        $id = $this->getRequest()->getParam('id');
        $condition = array(
            'D.id' => $id
        );
        $modelApiEnterpriseExtendGrant = new Model_ApiEnterpriseExtendGrant();
        $items = $modelApiEnterpriseExtendGrant->getItemsByWhere($condition);
        $item = array();
        if(isset($items[0]) && !empty($items[0])){
            $item = $items[0];
        }
        
		if($this->getRequest()->isPost()) {
			if(!$this->_checkPost()) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
            $appKey = $this->getRequest()->getParam('app_key');
            $productId = $this->getRequest()->getParam('product_id');
            $callbackUrl = $this->getRequest()->getParam('callback_url');
            $status = $this->getRequest()->getParam('status');
            if($productId == $item['product_id']) {
                $rowProductId = FALSE;
            }else{
                $rowProductId = $modelApiEnterpriseExtendGrant->getRowByField(array('id'), array('product_id'=>$productId));
            }
			if(!$rowProductId) {
                $update = array(
                    'app_key' => $appKey,
                    'product_id' => $productId,
                    'callback_url' => $callbackUrl,
                    'status' => $status
                );
                $where = 'id="'.$id.'"';
                $modelApiEnterpriseExtendGrant->update($update, $where);
                return Common_Protocols::generate_json_response();
            }else{
                return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('service_exists'));
            }
		}
        
        $this->view->requestId =  $id;
        $this->view->item =  $item;
	}
	
	/**
     *  更新action,主要是修改状态，启用，禁用，删除
     */
	public function updateAction() {
        $id = $this->getRequest()->getParam('id');
        $status = $this->getRequest()->getParam('status');
        if(!$id || !$status) {
            return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED);
        }
        $modelApiEnterpriseExtendGrant = new Model_ApiEnterpriseExtendGrant();
        $update = array('status' => $status);
        $modelApiEnterpriseExtendGrant->update($update, array("id='{$id}'"));
        $this->redirect($this->view->url(array('controller'=>'service', 'action'=>'index')));
    }
    
    /**
     * 检查提交的数据是否合法
     * 
     * @return bool
     */
    private function _checkPost() {
		$post = ZendX_Validate::factory($_POST)->labels(array(
				'app_key' => 'app_key',
				'product_id' => 'product_id',
                                'callback_url' => 'callback_url',
				'status' => 'status',
		));
		$post->rules(
				'app_key',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',32))
				)
		);
		$post->rules(
				'product_id',
				array(
						array('not_empty'),
						array('max_length',array(':value',11))
				)
		);
		$post->rules(
				'callback_url',
				array(
						array('not_empty'),
						array('min_length',array(':value',10)),
                        array('max_length',array(':value',255))
				)
		);
  		$post->rules(
				'status',
				array(
                                    array('in_array', array(':value', array('enable','disable')))
				)
		);
		return $post->check();
	}
}