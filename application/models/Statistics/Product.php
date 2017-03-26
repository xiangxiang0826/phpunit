<?php
/**
 * 统计功能（产品）
 * @author xiangxiang
 *
 */
class Model_Statistics_Product extends ZendX_Model_Base {
	protected $_schema = 'cloud';
	protected $_table = 'EZ_PRODUCT';
	private $_model, $_makeModel;
	
	public function __construct(){
		parent::__construct();
		$this->_model = new Model_Product();
		$this->_makeModel = new Model_MakeProduct();
	}
	
	/* 统计产品总数  */
	public function getTotal(){
		return $this->_model->getTotal("t.status = 'enable'");
	}
	
	/*统计待审核产品总数  */
	public function getUnaudit(){
		return $this->_makeModel->getTotal("t.status IN ('publish_failed','pending')");		
	}
	
	/* 获取所有产品列表 */
	public function getList(){
		return $this->_model->GetList(array('status'=>'enable'),0,1000);
	}
}