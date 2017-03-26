<?php

/**
 * 扩展分类Model
 *
 * @author xiangxiang<xiangxiang@wondershare.cn>
 * @date 2015-03-03
 */
class Model_ApiEnterpriseExtendGrant_Test extends EZX_Framework_DatabaseTestCase {
	protected $_model;
	
	public function setUp(){
		parent::setUp();
		$this->_model = new Model_ApiEnterpriseExtendGrant(APPLICATION_ENV);
	}

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet(){
        $ds =   $this->createFlatXMLDataSet(dirname(__FILE__) . '/_files/api_enterprise_extend_grant.xml');
        $compositeDs = new PHPUnit_Extensions_Database_DataSet_CompositeDataSet(array($ds));
        return $compositeDs;
    }

    public function testGetList(){
    	$res = $this->_model->getList("", 0, 100);
    	$targetArr = array(
    			array(
    					'id' => "1",
    					'app_key' => "xx",
    					'product_id' => "xxtest",
    					'callback_url' => "",
    					'status' => "enable",
    					'ctime' => "2010-04-26 12:14:20",
    					'mtime' => "2010-04-26 12:14:20"
    					)
    			);
    	$this->assertSame($targetArr, $res);
    }
    
    public function testGettotal(){
    	$total = $this->_model->getTotal("");
    	$total_query = $this->_model->getTotal("t.id=1");
    	$this->assertSame(1, (int)$total);
    	$this->assertSame(1, (int)$total_query);
    }
    
    //这个函数在model中没有被调用，且存在 查询字段和表中的实际字段不一致的情况
    /**
    public function testGetListMixed(){
    	$res = $this->_model->getListMixed("", 0, 100);
    	var_dump($res);exit;
    }
    **/
    
    //这个函数在model中没有被调用，且存在 查询字段和表中的实际字段不一致的情况
    /**
    public function testGetItemMixed(){
    	$res = $this->_model->getItemMixed("");
    	var_dump($res);exit;
    }
    **/
    
    public function testGetItemsByWhere(){
    	$res = $this->_model->getItemsByWhere(array());
    	$targetArr = array(
    				array(
    						'id' => "1",
    						'app_key' => "xx",
    						'product_id' => "xxtest",
    						'callback_url' => "",
    						'status' => "enable",
    						'ctime' => "2010-04-26 12:14:20",
    						'mtime' => "2010-04-26 12:14:20"
    						)
    			);
    	$this->assertSame($targetArr, $res);
    }
}