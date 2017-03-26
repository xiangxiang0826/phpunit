<?php

class Model_OnlineFeedback extends ZendX_Model_Base
{
	protected $_schema = 'cloud';
	protected $_table = 'EZ_ONLINE_FEEDBACK';
	
	/**
	 * 查找符合条件的在线反馈数
	 */
	public function getTotal($query){
		$sql = "SELECT COUNT(1) as total FROM {$this->_table} of 
				WHERE 1=1" . $query;
		return $this->get_db()->fetchOne($sql);
	}
	
	/**
	 * 查找符合条件的在线反馈数
	 */
	public function getCount($query, $subQuery){
		if($subQuery){
			$sql = "SELECT count(1) total FROM {$this->_table} of
					LEFT JOIN EZ_PRODUCT p ON of.product_id = p.id
					LEFT JOIN EZ_APP_USER au ON of.app_id = au.id
					LEFT JOIN EZ_USER u ON u.id = au.user_id
					LEFT JOIN EZ_ENTERPRISE e ON e.label = of.label
					JOIN ( SELECT  DISTINCT feedback_id FROM EZ_FEEDBACK_TAG_MAP $subQuery) sub ON sub.feedback_id = of.id
					WHERE 1=1" . $query ;
		}else{
			$sql = "SELECT count(1) total FROM {$this->_table} of
					LEFT JOIN EZ_PRODUCT p ON of.product_id = p.id
					LEFT JOIN EZ_APP_USER au ON of.app_id = au.id
					LEFT JOIN EZ_USER u ON u.id = au.user_id
					LEFT JOIN EZ_ENTERPRISE e ON e.label = of.label
					WHERE 1=1" . $query ;
		}
		return $this->get_db()->fetchOne($sql);;
	}
	
	/**
	 * 查找符合条件的在线反馈数组和反馈总数
	 */
	public function getList($query, $subQuery, $offset, $rows){
		if($subQuery){
			$sql = "SELECT of.*,p.name product_name, e.company_name enterprise_name FROM {$this->_table} of 
					LEFT JOIN EZ_PRODUCT p ON of.product_id = p.id
					LEFT JOIN EZ_APP_USER au ON of.app_id = au.id
					LEFT JOIN EZ_USER u ON u.id = au.user_id
					LEFT JOIN EZ_ENTERPRISE e ON e.label = of.label
					JOIN ( SELECT  DISTINCT feedback_id FROM EZ_FEEDBACK_TAG_MAP $subQuery) sub ON sub.feedback_id = of.id
					WHERE 1=1" . $query ." ORDER BY of.ctime DESC, of.reply_time DESC limit $offset, $rows";
		}else{
			$sql = "SELECT of.*,p.name product_name, e.company_name enterprise_name FROM {$this->_table} of 
					LEFT JOIN EZ_PRODUCT p ON of.product_id = p.id
					LEFT JOIN EZ_APP_USER au ON of.app_id = au.id
					LEFT JOIN EZ_USER u ON u.id = au.user_id
					LEFT JOIN EZ_ENTERPRISE e ON e.label = of.label
					WHERE 1=1" . $query ." ORDER BY of.ctime DESC, of.reply_time DESC limit $offset, $rows";
		}
		$res = array(); 
		
		$res['item'] = $this->get_db()->fetchAll($sql);
		return $res;
	}
	
	public function getFeedbackById($id){
		$sql = "SELECT * FROM {$this->_table} WHERE id='$id'";
		return $this->get_db()->fetchRow($sql);
	}
}