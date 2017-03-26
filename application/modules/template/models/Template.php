<?php

/**
 * 模版的模型
 * 
 * @author 刘通
 */
class Template_Models_Template extends ZendX_Cms_Model
{
	protected $_schema = 'oss';
	protected $_table = 'TEMPLATE';
	protected $pk = 'tpl_id';
	/**
	 * 获取模版列表
	 */
	function getList($site_id, $start, $count, $sort, $order, $search = array())
	{
		$conditions = $this->get_db()->quoteInto("`site_id`=? AND {$this->able_condition}", $site_id);
		if($search)
		{
			if(!empty($search['module']))
			{
				$conditions .= " AND `module`='{$search['module']}'";
			}
			
			if(!empty($search['type']))
			{
				$conditions .= " AND `type`='{$search['type']}'";
			}
			
			if(!empty($search['name']))
			{
				$conditions .= $this->get_db()->quoteInto(" AND `name` LIKE ?", '%' . $search['name'] . '%');
			}
		}
		
		$total = $this->get_db()->fetchOne("SELECT count(*) FROM `{$this->_table}` WHERE {$conditions}");
		$sql = "SELECT `tpl_id` AS `id`, `tpl_id`, `name`, `page_table`, `edit_time`, `user_name` 
				FROM `{$this->_table}` 
				WHERE {$conditions}
				ORDER BY `{$sort}` {$order}
				LIMIT {$start}, {$count}";
		
		$rows = $this->get_db()->fetchAll($sql);
		return compact('total', 'rows');
	}
	
	function save($data)
	{
		$tpl_id = $data['tpl_id'];
		$data = $this->data($data);
		
		if($tpl_id)
		{
			unset($data['module'], $data['type']);
			$this->get_db()->update($this->_table, $data, "`tpl_id`={$tpl_id}");
		}
		else
		{
			if(!strncasecmp($data['type'], 'list', 4))
			{
				$data['is_list'] = 'Y';
			}
			$this->get_db()->insert($this->_table, $data);
			$tpl_id = $this->get_db()->lastInsertId();
		}
		
		return $tpl_id;
	}
	
	function delete($tpl_id)
	{
		parent::deleted("`tpl_id`={$tpl_id}");
		return true;
	}
	
	/**
	 * 创建页面表
	 * 
	 * @param array $data
	 */
	function createTable($data)
	{
		$table = $data['page_table'];
		$sql = "CREATE TABLE `{$table}` LIKE `TEMPLATE_PAGE_DEMO`";
		$this->get_db()->query($sql);
		
		if(Cms_Template_Config::getEntityId($data))
		{
			$this->get_db()->query("CREATE INDEX entity_id ON `{$table}`(`entity_id`)");
		}
	}
	
	/**
	 * 根据多个ID获取对应的模版信息
	 * 
	 * @param string | array $tpl_ids
	 */
	function getInfoByIds($tpl_ids)
	{
		if(is_array($tpl_ids))
		{
			$tpl_ids = implode(',', $tpl_ids);
		}
		
		$sql = "SELECT * FROM {$this->_table} WHERE `tpl_id` IN ({$tpl_ids}) AND {$this->able_condition}";
		return $this->get_db()->fetchAll($sql);
	}
	
	function getInfoAll($site_id)
	{
		$sql = "SELECT * FROM `{$this->_table}`
				WHERE `site_id`={$site_id} AND {$this->able_condition}";
		$rows = $this->get_db()->fetchAll($sql);
		
		foreach($rows as $key => $row)
		{
			unset($rows[$key]['content'], $rows[$key]['action_name'], $rows[$key]['user_id_a'], $rows[$key]['user_id_e']);
			unset($rows[$key]['add_time'], $rows[$key]['edit_time'], $rows[$key]['user_name'], $rows[$key]['status']);
		}
		
		return $rows;
	}
	
	function getContentById($tpl_id)
	{
		$sql = "SELECT `content` FROM `{$this->_table}` WHERE `tpl_id`={$tpl_id}";
		return $this->get_db()->fetchOne($sql);
	}
	
	function getDefaultTpl($site_id, $module, $type)
	{
		$sql = "SELECT `tpl_id`, `name`, `site_id` FROM `{$this->_table}`
				WHERE `site_id`={$site_id}
					AND {$this->able_condition}
					AND `module`='{$module}'
					AND `type`='{$type}'
					AND `is_default`='Y'";
		return $this->get_db()->fetchRow($sql);
	}
	
	/**
	 * 获取相同域名的所有模版ID
	 * 
	 * @param integer	$site_id
	 * @param string	$host
	 */
	function getIdsByHost($site_id, $host)
	{
		$select = $this->get_db()->select();
		$select->from($this->_table, array('tpl_id'))
			->where('site_id=?', $site_id)
			->where('`host`=?', $host);
			
		$rows = $this->get_db()->fetchAll($select);
		$return = array();
		foreach($rows as $row)
		{
			$return[] = $row['tpl_id'];
		}
		
		return $return;
	}
	
		
	/**
	 * 查看其它站点的html_path，是否有重复的
	 * 
	 * @param integer	$site_id
	 * @param array		$path
	 */
	function checkHtmlPath($site_id, $path)
	{
		$sql = "SELECT count(*) FROM `{$this->_table}` WHERE `site_id`<>{$site_id} AND `html_path`='{$path}' AND {$this->able_condition}";
		return !$this->get_db()->fetchOne($sql);
	}
	
	/**
	 * 根据获取所有模版和站点的ID对应关系
	 */
	function getTplSiteIdMap()
	{
		$sql = "SELECT `tpl_id`, `site_id` FROM `{$this->_table}` WHERE {$this->able_condition}";
		$rows = $this->get_db()->fetchAll($sql);
		
		$return = array();
		foreach($rows as $row)
		{
			$return[$row['tpl_id']] = $row['site_id'];
		}
		
		return $return;
	}
}