<?php

/**
 * 块模型
 * 
 * @author 刘通
 */
class Template_Models_Block extends ZendX_Cms_Model
{
	
	protected $_schema = 'oss';
	protected $_table = 'TEMPLATE_BLOCK';
	protected $pk = 'blk_id';
	function getList($site_id, $start, $count, $search=array())
	{
		$where = "`site_id`={$site_id} AND {$this->able_condition}";
		
		$total = $this->get_db()->fetchOne("SELECT count(*) FROM `{$this->_table}` WHERE {$where}");
		$sql = "SELECT `blk_id` AS `id`, `blk_id`, `name`, `file_path`, `edit_time`, `user_name`
				FROM `{$this->_table}`
				WHERE {$where}
				ORDER BY `blk_id` DESC
				LIMIT {$start}, {$count}";
		$rows = $this->get_db()->fetchAll($sql);
		return compact('total', 'rows');
	}
	
	function save($data)
	{
		$blk_id = (int) $data['blk_id'];
		$data = $this->data($data);
		
		if($blk_id)
		{
			$this->get_db()->update($this->_table, $data, "`blk_id`={$blk_id}");
		}
		else
		{
			$this->get_db()->insert($this->_table, $data);
			$blk_id = $this->get_db()->lastInsertId();
		}
		
		return $blk_id;
	}
	
	function delete($blk_id)
	{
		parent::deleted("`blk_id`={$blk_id}");
		return true;
	}
	
	/**
	 * 查看其它站点的html_path，是否有重复的
	 * 
	 * @param integer	$blk_id
	 * @param string	$html_path
	 * @param string	$file_path
	 */
	function checkFilePath($blk_id, $html_path, $file_path)
	{
		$sql = "SELECT `blk_id` FROM `{$this->_table}` 
				WHERE `html_path`='{$html_path}' AND `file_path`='{$file_path}' AND {$this->able_condition}";
		$db_id = $this->get_db()->fetchOne($sql);
		
		if($db_id && $db_id != $blk_id)
		{
			return false;
		}
		
		return true;
	}
}