<?php

/**
 * 块hook
 * 
 * @author 刘通
 */
class Hooks_Block extends Cms_Hook
{
	/**
	 * 添加前动作
	 * 		验证html_path修改权限
	 * 
	 * @see Cms_Hook::beforeAdd()
	 */
	function beforeAdd($data)
	{
		$site_info = Cms_Site::getInfoById($this->site_id);
		$this->_checkFilePath($site_info['html_path'], $data);
		
		return true;
	}
	
	/**
	 * 添加后动作
	 * 		编译标签
	 * 
	 * @see Cms_Hook::beforeAdd()
	 */
	function afterAdd($data)
	{
		$cacheName = Cms_Block::cacheName($data);
		Cms_Template_Parse::compile($data['content'], $cacheName);
		
		return true;
	}
	
	/**
	 * 编辑前动作
	 * 		验证html_path和file_path组合是否唯一
	 * 		如果内容有修改，重新编译标签
	 * 		如果html_path或file_path有改动，并且有生成的文件，删除并发送任务
	 * 
	 * @see Cms_Hook::beforeEdit()
	 */
	function beforeEdit($old_data, $data)
	{
		//判断修改html_path权限
		$this->_checkFilePath($old_data['html_path'], $data);
		
		if($old_data['content'] != $data['content'])
		{
			$cacheName = Cms_Block::cacheName($data);
			Cms_Template_Parse::compile($data['content'], $cacheName);
		}
		
		if($data['html_path'] != $old_data['html_path'] OR $data['file_path'] != $old_data['file_path'])
		{
			$file_path = Cms_Block::getFilePath($old_data);
			Cms_Utility_File::deleteNodeBackTrace($file_path);
		}
		
		return true;
	}
	
	function afterEdit($data)
	{
		
	}
	
	/**
	 * 删除前动作
	 * 		判断模版中有没有引用这个块，如果有则不可以删除
	 * 
	 * @see Cms_Hook::beforeDelete()
	 */
	function beforeDelete($data)
	{
		return true;
	}
	
	/**
	 * 删除后动作
	 * 		删除生成的文件
	 * 		删除编译后的PHP缓存
	 * 
	 * @see Cms_Hook::afterDelete()
	 */
	function afterDelete($data)
	{
		$file_path = Cms_Block::getFilePath($data);
		Cms_Utility_File::deleteNodeBackTrace($file_path);
		
		$cache_file = Cms_Template_Cache::getInstance()->getCachePath() . Cms_Block::cacheName($data);
		if(file_exists($cache_file))
		{
			unlink($cache_file);
		}
		
		return true;
	}
	
	/**
	 * 生成后动作
	 * 		发送任务
	 */
	function afterCreate($data)
	{
		$file_path = Cms_Block::getFilePath($data);
		Cms_Task::getInstance()->send(array($file_path), 'edit');
		
		return true;
	}
	
	/**
	 * 验证html_path，和template的钩子中的类似
	 * 		1) 只有管理员可以修改
	 * 		2) 不能和其它站点的重复
	 * 
	 * @param string	$html_path
	 * @param array		$data
	 */
	private function _checkFilePath($html_path, $data)
	{		
		//判断是否和其它站点的html_path重复
		$blk_model = new Template_Models_Block();
		
		if(!$blk_model->checkFilePath($data['blk_id'], $data['html_path'], $data['file_path']))
		{
			Cms_Func::response(Cms_L::_('file_path_dup'));
		}
		
		return true;
	}
}