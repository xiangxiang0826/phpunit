<?php
/**
 * 已经发布产品管理
 * @author zhouxh
 *
 */
class Model_Message extends ZendX_Model_Base
{
	protected $_schema = 'cloud';
	protected $_table = 'EZ_MANAGER_MESSAGE';
	CONST STATUS_PENDING = 'pending';
	CONST STATUS_SENDING = 'sending';
	CONST STATUS_FINISHED= 'finished';
	CONST STATUS_FAILED= 'failed';
	CONST STATUS_NEED_CHECK= 'need_check';
	CONST STATUS_CHECK_SUCC= 'check_success';
	CONST STATUS_CHECK_FAILED= 'check_failed';
	
	
	
	public function createCondition($condition, $type='1'){
		$select = $this->get_db()->select();
		$select->from(array('t' => $this->_table));
		$select->joinLeft(array('e' => 'EZ_ENTERPRISE'), "t.enterprise_id=e.id", array('e_id' => 'id', 'company_name'));
		//待审核
		if($type == '1') {
			$select->where("t.status =?", 'need_check');
		} else {
			$select->joinLeft(array('tem1' => new Zend_Db_Expr("(SELECT manage_message_id,COUNT(*) AS s_num FROM EZ_USER_MESSAGE GROUP BY manage_message_id)")), "t.id=tem1.manage_message_id");
			$select->joinLeft(array('tem2' => new Zend_Db_Expr("(SELECT manage_message_id,COUNT(*) AS read_num FROM EZ_USER_MESSAGE WHERE EZ_USER_MESSAGE.status='readed' GROUP BY manage_message_id)")), "t.id=tem2.manage_message_id");
			//已经审核
			$select->where('t.status !="need_check"');
		}
		if(!empty($condition)) {
			foreach ($condition as $key=>$v) {
				if($key == 'product') {
					$select->where("CONCAT(CONCAT(',',products),',') LIKE CONCAT(CONCAT('%,', {$v}),',%')");
				} else if($key == 'time'){
				    $select->where($v);
				} else {
					$select->where("$key = ?", $v);
				}
			}
		}
		$select->order('ctime DESC');
		return $select;
	}
	
	
   /**
    * 获取状态列表
    * @return multitype:string
    */
	public function getStatusMap(){
		return array(
						    self::STATUS_CHECK_FAILED => '审核不通过',
						    self::STATUS_FINISHED => '已发送',
						    self::STATUS_PENDING => '待发送',
						    self::STATUS_SENDING => '发送中',
						   self::STATUS_NEED_CHECK => '待审核',
						);
	}
	/**
	 * 通过状态查询消息数目
	 */
	public function getNumberBystatus($status){
		$sql = 'SELECT COUNT(*) AS number FROM '. $this->_table .   " WHERE status =?";
		$sql = $this->get_db()->quoteInto($sql, $status);
		return $this->get_db()->fetchOne($sql);
	}
	
	
}