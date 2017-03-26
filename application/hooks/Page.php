<?php

/**
 * 页面操作Hook
 */
class Hooks_Page extends Cms_Hook
{
	private $_set = array();
	
	/**
	 * 添加页面前动作
	 * 		如果状态是启用状态，判断title和URL是否重复，如果重复，必须手动禁用一个，原则就是保证只有一个是启用状态
	 * 		这个要和启用功能配合，启用时也要判断title和URL重复的有没处于禁用状态
	 * 		
	 * 		验证默认页面，如果是ppc站点，一个实体可能对应多个页面，但是只有一个是默认页面
	 * 
	 * 		验证URL规范
	 * 
	 * 
	 * @see Cms_Hook::beforeAdd()
	 */
	function beforeAdd($data)
	{
		if($data['status'] == 'enable')
		{
			$this->_checkDup($data);
			$this->_checkDefault($data);
		}
		
		$this->_checkUrlRule($data);
		$this->_checkCreateDirectoryPrev($data);//是否有新建目录的权限
		
		return true;
	}
	
	/**
	 * 添加页面后
	 * 		操作如下：
	 * 			1) 如果$data['status']=='enable'更新实体表的page_url, tpl_id, page_id
	 * 			2) 更新全站搜索表
	 * 
	 * @see Cms_Hook::afterAdd()
	 */
	function afterAdd($data)
	{
		if($data['status'] == 'enable')
		{
			$this->_set = array(
					'tpl_id' => $this->tpl_id,
					'page_id' => $data['page_id'],
					'page_url' => $data['url'],
					'page_keyword' => $data['keyword']
			);
				
			$this->_synEntityTable($data);
		}
		
		//更新全站搜索表site_pages
		$this->updateSitePage($data, 'insert');
		
		return true;
	}
	
	/**
	 * 编辑页面前
	 * 		1. 判断默认页面有无重复
	 * 
	 * 		2. 如果修改URL，先判断权限
	 * 			没权限，返回错误信息
	 * 			有权限
	 * 				验证URL规则是否符合
	 * 				查看有无静态页面，有就删除，并回溯删除空文件夹，然后发送任务
	 * 
	 * 		3. 设置实体表信息，根据状态做更新实体表的准备
	 * 		4. 根据状态的变化触发Cms_Hook::afterDelete()、Cms_Hook::afterDisable()
	 * @see Cms_Hook::beforeEdit()
	 */
	function beforeEdit($old_data, $data)
	{
		$this->_checkDefault($data);
		
		//修改了URL
		if($data['url'] != $old_data['url'])
		{
			
			$this->_checkCreateDirectoryPrev($data);//是否有新建目录的权限
			
			//验证URL规则
			$this->_checkUrlRule($data);
			
			//处理旧页面
			$page_url = Cms_Template::getHtmlPath($this->tpl_id) . $old_data['url'];
			$tpl_info = Cms_Template::getInfoById($this->tpl_id);
			Cms_Utility_File::deleteNodeBackTrace($page_url, $tpl_info['is_list']);
		}
		
		//设置实体表的信息
		$this->_set['page_status'] = 'modified';
		$this->_set['page_url'] = $data['url'];
		$this->_set['page_keyword'] = $data['keyword'];
		
		//根据状态的变化触发Cms_Hook::beforeAble()、Cms_Hook::afterAble()、Cms_Hook::afterDelete()、Cms_Hook::afterDisable()
		switch($data['status'])
		{
			case 'enable':
				$this->beforeAble($data);
				$this->afterAble($data);
				break;
			case 'disable':
				$this->afterDisable($data);
				break;
			case 'deleted':
				$this->afterDelete($data);
				break;
		}
		
		return true;
	}
	
	/**
	 * 编辑后动作
	 * 		更新全站搜索表，在状态相关操作中已处理
	 * 
	 * @see Cms_Hook::afterEdit()
	 */
	function afterEdit($data)
	{
		return true;
	}
	
	function beforeDelete($data)
	{
		return true;
	}
	
	/**
	 * 删除后动作
	 * 		同self::afterDisable()
	 * 
	 * @see Cms_Hook::afterDelete()
	 */
	function afterDelete($data)
	{
		return $this->afterDisable($data);
	}
	
	/**
	 * 禁用后动作
	 * 		1) 修改实体表
	 * 		2) 如果已生成，删除文件并回溯删除空文件夹，继而发送任务
	 * 		3) 更新
	 * 
	 * @param array $data
	 */
	function afterDisable($data)
	{
		$this->_set['tpl_id'] = '';
		$this->_set['page_id'] = '';
		$this->_set['page_url'] = '';
		$this->_set['page_status'] = 'new';
		$this->_synEntityTable($data);
		
		//删除文件，并发送任务
		$file_path = Cms_Template::getHtmlPath($this->tpl_id) . $data['url'];
		$tpl_info = Cms_Template::getInfoById($this->tpl_id);
		Cms_Utility_File::deleteNodeBackTrace($file_path, $tpl_info['is_list']);
		
		//更新全站搜索表
		$this->updateSitePage($data);
		
		return true;
	}
	
	/**
	 * 禁用前动作
	 * 		查看title和url有无和启用状态的页面重复
	 * 		查看页面有无在其它页面存在
	 * 
	 * @param array $data
	 */
	function beforeAble($data)
	{
		$this->_checkDup($data);
		if(!empty($data['entity_id']))
		{
			Cms_Page::checkEntityDup($this->site_id, $this->tpl_id, $data);
		}
		
		return true;
	}
	
	/**
	 * 禁用后动作
	 * 		
	 * 		更新实体表
	 * 		更新全站搜索表
	 * @param array $data
	 */
	function afterAble($data)
	{
		//更新实体表
		$this->_set['tpl_id'] = $this->tpl_id;
		$this->_set['page_id'] = $data['page_id'];
		$this->_set['page_url'] = $data['url'];
		$this->_synEntityTable($data);
		
		//更新全站搜索表
		$this->updateSitePage($data);
		
		return true;
	}
	
	/**
	 * 检查有无和启用状态的页面重复
	 * 		思路：根据模版ID，获取本站点和本模版域名相同的模版ID，然后去全站搜索表中查找这些模版的页面的title和url是否重复
	 */
	private function _checkDup($data)
	{
		$page_info = Cms_Page::checkDup($this->site_id, $this->tpl_id, $data);
		
		$tpl_info = Cms_Template::getInfoById($this->tpl_id);
		
		if($page_info['title'] && $page_info['url'])
		{
			Cms_Func::jsonResponse(Cms_L::_('title_url_dup', array('name'=>$tpl_info['name'])), 'error', $page_info);
		}
		else if($page_info['title'])
		{
			Cms_Func::jsonResponse(Cms_L::_('title_dup', array('name'=>$tpl_info['name'])), 'error', array('title'=>$page_info['title']));
		}
		else if($page_info['url'])
		{
			Cms_Func::jsonResponse(Cms_L::_('url_dup', array('name'=>$tpl_info['name'])), 'error', array('url'=>$page_info['url']));
		}
		
		return true;
	}
	
	/**
	 * 验证URL规则
	 * 		1. 如果是列表页，则URL必须以 index.html结尾
	 */
	private function _checkUrlRule($data)
	{
		$tpl_info = Cms_Template::getInfoById($this->tpl_id);
		
		if($tpl_info['is_list'] == 'Y')
		{
			if(!preg_match('/\/index\.html$/', $data['url']))
			{
				Cms_Func::response(Cms_L::_('list_page_url_rule', array('name'=>$tpl_info['name'])));
			}
		}
		
		return true;
	}
	
	/**
	 * 检查是否有新建目录的权限
	 *
	 * @param array $data
	 */
	private function _checkCreateDirectoryPrev($data)
	{
	    $path = Cms_Template::getHtmlPath($this->tpl_id);
	    $path = dirname($path.$data['url']);
	    return true;
	}
	
	/**
	 * 验证默认页面
	 * 
	 * @param array $data
	 */
	private function _checkDefault($data)
	{
		$site_info = Cms_Site::getInfoById($this->site_id);
		
		if($site_info['type'] != 'ppc')
		{
			return true;
		}
		
		if(isset($data['is_default']) && $data['is_default'] == 'Y')
		{
			$tpl_info = Cms_Template::getInfoById($this->tpl_id);
			$db = Cms_Db::getConnection();
			
			$condition = "`entity_id`={$data['entity_id']} AND `is_default`='Y' AND `status`='enable'";
			if($tpl_info['module'] == 'product' && $tpl_info['type'] == 'switch')
			{
				$condition .= $db->quoteInto(" AND `switch_name`=?", $data['switch_name']);
			}
			
			$sql = "SELECT `page_id` FROM `{$tpl_info['page_table']}` WHERE {$condition}";
			$page_id = $db->fetchOne($sql);
			if($page_id && $page_id != $data['page_id'])
			{
				Cms_Func::jsonResponse(Cms_L::_('default_page_dup', array('site_id'=>$this->site_id, 'tpl_id'=>$this->tpl_id, 'page_id'=>$page_id)), 'error', array('dom_id'=>'is_default'));
			}
		}
		
		return true;
	}
	
	/**
	 * 同步实体表
	 * 
	 * @param array $data
	 */
	protected function _synEntityTable($data)
	{
		if(!$this->_set)
		{
			return false;
		}
		
		$table_info = Cms_Template_Config::getSynTableByTplId($this->tpl_id);
		if(!$table_info)
		{
			return false;
		}
		
		//ppc导向的站点，只同步is_default页面
		$site_info = Cms_Site::getInfoById($this->site_id);
		if($site_info['type'] == 'ppc' && isset($data['is_default']) && $data['is_default'] == 'N')
		{
			return false;
		}
		
		$db = Cms_Db::getConnection();
		$db->update($table_info['syn_table'], $this->_set, $this->_getSynWhere($table_info['entity_id'], $data));
		
		return true;
	}
	
	/**
	 * 获取更新实体表时的条件
	 * 
	 * @param string	$entity_field	实体ID字段名，example：cat_id、art_id、pro_id
	 * @param array		$data
	 */
	private function _getSynWhere($entity_field, array $data)
	{
		$syn_where = "`{$entity_field}`={$data['entity_id']}";
		
		//切换页
		$tpl_info = Cms_Template::getInfoById($this->tpl_id);
		if($tpl_info['module'] == 'product' && $tpl_info['type'] == 'switch')
		{
			$switch_model = new Product_Models_Switch();
			$switch_id = $switch_model->getIdByName($this->site_id, $data['switch_name']);
			$switch_id = (int) $switch_id;
			$syn_where .= " AND `switch_id`={$switch_id}";
		}
		
		return $syn_where;
	}
}