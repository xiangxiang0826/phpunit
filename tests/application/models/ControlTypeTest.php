<?php

/**
 * 扩展分类Model
 *
 * @author xiangxiang<xiangxiang@wondershare.cn>
 * @date 2015-03-03
 */
class Model_ControlTypeTest extends EZX_Framework_DatabaseTestCase {
	protected $_model;
	
	public function setUp(){
		parent::setUp();
		$this->_model = new Model_ControlType(APPLICATION_ENV);
	}

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet(){
        $ds =   $this->createFlatXMLDataSet(dirname(__FILE__) . '/_files/control_type.xml');
        $compositeDs = new PHPUnit_Extensions_Database_DataSet_CompositeDataSet(array($ds));
        return $compositeDs;
    }
	
    public function testGetList(){
    	$res = $this->_model->getList('', 0, 10);
    	$targetArr = array(
    			'counts' => "1",
    			'list' =>array(
    				array(
    					 'id' => "1",
						 'name' => "XX",
						 'remark' => "xx",
						 'status' => "enable"
    				)
    			)
    			);
    	$this->assertSame($res, $targetArr);
    }
    
    public function testDelete(){
    	$res = $this->_model->delete(1);
    	$this->assertSame($res, 1);
    }
}