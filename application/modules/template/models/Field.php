<?php

/**
 * template_fields model
 * 
 * @author 刘通
 */
class Template_Models_Field extends ZendX_Cms_Model {
	
	protected $_schema = 'oss';
	protected $_table = 'TEMPLATE_FIELD';
	protected $pk = 'field_id';
	function getList($tpl_id)
	{
		$tpl_id = (int) $tpl_id;
		$sql = "SELECT 
					`field_id` AS `id`, 
					`field_id`, 
					`field_name`, 
					`field_type`, 
					`input_label`
				FROM `{$this->_table}`
				WHERE `tpl_id`={$tpl_id} AND {$this->able_condition}";
		$rows = $this->get_db()->fetchAll($sql);
		return array('rows'=>$rows);
	}
	
	function save($data)
	{
		$field_id = $data['field_id'];
		$data = $this->data($data);
		
		if($field_id)
		{
			$this->get_db()->update($this->_table, $data, "`field_id`={$field_id}");
		}
		else
		{
			$this->get_db()->insert($this->_table, $data);
			$field_id = $this->get_db()->lastInsertId();
		}
		
		return $field_id;
	}
	
	/**
	 * 根据模版ID获取字段信息
	 * 
	 * @param integer $tpl_id
	 */
	function getInfoByTid($tpl_id)
	{
		$tpl_id = (int) $tpl_id;
		$sql = "SELECT 
					`field_id` AS `id`, 
					`field_id`, 
					`field_name`, 
					`field_type`, 
					`input_label`, 
					`input_type`,
					`input_option`, 
					`input_width`, 
					`input_height`, 
					`input_value`,
					`is_unique`,
					`is_null`
				FROM `{$this->_table}`
				WHERE `tpl_id`={$tpl_id} AND {$this->able_condition}
				ORDER BY `order_edit`";
		$rows = $this->get_db()->fetchAll($sql);
		return $rows;
	}
	
	/**
	 * 验证字段名是否合法
	 */
	function checkFieldName($field_name, $tpl_id)
	{
		$tpl_id = (int) $tpl_id;
		
		if(!$field_name)
		{
			return false;
		}
		
		if(preg_match('/[^a-z\d_]/', $field_name))
		{
			return false;
		}
		
		$count = $this->get_db()->fetchOne("SELECT count(*) FROM `{$this->_table}` WHERE `tpl_id`={$tpl_id} AND `field_name`='{$field_name}'");
		if($count)
		{
			return false;
		}
		
		$sql = "SHOW COLUMNS FROM `TEMPLATE_PAGE_{$tpl_id}`";
		$rows = $this->get_db()->fetchAll($sql);
		foreach($rows as $row)
		{
			if($row['Field'] == $field_name)
			{
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * 删除字段
	 */
	function delete($field_id)
	{
		parent::deleted("`field_id`={$field_id}");
		return true;
	}
	
	/**
	 * 获取可搜索和可列表的字段
	 */
	function getSearchFields($tpl_id)
	{
		$sql = "SELECT `field_name`, `input_label` FROM `{$this->_table}` WHERE `tpl_id`={$tpl_id} AND `is_search`='Y' AND {$this->able_condition}";
		return $this->get_db()->fetchAll($sql);
	}
	
	/**
	 * 获取可以列表的字段
	 */
	function getListFields($tpl_id)
	{
		$sql = "SELECT `field_name`, `input_label`, `input_width` FROM `{$this->_table}` WHERE `tpl_id`={$tpl_id} AND `is_list`='Y' AND {$this->able_condition}";
		return $this->get_db()->fetchAll($sql);
	}
}