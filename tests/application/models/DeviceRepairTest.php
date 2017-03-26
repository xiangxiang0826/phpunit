<?php

class DeviceRepairTest extends EZX_Framework_DatabaseTestCase {

	protected function getDataSet() {
		$ds =   $this->createFlatXMLDataSet(dirname(__FILE__) . '/_files/device_repair.xml');
		$ds1 =   $this->createFlatXMLDataSet(dirname(__FILE__) . '/_files/service_repair_attach.xml');
		$compositeDs = new PHPUnit_Extensions_Database_DataSet_CompositeDataSet(array($ds, $ds1));
		return $compositeDs;
	}
	
	public function testGetDetailInfo(){
		$model = new Model_DeviceRepair();
		$info = $model->getDetailInfo(100);
		$this->assertEquals(100, $info['id']);
		$this->assertEquals(2, count($info['attachment']));
		$info = $model->getDetailInfo(101);
		$this->assertEquals(0, count($info['attachment']));
	}
	
	public function testGetHistoryList(){
		$model = new Model_DeviceRepair();
		$deviceId = 'admin';
		$info = $model->getHistoryList($deviceId);
		$this->assertEquals(2, count($info));
	}
	

	

}