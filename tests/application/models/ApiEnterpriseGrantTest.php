<?php

/**
 * 扩展分类Model
 *
 * @author xiangxiang<xiangxiang@wondershare.cn>
 * @date 2015-03-03
 */
class Model_ApiEnterpriseGrant_Test extends EZX_Framework_DatabaseTestCase {
	protected $_model;
	
	public function setUp(){
		parent::setUp();
		$this->_model = new Model_ApiEnterpriseGrant(APPLICATION_ENV);
	}

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet(){
        $ds =   $this->createFlatXMLDataSet(dirname(__FILE__) . '/_files/api_enterprise_grant.xml');
        $compositeDs = new PHPUnit_Extensions_Database_DataSet_CompositeDataSet(array($ds));
        return $compositeDs;
    }

    public function testGetList(){
    	$res = $this->_model->getList("", 0, 100);
    	$targetArr = array(
    			array(
    					'id' => "1",
						'name' => "xx",
						'remark' => "xxtest",
						'app_key' => "xxx",
						'app_salt' => "xxx",
						'enterprise_id' => "1",
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
    
    public function testGetListMixed(){
    	$res = $this->_model->getListMixed("", 0, 100);
    	$res_query = $this->_model->getListMixed("t.id=1", 0, 100);
    	$targetArr = array(
    			array(
    					'username' => "test",
    					'company_name' =>	NULL,
    					'id' => "1",
    					'ctime' => "2010-04-26 12:14:20",
    					'name' => "xx",
    					'app_key' => "xxx",
    					'status' => "enable"
    					));
    	$this->assertSame($targetArr, $res);
    	$this->assertSame($targetArr, $res_query);
    }
    
    public function testGetItemMixed(){
    	$res = $this->_model->getItemMixed("");
    	$res_query = $this->_model->getItemMixed("t.id=1");
    	$targetArr = array(
    			array(
    					'id' => "1",
    					'name' => "xx",
    					'remark' => "xxtest",
    					'app_key' => "xxx",
    					'app_salt' => "xxx",
    					'enterprise_id' => "1",
    					'status' => "enable",
    					'ctime' => "2010-04-26 12:14:20",
    					'mtime' => "2010-04-26 12:14:20",
    					'username' => "test",
    					'company_name' =>	NULL
    					)
    			);
    	$this->assertSame($targetArr, $res);
    	$this->assertSame($targetArr, $res_query);
    }
}