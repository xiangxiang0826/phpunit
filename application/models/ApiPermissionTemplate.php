<?php
/** 
 * 添加数据表模型
 * 
 * @author etong <zhoufeng@wondershare.cn>
 * @version $v1 2014/08/12 10:39 $
 */
class Model_ApiPermissionTemplate extends ZendX_Model_Base {
	protected $_schema = 'cloud';
	protected $_table = 'EZ_API_PERMISSION_TEMPLATE';
	protected $pk = 'id';
    
	/**
	 * 获取权限表和厂商表的详情数组
	 * 
	 * @param array $condition
	 */
	public function getItemsByWhere($condition) {
        $select = $this->get_db()->select();
		$select->from(array('D' => $this->_table));
		if(!empty($condition)) {
			foreach ($condition as $key=>$value) {
                $select->where("$key=?", $value);
			}
		}
        // 但是，读取数据的方法相同
        $sql = $select->__toString();
        return $this->get_db()->fetchAll($sql);
	}
    
	/**
	 *  删除数据
     *  
	 *  @param  $where string
	*/
	public function deleteByWhere($where) {
        $where = $where?$where:'1=0';
		$sql = "DELETE  FROM {$this->_table} WHERE ".$where;
        // print_r($sql);exit(',ln='.__line__);
		return $this->get_db()->query($sql);
	}
    
	/**
	 * 获取权限表和厂商表的详情
	 * 
	 * @param string $id
	 */
	public function getItemMixed($query) {
        $sql = "SELECT t.*, a.name as auth_name, a.status as auth_status FROM `EZ_API_PERMISSION` as a INNER JOIN `{$this->_table}` as t ON a.id = t.perm_id ";
		if(!empty($query)){
			$sql .= " WHERE ".$query."";
		}
		return $this->get_db()->fetchAll($sql);
	}
    
	/**
	 * 获取所有记录
	 */
	function getList($query = "", $offset, $rows)
	{
		if(empty($query)){
			$sql = "SELECT  t.* FROM `{$this->_table}` AS t   ORDER BY t.id DESC limit $offset,$rows";
		}else{
			$sql = "SELECT  t.* FROM `{$this->_table}` AS t   WHERE ".$query." ORDER BY t.id DESC limit $offset,$rows";
		}
		return $this->get_db()->fetchAll($sql);
	}
    
	/**
	 * 获取所有记录
	 */
	function getAllList($query = "")
	{
		if(empty($query)){
			$sql = "SELECT  t.* FROM `{$this->_table}` AS t   ORDER BY t.id ASC";
		}else{
			$sql = "SELECT  t.* FROM `{$this->_table}` AS t   WHERE ".$query." ORDER BY t.id ASC";
		}
		return $this->get_db()->fetchAll($sql);
	}
    
	/**
	 * 获得总记录数
	 */
	public function getTotal($query = "")
	{
		if(empty($query)){
			$sql = "SELECT count(*) AS total FROM `{$this->_table}` AS t";
		}else{
            $sql = "SELECT count(*) AS total FROM `{$this->_table}` AS t WHERE ".$query;
		}
		return $this->get_db()->fetchAll($sql);
	}
}