<?php
/**
 * 处理记录单元测试
 * @author zhouxh
 *
 */
class DeviceRepairRecordTest extends EZX_Framework_DatabaseTestCase {
	
	protected function getDataSet() {
		$ds =   $this->createFlatXMLDataSet(dirname(__FILE__) . '/_files/service_repair_record.xml');
		$ds1 =  $this->createFlatXMLDataSet(dirname(__FILE__) . '/_files/device_repair.xml');
		$compositeDs = new PHPUnit_Extensions_Database_DataSet_CompositeDataSet(array($ds, $ds1));
		return $compositeDs;
	}
	
	public function testGetRecordList(){
		$model = new Model_DeviceRepairRecord();
		$list = $model->getRecordList(100);
		$num = count($list);
		$this->assertEquals(2, $num);
		$list = $model->getRecordList(1);
		$this->assertEquals(array(), $list);
	}
	
	public function testAddRecord(){
		$model = new Model_DeviceRepairRecord();
		$repairModel = new Model_DeviceRepair();
		$repair = $repairModel->getFieldsById('mtime', 101);
		$res = $model->addRecord('content', '101', 'new');
		$this->assertNotEmpty($res);
		$row = $model->getFieldsById(array('content', 'status'), $res);
		$this->assertEquals(array('content' => 'content', 'status' => 'new'), $row);
		$newRepair = $repairModel->getFieldsById('mtime', 101);
		$this->assertNotSame($newRepair['mtime'], $repair['mtime']);
	}
	
}
