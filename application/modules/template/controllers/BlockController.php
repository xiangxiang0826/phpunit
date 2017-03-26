<?php

/**
 * 块控制器
 * 
 * @author 刘通
 */
class Template_BlockController extends ZendX_Cms_Controller
{
	protected $_model;
	
	function init()
	{
		parent::init();
		$this->_model = new Template_Models_Block();
	}
	
	function indexAction()
	{
		
	}
	
	function dataAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout()->disableLayout();
		
		$page = $this->_request->getPost('page', 1);
		$rows = $this->_request->getPost('rows', 15);
		$start = ($page - 1) * $rows;
		
		$search = $this->_request->getParam('S');
		
		$data = $this->_model->getList($this->site_id, $start, $rows, $search);
		
		echo json_encode($data);
	}
	
	function editAction()
	{
		$blk_id = (int) $this->_request->getParam('blk_id');
		
		if($blk_id)
		{
			$block_info = $this->_model->getInfoById($blk_id);
			$this->view->block_info = $block_info;
		}
		
		//获取网站的html_path
		$site_info = Cms_Site::getInfoById($this->site_id);
		$this->view->site_info = $site_info;
	}
	
	function saveAction()
	{
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout()->disableLayout();

		$data = $this->_request->getPost('data');
		$data['site_id'] = $this->site_id;
		$data['html_path'] = trim($data['html_path'], '/');
		$data['file_path'] = trim($data['file_path'], '/');

		//判断file_path是否有重复
		$blk_model = new Template_Models_Block();

		//开始入库，并且两次调用钩子处理缓存文件生成
		$hook = new Hooks_Block(array('site_id'=>$this->site_id));
		if($data['blk_id'])
		{
			$blk_info = $blk_model->getInfoById($data['blk_id']);
			$hook->beforeEdit($blk_info, $data);
		}
		else 
		{
			$hook->beforeAdd($data);
		}

		$blk_id = $this->_model->save($data);

		if(!$data['blk_id'])
		{
			$data['blk_id'] = $blk_id;
			$hook->afterAdd($data);
		}

		$this->json_ok('操作成功', array('id'=>$blk_id));
	}
	
	/**
	 * 删除块
	 */
	function deleteAction()
	{
		$blk_id = (int) $this->_request->getParam('blk_id');
		
		$hook = new Hooks_Block(array('site_id'=>$this->site_id));
		
		$blk_model = new Template_Models_Block();
		$blk_info = $blk_model->getInfoById($blk_id);
		
		//删除前的钩子
		$hook->beforeDelete($blk_info);
		$blk_model->delete($blk_id);
		
		//删除后的钩子
		$hook->afterDelete($blk_info);
		
		$this->json_ok(Cms_L::_('action_ok'));
	}
	
	/**
	 * 块的静态文件生成
	 */
	function createAction()
	{
		$blk_id = (int) $this->_request->getParam('blk_id');
		
		$blk_model = new Template_Models_Block();
		$blk_info = $blk_model->getInfoById($blk_id);
		
		//引入数据接口的命名空间
		new Cms_Loader(array(
				'basePath'  => APPLICATION_PATH,
				'namespace' => 'Datas_',
		));
		
		//获取站点信息
		$site_info = Cms_Site::getInfoById($this->site_id);
		$site_id = $site_info['site_id'];
		$host = $site_info['host'];
		$host_js = $site_info['host_js'];
		$host_css = $site_info['host_css'];
		$payment_mode = $site_info['payment_mode'];
		$host_image = $site_info['host_image'];
		
		$blk_cache_file = Cms_Template_Cache::getInstance()->getBlkFile($blk_info);
		$file_path = Cms_Block::getFilePath($blk_info);
		ob_start();
		include $blk_cache_file;
		$content = ob_get_clean();
		
		Cms_Func::writeFile($file_path, $content);
		
		$hook = new Hooks_Block(array('site_id'=>$this->site_id));
		$hook->afterCreate($blk_info);
		
		$this->json_ok(Cms_L::_('action_ok'));
	}
}