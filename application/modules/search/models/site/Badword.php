<?php

/**
 * site_badwords表的model
 * 
 * @author 刘通
 */
class Search_Models_Site_Badword extends ZendX_Cms_Model
{
	function __construct()
	{
		parent::__construct();
		$this->table = 'site_badwords';
	}
	
	function getAll($site_id)
	{
		$sql = "SELECT `word` FROM `{$this->table}` WHERE `site_id`={$site_id}";
		$rows = $this->db->fetchAll($sql);
		
		$return = array();
		foreach($rows as $row)
		{
			$return[] = $row['word'];
		}
		
		return $return;
	}
}