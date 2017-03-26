<?php

/**
 * 页面控制器
 *
 * @author 刘通
 */
class Template_PageController extends ZendX_Cms_Controller 
{
	function indexAction()
	{
		$tpl_id = (int)$this->_request->getParam('tpl_id');
		if(!$tpl_id)
		{
			Cms_Func::response(Cms_L::_('tpl_id_error'));
		}
		
		$this->view->tpl_id = $tpl_id;
		
		//获取可搜索和可列表的字段
		$field_model = new Template_Models_Field();
		$this->view->search_fields = $field_model->getSearchFields($tpl_id);
		$this->view->list_fields = $field_model->getListFields($tpl_id);
		
		$tpl_model = new Template_Models_Template();
		$tpl_info = $tpl_model->getInfoById($tpl_id);
		
		//check地址
		$tpl_info['html_path'] = Cms_Template::getCheckHost($tpl_id);
		
		$this->view->tpl_info = $tpl_info;
		
		//获取entity_id的语言项
		$entity_i18n_name = Cms_Template_Config::getEntityId($tpl_info);
		$this->view->entity_i18n_name = $entity_i18n_name;
	}
	
	function dataAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout()->disableLayout();
		
		$tpl_id = $this->_request->getParam('tpl_id');
		
		$page = $this->_request->getPost('page', 1);
		$rows = $this->_request->getPost('rows', 15);
		$start = ($page - 1) * $rows;
		
		$sort = $this->_request->getPost('sort', 'page_id');
		$order = $this->_request->getPost('order', 'desc');
		
		$search = $this->_request->getParam('S');
		
		$page_model = new Template_Models_Page();
		$data = $page_model->setTable($tpl_id)->getList($this->site_id, $start, $rows, $sort, $order, $search);
		if($data['total']) {
			foreach($data['rows'] as &$row) {
				foreach($row as &$r) {
					$r = mb_strimwidth(strip_tags($r),0,100,'...');
				}
			}
		}
		echo json_encode($data);
	}
	
	function editAction()
	{
		$tpl_id = (int) $this->_request->getParam('tpl_id');
		$page_id = (int) $this->_request->getParam('page_id');
		
		//获取模版信息
		$tpl_model = new Template_Models_Template();
		$tpl_info = $tpl_model->getInfoById($tpl_id);
		
		if($page_id)
		{
			//获取页面信息
			$page_model = new Template_Models_Page();
			$page_info = $page_model->setTable($tpl_id)->getInfoById($page_id);
			$this->view->page_info = $page_info;
		}
		else	//从其他模块连接过来的添加信息，传过来entity_id
		{
			$entity_id = (int) $this->_request->getParam('entity_id');
			if($entity_id > 0)
			{
				$this->view->entity_id = $entity_id;
			}
			
			$data = $this->_request->getParam('data');
			if($data)
			{
				$this->view->data = $data;
			}
		}
		
		//获取扩展字段信息
		$field_model = new Template_Models_Field();
		$field_info = $field_model->getInfoByTid($tpl_id);
		
		if($field_info)
		{
			foreach($field_info as &$field)
			{
				if(!empty($page_info[$field['field_name']]))
				{
					$field['input_value'] = $page_info[$field['field_name']];
				}
				
				$field['input'] = Template_Objects_Input::getObject($field['input_type'])->getInput($field);
			}
			
			$this->view->field_info = $field_info;
		}

		$this->view->tpl_id = $tpl_id;
		$this->view->tpl_info = $tpl_info;
		
		//获取entity_id的语言项
		$this->view->entity_i18n_name = Cms_Template_Config::getEntityId($tpl_info);
		
		//判断站点类型，如果是ppc的，则给其开放默认页面选项
		$this->view->site_info = Cms_Site::getInfoById($this->site_id);
		
		//页面Js路径
		$this->view->js_file = "resources/js/template/{$tpl_info['module']}/{$tpl_info['type']}.js";
	}
	
	function saveAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout()->disableLayout();
		$tpl_id = (int) $this->_request->getPost('tpl_id');
		$data = $this->_request->getPost('data');
		$data = array_map('trim', $data);
		$data['url'] = '/' . trim($data['url'], '/');				//处理URL
		$data['keyword'] = trim($data['keyword'], ',');				//处理keyword
		$data['keyword'] = preg_replace(array('/[ ]{2,}/', '/[ ]+,/'), array(' ', ','), $data['keyword']);
		
		$page_model = new Template_Models_Page();
		$page_model->setTable($tpl_id);
		
		$hook = new Hooks_Page(array('site_id'=>$this->site_id, 'tpl_id'=>$tpl_id));
		
		//调用Hook
		if($data['page_id'])
		{
			$old_data = $page_model->getInfoById($data['page_id']);
			$hook->beforeEdit($old_data, $data);
		}
		else 
		{
			$hook->beforeAdd($data);
		}
		
		$page_id = $page_model->save($data);
		
		//调用Hook
		if($data['page_id'])
		{
			$hook->afterEdit($data);
		}
		else 
		{
			$data['page_id'] = $page_id;
			$hook->afterAdd($data);
		}
		
		$this->json_ok(Cms_L::_('action_ok'), array('id'=>$page_id));
	}
	
	function deleteAction()
	{
		
		$tpl_id = (int) $this->_request->getPost('tpl_id');
		$page_id = (int) $this->_request->getPost('page_id');
		
		$page_model = new Template_Models_Page();
		$page_model->setTable($tpl_id)->delete($page_id);
		
		//调用钩子
		$hook = new Hooks_Page(array('site_id'=>$this->site_id, 'tpl_id'=>$tpl_id));
		$hook->afterDelete($page_model->getInfoById($page_id));
		
		$this->json_ok('');
	}
	
	/**
	 * 控制页面状态
	 */
	function stateAction()
	{
		$tpl_id = (int) $this->_request->getPost('tpl_id');
		$page_id = (int) $this->_request->getPost('page_id');
		$state = $this->_request->getPost('status');
		
		$page_model = new Template_Models_Page();
		$page_model->setTable($tpl_id);
		
		$hook = new Hooks_Page(array('site_id'=>$this->site_id, 'tpl_id'=>$tpl_id));
		
		if($state == 'enable')
		{
			//调用钩子
			$hook->beforeAble($page_model->getInfoById($page_id));
			
			$page_model->able($page_id);
			
			//调用钩子
			$hook->afterAble($page_model->getInfoById($page_id));
		}
		else 
		{
			$page_model->disable($page_id);
			
			//调用钩子
			$hook->afterDisable($page_model->getInfoById($page_id));
		}
		$this->json_ok('');
	}
	
	//预览页面
	function previewAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout()->disableLayout();
		
		$tpl_id = (int) $this->_request->getParam('tpl_id');
		$page_id = (int) $this->_request->getParam('page_id');
		
		$create_object = new Cms_Template_Create($tpl_id);
		echo $create_object->preview($page_id);
	}
	
	//查询关键字重复度
	function dupAction()
	{
		$this->_helper->layout()->disableLayout();
		
		$tpl_id = (int) $this->_request->getParam('tpl_id');
		$data = $this->_request->getParam('data');
		$keywords = $data['keyword'];
		$page_id = (int) $data['page_id'];
		
		$site_page_model = new Search_Models_Site_Pages();
		$data = $site_page_model->getInfoByKeyword($this->site_id, $tpl_id, $page_id, $keywords);
		
		$this->view->data = $data;
	}
}