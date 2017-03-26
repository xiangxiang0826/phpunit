<?php

class Model_OnlineFeedbackTag extends ZendX_Model_Base
{
	protected $_schema = 'cloud';
	protected $_table = 'EZ_ONLINE_FEEDBACK_TAG';	
	
	/* 获取指定标签 */
	public function getList($where){
		$where = $where ? 'WHERE '.$where:'';		
		$sql = "SELECT id,name FROM {$this->_table} ". $where ;
		return $this->get_db()->fetchAll($sql);
	}
	
	/* 获取所有标签  */
	public function getClientList(){
		$sql = "SELECT id,name FROM {$this->_table} ";
		return $this->get_db()->fetchAll($sql);
	}
}