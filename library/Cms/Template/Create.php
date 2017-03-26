<?php

/**
 * 模版生成类
 * 
 * @author 刘通
 */
class Cms_Template_Create
{
	private static $_instance = null;
	private $_site_info = null;
	private $_tpl_info = null;
	private $_cache_file = null;
	
	function __construct($tpl_id)
	{
		new Cms_Loader(array(
				'basePath'  => APPLICATION_PATH,
				'namespace' => 'Datas_',
		));
		
		$tpl_info = Cms_Template::getInfoById($tpl_id);
		$this->_tpl_info = $tpl_info;
		$this->_tpl_info['html_path'] = Cms_Template::getHtmlPath($tpl_id);
		$this->_site_info = Cms_Site::getInfoById($tpl_info['site_id']);
		$this->_cache_file = Cms_Template_Cache::getInstance()->getTplFile($tpl_info);
	}
	
	/**
	 * 根据模版id生成该模版的所有able的页面
	 * 
	 * @param integer $tpl_id 模版ID
	 */
	function template($start=0)
	{
		$page_model = new Template_Models_Page();
		$page_list = $page_model->setTable($this->_tpl_info['tpl_id'])->getInfoList($start);
		
		//生成前，如果是列表模版，则要要先发送删除分页的任务
		if($start == 0 && $this->_tpl_info['is_list'] == 'Y')
		{
			//调用钩子
			$hook = $this->_getHookObject();
			$hook->beforeCreate($page_model->getAllCreatedUrl("1"));
		}
		
		foreach($page_list['rows'] as $page_info)
		{
			$this->create($page_info);
		}
		
		//生成完毕，修改生成时间
		if($page_list['total'] <= $start + $page_list['limit'])
		{
			//调用钩子
			$hook = $this->_getHookObject();
			$hook->afterCreate($page_model->getAllCreatedUrl("1"));
			
			//修改页面生成时间
			$page_model->update(array('html_time'=>date('Y-m-d H:i:s')), "`status`='enable'");
		}
		
		unset($page_list['rows']);
		return $page_list;
	}
	
	/**
	 * 根据模版id和页面id生成页面
	 * 
	 * @param integer $tpl_id 模版ID
	 * @param integer $page_id 页面ID
	 */
	function page($page_id)
	{
		//页面信息
		$page_model = new Template_Models_Page();
		$page_info = $page_model->setTable($this->_tpl_info['tpl_id'])->getInfoById($page_id);
		if($page_info['status'] != 'enable')
		{
			return false;
		}
		
		//生成前，如果是列表模版，则要要先发送删除分页的任务
		if($this->_tpl_info['is_list'] == 'Y')
		{
			//调用钩子
			$hook = $this->_getHookObject();
			$hook->beforeCreate(array($page_info['url']));
		}
		
		$this->create($page_info);
		
		//调用钩子
		$hook = $this->_getHookObject();
		$hook->afterCreate(array($page_info['url']), $page_id);
		
		//修改生成状态
		$page_model->update(array('html_time'=>date('Y-m-d H:i:s')), "`page_id`={$page_id}");
		
		return true;
	}
	
	function multipage($start, $page_ids)
	{
		if(!preg_match('/[0-9]+(,[0-9]+)*/', $page_ids))
		{
			Cms_Func::jsonResponse('parameter error');
		}
		
		$page_model = new Template_Models_Page();
		$page_list = $page_model->setTable($this->_tpl_info['tpl_id'])->getInfoList($start, $page_ids);
		
		//生成前，如果是列表模版，则要要先发送删除分页的任务
		if($start == 0 && $this->_tpl_info['is_list'] == 'Y')
		{
			//调用钩子
			$hook = $this->_getHookObject();
			$hook->beforeCreate($page_model->getAllCreatedUrl("`page_id` IN ({$page_ids})"));
		}
		
		foreach($page_list['rows'] as $page_info)
		{
			$this->create($page_info);
		}
		
		//生成完毕，修改生成时间
		if($page_list['total'] <= $start + $page_list['limit'])
		{
			//调用钩子
			$hook = $this->_getHookObject();
			$hook->afterCreate($page_model->getAllCreatedUrl("`page_id` IN ({$page_ids})"), $page_ids);
			
			//处理页面生成时间
			$page_model->update(array('html_time'=>date('Y-m-d H:i:s')), "`page_id` IN ({$page_ids}) AND `status`='enable'");
		}
		
		unset($page_list['rows']);
		return $page_list;
	}
	
	/**
	 * 根据站点id生成该站点的所有able的页面
	 * 
	 * @param integer $site_id 站点ID
	 */
	function site($site_id)
	{
		
	}
	
	function create($page_info)
	{
		if($this->_tpl_info['is_list'] == 'Y')			//分页生成
		{
			$page = 1;
			$index_url = $page_info['url'];
			do
			{
				if($page > 1)					//修改分页信息
				{
					$page_info['url'] = str_replace('/index.html', "/{$page}.html", $index_url);
				}
				$total_page = $this->_content($page_info, $page++);
			}while($page <= $total_page && $total_page < 100);
		} else {							//正常页面
			$this->_content($page_info);
		}
	}
	
	/**
	 * 生成页面内容
	 * 
	 * @param array		$page_info
	 * @param integer	$page		当前页数
	 * @param integer	$return		是否返回内容，有两种清空，预览时返回，生成时不返回内容
	 */
	private function _content($page_info, $page=0, $return=false)
	{
		extract($page_info);
		$host = $this->_tpl_info['host'];
		$host_js = $this->_site_info['host_js'];
		$host_css = $this->_site_info['host_css'];
		$host_image = $this->_site_info['host_image'];
		$payment_mode = $this->_site_info['payment_mode'];
		$site_id = $this->_tpl_info['site_id'];
		
		ob_start();
		include $this->_cache_file;
		$content = ob_get_clean();
		
		/**
		 * 替换内容中出现的标签，为防止无限循环，只替换三层
		 * 判断是否有标签的依据是有输出标签/{=[^{}]+}/
		 */
		$___i = 0;						//防止与模版中变量重复
		while(preg_match('/{=[^{}]+}/', $content))
		{
			$___tmp_file = 'tmp/' . md5($this->_tpl_info['tpl_id'] . $page_info['page_id']) . '.php';
			Cms_Template_Parse::compile($content, $___tmp_file);
			ob_start();
			include APPLICATION_PATH . '/cache/templates/' . $___tmp_file;
			$content = ob_get_clean();
			@unlink(APPLICATION_PATH . '/cache/templates/' . $___tmp_file);
			
			if(++$___i == 3)			//三次了，直接退出
			{
				break;
			}
		}
		
		if($return)
		{
			return $content;
		}
		
		Cms_Func::writeFile($this->_tpl_info['html_path'] . $page_info['url'], $content);
		return (isset($total) && isset($pagesize) && $pagesize > 0) ? ceil($total / $pagesize) : 1;
	}
	
	/**
	 * 预览页面
	 * 
	 * @param integer $page_id
	 */
	function preview($page_id)
	{
		$page_model = new Template_Models_Page();
		$page_info = $page_model->setTable($this->_tpl_info['tpl_id'])->getInfoById($page_id);
		$content = $this->_content($page_info, 1, true);
		
		//处理ssi
		$content = Cms_Block::replace($content, $this->_tpl_info['html_path']);
		return $content;
	}
	
	/**
	 * 获取钩子对象
	 */
	private function _getHookObject()
	{
		return new Hooks_Create(array('site_id'=>$this->_tpl_info['site_id'], 'tpl_id'=>$this->_tpl_info['tpl_id']));
	}
}