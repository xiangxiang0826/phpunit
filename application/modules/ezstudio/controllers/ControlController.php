<?php
/* 
 * 控件管理模块
 *  */
class Ezstudio_ControlController extends ZendX_Controller_Action
{
	const TYPE_LIMIT = 100;
	public function init() {
		parent::init();
		$this->status_map = array(
				Model_ControlType::STATUS_ENABLE=>'生效',
				Model_ControlType::STATUS_DISABLE=>'失效',
		);
	}
	
	/* 控件列表 */
	public function indexAction() {
		$search_data = $this->getRequest()->get('search');
		$page = $this->_request->get("page");
		$page = !empty($page) ? $page : 1;
		$offset = ($page-1) * $this->page_size;
		$control_model = new Model_Control();
		$control_list = $control_model->getList($search_data, $offset,$this->page_size);
		$this->view->control_list = $control_list['counts'] ? $control_list['list'] : array();
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($control_list['counts']));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($this->page_size);
		$control_type_model = new Model_ControlType();
		$type_list = $control_type_model->getList(array(), 0, self::TYPE_LIMIT);
		$type_map = array();
		foreach($type_list['list'] as $type) {
			$type_map[$type['id']] = $type;
		}
		$this->view->type_map = $type_map;
		$this->view->pagenation = $pagenation;
		$this->view->search = $search_data;
		$this->view->status_map = $this->status_map;
	}
	
	/* 控件类型列表 */
	public function typeAction() {
		$search_data = $this->getRequest()->get('search');
		$page = $this->_request->get("page");
		$page = !empty($page) ? $page : 1;
		$offset = ($page-1) * $this->page_size;
		$control_type_model = new Model_ControlType();
		$type_list = $control_type_model->getList($search_data, $offset,$this->page_size);
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
	public function addtypeAction() {
		/* post方法提交，则保存数据 */
		if($this->getRequest()->isPost()) {
			if(!$this->checkTypePost()) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
			$control_type_model = new Model_ControlType();
			$row = $control_type_model->getRowByField(array('id'),array('name'=>$_POST['name']));
			if($row) {
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('control_type_exists'));
			}
			$control_type_model->insert($_POST);
			return Common_Protocols::generate_json_response();
		}
		$this->view->status_map = $this->status_map;
	}
	
	/* 功能类型详情 */
	public function typedetailAction() {
		$id =  $this->getRequest()->get('id');
		$control_type_model = new Model_ControlType();
		$data = $control_type_model->getFieldsById(array('id','name','remark','status'),$id);
		$this->view->control_type_info = $data;
		$this->view->status_map = $this->status_map;
	}
	
	/* 更新控件类型 */
	public function updatetypeAction() {
		if($this->getRequest()->isPost()) {
			if(!$this->checkTypePost()) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
			$id = $this->getRequest()->getPost('id');
			$control_type_model = new Model_ControlType();
			$control_type_model->update($_POST,array("id='{$id}'"));
			return Common_Protocols::generate_json_response();
		}
	}
	
	/**
	 * 增加控件
	 *   */
	public function addAction() {
		/* post方法提交，则保存数据 */
		if($this->getRequest()->isPost()) {
			$check = $this->checkPost();
			if($check !== true) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $check);
			}
			$control_model = new Model_Control();
			$row = $control_model->getRowByField(array('id'),array('name'=>$_POST['name']));
			if($row) {
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('control_exists'));
			}
			$row = $control_model->getRowByField(array('id'),array('label'=>$_POST['label']));
			if($row) {
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('control_label_exists'));
			}
			$user_info = Common_Auth::getUserInfo();
			$_POST['cuser'] = $user_info['id'];
			$_POST['html_code'] = json_encode(array('dom'=>$this->getRequest()->getPost('dom_code'),'selected'=>$this->getRequest()->getPost('selected_code')));
			unset($_POST['dom_code']);
			unset($_POST['selected_code']);
			$control_model->insert($_POST);
			return Common_Protocols::generate_json_response();
		}
		$control_type_model = new Model_ControlType();
		$type_list = $control_type_model->getList(array(), 0, self::TYPE_LIMIT);
		$type_map = array();
		foreach($type_list['list'] as $type) {
			$type_map[$type['id']] = $type;
		}
		$this->view->type_map = $type_map;
	}
	
	/* 功能详情 */
	public function detailAction() {
		$id =  $this->getRequest()->get('id');
		$uploadConfig = $this->getInvokeArg('bootstrap')->getOption('upload');
		$control_model = new Model_Control();
		$data = $control_model->getFieldsById(array('id','name','label','icon','control_type_id','html_code','description','js_code'),$id);
		$control_type_model = new Model_ControlType();
		$type_list = $control_type_model->getList(array(), 0, self::TYPE_LIMIT);
		$type_map = array();
		foreach($type_list['list'] as $type) {
			$type_map[$type['id']] = $type;
		}
		$data['html_code'] = json_decode($data['html_code'], true);
		$this->view->control_info = $data;
		$this->view->type_map = $type_map;
		$this->view->upload_config = $uploadConfig;
	}
	
	/* 更新action */
	public function updateAction() {
		if($this->getRequest()->isPost()) {
			$check = $this->checkPost();
			if($check !== true) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $check);
			}
			$id = $this->getRequest()->getPost('id');
			$control_model = new Model_Control();
			$user_info = Common_Auth::getUserInfo();
			$_POST['muser'] = $user_info['id'];
			$_POST['mtime'] = date('Y-m-d H:i:s');
			$_POST['html_code'] = json_encode(array('dom'=>$this->getRequest()->getPost('dom_code'),'selected'=>$this->getRequest()->getPost('selected_code')));
			unset($_POST['dom_code']);
			unset($_POST['selected_code']);
			$control_model->update($_POST,array("id='{$id}'"));
			return Common_Protocols::generate_json_response();
		}
	}
	
	/* 删除控件*/
	public function deleteAction() {
		$id =  $this->getRequest()->getPost('id');
		$control_model = new Model_Control();
		$control_model->delete($id);
		return Common_Protocols::generate_json_response();
	}
	
	/* 删除控件类型*/
	public function deletetypeAction() {
		$id =  $this->getRequest()->getPost('id');
		$control_type_model = new Model_ControlType();
		$result = $control_type_model->delete($id);
		return $result ? Common_Protocols::generate_json_response() : Common_Protocols::generate_json_response(NULL, Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('has_control_in_type'));
	}
	
	/* 控件表单提交检测 */
	private function checkPost() {
		$post = ZendX_Validate::factory($_POST)->labels(array(
				'name' => 'name',
				'label' => 'label',
				'description' => 'description',
				'html_code' => 'Html Code',
				'js_code' => 'Js Code',
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
				'label',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',32))
				)
		);
		$post->rules(
				'dom_code',
				array(
						array('not_empty'),
						array('min_length',array(':value', 5))
				)
		);
		
		$post->rules(
				'js_code',
				array(
						array('min_length',array(':value', 5))
				)
		);
		$post->rules(
				'description',
				array(
						array('min_length',	array(':value',	6)),
						array('max_length',array(':value',512))
				)
		);
		$post->rules(
				'control_type_id',
				array(
						array('not_empty'),
						array('numeric')
				)
		);
		$post->rules(
				'icon',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value',256))
				)
		);
		$check = $post->check();
		if($check) return true;
		$errors = $post->errors('validate');
		return array_shift($errors);
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