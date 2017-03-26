<?php
/**
 * 控件类型功能管理
 * @author liujd
 *
 */
class Model_ControlType extends ZendX_Model_Base {
	protected $_schema = 'cloud';
	protected $_table = 'EZ_CONTROL_TYPE';
	
	const STATUS_ENABLE = 'enable';
	const STATUS_DISABLE = 'disable';
	
	/* 根据条件列出所有控件类型 */
	public function getList($query = "",$offset,$rows) {
		$condition_string = "1";  // 默认是 where 1
		$queryString = array();
		if(!empty($query['name'])) {
			$queryString[] = $this->get_db()->quoteInto("a.`name` LIKE  ?", "%{$query['name']}%");
		}
		
		if(!empty($queryString)) {
			$condition_string = implode(" AND ", $queryString);
		}
		$sql = "SELECT count(a.id) as counts FROM `{$this->_table}`  a WHERE  {$condition_string} LIMIT 1";
		$data = $this->get_db()->fetchRow($sql);
		if($data['counts'] < 1) {
			return $data;
		}
		$sql = "SELECT a.* FROM `{$this->_table}` a  WHERE {$condition_string} ORDER BY a.`id` DESC LIMIT {$offset},{$rows}";
		$data['list'] = $this->get_db()->fetchAll($sql);
		return $data;
	}
	

	/**
	 *  删除action 
	 *  @param  $id int 
	*/
	public function delete($id) {
		$sql = "SELECT id FROM EZ_CONTROL WHERE control_type_id = ? LIMIT 1";
		$row = $this->get_db()->fetchRow($sql, $id);
		if($row) return false;
		return $this->get_db()->delete($this->_table, array('id =?' => $id));
	}
}