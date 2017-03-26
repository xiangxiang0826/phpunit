<?php
/**
 * 开发平台产品功能管理
 * @author liujd
 *
 */
class Model_ProductAction extends ZendX_Model_Base {
	protected $_schema = 'cloud';
	protected $_table = 'EZ_PRODUCT_ACTION';
	
	const STATUS_ENABLE = 'enable';
	const STATUS_DISABLE = 'disable';
	const STATUS_DELETE = 'deleted';
	
	/* 根据条件列出所有产品 */
	public function GetList($query = "",$offset,$rows) {
		$condition_string = " `status`  <> '". self::STATUS_DELETE."'";  // 默认是 where 1
		$queryString = array();
		if(!empty($query['product_category_id'])) {
			$queryString[] =  $this->get_db()->quoteInto("a.`product_category_id` = ?", "{$query['product_category_id']}");
		}
		if(!empty($query['name'])) {
			$queryString[] = $this->get_db()->quoteInto("a.`name` LIKE  ?", "%{$query['name']}%");
		}
		
		if(!empty($query['action'])) {
			$queryString[] = $this->get_db()->quoteInto("a.`action`  LIKE  ?", "%{$query['action']}%");
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
		$data['status'] = self::STATUS_DELETE;
		return $this->get_db()->update($this->_table, $data, array("id = '{$id}'"));
	}
    
    /**
     * 带追加条件的查询
     * 
     * @param array $fields
     * @param array $params
     * @param string $addCondition
     * @return array
     */
	public function getRowByFieldWithAddition($fields, $params, $addCondition = '') {
		$select = $this->select();
		$select->from($this->_table, $fields);
		foreach($params as $field => $value) {
			$select->where("`{$field}` = ?", $value);
		}
        if($addCondition){
            $select->where($addCondition);
        }
		$result = $this->get_db()->fetchRow($select);
		return $result;
	}
}