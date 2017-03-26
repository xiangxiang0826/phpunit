<?php

/**
 * 扩展分类Model
 *
 * @author xiangxiang<xiangxiang@wondershare.cn>
 * @date 2015-03-03
 */
class Model_ApiPermission_Test extends EZX_Framework_DatabaseTestCase {
	protected $_model;
	
	public function setUp(){
		parent::setUp();
		$this->_model = new Model_ApiPermission(APPLICATION_ENV);
	}

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet(){
        $ds =   $this->createFlatXMLDataSet(dirname(__FILE__) . '/_files/api_permission.xml');
        $compositeDs = new PHPUnit_Extensions_Database_DataSet_CompositeDataSet(array($ds));
        return $compositeDs;
    }

    public function testGetList(){
    	$res = $this->_model->getList("", 0, 100);
    	$res_query = $this->_model->getList("t.id=1", 0, 100);
    	$targetArr = array(
    			array(
    					 'id' => "1",
						 'name' => "xx",
						 'uri' => "xxtest",
						 'status' => "enable"
    					)
    			);
    	$this->assertSame($targetArr, $res);
    	$this->assertSame($targetArr, $res_query);
    }
    
    
    public function testGettotal(){
    	$total = $this->_model->getTotal("");
    	$total_query = $this->_model->getTotal("t.id=1");
    	$this->assertSame(1, (int)$total);
    	$this->assertSame(1, (int)$total_query);
    }
    
    /** 没有发现此函数调用，发现代码中的字段和数据表中的字段不一致
    public function testSave(){
    }
    **/
    
    public function testGetItemsByWhere(){
    	$res = $this->_model->getItemsByWhere(array());
    	$res_query = $this->_model->getItemsByWhere(array('D.id'=>1));
    	$targetArr = array(
    			array(
    					'id' => "1",
    					'name' => "xx",
    					'uri' => "xxtest",
    					'status' => "enable"
    					)
    			);
    	$this->assertSame($targetArr, $res);
    	$this->assertSame($targetArr, $res_query);
    }
}