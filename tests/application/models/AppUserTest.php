<?php

/**
 * 扩展分类Model
 *
 * @author xiangxiang<xiangxiang@wondershare.cn>
 * @date 2015-03-03
 */
class Model_AppUser_Test extends EZX_Framework_DatabaseTestCase {
	protected $_model;
	
	public function setUp(){
		parent::setUp();
		$this->_model = new Model_AppUser(APPLICATION_ENV);
	}

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet(){
        $ds =   $this->createFlatXMLDataSet(dirname(__FILE__) . '/_files/app_user.xml');
        $compositeDs = new PHPUnit_Extensions_Database_DataSet_CompositeDataSet(array($ds));
        return $compositeDs;
    }
	
    public function testGetUserByAppId(){
    	$res = $this->_model->getUserByAppId('pswsadfslkdfasosdfasdfasdffsdsdf');
    	$targetArr = array(
    			'id' => "pswsadfslkdfasosdfasdfasdffsdsdf",
    			'password' => "asdasdasd",
    			'user_id' => "1",
    			'platform' => "ios",
    			'label' => "ezu",
    			'version' => "1.0.0",
    			'lang' => "zh",
    			'device_token' => "asdasd",
    			'model' => "",
    			'ip' => "12123123123",
    			'is_test' => "0",
    			'imei' => "",
    			'country' => "中国",
    			'ctime' => "2010-04-26 12:14:20",
    			'mtime' => "2010-04-26 12:14:20",
    			'mac' => "",
    			'extend_field' => ""
    			);
    	$this->assertSame($res, $targetArr);
    }
    
    public function testGetAppCount(){
    	$res = $this->_model->getAppCount(array());
    	$this->assertSame(1, (int)$res);
    }
    
    public function testStatByDate(){
    	$res = $this->_model->statByDate("", '2010-04-23 00:00:00', '2010-04-29 00:00:00');
    	$targetArr = array(
    			array(
    					'total' => "1",
    					'date' => "2010-04-26"
    					)
    			);
    	$this->assertSame(1, (int)$res);
    }
}