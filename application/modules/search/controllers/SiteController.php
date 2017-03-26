<?php

/**
 * 整站搜索
 */
class Search_SiteController extends ZendX_Custom_Controller
{
	private $_model;
	function init()
	{
		parent::init();
		$this->_model = new Search_Models_Site_Pages();
	}
	
	function indexAction()
	{
		$module_all = Cms_Template_Config::getModuleAll();
		$this->view->module_all = $module_all;
		
		$badword_model = new Search_Models_Site_Badword();
		$badword_all = $badword_model->getAll($this->site_id);
		$this->view->badword_all = $badword_all;
		
		//接收传送过来的参数
		$search = $this->_request->getParam('S', array());
		$search['html'] = $this->_request->getParam('html');
		if($search['html'])
		{
			$search['html'] = urldecode($search['html']);
		}
		
		$search = array_filter($search);
		
		if($search)
		{
			$this->view->search = $search;
		}
	}
	
	function dataAction()
	{
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$p_info = $this->getPageInfo('id');
		$search = $this->_request->getParam('S');
		
		$data = $this->_model->getList($this->site_id, $p_info, $search);
		echo json_encode($data);
	}
	
	/**
	 * 更新数据库的文件页面
	 */
	function updateAction()
	{
		set_time_limit(0);
		//查询模版
		$tpl_list = Cms_Template::getListBySiteId($this->site_id);
		$tpl_list = array_values($tpl_list);
		$curr = $this->_request->getParam('curr', 0);
		
		$return = array();
		if(isset($tpl_list[$curr]))
		{
			$tpl_name = $tpl_list[$curr]['name'];
			$this->_model->updatePages($tpl_list[$curr]);
			++$curr;
			$return = array('done'=>false, 'tpl_name'=>$tpl_name, 'curr'=>$curr);
		}
		else 
		{
			$return['done'] = true;
		}
		
		$this->json_ok('', $return);
	}
}