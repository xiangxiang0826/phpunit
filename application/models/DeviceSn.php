<?php

class Model_DeviceSn extends ZendX_Model_Base
{
	protected $_schema = 'cloud';
	protected $_table = 'EZ_DEVICE_SN';
	
	/**
	 * 根据 device_id 获取sn号码
	 * @param int $id
	 * @return string
	 */
	public function getDeviceById($id)
	{
		$sql = "SELECT sn FROM {$this->_table} WHERE device_id='$id'";
		return $this->get_db()->fetchOne($sql);
		
	}	
}