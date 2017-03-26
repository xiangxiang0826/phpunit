<?php
/**
 * 控件功能管理
 * @author liujd
 *
 */
class Model_Control extends ZendX_Model_Base {
	protected $_schema = 'cloud';
	protected $_table = 'EZ_CONTROL';
	
	/* 根据条件列出所有产品 */
	public function getList($query = "",$offset,$rows) {
		$condition_string = " 1 ";  // 默认是 where 1
		$queryString = array();
		if(!empty($query['name'])) {
			$queryString[] =  $this->get_db()->quoteInto("a.`name` LIKE ?", "%{$query['name']}%");
		}
		if(!empty($query['control_type_id'])) {
			$queryString[] = $this->get_db()->quoteInto("a.`control_type_id` =  ?", $query['control_type_id']);
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
	 *  删除control
	 *  @param  $id int
	 */
	public function delete($id) {
		return $this->get_db()->delete($this->_table, array('id =?' => $id));
	}
}