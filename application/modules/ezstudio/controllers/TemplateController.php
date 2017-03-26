<?php
/* 
 * 模板管理模块
 * Created By liujd@wondershare.com
 *  */
class Ezstudio_TemplateController extends ZendX_Controller_Action
{
	const TYPE_LIMIT = 100;
	public function init() {
		parent::init();
		$this->status_map = array(
				Model_TemplateType::STATUS_ENABLE=>'生效',
				Model_TemplateType::STATUS_DISABLE=>'失效',
		);
	}
	
	/* 模板列表 */
	public function indexAction() {
		$search_data = $this->getRequest()->get('search');
		$page = $this->_request->get("page");
		$page = !empty($page) ? $page : 1;
		$offset = ($page-1) * $this->page_size;
		$template_model = new Model_Template();
		$control_list = $template_model->GetList($search_data, $offset,$this->page_size);
		$this->view->control_list = $control_list['counts'] ? $control_list['list'] : array();
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($control_list['counts']));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($this->page_size);
		$category_list = array();
		$category = new Model_ProductCate();
		$tmp_list = $category->getListData();
		foreach($tmp_list as $li) {
			$category_list[$li['id']] = $li;
		}
		$this->view->category = $category_list;
		$template_type_model = new Model_TemplateType();
		$type_list = $template_type_model->GetList(array(), 0, self::TYPE_LIMIT);
		$type_map = array();
		if($type_list['counts']) {
			foreach($type_list['list'] as $type) {
				$type_map[$type['id']] = $type;
			}
		}
		$this->view->type_map = $type_map;
		$this->view->pagenation = $pagenation;
		$this->view->search = $search_data;
		$this->view->status_map = $this->status_map;
	}
	
	/**
	 * 增加模板
	 *   */
	public function addAction() {
		/* post方法提交，则保存数据 */
		if($this->getRequest()->isPost()) {
			$check = $this->checkPost();
			if($check !== true) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $check);
			}
			$template_model = new Model_Template();
			$row = $template_model->getRowByField(array('id'),array('name'=>$_POST['name'],'template_type_id'=>$_POST['template_type_id']));
			if($row) {
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('template_exists'));
			}
			$user_info = Common_Auth::getUserInfo();
			$_POST['create_user'] = $user_info['id'];
			$template_model->insert($_POST);
			return Common_Protocols::generate_json_response();
		}
		$template_type_model = new Model_TemplateType();
		$type_list = $template_type_model->GetList(array(), 0, self::TYPE_LIMIT);
		$type_map = array();
		foreach($type_list['list'] as $type) {
			$type_map[$type['id']] = $type;
		}
		$category = new Model_ProductCate();
		$this->view->category = $category->dumpTree();
		$this->view->type_map = $type_map;
		$this->view->status_map = $this->status_map;
	}
	
	/* 模板详情 */
	public function detailAction() {
		$id =  $this->getRequest()->get('id');
		$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
		$template_model = new Model_Template();
		$template_info = $template_model->getFieldsById(array('id','name','template_type_id','product_category_id','icon','remark','save_path','status'),$id);
		$category = new Model_ProductCate();
		$this->view->category = $category->dumpTree();
		$template_type_model = new Model_TemplateType();
		$type_list = $template_type_model->GetList(array(), 0, self::TYPE_LIMIT);
		$type_map = array();
		foreach($type_list['list'] as $type) {
			$type_map[$type['id']] = $type;
		}
		$this->view->type_map = $type_map;
		$this->view->status_map = $this->status_map;
		$template_info['full_save_path'] = "{$uploadConfig['baseUrl']}{$template_info['save_path']}";
		$template_info['file_name'] = basename($template_info['save_path']);
		$template_info['full_icon'] = "{$uploadConfig['baseUrl']}{$template_info['icon']}";
		$this->view->template_info = $template_info;
	}
	
	/* 更新模板 */
	public function updateAction() {
		if($this->getRequest()->isPost()) {
			$check = $this->checkPost();
			if($check !== true) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $check);
			}
			$id = $this->getRequest()->getPost('id');
			$template_model = new Model_Template();
			$template_model->update($_POST,array("id='{$id}'"));
			return Common_Protocols::generate_json_response();
		}
	}
	
	/* 删除模板*/
	public function deleteAction() {
		$id =  $this->getRequest()->getPost('id');
		$template_model = new Model_Template();
		$template_model->delete($id);
		return Common_Protocols::generate_json_response();
	}

	/* 模板表单提交检测 */
	private function checkPost() {
		$post = ZendX_Validate::factory($_POST)->labels(array(
				'name' => 'name',
				'product_category_id' => 'Product Category_id',
				'template_type_id' => 'Template Type_id',
				'save_path' => 'Save Path',
				'icon' => 'Template Icon',
				'remark' => 'Remark',
		));
		$post->rules(
				'name',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
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
		$post->rules(
				'template_type_id',
				array(
						array('not_empty'),
						array('numeric')
				)
		);
		
		$post->rules(
				'save_path',
				array(
						array('not_empty'),
						array('min_length',array(':value', 5)),
						array('max_length',array(':value',255))
				)
		);
		$post->rules(
				'icon',
				array(
						array('not_empty'),
						array('min_length',	array(':value',	5)),
						array('max_length',array(':value',512))
				)
		);
		$post->rules(
				'remark',
				array(
						array('min_length',	array(':value',	5)),
						array('max_length',array(':value',512))
				)
		);
		$check = $post->check();
		if($check) return true;
		$errors = $post->errors('validate');
		return array_shift($errors);
	}
}