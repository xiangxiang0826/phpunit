<?php
/**
 * 生成的控制器
 * 
 * @author 刘通
 */
class Template_CreateController extends ZendX_Cms_Controller
{
	function init()
	{
		parent::init();
				
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout()->disableLayout();
	}
	
	function indexAction()
	{
		
	}
	
	/**
	 * 根据模版id生成该模版的所有able的页面
	 */
	function templateAction()
	{
		$tpl_id = $this->_request->getParam('tpl_id');
		$start = $this->_request->getParam('start', 0);
		
		$create_object = new Cms_Template_Create($tpl_id);
		$state_info = $create_object->template($start);
		$this->json_ok('', array('total'=>$state_info['total'], 'done'=>$start + $state_info['limit']));
	}
	
	/**
	 * 根据模版id和页面id生成页面
	 */
	function pageAction()
	{
		$tpl_id = $this->_request->getParam('tpl_id');
		$page_id = $this->_request->getParam('page_id');
		$create_object = new Cms_Template_Create($tpl_id);
		$create_object->page($page_id);
		$this->json_ok(Cms_L::_('create_ok'), array('total'=>1, 'done'=>1));
	}
	
	function multipageAction()
	{
		$tpl_id = $this->_request->getParam('tpl_id');
		$page_ids = $this->_request->getParam('page_ids');
		$start = $this->_request->getParam('start', 0);
		
		$create_object = new Cms_Template_Create($tpl_id);
		$state_info = $create_object->multipage($start, $page_ids);
		$this->json_ok('', array('total'=>$state_info['total'], 'done'=>$start + $state_info['limit']));
	}
	
	/**
	 * 根据站点id生成该站点的所有able的页面
	 */
	function siteAction()
	{
		
	}
}