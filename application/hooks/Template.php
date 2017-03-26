<?php

/**
 * 模版Hook
 * 
 * @author 刘通
 */
class Hooks_Template extends Cms_Hook
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
		$this->_checkHtmlPath($site_info['html_path'], $data);
		
		return true;
	}
	
	/**
	 * 添加后动作
	 * 		创建表没有放到钩子里
	 * 
	 * @see Cms_Hook::afterAdd()
	 */
	function afterAdd($data)
	{
		//删除缓存
		Cms_Cache::getInstance()->delete('template_info_' . $this->site_id);
		Cms_Cache::getInstance()->delete('tpl_site_id_maps');
		
		//添加附加字段
		$tpl_info = Cms_Template::getInfoById($data['tpl_id']);
		$tpl_cfg = Cms_Template_Config::getCfgInfo($tpl_info);
		if(!empty($tpl_cfg['add_field']))
		{
			$user_info = Cms_Auth::getUserInfo();
			$page_obj = new Template_Objects_Page($data['tpl_id']);
			
			$db = Cms_Db::getConnection();
			
			foreach($tpl_cfg['add_field'] as $field)
			{
				$field['tpl_id'] = $data['tpl_id'];
				$field['user_id_a'] = $user_info['user_id'];
				$field['user_id_e'] = $user_info['user_id'];
				$field['add_time'] = date('Y-m-d H:i:s');
				$field['edit_time'] = date('Y-m-d H:i:s');
				$field['user_name']	= $user_info['user_name'];
				
				$db->insert('template_fields', $field);
				$page_obj->addField($field);
			}
		}
		
		//解析模版
		$tpl_file = Cms_Template::cacheName($data);
		Cms_Template_Parse::compile($data['content'], $tpl_file);
		
		return true;
	}
	
	/**
	 * 编辑后动作
	 * 		1) 判断修改html_path权限
	 * 		2) 解析模版
	 * 		3) 清除缓存
	 * 		4) 更新模版页面表的编辑时间
	 * 
	 * @see Cms_Hook::afterEdit()
	 */
	function beforeEdit($old_data, $data)
	{
		//判断修改html_path权限
		$this->_checkHtmlPath($old_data['html_path'], $data);
		
		//解析模版
		if($data['content'] != $old_data['content'])
		{
			$tpl_file = Cms_Template::cacheName($data);
			Cms_Template_Parse::compile($data['content'], $tpl_file);
		}
		
		//删除缓存
		Cms_Cache::getInstance()->delete('template_info_' . $this->site_id);
		
		//更新模版页面的修改时间
		$page_model = new Template_Models_Page();
		$page_model->setTable($data['tpl_id'])->update(array('edit_time'=>date('Y-m-d H:i:s')), "1");
		return true;
	}
	
	function afterEdit($data)
	{
		
	}
	
	function beforeDelete($data)
	{
		
	}
	
	/**
	 * 删除前动作
	 * 		有页面就不允许删除
	 * 
	 * @see Cms_Hook::afterDelete()
	 */
	function afterDelete($data)
	{
		$page_model = new Template_Models_Page();
		$page_model->setTable($data['tpl_id']);
		if($page_model->getValidCount() > 0)
		{
			Cms_Func::response(Cms_L::_('has_valid_page'));
		}
		
		return true;
	}
	
	/**
	 * 验证html_path
	 * 		1) 只有管理员可以修改
	 * 		2) 不能和其它站点的重复
	 * 
	 * @param string	$html_path
	 * @param array		$data
	 */
	private function _checkHtmlPath($html_path, $data)
	{
		
		//判断是否和其它站点的html_path重复
		$template_model = new Template_Models_Template();
		
		if(!$template_model->checkHtmlPath($this->site_id, $data['html_path']))
		{
			Cms_Func::response(Cms_L::_('html_path_dup'));
		}
		
		return true;
	}
}