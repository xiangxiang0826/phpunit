<?php
/**
 * 页面模型
 * 		页面表说明：该表中有两个时间比较特殊，edit_time和html_time，用这两个时间判断是否生成和修改
 * 
 * @author 刘通
 */
class Template_Models_Page extends ZendX_Cms_Model
{
	private $tpl_id;
	protected $_schema = 'oss';
	protected $pk = 'page_id';
	protected $_table = 'TEMPLATE_PAGE_DEMO';
	function setTable($tpl_id)
	{
		$this->_table = 'TEMPLATE_PAGE_' . $tpl_id;
		$this->tpl_id = $tpl_id;
		return $this;
	}
	
	/**
	 * 搜索页面列表
	 * 
	 * @param integer $site_id		只在根据文件存在与否改变页面的html_time时有用
	 * @param integer $start
	 * @param integer $rows
	 * @param array $search
	 */
	function getList($site_id, $start, $rows, $sort, $order, $search=array())
	{
		if(!$this->_table)
		{
			
		}
		
		$condition = "`status`<>'deleted'";
		
		/**
		 * +------------------------------------------
		 * | 搜索条件
		 * +------------------------------------------
		 * |	<select name="S[type][]">
    	 * |		<option value="1">包含</option>
		 * |		<option value="2">不包含</option>
		 * |		<option value="3">等于</option>
		 * |		<option value="4">不等于</option>
		 * |		<option value="5">大于</option>
		 * |		<option value="6">小于</option>
		 * |		<option value="7">大于等于</option>
		 * |		<option value="8">小于等于</option>
		 * |	</select>
		 * +--------------------------------------------
		 */
		if($search)
		{
			$s_count = count($search['condition']);
			for($i=0; $i<$s_count; $i++){
				if($search['condition'][$i] && $search['keyword'][$i])
				{
					$keyword = trim($search['keyword'][$i]);
					switch($search['type'][$i]){
						case 1:
							$option = " LIKE ?";
							$keyword = '%' . $keyword . '%';
							break;
						case 2:
							$option = " NOT LIKE ?";
							$keyword = '%' . $keyword . '%';
							break;
						case 3:
							$option = "=?";
							break;
						case 4:
							$option = "!=?";
							break;
						case 5:
							$option = ">?";
							break;
						case 6:
							$option = "<?";
							break;
						case 7:
							$option = ">=?";
							break;
						case 8:
							$option = "<=?";
							break;
						default:
							$option = false;
							break;
					}
						
					if($option){
						$condition .= $this->get_db()->quoteInto(" AND {$search['condition'][$i]} {$option}", $keyword);
					}
				}
			}
			
			if($search['status'] < 2)
			{
				/**
				 * +---------------------------------------------------------
				 * | 搜索静态是否生成，标志是静态文件是否存在，但是这个如果不能在数据
				 * | 库中体现则有无法搜索，因为单纯的靠html_time字段不可靠，有人为
				 * | 删除静态文件的可能，这里就同步一下静态文件和数据库
				 * +---------------------------------------------------------
				 */
				$this->_synFileDb();
				if($search['status']){
					$condition .= " AND `html_time`>0";
				}else{
					$condition .= " AND `html_time`=0";
				}
			}
		}
		
		$total = $this->get_db()->fetchOne("SELECT count(*) FROM {$this->_table} WHERE {$condition}");
		
		$sql = "SELECT 
					`page_id` AS `id`,
					p.*
				FROM {$this->_table} p
				WHERE {$condition} 
				ORDER BY `{$sort}` {$order} 
				LIMIT {$start}, {$rows}";
		$rows = $this->get_db()->fetchAll($sql);
		
		return compact('total', 'rows');
	}
	
	/**
	 * 根据文件是否存在来更新数据库，如果文件不存在了，将对应文档的生成时间标识为0
	 */
	private function _synFileDb()
	{
		$select = $this->get_db()->select();
		$select->from($this->_table, array('page_id', 'url'));
		$query = $this->get_db()->query($select);
		
		$update_ids = array();
		
		$html_path = Cms_Template::getHtmlPath($this->tpl_id);
		while($rs = $query->fetch())
		{
			$file = $html_path . $rs['url'];
			if(!file_exists($file))
			{
				$update_ids[] = $rs['page_id'];
			}
		}
		
		if($update_ids)
		{
			$in = implode(',', $update_ids);
			$this->get_db()->update($this->_table, array('html_time'=>0), "page_id IN ({$in})");
		}
	}
	
	function save($data)
	{
		$page_id = (int) $data['page_id'];
		$data = $this->data($data);
		
		if(empty($data['entity_id']))
		{
			$data['entity_id'] = $data['entity_id'] = '';
		}
		
		if($page_id)
		{
			$this->update($data, "`page_id`={$page_id}");
		}
		else
		{
			$this->get_db()->insert($this->_table, $data);
			$page_id = $this->get_db()->lastInsertId();
		}
		
		return $page_id;
	}
	
	function delete($page_id)
	{
		$where = "`page_id`={$page_id}";
		$this->update(array('html_time'=>0), $where);
		return parent::deleted($where);
	}
	
	function disable($page_id)
	{
		$where = "`page_id`={$page_id}";
		$this->update(array('html_time'=>0), $where);
		return parent::disable($where);
	}
	
	function able($page_id)
	{
		parent::able("`page_id`={$page_id}");
		return true;
	}
	
	/**
	 * 更新页面信息
	 * 
	 * @param array $data
	 * @param string $where
	 */
	function update($data, $where)
	{
		return $this->get_db()->update($this->_table, $data, $where);
	}
	
	/**
	 * 根据实体ID获取页面信息
	 * 
	 * @param integer	$entity_id
	 * @param array		$data		和实体ID确定唯一的其它字段
	 */
	function getInfoByEntityId($entity_id, array $data=array())
	{
		$condition = "`entity_id`={$entity_id}";
		foreach($data as $field => $value)
		{
			$condition .= $this->get_db()->quoteInto(" AND `{$field}`=?", $value);
		}
		
		$sql = "SELECT * FROM `{$this->_table}` WHERE {$condition}";
		return $this->get_db()->fetchRow($sql);
	}
	
	/**
	 * 给静态生成时，提取页面用
	 * 
	 * @param integer $offset 开始偏移
	 * @param string $page_ids 记录的ids，如果为null，则提取全部state=able的记录
	 */
	function getInfoList($offset, $page_ids=null)
	{
		$condition = $this->able_condition;
		if(null != $page_ids)
		{
			$condition = "`page_id` IN ({$page_ids}) AND {$condition}";
		}
		
		$total = $this->get_db()->fetchOne("SELECT count(*) FROM {$this->_table} WHERE {$condition}");
		
		$limit = intval(sqrt($total));
		
		$sql = "SELECT * FROM `{$this->_table}` WHERE {$condition} LIMIT {$offset}, {$limit}";
		$rows = $this->get_db()->fetchAll($sql);
		return compact('total', 'limit', 'rows');
	}
	
	/**
	 * 获取非删除状态的页面数量，供删除模版时用
	 */
	function getValidCount()
	{
		$sql = "SELECT count(*) FROM `{$this->_table}` WHERE `status`<>'deleted'";
		return $this->get_db()->fetchOne($sql);
	}
	
	/**
	 * 获取所有页面信息，主要供全站搜索同步表
	 */
	function getInfoAll()
	{
		$sql = "SELECT 
					`page_id`, 
					`title`,
					`keyword`,
					`url`,
					`entity_id`,
					`entity_name`,
					`html_time`,
					`status` 
				FROM `{$this->_table}`";
		$rows = $this->get_db()->fetchAll($sql);
		
		$return = array();
		foreach($rows as $row)
		{
			$return[$row['page_id']] = $row;
		}
		return $return;
	}
	
	/**
	 * 获取所有生成的URL，只提供给静态生成时使用
	 */
	function getAllCreatedUrl($condition)
	{
		static $return = null;
		if(null === $return)
		{
			$condition .= " AND {$this->able_condition}";
			$sql = "SELECT `url` FROM `{$this->_table}` WHERE {$condition}";
			$rows = $this->get_db()->fetchAll($sql);
			
			$return = array();
			foreach($rows as $row)
			{
				$return[] = $row['url'];
			}
		}
		
		return $return;
	}
}