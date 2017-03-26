<?php
/**
 * 企业应用模型
 * @author zhouxh
 */
class Model_EnterpriseApp extends ZendX_Model_Base
{
	protected $_schema = 'cloud';
	protected $_table = 'EZ_ENTERPRISE_APP';
	const PLANTFORM_IOS = 'ios';
	const PLANTFORM_ANDROID = 'android';
	
	/**
	 * 查询企业e族APP列表
	 */
	public function getList($query = "", $offset, $rows){
		if(empty($query)) {
		$sql = "SELECT a.id,a.enterprise_id,e.company_name,e.label,a.name,a.ctime,a.version,temp.v_num,temp.upgrade_type_id,m.icon AS logo FROM EZ_ENTERPRISE_APP AS a LEFT JOIN EZ_ENTERPRISE AS e ON  a.enterprise_id=e.id LEFT JOIN EZ_ENTERPRISE_APP_MATERIAL as m ON a.id = m.app_id LEFT JOIN (SELECT upgrade_type_id,ctime,COUNT(*) AS v_num FROM EZ_UPGRADE_VERSION GROUP BY upgrade_type_id) AS temp
ON a.upgrade_type_id= temp.upgrade_type_id WHERE is_ezapp=0 AND a.type IN('edit','develop') AND pack_status='finish' ORDER BY temp.ctime DESC LIMIT :offset,:limit";
		} else {
			$sql = "SELECT a.id,a.enterprise_id,e.company_name,e.label,a.name,a.ctime,a.version,temp.v_num,temp.upgrade_type_id,m.icon AS logo FROM EZ_ENTERPRISE_APP AS a LEFT JOIN EZ_ENTERPRISE AS e ON  a.enterprise_id=e.id LEFT JOIN EZ_ENTERPRISE_APP_MATERIAL as m ON a.id = m.app_id LEFT JOIN (SELECT upgrade_type_id,ctime,COUNT(*) AS v_num FROM EZ_UPGRADE_VERSION GROUP BY upgrade_type_id) AS temp
ON a.upgrade_type_id= temp.upgrade_type_id WHERE is_ezapp=0 AND a.type IN('edit','develop') AND pack_status='finish' AND {$query} ORDER BY temp.ctime DESC LIMIT :offset,:limit";
		}
		$smtp = $this->get_db()->prepare($sql);
		$smtp->bindParam(':offset', $offset, PDO::PARAM_INT);
		$smtp->bindParam(':limit', $rows, PDO::PARAM_INT);
		$smtp->execute();
		return $smtp->fetchAll();
	}
	
	/**
	 * 获得总记录数
	 */
	public function getTotal($query = "")
	{
		if(empty($query)){
			$sql = "SELECT count(*) AS total FROM `{$this->_table}` AS a WHERE is_ezapp=0 AND a.type IN('edit','develop') AND pack_status='finish'" ;
		}else{
			$sql = "SELECT count(*) AS total FROM `{$this->_table}` AS a  WHERE is_ezapp=0 AND a.type IN('edit','develop') AND pack_status='finish' AND ".$query;
		}
		return $this->get_db()->fetchOne($sql);
	}
	/**
	 * 查看详情
	 * @param int $id
	 */
	public function detail($id) 
	{
		$sql = 'SELECT a.* FROM ' . $this->_table . ' AS a LEFT JOIN EZ_ENTERPRISE AS e ON  a.enterprise_id=e.id LEFT JOIN EZ_UPGRADE_VERSION AS v ON a.upgrade_type_id=v.upgrade_type_id WHERE a.id=?';
		$sql = $this->get_db()->quoteInto($sql, $id);
		$detail = $this->get_db()->fetchRow($sql);
		return $detail;
	}
	/**
	 * 查询APP
	 * @param array $condition
	 * @return boolean|Ambigous <string, boolean, mixed>
	 */
	public function getInfoByCondition(array $condition){
		if(empty($condition)) {
			return false;
		}
		$select = $this->get_db()->select();
		$select->from('EZ_ENTERPRISE_APP AS A', 'A.*');
		foreach ($condition as $where) {
	        $select->where($where);
		}
		$select->joinLeft('EZ_ENTERPRISE_APP_MATERIAL AS V', 'A.id=V.app_id', array('icon'));
		return $this->get_db()->fetchRow($select);
	}
	/**
	 * 生成查询条件
	 */
	public function createCondition($condition){
		$select = $this->select()->from(array('V'=>'EZ_UPGRADE_VERSION'));
		$select->joinLeft(array('A'=>$this->_table), "A.upgrade_type_id=V.upgrade_type_id", array('A.platform'));
		if(is_array($condition)){
			foreach ($condition as $k=>$v){
	             $select->where("A.{$k}=?",$v);
			}
		}
		$select->order('V.ctime DESC');
		$select->where('is_ezapp=1');
		return $select;
	}
	/**
	 * 查看app信息
	 * @param int $id
	 */
	public function getAppInfoByid($id, $lang='zh-cn') {
		$select = $this->select()->from(array('T'=>$this->_table));
		$select->where('T.id=?', $id);
		$select->joinLeft(array('e' => 'EZ_ENTERPRISE'), "e.id=T.enterprise_id", array('company_name'));
		$appInfo = $this->get_db()->fetchRow($select);
		if($appInfo) {
		    $sql = 'SELECT image FROM EZ_ENTERPRISE_APP_IMAGE WHERE app_id=?';
		    $sql = $this->get_db()->quoteInto($sql, $id);
		    $appInfo['images'] = $this->get_db()->fetchAll($sql);
		}
		if($appInfo) {
			$sql = 'SELECT name as m_name, icon,logo,start_page,description as m_description FROM EZ_ENTERPRISE_APP_MATERIAL WHERE app_id=? AND lang=?';
			$extInfo = $this->get_db()->fetchRow($sql, array($id, $lang));
			if($extInfo) {
				$appInfo = array_merge($appInfo,$extInfo);
			}
		}
		return $appInfo;
	}

	
	/**
	 * 更新app截图
	 * @param array $data
	 * @param int $appId
	 */
	public function updateImage(array $data, $appId){
		$sql = 'app_id=?';
		$sql = $this->get_db()->quoteInto($sql, $appId);
		$this->get_db()->delete('EZ_ENTERPRISE_APP_IMAGE', $sql);
		foreach ($data as $row) {
			$this->get_db()->insert('EZ_ENTERPRISE_APP_IMAGE', array('app_id' => $appId, 'image' =>$row));
		}
	}
	/**
	 * 查询语言列表
	 * @param int $appId
	 * @return Ambigous <multitype:, multitype:mixed >
	 */
	public function getLangList($appId) {
	     $sql ="SELECT DISTINCT(lang) FROM EZ_ENTERPRISE_APP_MATERIAL WHERE app_id=$appId";
	     $langs = $this->get_db()->fetchCol($sql);
	     return $langs;
	}
	/**
	 * 删除应用
	 * @param int $appId
	 */
	public function delApp($appId, $serverPath){
		$select = $this->get_db()->select();
		$select->from('EZ_UPGRADE_VERSION', array('file_path'));
		$select->where("id =?", $appId);
		$filePath = $this->get_db()->fetchOne($select);
		if($filePath) {
			try {
				unlink("$serverPath . $filePath");
				$sql = 'DELETE FROM EZ_UPGRADE_VERSION WHERE id=?';
				$sql = $this->get_db()->quoteInto($sql, $appId);
				if ($this->get_db()->query($sql) ) {
				   return true;	
				} else {
					return false;
				}
			} catch (Exception $e) {
				$log = new Logger();
				$log->info($e->getMessage());
				return false;
			}
		}
		return false;
	}
	/**
	 * 查询最新版本
	 * @param string $upgradeId
	 * @return mixed
	 */
	public function findLatestVersion($upgradeId){
		$select = $this->get_db()->select();
		$select->from('EZ_UPGRADE_VERSION', array('file_path', 'v_description'=>'description'))->where("upgrade_type_id={$upgradeId}");
		$select->order('version DESC');
		$version = $this->get_db()->fetchRow($select);
		return $version;
	}
	
	/**
	 * 获取企业某个平台的APP信息
	 * @param int $uid
	 * @param string $platform
	 * @return mixed
	 */
	public function getByPlatform($uid, $platform)
	{
		$select = $this->select()->from($this->_table)
		->where('enterprise_id=?', $uid)
		->where('platform=?', $platform);
	
		return $this->get_db()->fetchRow($select);
	}
	
	/**
	 * 获取企业某个平台的APP信息
	 * @param string $label
	 * @param string $platform
	 * @return mixed
	 */
	public function getByLabel($label, $platform)
	{
		$sql = " SELECT * FROM EZ_ENTERPRISE e JOIN {$this->_table} ep ON e.id=ep.enterprise_id WHERE  e.label='$label' AND ep.platform='$platform'";	
		return $this->get_db()->fetchRow($sql);
	}
}