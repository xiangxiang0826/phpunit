<?php

class Model_FeedbackTagMap extends ZendX_Model_Base
{
	protected $_schema = 'cloud';
	protected $_table = 'EZ_FEEDBACK_TAG_MAP';	
	
	public function getList($id){
		if(empty($id)){
			return array();
		}
		$sql = "SELECT tag_id FROM {$this->_table} WHERE feedback_id='$id'";
		return $this->get_db()->fetchAll($sql);
	}
}