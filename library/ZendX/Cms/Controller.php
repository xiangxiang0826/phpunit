<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * 控制器基类
 *
 * @author 刘通
 */
class ZendX_Cms_Controller extends ZendX_Controller_Action {
	protected $site_id;
	public $logger = null;
	
	public function init() {
		parent::init();
		$task_config = ZendX_Config::get('task','task');
		$this->site_id = $this->view->site_id = $task_config['site_id'];
		$this->view->site_info = Cms_Site::getInfoById($this->site_id);
	}
	
	/**
	 * 返回成功的json数据
	 *
	 * @param string $msg
	 * @param array $othre_arr
	 *
	 * @author LT
	 */
	function json_ok($msg, $append_arr=null)
	{
		Cms_Func::jsonResponse($msg, 'ok', $append_arr);
	}
	
	/**
	 * 返回失败的json数据
	 *
	 * @param string $msg
	 * @param array $othre_arr
	 *
	 * @author LT
	 */
	function json_err($msg, $append_arr=null)
	{
		Cms_Func::jsonResponse($msg, 'error', $append_arr);
	}
	
	/**
	 * 获取分页信息
	 */
	function getPageInfo($pk=null, $rows=15)
	{
		$page = (int) $this->_request->getPost('page', 1);
		$limit = (int) $this->_request->getPost('rows', $rows);
		$sort = $this->_request->getPost('sort', $pk);
		$order = $this->_request->getPost('order', 'desc');
		$start = ($page - 1) * $limit;
		return compact('start', 'limit', 'sort', 'order');
	}
}