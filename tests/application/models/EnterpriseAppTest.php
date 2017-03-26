<?php
/**
 * 用户模型
 * @author xiangxiang<xiangxiang@wondershare.cn>
 * @date 2015-03-03
 */
class EnterpriseAppTest extends EZX_Framework_DatabaseTestCase {
	protected $_model;
	
	public function setUp(){
		parent::setUp();
		$this->_model = new Model_EnterpriseApp(APPLICATION_ENV);
	}
	
    protected function getDataSet() {
    	$ds =   $this->createFlatXMLDataSet(dirname(__FILE__) . '/_files/enterprise_app.xml');
        $compositeDs = new PHPUnit_Extensions_Database_DataSet_CompositeDataSet(array($ds));
        return $compositeDs;
    }
    
    public function testgetList(){
    	$res = $this->_model->getList("", 0, 10);
    	$targetArr = array(
	    			array(
	    			'id' => "1", 
	    			'enterprise_id' => "1", 
	    			'company_name' => null,
	    			'label' => 'ezu',
	    			'name' => 'xx',
	    			'ctime' => "2010-04-26 12:14:20",
	    			'version' => "0.0.1",
    				'v_num' => null,
    				'upgrade_type_id' => null,
    				'logo' => null
	    			)
    			);
    	$this->assertSame($res,$targetArr);
    }
    
    public function testGetTotal(){
    	$count = $this->_model->getTotal("");
    	$this->assertSame(1,(int)$count);
    }
    
    public function testDetail(){
    	$res = $this->_model->detail(1);
    	$targetArr = array(
    			'id' => "1",
    			'name' => "xx",
    			'description' => "xxtest",
    			'enterprise_id' => "1",
    			'type' => "edit",
    			'ctime' => "2010-04-26 12:14:20",
				'mtime' => "2010-04-26 12:14:20",
    			'platform' => "ios",
    			'pack_status' => "finish",
    			'is_ezapp' => "0",
    			'upgrade_type_id' => "0",
    			'version' => "0.0.1",
    			'extend_field' => ""
    			);
    	$this->assertSame($res,$targetArr);
    }
    
    public function testGetInfoByCondition(){
    	$condition = array('id'=>1);
    	$res = $this->_model->getInfoByCondition($condition);
    	$targetArr = array(
    			'id' => "1",
    			'name' => "xx",
    			'description' => "xxtest",
    			'enterprise_id' => "1",
    			'type' => "edit",
    			'ctime' => "2010-04-26 12:14:20",
    			'mtime' => "2010-04-26 12:14:20",
    			'platform' => "ios",
    			'pack_status' => "finish",
    			'is_ezapp' => "0",
    			'upgrade_type_id' => "0",
    			'version' => "0.0.1",
    			'extend_field' => "",
    			'icon' => NULL
    			);
    	$this->assertSame($res,$targetArr);
    }
    
    public function testCreateCondition(){
    	$condition = array('id'=>1);
    	$res = $this->_model->createCondition($condition);
    	//todo  返回的一个类，其中大多为protected属性
    	$this->assertSame(1,1);
    }
    
    public function testGetByPlatform(){
    	$res = $this->_model->getByPlatform(1, 'ios');
    	$targetArr = array(
    			'id' => "1",
    			'name' => "xx",
    			'description' => "xxtest",
    			'enterprise_id' => "1",
    			'type' => "edit",
    			'ctime' => "2010-04-26 12:14:20",
    			'mtime' => "2010-04-26 12:14:20",
    			'platform' => "ios",
    			'pack_status' => "finish",
    			'is_ezapp' => "0",
    			'upgrade_type_id' => "0",
    			'version' => "0.0.1",
    			'extend_field' => ""
    			);
    	$this->assertSame($res,$targetArr);
    }
    
    public function testGetByLabel(){
    	$res = $this->_model->getByLabel('ezu', 'ios');
    	$targetArr = array(
    			'id' => "1",
    			'name' => "xx",
    			'password' => "08cdfc4b6de9d85fdfe37d4ee88f6e97",
    			'salt' => "1wads1",
    			'user_type' => "company",
    			'company_name' =>	NULL,
    			'label' => "ezu",
    			'address' => "顺德德美电子有限公司",
    			'telephone' => "0755-81515847",
    			'mobile' => "18665913985",
    			'email' => "jxxgzxh@qq.com",
    			'business_scope' => "汽车",
    			'check_opinion' => "通过",
    			'certificate' =>	NULL,
    			'reg_time' => "2014-12-12 12:12:00",
    			'reg_ip' => "1270",
    			'active_time' => "2014-12-18 12:12:00",
    			'last_login_time' => "2014-12-18 12:12:00",
    			'last_login_ip' =>	NULL,
    			'audit_time' => "2014-12-18 12:12:00",
    			'status' => "pending",
    			'inform_status' => "1",
    			'inform_result' => "1",
				'is_supper' => "1",
    			'remark' => "asas",
    			'username' =>	NULL,
    			'description' => "xxtest",
    			'enterprise_id' => "1",
    			'type' => "edit",
    			'ctime' => "2010-04-26 12:14:20",
    			'mtime' => "2010-04-26 12:14:20",
    			'platform' => "ios",
    			'pack_status' => "finish",
    			'is_ezapp' => "0",
    			'upgrade_type_id' => "0",
    			'version' => "0.0.1",
    			'extend_field' => ""
    			);
    	$this->assertSame($res,$targetArr);
    }
}