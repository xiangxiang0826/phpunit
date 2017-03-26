<?php
/* 
 * 功能管理模块
 *  */
class Ezstudio_ActionController extends ZendX_Controller_Action
{
	
	public function indexAction() {
		$search_data = $this->getRequest()->get('search');
		$page = $this->_request->get("page");
		$page = !empty($page) ? $page : 1;
		$offset = ($page-1) * $this->page_size;
		$product_action_model = new Model_ProductAction();
		$action_list = $product_action_model->GetList($search_data, $offset,$this->page_size);
		$this->view->action_list = $action_list['counts'] ? $action_list['list'] : array();
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($action_list['counts']));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($this->page_size);
		$category_map = array();
		$productCate = new Model_ProductCate();
		$category_tree = $productCate->dumpTree();
		foreach($category_tree as $category) {
			$category_map[$category['id']] = $category;
		}
		$this->view->category_map = $category_map;
		$this->view->category = $category_tree;
		$this->view->pagenation = $pagenation;
		$this->view->search = $search_data;
	}
	
	/**  
	 * 增加功能
	 *   */
	public function addAction() {
		/* post方法提交，则保存数据 */
		if($this->getRequest()->isPost()) {
			if(!$this->checkPost()) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
			$product_action_model = new Model_ProductAction();
            // 添加校验规则，同一类产品的功能名须确保唯一，不同类产品功能名可以重复；
            $queryArr = array('name'=>$_POST['name'],'action'=>$_POST['action']);
            $product_category_id = $this->getRequest()->getParam('product_category_id');
            if($product_category_id){
                $queryArr['product_category_id'] = $product_category_id;
            }
            $queryArrName = $queryArr;
            $queryArrAction = $queryArr;
            unset($queryArrName['action']);
            unset($queryArrAction['name']);
            // print_r($product_category_id);exit(',ln='.__line__);
			$rowName = $product_action_model->getRowByField(array('id'), $queryArrName);
            $rowAction = $product_action_model->getRowByField(array('id'), $queryArrAction);
			if($rowName) {
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('name_exists'));
			}
			if($rowAction) {
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('action_exists'));
			}
			
			$product_action_model->insert($_POST);
			return Common_Protocols::generate_json_response();
		}
		$productCate = new Model_ProductCate();
		$category_tree = $productCate->dumpTree();
		$this->view->category = $category_tree;
	}
	
	/* 删除action */
	public function deleteAction() {
		$id =  $this->getRequest()->getPost('id');
		$product_action_model = new Model_ProductAction();
		$product_action_model->delete($id);
		return Common_Protocols::generate_json_response();
	}
	
	/* 功能详情 */
	public function detailAction() {
		$id =  $this->getRequest()->get('id');
		$product_action_model = new Model_ProductAction();
		$data = $product_action_model->getFieldsById(array('id','product_category_id','name','action','description','status'),$id);
		$this->view->action_info = $data;
		$productCate = new Model_ProductCate();
		$category_tree = $productCate->dumpTree();
		$this->view->category = $category_tree;
	}
	
	/* 更新action */
	public function updateAction() {
		if($this->getRequest()->isPost()) {
			if(!$this->checkPost()) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
			$id = $this->getRequest()->getPost('id');
            // 添加检测是否和其它已经存在的名称和功能标识冲突
            // 添加校验规则，同一类产品的功能名须确保唯一，不同类产品功能名可以重复；
            $queryArr = array('name'=>$_POST['name'],'action'=>$_POST['action']);
            $product_category_id = $this->getRequest()->getParam('product_category_id');
            if($product_category_id){
                $queryArr['product_category_id'] = $product_category_id;
            }
            $queryArrName = $queryArr;
            $queryArrAction = $queryArr;
            unset($queryArrName['action']);
            unset($queryArrAction['name']);
            // print_r($product_category_id);exit(',ln='.__line__);
            $product_action_model = new Model_ProductAction();
			$rowName = $product_action_model->getRowByFieldWithAddition(array('id'), $queryArrName, '`id` != "'.$id.'"');
            $rowAction = $product_action_model->getRowByFieldWithAddition(array('id'), $queryArrAction, '`id` != "'.$id.'"');
			if($rowName) {
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('name_exists'));
			}
			if($rowAction) {
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('action_exists'));
			}
			$product_action_model->update($queryArr,array("id='{$id}'"));
			return Common_Protocols::generate_json_response();
		}
	}
	
	private function checkPost() {
		$post = ZendX_Validate::factory($_POST)->labels(array(
				'name' => 'name',
				'action' => 'action',
				'description' => 'description',
				'product_category_id' => 'product_category_id',
		));
		$post->rules(
				'name',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',10))
				)
		);
		$post->rules(
				'action',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',50))
				)
		);
		$post->rules(
				'description',
				array(
						array('min_length',	array(':value',	6)),
						array('max_length',array(':value',255))
				)
		);
		$post->rules(
				'product_category_id',
				array(
						array('not_empty'),
						array('numeric')
				)
		);
		return $post->check();
	}
	
}