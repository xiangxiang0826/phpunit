<?php
/**
 * ota升级模型
 * @author liujd@wondershare.cn
 * 2014-07-10
 */
class Model_Upgrade extends ZendX_Model_Base {
	protected $_schema = 'cloud';
	protected $_table = 'EZ_UPGRADE_TYPE';
	protected $_version_table = 'EZ_UPGRADE_VERSION';
    protected $_category_table = 'EZ_UPGRADE_CATEGORY';


    const DEVICE_TYPE_TEST = 'test';
	const DEVICE_TYPE_FORMAL = 'formal';
    
	const DEVICE_EXEC_TYPE_ALL = 'all';
	const DEVICE_EXEC_TYPE_INCREMENT = 'increment';
	
	const STATUS_ENABLE = 'enable';
	const STATUS_DISABLE = 'disable';
	
	const PUBLISHING_STATUS = 'publishing';
	const PUBLISHED_STATUS = 'published';
	const UNPUBLISHED_STATUS = 'unpublished';
    
    
    
	/**
	 * 获取升级类型列表
	 */
	function ListType($query = "",$offset,$rows) {
		$condition_string = '1';  // 默认是 where 1
		$queryString = array();
		if(!empty($query['id'])) {
			$ids = '';
			if(is_array($query['id'])) {
				$ids = implode(',',$query['id']);
			} else {
				$ids = $query['id'];
			}
			$queryString[] =  "a.`id` IN ({$ids})";
		}
		if(!empty($query['label'])) {
			$queryString[] = $this->get_db()->quoteInto("a.`label` =  ?", $query['label']);
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
	 * 获取升级版本列表
	 */
	function ListVersion($query = "", $offset, $rows) {
		$condition_string = '1';  // 默认是 where 1
		$queryString = array();
		if(!empty($query['upgrade_type_id'])) {
			$ids = '';
			if(is_array($query['upgrade_type_id'])) {
				$ids = implode(',',$query['upgrade_type_id']);
			} else {
				$ids = $query['upgrade_type_id'];
			}
			$queryString[] =  "a.`upgrade_type_id` IN ({$ids})";
		}
		if(isset($query['product_id'])) {
			$queryString[] = $this->get_db()->quoteInto("a.`product_id` =  ?", $query['product_id']);
		}
		if(!empty($queryString)) {
			$condition_string = implode(" AND ", $queryString);
		}
		$sql = "SELECT count(a.id) as counts FROM `{$this->_version_table}`  a WHERE  {$condition_string} LIMIT 1";
		$data = $this->get_db()->fetchRow($sql);
		if($data['counts'] < 1) {
			return $data;
		}
		$sql = "SELECT a.* FROM `{$this->_version_table}` a  WHERE {$condition_string} ORDER BY a.`id` DESC LIMIT {$offset},{$rows}";
		$data['list'] = $this->get_db()->fetchAll($sql);
		return $data;
	}
	
	/* 获取最新版本 */
	public function GetLatestVersion($upgrade_type_id, $device_type = 'formal') {
		$sql = "SELECT version,upgrade_type_id,product_id,ctime,exec_type,device_type,
		 NAME,description,file_path,check_sum,cuser,muser,mtime,is_force,`status`,publish_status,extend_field
		 FROM `{$this->_version_table}`  WHERE upgrade_type_id = ? AND `status` = '". self::STATUS_ENABLE ."' 
		 AND publish_status = '". self::PUBLISHED_STATUS ."' AND device_type = '{$device_type}' 
		 ORDER BY version DESC LIMIT 1";
		return $this->get_db()->fetchRow($sql, array($upgrade_type_id));
	}
	
	/* 根据特定字段获取版本信息 */
	public function getVersionByField($fields, $params) {
		$select = $this->select();
		$select->from($this->_version_table, $fields);
		foreach($params as $field => $value) {
			$select->where("`{$field}` = ?", $value);
		}
		$result = $this->get_db()->fetchRow($select);
		return $result;
	}
	
	/* 增加新版本 */
	public function InsertVersion($data) {
		return $this->get_db()->insert($this->_version_table, $data);
	}
	
	/* 编辑版本 */
	public function UpdateVersion($data, $where) {
		return $this->get_db()->update($this->_version_table, $data, $where);
	}
	
	/* 批量获取最新版本 */
	public function GetMaxVersionByIds($upgrade_type_ids) {
		if(empty($upgrade_type_ids)) return array();
		$ids = implode(',',$upgrade_type_ids);
		$sql = "SELECT MAX(version) AS version,upgrade_type_id,product_id,ctime,exec_type,device_type,
		NAME,description,file_path,check_sum,cuser,muser,mtime,is_force,STATUS,publish_status,extend_field
		FROM `{$this->_version_table}`  WHERE upgrade_type_id IN ({$ids}) AND status = '". self::STATUS_ENABLE ."' GROUP BY upgrade_type_id";
		$data =  $this->get_db()->fetchAll($sql);
		return $data ? $data : array();
	}
	
	/* 获取最新版本*/
	public function GetLatestByLabel($label, $device_type='formal') {
		$sql = "SELECT a.version,a.upgrade_type_id,a.product_id,a.ctime,a.exec_type,a.device_type,
		a.NAME,a.description,a.file_path,a.check_sum,a.cuser,a.muser,a.mtime,a.is_force,a.`status`,a.publish_status,a.extend_field
		FROM `{$this->_version_table}` a INNER JOIN {$this->_table} b ON a.upgrade_type_id = b.id  WHERE b.label = ? AND a.status='".self::STATUS_ENABLE."'
		AND a.publish_status = '". self::PUBLISHED_STATUS ."' AND a.device_type = '{$device_type}' ORDER BY a.version DESC LIMIT 1";
		return $this->get_db()->fetchRow($sql, array($label));
	}

	/**
	 * 获取所有资源列表
	 */
	function getListByType($query = "", $offset,$rows) {
        
        $condition_string = '1';  // 默认是 where 1
		$queryString = array('1');
		if(!empty($query['tid'])) {
			$queryString[] =  'a.`upgrade_type_id` = "'.$query['tid'].'"';
        }
		if(!empty($queryString)) {
			$condition_string = implode(" AND ", $queryString);
		}
        // print_r($condition_string);exit(',ln='.__line__);
		$sql = "SELECT count(a.id) as counts FROM `{$this->_version_table}`  a WHERE  {$condition_string} LIMIT 1";
		$data = $this->get_db()->fetchRow($sql);
		if($data['counts'] < 1) {
			return $data;
		}
		$sql = "SELECT a.* FROM `{$this->_version_table}` a  WHERE {$condition_string} ORDER BY a.`id` DESC LIMIT {$offset},{$rows}";
		$data['list'] = $this->get_db()->fetchAll($sql);
        return $data;
	}
    
    // 以下方法为OTA升级管理模块添加，区别标志，所有通过的OTA资源都是以G_开头的
    
	/**
	 * 获取所有资源列表
	 */
	function getList($query = "",$offset,$rows) {
		$condition_string = '1';  // 默认是 where 1
		$queryString = array('a.`cid` > 1');
		if(!empty($query['name'])) {
			$queryString[] =  'a.`name` like "%'.$query['name'].'%"';
        }
		if(!empty($query['cid'])) {
			$queryString[] =  'a.`cid` = "'.$query['cid'].'"';
        }
		if(!empty($queryString)) {
			$condition_string = implode(" AND ", $queryString);
		}
        // print_r($condition_string);exit(',ln='.__line__);
		$sql = "SELECT count(a.id) as counts FROM `{$this->_table}`  a WHERE  {$condition_string} LIMIT 1";
		$data = $this->get_db()->fetchRow($sql);
		if($data['counts'] < 1) {
			return $data;
		}
		$sql = "SELECT a.* FROM `{$this->_table}` a  WHERE {$condition_string} ORDER BY a.`id` DESC LIMIT {$offset},{$rows}";
		$data['list'] = $this->get_db()->fetchAll($sql);
        // print_r($data);exit(',ln='.__line__);
		return $data;
	}
    
    function getTypeList($offset = 0, $limit = 0){
		$sql = "SELECT * FROM `{$this->_category_table}` WHERE id > 1";
        $sqlAdd = '';
        if($limit){
            $sqlAdd = ' LIMIT '.$offset.','.$limit;
        }
		$data = $this->get_db()->fetchAll($sql.$sqlAdd);
        if(!$data && !is_array($data) ){
            $data = array();
        }
		return $data;
    }
    
    function getTypeTotal(){
		$sql = "SELECT count(*) as total FROM `{$this->_category_table}` WHERE id > 1";
		$data = $this->get_db()->fetchRow($sql);
        $count = 0;
        if( is_array($data) && isset($data['total']) ){
            $count = $data['total'];
        }
		return $count;
    }
    
    function getFullTypeList(){
        $condition_string = "label like 'G_%'";
		$sql = "SELECT * FROM `{$this->_table}` WHERE  {$condition_string}";
		$data = $this->get_db()->fetchAll($sql);
        if(!$data && !is_array($data) ){
            $data = array();
        }
		return $data;
    }
    
	/* 根据key=>value找记录 */
	public function getCategoryRowByField($fields, $params = array(), $paramsWithout = array()) {
		$select = $this->select();
		$select->from($this->_category_table, $fields);
		foreach($params as $field => $value) {
			$select->where("`{$field}` = ?", $value);
		}
		foreach($paramsWithout as $field => $value) {
			$select->where("`{$field}` != ?", $value);
		}
		$result = $this->get_db()->fetchRow($select);
		return $result;
	}
    
	/**
	 * 插入记录
	 * @param array $data
	 * @return bool
	 */
	public function insertCategory($data)
	{
		$ret = $this->get_db()->insert($this->_category_table, $data);
		return $ret ? $this->get_db()->lastInsertId($this->_category_table) : false;
	}
    
	/**
	 * 插入记录
	 * @param array $data
	 * @return bool
	 */
	public function updateCategory($data, $where)
	{
		$ret = $this->get_db()->update($this->_category_table, $data, $where);
		return $ret ? TRUE : false;
	}
    
	/**
	 * 删除记录
	 * @param array $where
	 * @return bool
	 */
	public function deleteCategory($where) {
		$result = $this->get_db()->delete($this->_category_table, $where);
		return $result;
	}
    
	/* *
     * 升级最新版本 
     */
	public function updateLatestVersion($data, $where) {
		$ret = $this->get_db()->update($this->_table, $data, $where);
		return $ret ? TRUE : false;
	}
	
	/**
	 * 删除版本
	 * @param int $versionId 版本Id
	 * @param string $basePath 版本包基本路径
	 * @return number
	 */
	public function deleteVersion($versionId, $basePath){
		$select = $this->select();
		$select->from($this->_version_table, 'file_path');
		$select->where('id = ?', $versionId);
		//删除版本关联的文件
		$version = $this->get_db()->fetchRow($select);
		$filePath = $basePath . $version['file_path'];
		if(file_exists($filePath)) {
			unlink($filePath);
		}
		$where = $this->get_db()->quoteInto('id = ?', $versionId);
		$result = $this->get_db()->delete($this->_version_table, $where);
		return $result;
	}
	
	/**
	 * 删除升级资源标识
	 * @param unknown_type $id
	 * @return number
	 */
	public function deleteUpgradeType($id){
		$where = $this->get_db()->quoteInto('id = ?', $id);
		$result = $this->get_db()->delete($this->_table, $where);
		return $result;
	}
	
	/**
	 * 通过资源标识查询该标识下的版本
	 * @param int $upgradeTypeId
	 * @return Ambigous <multitype:, multitype:mixed Ambigous <string, boolean, mixed> >
	 */
	public function getVersionIds($upgradeTypeId) {
		$select = $this->select();
		$select->from($this->_version_table, 'id');
		$select->where('upgrade_type_id = ?', $upgradeTypeId);
		$result = $this->get_db()->fetchCol($select);
		return $result;
	}
	
	/**
	 * 查询升级标识
	 * @param int $versionId
	 * @return Ambigous <string, boolean, mixed>
	 */
	public function getVersionUpgradeId($versionId){
		$select = $this->select();
		$select->from($this->_version_table, 'upgrade_type_id');
		$select->where('id = ?', $versionId);
		$upgradeTypeId = $this->get_db()->fetchOne($select);
		return $upgradeTypeId;
	}
	
	
}
?>