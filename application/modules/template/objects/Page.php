<?php

/**
 * Alter TEMPLATE_PAGE_\d表
 * 
 * @author 刘通
 */
class Template_Objects_Page extends ZendX_Cms_Model {
	protected $_schema = 'oss';
	protected $_table = 'TEMPLATE_PAGE_';
	function __construct($tpl_id) {
		parent::__construct();
		$this->setTable($tpl_id);
	}
	
	function setTable($tpl_id)
	{
		$this->_table = 'TEMPLATE_PAGE_' . $tpl_id;
	}
	
	function addField(array $data)
	{
		if(!empty($data['field_id']))
		{
			$sql = "ALTER TABLE `{$this->_table}` CHANGE COLUMN `{$data['field_name']}` `{$data['field_name']}` {$data['field_type']}";
		}
		else 
		{
			$sql = "ALTER TABLE `{$this->_table}` ADD COLUMN `{$data['field_name']}` {$data['field_type']}";
		}
		
		if($data['field_len'] > 0)
		{
			$sql .= "({$data['field_len']})";
		}
		
		$sql .= " NOT NULL";
		
		$this->get_db()->query($sql);
	}
}