<?php
/**
 * 频道资讯单元测试
 * @author zhouxh
 *
 */
class Model_ChannelContentTest extends EZX_Framework_DatabaseTestCase {
	/**
	 * 
	 * @var Model_ChannelContent
	 */
	protected $model;
	
	public function setUp(){
		parent::setUp();
		$this->model = new Model_ChannelContent();
	}
	protected function getDataSet() {
		$ds =   $this->createFlatXMLDataSet(dirname(__FILE__) . '/_files/channel_content.xml');
		$ds1 =   $this->createFlatXMLDataSet(dirname(__FILE__) . '/_files/channel_fav.xml');
		$compositeDs = new PHPUnit_Extensions_Database_DataSet_CompositeDataSet(array($ds, $ds1));
		return $compositeDs;
	}
	/**
	 * 测试收藏数查询
	 */
	public function testFindAllFavNumber(){	
		$list = $this->model->findAllFavNumber();
		$expectResult = array('1' => 2, '2' => 1, '3' => 1);
		$this->assertEquals($expectResult, $list);
	}
	
	/**
	 * 测试添加
	 */
	public function testAddInfo(){
		$id = $this->model->addInfo('title', '内容', '新浪网', 'wwww.sina.com', 'http://www.abc.com/1.jpg');
		$this->assertNotNull($id);
		$result = $this->model->getFieldsById('*', $id);
		$this->assertEquals($result['title'], 'title');
	}
	
	public function testEditInfo(){
		$affect = $this->model->editInfo('修改标题', '修改内容', '百度', 'www.baidu.com', 'http://images.baidu.com/1.jpg', 1);
		$this->assertEquals(1, $affect);
		$result = $this->model->getFieldsById('*', 1);
		$this->assertEquals('修改标题', $result['title']);
		$this->assertEquals('修改内容', $result['content']);
	}
	/**
	 * 删除
	 */
	public function testDeleteInfo(){
		$res = $this->model->deleteInfo(2);
		$this->assertEquals(1, $res);
	}
	
}