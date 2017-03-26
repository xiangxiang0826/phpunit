<?php

/**
 * 扩展分类Model
 *
 * @author xiangxiang<xiangxiang@wondershare.cn>
 * @date 2015-03-03
 */
class Model_ControlTest extends EZX_Framework_DatabaseTestCase {
	protected $_model;
	
	public function setUp(){
		parent::setUp();
		$this->_model = new Model_Control(APPLICATION_ENV);
	}

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet(){
        $ds =   $this->createFlatXMLDataSet(dirname(__FILE__) . '/_files/control.xml');
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
    					'label' => "xx_label",
    					'icon' => "xx_icon",
    					'control_type_id' => "12",
    					'html_code' => "html_test",
    					'js_code' => "alert('haha');",
    					'description' => "asdasd",
    					'cuser' => "12",
    					'ctime' => "2010-04-26 12:14:20",
    					'mtime' => "2010-04-26 12:14:20",
    					'muser' => "0"
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