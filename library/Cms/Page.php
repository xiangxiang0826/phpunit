<?php

/**
 * 模版页面的封装
 * 		这个和Template_PageController类似，主要是自动添加页面
 * 
 * @author 刘通
 */
class Cms_Page
{
	/**
	 * 自动添加页面
	 * 
	 * @param integer	$site_id
	 * @param string	$module		模版模块
	 * @param string	$type		模版类型
	 * @param array		$data		添加的数据
	 * 		example array(
	 * 					'entity_id'		=> '',			//必填项
	 * 					'entity_name'	=> '',
	 * 					'url'			=> '',			//必填项
	 * 					'title'			=> '',			//必填项
	 * 					'keyword'		=> '',
	 * 					'description'	=> '',
	 * 				)
	 */
	static function autoAdd($site_id, $module, $type, $data)
	{
		//先验证$data中有没有entity_id，如果没有，直接返回false
		if(empty($data['entity_id']) OR empty($data['url']) OR empty($data['title']))
		{
			return false;
		}
		
		//获取默认模版
		$tpl_info = Cms_Template::getDefaultTpl($site_id, $module, $type);
		
		//没有发现默认模版
		if(!$tpl_info)
		{
			return false;
		}
		
		//查询表中是否有数据
		$page_model = new Template_Models_Page();
		$page_model->setTable($tpl_info['tpl_id']);
		
		$page_info = $page_model->getInfoByEntityId($data['entity_id']);
		$user_info = Common_Auth::getUserInfo();
		
		$data['user_id_e'] = $user_info['user_id'];
		$data['edit_time'] = date('Y-m-d H:i:s');
		$data['state'] = 'able';
		
		$page_hook = new Hooks_Page(array('site_id'=>$site_id, 'tpl_id'=>$tpl_info['tpl_id']));
		
		if($page_info)
		{
			$data['page_id'] = $page_info['page_id'];
			$page_hook->beforeEdit($page_info, $data);
		}
		else
		{
			$data['page_id'] = 0;
			$data['user_id_a'] = $user_info['user_id'];
			$data['add_time'] = date('Y-m-d H:i:s');
			$page_hook->beforeAdd($data);
		}
		
		$page_id = $page_model->save($data);
		
		if(!$page_info)
		{
			$data['page_id'] = $page_id;
			$page_hook->afterAdd($data);
		}
		
		return true;
	}
	
	/**
	 * 自动删除页面
	 */
	static function autoDelete($site_id, $module, $type, $entity_id)
	{
		$tpl_info = Cms_Template::getDefaultTpl($site_id, $module, $type);
		if(!$tpl_info)
		{
			return false;
		}
		
		$page_model = new Template_Models_Page();
		$page_model->setTable($tpl_info['tpl_id']);
		
		$page_info = $page_model->getInfoByEntityId($entity_id);
		if($page_info)
		{
			$page_model->delete($page_info['page_id']);
			$page_hook = new Hooks_Page(array('site_id'=>$site_id, 'tpl_id'=>$tpl_info['tpl_id'], 'page_state'=>'new'));
			$page_hook->afterDelete($page_info);
		}
		
		return true;
	}
	
	/**
	 * 给其它模块调用的删除页面
	 * 
	 * @param integer	$tpl_id
	 * @param array		$page_ids
	 */
	static function deletePage($tpl_id, array $page_ids)
	{
		$page_model = new Template_Models_Page();
		$page_model->setTable($tpl_id);
		
		$db = Cms_Db::getConnection();
		$where = $db->quoteInto("`page_id` IN (?)", $page_ids);
		$page_model->deleted($where);
		
		$tpl_info = Cms_Template::getInfoById($tpl_id);
		
		$page_hook = new Hooks_Page(array('site_id'=>$tpl_info['site_id'], 'tpl_id'=>$tpl_id));
		foreach($page_ids as $page_id)
		{
			$page_info = $page_model->getInfoById($page_id);
			$page_hook->afterDelete($page_info);
		}
		
		return true;
	}
	
	/**
	 * 给其它模块调用的修改页面状态
	 * 
	 * @param integer	$tpl_id
	 * @param array		$page_ids
	 */
	static function updateState($tpl_id, array $page_ids)
	{
		$user_info = Common_Auth::getUserInfo();
		
		$set = array(
			'user_id_e' => $user_info['user_id'],
			'edit_time' => date('Y-m-d H:i:s'),
			'user_name' => $user_info['user_name']
		);
		
		$page_model = new Template_Models_Page();
		$page_model->setTable($tpl_id);
		
		$where = Cms_Db::getConnection()->quoteInto("`page_id` IN (?)", $page_ids);
		$page_model->update($set, $where);
		
		return true;
	}
	
	/**
	 * 验证标题和URL是否重复
	 * 
	 * @param integer	$site_id
	 * @param integer	$tpl_id
	 * @param array		$data
	 * @return array
	 */
	static function checkDup($site_id, $tpl_id, $data)
	{
		$tpl_info = Cms_Template::getInfoById($tpl_id);
		//查找相同域名的tpl_id
		$template_model = new Template_Models_Template();
		$tpl_ids = $template_model->getIdsByHost($site_id, $tpl_info['host']);
		
		$site_page_model = new Search_Models_Site_Pages();
		$page_info_t = $site_page_model->getInfoForCheck($tpl_ids, array('title'=>$data['title']));
		$page_info_u = $site_page_model->getInfoForCheck($tpl_ids, array('url'=>$data['url']));
		
		//当编辑页面时，把自己剔除
		if($data['page_id'])
		{
			foreach($page_info_t as $key => $page)
			{
				if($page['tpl_id'] == $tpl_id AND $page['page_id'] == $data['page_id'])
				{
					unset($page_info_t[$key]);
				}
			}
			
			foreach($page_info_u as $key => $page)
			{
				if($page['tpl_id'] == $tpl_id AND $page['page_id'] == $data['page_id'])
				{
					unset($page_info_u[$key]);
				}
			}
		}
		
		return array('title'=>$page_info_t, 'url'=>$page_info_u);
	}
	
	/**
	 * 验证实体是否重复
	 * 		同一个类型的模版，只有一个是允许启用的
	 */
	static function checkEntityDup($site_id, $tpl_id, $data)
	{
		$tpl_info = Cms_Template::getInfoById($tpl_id);
		$entity_id = $data['entity_id'];
		
		$db = Cms_Db::getConnection();
		
		if($tpl_info['type'] == 'other')			//other模版，只验证自己模版中有无重复实体ID
		{
			$tpl_list = array($tpl_info);
		}
		else 
		{
			$tpl_list = Cms_Template::getListByModuleType($site_id, $tpl_info['module'], $tpl_info['type']);
		}
		
		foreach($tpl_list as $_tpl)
		{
			//获取确定唯一的字段
			$sql = "SELECT `field_name` FROM `template_fields` WHERE `tpl_id`={$_tpl['tpl_id']} AND `state`='able' AND `is_unique`='Y'";
			$rows = $db->fetchAll($sql);
			$condition = '';
			foreach ($rows as $row)
			{
				if(isset($data[$row['field_name']]))
				{
					$condition .= $db->quoteInto(" AND `{$row['field_name']}`=?", $data[$row['field_name']]);
				}
			}
			
			//查询重复字段
			if($tpl_info['tpl_id'] == $_tpl['tpl_id'])
			{
				$condition .= " AND `state`<>'deleted'";
			}
			else 
			{
				$condition .= " AND `state`='able'";
			}
			
			$sql = "SELECT `page_id` FROM `{$_tpl['page_table']}` WHERE `entity_id`={$entity_id}{$condition}";
            $page_id = $db->fetchOne($sql);
            if($page_id)
            {
            	if($_tpl['tpl_id'] == $tpl_id && $page_id == $data['page_id'])			//自己跳过
            	{
            		continue;
            	}
                $tmp_tpl_info = Cms_Template::getInfoById($_tpl['tpl_id']);
				Cms_Func::response(Cms_L::_('in_other_tpl', array('name'=>$tmp_tpl_info['name'], 'tpl_id'=>$_tpl['tpl_id'], 'page_id'=>$page_id)));
            }
		}
		
		return true;
	}
	
	/**
	 * 把一个字符串组成一个合法的URL
	 * 
	 * @param string $str
	 */
	static function string2url($str)
	{
		$str = preg_replace('/[^a-z\d]/i', '-', $str);
		$str = preg_replace('/-{2,}/', '-', $str);
		$str = trim($str, '-');
		$str = strtolower($str);
		
		return $str;
	}
}