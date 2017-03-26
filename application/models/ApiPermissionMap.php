<?php
/** 
 * 添加数据表模型
 * 
 * @author etong <zhoufeng@wondershare.cn>
 * @version $v1 2014/08/09 21:42 $
 */
class Model_ApiPermissionMap extends ZendX_Model_Base {
	protected $_schema = 'cloud';
	protected $_table = 'EZ_API_PERMISSION_MAP';
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
	 * 添加企业用户的默认权限
	 * @param string $userType 企业类型 
	 * @param int $apiId 企业API ID
	 * @return boolean
	 */
	public function addEnterprisePer($userType, $apiId){
		if($userType == 'company') {
			$permissionLabel = 'general_et_api';
		} elseif($userType == 'personal') {
			$permissionLabel = 'personal_et_api';
		} else {
			return false;
		}
		$sql = "SELECT permission FROM `EZ_API_PERMISSION_TEMPLATE` WHERE label='{$permissionLabel}'";
		$permissionStr = $this->get_db()->fetchOne($sql);
		$permissionArr = explode(',', $permissionStr);
		foreach ($permissionArr as $row) {
			$bind = array(
					'api_id' => $apiId,
					'perm_id' => $row
			);
			$this->insert($bind);
		}
	}
}