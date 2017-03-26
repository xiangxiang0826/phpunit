<?php

/**
 * 全站页面model
 * 		本表保存了所有页面的公共信息，主要用途是全站搜索和判断URL和Title是否重复
 * 		所以每次对页面操作都要同步该表信息
 * 		生成后也要同步该表信息
 */
class Search_Models_Site_Pages extends ZendX_Cms_Model
{
	protected $_schema = 'oss';
	protected $_table = 'SEARCH_SITE_PAGES';
	protected $pk = 'id';
	
	function getList($site_id, $p_info, $search=null)
	{
		$condition = "`site_id`={$site_id}";
		if($search)
		{
			$search = array_filter($search);
			$eq_field = array('module', 'entity_id', 'status');
			foreach($search as $field => $value)
			{
				if($field == 'html')
				{
					continue;
				}
				
				if(in_array($field, $eq_field))
				{
					$condition .= $this->get_db()->quoteInto(" AND `{$field}`=?", $value);
				}
				else 
				{
					$condition .= $this->get_db()->quoteInto(" AND `{$field}` LIKE ?", '%' . $value . '%');
				}
			}
			
			//处理页面内容
			if(isset($search['html']))
			{
				if(strpos($search['html'], ':OR:'))
				{
					$html_arr = explode(':OR:', $search['html']);
					$html_arr = array_map('trim', $html_arr);
					
					$tmp = array();
					foreach($html_arr as $val)
					{
						$tmp[] = $this->get_db()->quoteInto("`html` LIKE ?", '%' . $val . '%');
					}
					
					$condition .= " AND (" . implode(' OR ', $tmp) . ')';
				}
				elseif(strpos($search['html'], ':AND:'))
				{
					$html_arr = explode(':AND:', $search['html']);
					$html_arr = array_map('trim', $html_arr);
					
					$tmp = array();
					foreach($html_arr as $val)
					{
						$tmp[] = $this->get_db()->quoteInto("`html` LIKE ?", '%' . $val . '%');
					}
					
					$condition .= " AND (" . implode(' AND ', $tmp) . ')';
				}
				elseif(strpos($search['html'], ':REGEXP:'))
				{
					set_time_limit(0);
					$html_arr = explode(':REGEXP:', $search['html']);
					$html_arr = array_map('trim', $html_arr);
					
					$tmp = array();
					$top_time = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME'] - 600);
					$condition .= " AND `update_time`>'{$top_time}'";
					foreach($html_arr as $val)
					{
						$val = preg_replace('/\s+/', '[:space:]+', $val);
						$tmp[] = $this->get_db()->quoteInto("`html` REGEXP ?", '[^a-zA-z]' . $val . '[^a-zA-z]');
					}
					
					$condition .= " AND (" . implode(' OR ', $tmp) . ')';
				}
				else 
				{
					$condition .= $this->get_db()->quoteInto(" AND `html` LIKE ?", '%' . $search['html'] . '%');
				}
			}
		}
		
		$total = $this->get_db()->fetchOne("SELECT count(*) FROM {$this->_table} WHERE {$condition}");
		$sql = "SELECT * FROM `{$this->_table}` WHERE {$condition} ORDER BY `{$p_info['sort']}` {$p_info['order']} LIMIT {$p_info['start']}, {$p_info['limit']}";
		$rows = $this->get_db()->fetchAll($sql);
		
		return compact('total', 'rows');
	}
	
	function insert($data, $where = '')
	{
		$set_data = array(
			'site_id'	=> $data['site_id'],
			'tpl_id'	=> $data['tpl_id'],
			'page_id'	=> $data['page_id'],
			'title'		=> $data['title'],
			'keyword'	=> $data['keyword'],
			'url'		=> $data['url'],
			'entity_id'	=> isset($data['entity_id']) ? $data['entity_id'] : 0,
			'entity_name'=> isset($data['entity_name']) ? $data['entity_name'] : '',
			'module'	=> $data['module'],
			'status'		=> $data['status'],
			'update_time'=> date('Y-m-d H:i:s')
		);
		return $this->get_db()->insert($this->_table, $set_data);
	}
	
	function update($data,$where = '')
	{
		$set_data = array(
			'site_id'	=> $data['site_id'],
			'title'		=> $data['title'],
			'keyword'	=> $data['keyword'],
			'url'		=> $data['url'],
			'entity_id'	=> isset($data['entity_id']) ? $data['entity_id'] : 0,
			'entity_name'=> isset($data['entity_name']) ? $data['entity_name'] : '',
			'module'	=> $data['module'],
			'status'		=> $data['status'],
			'update_time'=> date('Y-m-d H:i:s')
		);
		return $this->get_db()->update($this->_table, $set_data, "`tpl_id`={$data['tpl_id']} AND `page_id`={$data['page_id']}");
	}
	
	/**
	 * 获取一个实体ID启用状态
	 * 		检查一个entity_id是否在其它同类型的模版中启用
	 * 
	 * @param integer	$site_id
	 * @param string	$module
	 * @param string	$type
	 * @param integer	$entity_id
	 */
	function getAblePage($site_id, $module, $type, $entity_id)
	{
		$sql = "SELECT `tpl_id`, `page_id` FROM `{$this->_table}`
				WHERE `site_id`={$site_id}
					AND {$this->able_condition}
					AND `module`='{$module}'
					AND `type`='{$type}'
					AND `entity_id`={$entity_id}";
		return $this->get_db()->fetchAll($sql);
	}
	
	/**
	 * 获取信息用来检查
	 * 		添加页面时检查title、url是否重复
	 * 
	 * @param array $tpl_ids
	 * @param array $options
	 */
	function getInfoForCheck($tpl_ids, array $options)
	{
		foreach($options as $field => $value)
		{
			$condition[] = $this->get_db()->quoteInto("`$field`=?", $value);
		}
		
		$condition = '(' . implode(' OR ', $condition) . ')';
		$condition = $this->get_db()->quoteInto("`tpl_id` IN (?) AND `index_id`=0 AND {$this->able_condition}", $tpl_ids) . " AND {$condition}";
		$sql = "SELECT `tpl_id`, `page_id` FROM `{$this->_table}` WHERE {$condition}";
		return $this->get_db()->fetchAll($sql); 
	}
	
	/**
	 * 根据条件获取页面信息
	 * 		检查页面关键字重复度时用到
	 * 
	 * @param integer $site_id
	 * @param integer $tpl_id		当前页面tpl_id
	 * @param integer $page_id		当前页面page_id
	 * @param integer $keywords		当前页面keyword
	 */
	function getInfoByKeyword($site_id, $tpl_id, $page_id, $keywords)
	{
		$keywords = explode(',', $keywords);
		$keywords = array_map('trim', $keywords);
		
		$data = array();
		foreach($keywords as $keyword)
		{
			$condition = $this->get_db()->quoteInto("`site_id`={$site_id} AND {$this->able_condition} AND FIND_IN_SET(?, `keyword`)", $keyword);
			$rows = $this->get_db()->fetchAll("SELECT `tpl_id`, `page_id` FROM `{$this->_table}` WHERE {$condition}");
			foreach($rows as $key => $row)
			{
				if($row['tpl_id'] == $tpl_id && $row['page_id'] == $page_id)
				{
					unset($rows[$key]);
				}
			}
			
			$data[$keyword] = $rows;
		}
		
		return $data;
	}
	
	/**
	 * 更新页面信息
	 * 
	 * @param array $tpl_info 模版信息
	 */
	function updatePages($tpl_info)
	{
		$page_model = new Template_Models_Page();
		$page_all = $page_model->setTable($tpl_info['tpl_id'])->getInfoAll();
		$this->_updatePages($tpl_info, $page_all);
		return true;
	}
	
	/**
	 * 正常页面的更新
	 * 		规则：查询html_time
	 * 
	 * @param array $tpl_info
	 * @param array $page_all
	 */
	private function _updatePages($tpl_info, $page_all)
	{
		$tpl_id = $tpl_info['tpl_id'];
		$site_info = Cms_Site::getInfoById($tpl_info['site_id']);
		$html_path = Cms_Template::getHtmlPath($tpl_id);
		
		//查询页面信息
		$sql = "SELECT `id`, `page_id`, `url`, `index_id`, `update_time` FROM `{$this->_table}` WHERE `tpl_id`={$tpl_id}";
		$rows = $this->get_db()->fetchAll($sql);
		
		$old_page_list = array();
		$old_page_ids = array();
		$pagination_list = array();				//存分页信息的
		foreach($rows as $row)
		{
			if($row['index_id'] == 0)			//不要分页信息
			{
				$old_page_list[$row['page_id']] = $row;
				$old_page_ids[] = $row['page_id'];
			}
			else 								//分页保存信息
			{
				$pagination_list[$row['index_id']][] = $row;
			}
		}
		
		//页面表中的page_ids
		$new_page_ids = array_keys($page_all);
		
		//分开添加、修改、删除的页面
		$add_page_ids = array_diff($new_page_ids, $old_page_ids);
		$del_page_ids = array_diff($old_page_ids, $new_page_ids);
		$edit_page_ids = array_intersect($new_page_ids, $old_page_ids);
		//先删除该删除，然后修改该修改的，最后添加新添加的
		if($del_page_ids)
		{
			$where = $this->get_db()->quoteInto("`tpl_id`={$tpl_id} AND `page_id` IN (?)", $del_page_ids);
			$this->get_db()->delete($this->_table, $where);
		}
		
		if($edit_page_ids)
		{
			foreach($edit_page_ids as $page_id)
			{
				$old_page_info = $old_page_list[$page_id];
				$page_info = $page_all[$page_id];
				
				//生成时间小于更新时间的不用更新
				if($page_info['html_time'] <= $old_page_info['update_time'])
				{
					continue;
				}
				
				unset($page_info['html_time']);
				
				$page_info['site_id'] = $tpl_info['site_id'];
				$page_info['tpl_id'] = $tpl_id;
				$page_info['module'] = $tpl_info['module'];
				$page_info['type']	= $tpl_info['type'];
				$page_info['update_time'] = date('Y-m-d H:i:s');
				
				$page_file = $html_path . $page_info['url'];
				if(is_file($page_file))
				{
					$page_info['html'] = Cms_Block::replace(file_get_contents($page_file), $html_path);
				}
				else 
				{
					$page_info['html'] = '';
				}
				
				$this->get_db()->update($this->_table, $page_info, "`tpl_id`={$tpl_id} AND `page_id`={$page_id}");
				
				//分页
				if($tpl_info['is_list'] == 'Y')
				{
					unset($page_info['title'], $page_info['keyword']);
					
					$url_prefix = dirname($page_info['url']);
					$page_files = Cms_Utility_File::getPageFiles(dirname($page_file));
					$paginations = isset($pagination_list[$old_page_info['id']]) ? $pagination_list[$old_page_info['id']] : array();
					
					$pagination_ids = array();
					foreach($paginations as $page)
					{
						$pagination_ids[$page['url']] = $page['id'];
					}
					
					foreach($page_files as $page)
					{
						$url_tmp = $url_prefix . '/' . basename($page);
						$page_info['url'] = $url_tmp;
						$page_info['html'] = Cms_Block::replace(file_get_contents($page), $html_path);
						
						if(isset($pagination_ids[$url_tmp]))			//编辑
						{
							$this->get_db()->update($this->_table, $page_info, "`id`={$pagination_ids[$url_tmp]}");
							unset($pagination_ids[$url_tmp]);
						}
						else											//添加 
						{
							$page_info['index_id'] = $old_page_info['id'];
							$this->get_db()->insert($this->_table, $page_info);
						}
					}
					
					if($pagination_ids)
					{
						$where = $this->get_db()->quoteInto("`id` IN (?)", $pagination_ids);
						$this->get_db()->delete($this->_table, $where);
					}
				}
			}
		}
		
		if($add_page_ids)
		{
			foreach($add_page_ids as $page_id)
			{
				$page_info = $page_all[$page_id];
				unset($page_info['html_time']);
				
				$page_file = $html_path . $page_info['url'];
				if(!file_exists($page_file))
				{
					$page_info['html'] = '';
				}
				else 
				{
 					$page_info['html'] = Cms_Block::replace(file_get_contents($page_file), $html_path);
				}
				
				$page_info['site_id'] = $tpl_info['site_id'];
				$page_info['tpl_id'] = $tpl_id;
				$page_info['module'] = $tpl_info['module'];
				$page_info['type'] = $tpl_info['type'];
				$page_info['update_time'] = date('Y-m-d H:i:s');
				$this->get_db()->insert($this->_table, $page_info);
			}
		}
		
		return true;
	}
}