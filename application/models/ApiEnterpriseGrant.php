<?php
/** 
 * 添加数据表模型
 * 
 * @author etong <zhoufeng@wondershare.cn>
 * @version $v1 2014/07/30 14:46 $
 */
class Model_ApiEnterpriseGrant extends ZendX_Model_Base {
	protected $_schema = 'cloud';
	protected $_table = 'EZ_API_ENTERPRISE_GRANT';
	protected $pk = 'id';
	
	/**
	 * 获取所有权限
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
	 * 获得总记录数
	 */
	public function getTotal($query = "")
	{
		if(empty($query)){
			$sql = "SELECT count(*) AS total FROM `{$this->_table}` AS t";
		}else{
            $sql = "SELECT count(*) AS total FROM `{$this->_table}` AS t WHERE ".$query;
		}
		return $this->get_db()->fetchOne($sql);
	}
    
	/**
	 * 获取权限表和厂商表的混合集合
	 * 
	 * @param string $id
	 */
	public function getListMixed($query = "", $offset, $rows) {
        
        $sql = "SELECT a.name as username, a.company_name, t.id, t.ctime, t.name, t.app_key, t.status FROM `EZ_ENTERPRISE` as a INNER JOIN `{$this->_table}` as t ON a.id = t.enterprise_id ";
		if(empty($query)){
			$sql .= " ORDER BY t.id DESC limit $offset,$rows";
		}else{
			$sql .= " WHERE ".$query." ORDER BY t.id DESC limit $offset,$rows";
		}
		return $this->get_db()->fetchAll($sql);
	}
    
	/**
	 * 获取权限表和厂商表的详情
	 * 
	 * @param string $id
	 */
	public function getItemMixed($query) {
        
        $sql = "SELECT t.*, a.name as username, a.company_name FROM `EZ_ENTERPRISE` as a INNER JOIN `{$this->_table}` as t ON a.id = t.enterprise_id ";
		if(!empty($query)){
			$sql .= " WHERE ".$query."";
		}
		return $this->get_db()->fetchAll($sql);
	}
}