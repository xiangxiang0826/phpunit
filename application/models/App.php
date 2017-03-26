<?php
/**
 * 企业APP Model
 * @author lvyz@wondershare.cn
 * 这个model在OSS中没有被别的类引用
 *
 */
class Model_Enterprise_App extends ZendX_Model_Base
{
	protected $_schema = 'cloud';
	protected $_table = 'EZ_ENTERPRISE_APP';
	
	/**
	 * 根据UID获取APP信息
	 * @param int $uid
	 * @return mixed
	 */
	public function getAllByUid($uid)
	{
		$select = $this->select()->from($this->_table)
			->where('enterprise_id=?', $uid);
		$rows = $this->getDb()->fetchAll($select);
		if(!empty($rows)) {
			$items = array();
			foreach($rows as $v) {
				$items[$v['platform']] = $v;
			}
			return $items;
		}
		return false;
	}
	
	/**
	 * 创建企业APP
	 * @param int $uid
	 * @param array $data
	 * @param string $paltform
	 * @return id
	 */
	public function create($uid, $data, $platform)
	{
		$app = $this->getByPlatform($uid, $platform);
		if(empty($app)) {
			$data['ctime'] = date('Y-m-d H:i:s');
			$data['mtime'] = date('Y-m-d H:i:s');
			$data['platform'] = $platform;
			$id = $this->insert($data);
		} else {
			$data['mtime'] = date('Y-m-d H:i:s');
			$this->update($data, array("enterprise_id=$uid", "platform='$platform'"));
			$id = $app['id'];
		}
		return $id;
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
		
		return $this->getDb()->fetchRow($select);
	}
	
	/**
	 * 获取遥控E族对应平台APP信息
	 * @param $platform
	 */
	public function getEzuAppByPlatform($platform)
	{
		$select = $this->select()->from($this->_table)
			->where('is_ezapp=1')
			->where('platform=?', $platform);
	
		return $this->getDb()->fetchRow($select);
	}
	
	/**
	 * 获取遥控E族的APP
	 * @return mixed
	 */
	public function getEzuApps()
	{
		$select = $this->select()->from($this->_table)
			->where('is_ezapp=1');
		
		$rows = $this->getDb()->fetchAll($select);
		if(!empty($rows)) {
			$items = array();
			foreach($rows as $v) {
				$items[$v['platform']] = $v;
			}
			return $items;
		}
		return false;
	}
	
	/**
	 * 根据 appid 和 Uid 获取APP详情信息
	 * @return mixed
	 */
	public function getByAppId($appid,$Uid)
	{
		$select = $this->select()->from($this->_table)
		->where('id=?',$appid)
		->where('enterprise_id=?',$Uid);
		return $this->getDb()->fetchRow($select);
	}
}