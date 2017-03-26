<?php

class Model_OnlineFeedbackMessage extends ZendX_Model_Base
{
	protected $_schema = 'cloud';
	protected $_table = 'EZ_ONLINE_FEEDBACK_MESSAGE';	
	
	public function getList($id){
		$sql = "SELECT fm.content_type, fm.type, fm.content, fm.ctime, fm.user_id, fm.reply_user, u.name FROM {$this->_table} fm
				LEFT JOIN EZ_USER u ON fm.user_id=u.id
				where feedback_id = ?";
		$sql = $this->get_db()->quoteInto($sql, $id);
		return $this->get_db()->fetchAll($sql);
	}	
}