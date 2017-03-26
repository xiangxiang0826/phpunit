<?php
/**
 * 用户模型
 * @author zhouxh
 *
 */
class UserTest extends EZX_Framework_DatabaseTestCase {
	
    protected function getDataSet() {
    	$ds =   $this->createFlatXMLDataSet(dirname(__FILE__) . '/_files/user.xml');
        $compositeDs = new PHPUnit_Extensions_Database_DataSet_CompositeDataSet(array($ds));
        return $compositeDs;
    }
    
    public function testUpdateUser(){
    	$user = new Model_User(APPLICATION_ENV);
    	$data = array('real_name' => '更新数据');
    	$affect = $user->update($data, 'id = 1');
    	$this->assertEquals($affect, 1);
    	$targetArr = array(
    			'id' => 1,
    			'user_name' =>'test',
    			'real_name' =>'更新数据',
    			'password' => '36bf3ec8db36b47890bb0223abcbe502',
    			'department' => null,
    			'phone' => null,
    			'email' => null,
    			'cuser' => 0,
    			'ctime' => '2010-04-26 12:14:20',
    			'last_login_time' =>'2010-04-26 12:14:20',
    			'status' =>'enable',
    	);
    	$my_data_set = new EZX_ArrayDataSet(array('SYSTEM_USER' => array($targetArr)));
    	$ds = new Zend_Test_PHPUnit_Db_DataSet_QueryDataSet(
            $this->getConnection()
        );
        $ds->addTable('SYSTEM_USER', 'SELECT * FROM SYSTEM_USER WHERE id = 1');
    	$this->assertDataSetsEqual($my_data_set, $ds);
    }
    
    
    
    
	
}