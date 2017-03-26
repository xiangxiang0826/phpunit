<?php 

/**
 * 模版控制器
 * 
 * @author 刘通
 */
class Template_TemplateController extends ZendX_Cms_Controller
{
	function indexAction()
	{
		$module_all = Cms_Template_Config::getModuleAll();
		$this->view->module_all = $module_all;
	}
	
	function dataAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout()->disableLayout();
		
		$page = $this->_request->getPost('page', 1);
		$rows = $this->_request->getPost('rows', 15);
		$start = ($page - 1) * $rows;
		
		$sort = $this->_request->getPost('sort', 'tpl_id');
		$order = $this->_request->getPost('order', 'desc');
		
		$search = $this->_request->getParam('S');
		
		$template_model = new Template_Models_Template();
		$data = $template_model->getList($this->site_id, $start, $rows, $sort, $order, $search);
		echo json_encode($data);
	}
	
	function editAction()
	{
		$tpl_id = (int) $this->_request->getParam('tpl_id');
		$template_model = new Template_Models_Template();
		
		//获取模版配置信息
		$tpl_cfg = Cms_Template_Config::getCfgAll();
		
		if($tpl_id)
		{
			$template_info = $template_model->getInfoById($tpl_id);
			$this->view->template_info = $template_info;
			
			$tpl_type = $tpl_cfg[$template_info['module']];
			$this->view->tpl_type = $tpl_type;
		}
		
		$this->view->tpl_module = $tpl_cfg;
		$this->view->site_info = Cms_Site::getInfoById($this->site_id);
	}
	
	/**
	 * Ajax:根据模块获取类型
	 */
	function gettypeAction()
	{
		$this->_helper->layout()->disableLayout();
		
		$mod = $this->_request->getParam('mod');
		$entity_id_name = $this->_request->getParam('entity_id_name', '');
		$tpl_type = Cms_Template_Config::getTypeByModule($mod);
		
		if(!empty($entity_id_name))
		{
		    foreach ($tpl_type AS $key => $value)
		    {
		        if(empty($value['entity_id']) || $value['entity_id'] != $entity_id_name)
		        {
		            unset($tpl_type[$key]);
		        }
		    }
		}
		//$this->_helper->ViewRenderer()->setNoRender();
		$this->view->tpl_type = $tpl_type;
		echo $this->view->Render('template_gettype.phtml');
		return;
	}
	
	/**
	 * Ajax:根据模块获取和类型获取模板列表
	 * @author:tjx
	 */
	function gettemplateAction()
	{
	    $this->_helper->layout()->disableLayout();
	
	    $mod = $this->_request->getParam('mod');
	    $type = $this->_request->getParam('type');
	    $not_tpl_ids = $this->_request->getParam('not_tpl_ids', '');
	    
	    $tpl_list = Cms_Template::getListByModuleType($this->site_id, $mod, $type);
	    if(!empty($not_tpl_ids))
	    {
	        $not_tpl_ids = explode(',', $not_tpl_ids);
	        foreach ($tpl_list AS $key => $value)
	        {
	            if(in_array($value['tpl_id'], $not_tpl_ids))
	            {
	                unset($tpl_list[$key]);
	            }
	        }
	    }
	    $this->view->tpl_list = $tpl_list;
	}
	
	function saveAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout()->disableLayout();
		
		$post_data = $this->_request->getPost('data');
		$post_data = array_map('trim', $post_data);
		$post_data['site_id'] = $this->site_id;
		$post_data['html_path'] = trim($post_data['html_path'], '/');
		
		$hook = new Hooks_Template(array('site_id'=>$this->site_id));
		
		$template_model = new Template_Models_Template();
		
		//判断默认是否存在
		if($post_data['is_default'] == 'Y')
		{
			$db_info = $template_model->getDefaultTpl($this->site_id, $post_data['module'], $post_data['type']);
			if($db_info && $post_data['tpl_id'] != $db_info['tpl_id'])
			{
				$this->json_err(Cms_L::_('default_dup', $db_info));
			}
		}
				
		//调用钩子
		if($post_data['tpl_id'])
		{
			$old_data = $template_model->getInfoById($post_data['tpl_id']);
			$hook->beforeEdit($old_data, $post_data);
		}
		else 
		{  
			$hook->beforeAdd($post_data);
		}
		
		$tpl_id = $template_model->save($post_data);
		
		//创建模版表
		if(!$post_data['tpl_id'])
		{
			$post_data['tpl_id'] = $tpl_id;
			$post_data['page_table'] = 'TEMPLATE_PAGE_' . $tpl_id;
			$template_model->save($post_data);
			$template_model->createTable($post_data);
			
			//调用钩子
			$hook->afterAdd($post_data);
		}
		
		echo $this->json_ok(Cms_L::_('action_ok'), array('id' => $tpl_id));
	}
	
	function deleteAction()
	{
		$tpl_id = (int) $this->_request->getPost('tpl_id');
		$tpl_model = new Template_Models_Template();
		
		$tpl_info = Cms_Template::getInfoById($tpl_id);
		$hook = new Hooks_Template(array('site_id'=>$this->site_id));
		$hook->afterDelete($tpl_info);
		
		$tpl_model->delete($tpl_id);
		
		$this->json_ok(Cms_L::_('action_ok'));
	}
	
	/**============================字段管理============================*/
	/**
	 * 字段列表
	 */
	function fieldAction()
	{
		$tpl_id = (int) $this->_request->getParam('tpl_id');
		$this->view->tpl_id = $tpl_id;
	}
	
	/**
	 * 字段数据
	 */
	function fielddataAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout()->disableLayout();
		
		$tpl_id = (int) $this->_request->getParam('tpl_id');
		$this->view->tpl_id = $tpl_id;
		
		$field_model = new Template_Models_Field();
		$data = $field_model->getList($tpl_id);
		echo json_encode($data);
	}
	
	/**
	 * 编辑字段
	 */
	function editfieldAction()
	{
		$tpl_id = (int) $this->_request->getParam('tpl_id');
		$field_id = (int) $this->_request->getParam('field_id');
		
		if($field_id)
		{
			$field_model = new Template_Models_Field();
			$this->view->field_info = $field_model->getInfoById($field_id);
		}
		
		$this->view->tpl_id = $tpl_id;
	}
	
	/**
	 * 保存字段
	 */
	function savefieldAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout()->disableLayout();
		
		$data = $this->_request->getPost('data');
		
		//--验证字段是否可用
		$field_model = new Template_Models_Field();
		if(!$data['field_id'] && !$field_model->checkFieldName($data['field_name'], $data['tpl_id']))
		{
			$this->json_err(Cms_L::_('field_name_conflict'), array('field'=>'field_name'));
		}
		
		foreach(array('input_width', 'input_height') as $field)
		{
			if($data[$field] <= 0)
			{
				unset($data[$field]);			//用数据库的默认值
			}
		}
		
		switch ($data['field_type'])
		{
			case 'char':
				$data['field_len'] = 255;
				break;
			case 'int':
			case 'text':
			case 'longtext':
			default:
				$data['field_len'] = 0;
				break;
		}
		
		/**
		 * 添加字段到页面表
		 */
		$page_object = new Template_Objects_Page($data['tpl_id']);
		$page_object->addField($data);
		
		/**
		 * 插入数据库
		 */
		$field_model = new Template_Models_Field();
		$field_id = $field_model->save($data);
		
		$this->json_ok(Cms_L::_('action_ok'), array('id'=>$field_id));
	}
	
	/**
	 * 删除字段
	 * 		最严谨的做法是：删除页面中的该字段，然后删除template_fields表中的该字段
	 * 		现在先只删除template_fields表中的该字段
	 */
	function deletefieldAction()
	{
		$field_id = (int) $this->_request->getPost('field_id');
		
		$field_model = new Template_Models_Field();
		$field_model->delete($field_id);
		
		$this->json_ok('');
	}
}