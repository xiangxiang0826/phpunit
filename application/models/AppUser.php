<?php
/**
 * 终端会员表
 * @author lvyz@wondershare.cn
 *
 */
class Model_AppUser extends ZendX_Model_Base
{
	protected $_schema = 'cloud';
	protected $_table = 'EZ_APP_USER';
	
	/**
	 * 在线反馈获取用户相关的APP信息
	 * @return mixed
	 */
	public function getUserByAppId($appid)
	{
		$select = $this->select()->from($this->_table)
		->where('id=?',$appid);
		return $this->get_db()->fetchRow($select);
	}
	
	/**
	 *
	 * @param unknown_type $query
	 * @param unknown_type $startTime
	 * @param unknown_type $endTime
	 * @return Ambigous <string, boolean, mixed>
	 */
	public function getAppCount($query, $startTime='', $endTime=''){
		$select = $this->select()->from($this->_table,array('total'=>'COUNT(1)'));
		foreach($query as $key=>$val){
			$select->where("$key = ?", $val);
		}
		if(!empty($startTime))
			$select->where('ctime >= ?', $startTime);
		if(!empty($endTime))
			$select->where('ctime <= ?', $endTime);
		return $this->get_db()->fetchOne($select);
	}
	
	/**
	 * 按时间统计APP安装量
	 * @param unknown_type $query
	 * @param unknown_type $startTime
	 * @param unknown_type $endTime
	 * @return Ambigous <string, boolean, mixed>
	 */
	public function statByDate($query, $startTime='', $endTime=''){
		$select = $this->select()->from($this->_table,array('total'=>'COUNT(1)','date'=>'DATE_FORMAT(ctime, \'%Y-%m-%d\')'));
		if(!empty($startTime))
			$select->where('ctime >= ?', $startTime);
		if(!empty($endTime))
			$select->where('ctime < ?', $endTime);
		$select->group('date');
		return $this->get_db()->fetchAll($select);
	}
}