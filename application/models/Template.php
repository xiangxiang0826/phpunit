<?php
/**
 * 模板管理
 * @author liujd@wondershare.cn
 *
 */
class Model_Template extends ZendX_Model_Base {
	protected $_schema = 'cloud';
	protected $_table = 'EZ_TEMPLATE';
	
	/* 根据条件列出所有模板 */
	public function GetList($query = "",$offset,$rows) {
		$condition_string = " 1 ";  // 默认是 where 1
		$queryString = array();
		if(!empty($query['name'])) {
			$queryString[] =  $this->get_db()->quoteInto("a.`name` LIKE ?", "%{$query['name']}%");
		}
		if(!empty($query['template_type_id'])) {
			$queryString[] = $this->get_db()->quoteInto("a.`template_type_id` =  ?", $query['template_type_id']);
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
		$sql = "DELETE FROM {$this->_table} WHERE id = ? ";
		return $this->get_db()->query($sql, array($id));
	}
}