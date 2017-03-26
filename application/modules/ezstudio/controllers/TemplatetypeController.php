<?php
/* 
 * 模板类型管理
 * created by liujd@wondershare.com
 *  */
class Ezstudio_TemplatetypeController extends ZendX_Controller_Action
{
	const TYPE_LIMIT = 100;
	public function init() {
		parent::init();
		$this->status_map = array(
				Model_TemplateType::STATUS_ENABLE=>'生效',
				Model_TemplateType::STATUS_DISABLE=>'失效',
		);
	}
	
	/* 模板类型列表 */
	public function indexAction() {
		$search_data = $this->getRequest()->get('search');
		$page = $this->_request->get("page");
		$page = !empty($page) ? $page : 1;
		$offset = ($page-1) * $this->page_size;
		$template_type_model = new Model_TemplateType();
		$type_list = $template_type_model->GetList($search_data, $offset,$this->page_size);
		$this->view->type_list = $type_list['counts'] ? $type_list['list'] : array();
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($type_list['counts']));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($this->page_size);
		$this->view->pagenation = $pagenation;
		$this->view->search = $search_data;
		$this->view->status_map = $this->status_map;
	}
	
	/**
	 * 增加控件类型类型
	 * */
	public function addAction() {
		/* post方法提交，则保存数据 */
		if($this->getRequest()->isPost()) {
			if(!$this->checkTypePost()) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('valid_input'));
			}
			$template_type_model = new Model_TemplateType();
			$row = $template_type_model->getRowByField(array('id'),array('name'=>$_POST['name']));
			if($row) {
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('control_type_exists'));
			}
			$template_type_model->insert($_POST);
			return Common_Protocols::generate_json_response();
		}
		$this->view->status_map = $this->status_map;
	}
	
	/* 功能类型详情 */
	public function detailAction() {
		$id =  $this->getRequest()->get('id');
		$template_type_model = new Model_TemplateType();
		$data = $template_type_model->getFieldsById(array('id','name','remark','status'),$id);
		$this->view->control_type_info = $data;
		$this->view->status_map = $this->status_map;
	}
	
	/* 更新控件类型 */
	public function updateAction() {
		if($this->getRequest()->isPost()) {
			if(!$this->checkTypePost()) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('valid_input'));
			}
			$id = $this->getRequest()->getPost('id');
			$template_type_model = new Model_TemplateType();
			$template_type_model->update($_POST,array("id='{$id}'"));
			return Common_Protocols::generate_json_response();
		}
	}
	
	/* 删除控件类型*/
	public function deleteAction() {
		$id =  $this->getRequest()->getPost('id');
		$template_type_model = new Model_TemplateType();
		$result = $template_type_model->delete($id);
		return $result ? Common_Protocols::generate_json_response() : Common_Protocols::generate_json_response(NULL, Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('has_template_in_type'));
	}
	
	/* 增加、更新控件类型是输入检测 */
	private function checkTypePost() {
		$post = ZendX_Validate::factory($_POST)->labels(array(
				'name' => 'name',
				'remark' => 'action',
		));
		$post->rules(
				'name',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',150))
				)
		);
		$post->rules(
				'remark',
				array(
						array('min_length',	array(':value',	2)),
						array('max_length',array(':value',150))
				)
		);
		return $post->check();
	}
}