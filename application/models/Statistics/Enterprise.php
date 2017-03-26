<?php
/**
 * 统计功能（厂商）
 * @author xiangxiang
 *
 */
class Model_Statistics_Enterprise extends ZendX_Model_Base {
	protected $_schema = 'cloud';
	protected $_table = 'EZ_ENTERPRISE';
	private $_model;
	
	public function __construct(){
		parent::__construct();
		$this->_model = new Model_Enterprise();
	}
	
	/* 统计厂商总数   */
	public function getTotal(){
		return $this->_model->getTotal("t.status = 'enable'");
	}
	
	/* 统计待审核厂商总数  */
	public function getUnaudit(){
		return $this->_model->getTotal("t.status='pending'");		
	}
}