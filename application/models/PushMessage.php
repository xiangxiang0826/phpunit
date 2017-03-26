<?php
/**
 * 消息推送的后台管理
 * 
 * @author etong<zhoufeng@wondershare.cn>
 *
 */
class Model_PushMessage extends ZendX_Model_Base
{
	protected $_schema = 'cloud';
	protected $_table = 'EZ_PUSH_MESSAGE';
    // 消息审核状态
    CONST STATUS_DRAFT = 'draft';
	CONST STATUS_PENDING = 'pending';
	CONST STATUS_AUDIT_SUCCESS = 'audit_success';
    CONST STATUS_AUDIT_FAILED = 'audit_failed';
	CONST STATUS_PUBLISHED= 'published';
	CONST STATUS_PUBLISH_FAILED= 'publish_failed';
    
    // 消息推送状态
    CONST STATUS_PUSH_NEW = 'new';
    CONST STATUS_PUSH_CREATE = 'create';
    CONST STATUS_PUSH_ANALYSIS = 'analysis';
    CONST STATUS_PUSH_ANALYSIS_FAILED = 'analysis_failed';
    CONST STATUS_PUSH_PENDING_SEND = 'pending_send';
    CONST STATUS_PUSH_SENDING = 'sending';
    CONST STATUS_PUSH_COMPLETE = 'complete';
	
	public function createCondition($condition, $type = 'notice', $enterprise_id = 1){
		$select = $this->get_db()->select();
		$select->from(array('t' => $this->_table));
        
        $select->where("status != ?", 'draft');
        $select->where("message_type = ?", $type);
        if($enterprise_id == Model_Enterprise::DEFAULT_EZ_ID){
            $select->where("enterprise_id = ?", $enterprise_id);
        }
        // print_r($condition);exit;
		//待审核
		if(!empty($condition)) {
			foreach ($condition as $key=>$v) {
                if($key == 't.status'){
                    if($v == Model_PushMessage::STATUS_AUDIT_SUCCESS){
                        $select->where('status in ("'.self::STATUS_AUDIT_SUCCESS.'","'.self::STATUS_PUBLISHED.'","'.self::STATUS_PUBLISH_FAILED.'")', '');
                    }else{
                        $select->where("$key = ?", $v);
                    }
                }elseif($key == 't.title'){
                    $select->where("$key like '%".$v."%'", '');
                }else{
                    $select->where("$key = ?", $v);
                }
			}
		}
		$select->order('id DESC');
		return $select;
	}
	

	public function createConditionForEnterprise($condition, $type = 'notice'){
		$select = $this->get_db()->select();
        $select->from(array('t' => $this->_table));
        $select->where("t.status != ?", 'draft');
        $select->where("t.enterprise_id != ?", Model_Enterprise::DEFAULT_EZ_ID);
        $select->where("t.message_type = ?", $type);
        $select->joinLeft(array('e' => 'EZ_ENTERPRISE'), "t.enterprise_id=e.id", array('company_name'));
		//待审核
		if(!empty($condition)) {
			foreach ($condition as $key=>$v) {
                if($key == 't.status'){
                    if($v == Model_PushMessage::STATUS_AUDIT_SUCCESS){
                        $select->where('t.status in ("'.self::STATUS_AUDIT_SUCCESS.'","'.self::STATUS_PUBLISHED.'","'.self::STATUS_PUBLISH_FAILED.'")', '');
                    }else{
                        $select->where("$key = ?", $v);
                    }
                }elseif($key == 't.title'){
                    $select->where("$key like '%".$v."%'", '');
                }else{
                    $select->where("$key = ?", $v);
                }
			}
		}
		$select->order('t.id DESC');
		return $select;
	}
	
   /**
    * 获取状态列表
    * @return multitype:string
    */
	public function getStatusMap(){
		return array(
            self::STATUS_PENDING => '待审核',
            self::STATUS_AUDIT_FAILED => '审核不通过',
            self::STATUS_AUDIT_SUCCESS => '审核通过'
        );
	}
    
   /**
    * 获取消息列表中显示的状态
    * @return multitype:string
    */
	public function getListStatusMap(){
		return array(
            self::STATUS_PENDING => '待审核',
            self::STATUS_AUDIT_FAILED => '审核不通过',
            self::STATUS_AUDIT_SUCCESS => '推送失败',
            self::STATUS_PUBLISHED => '发布成功',
            self::STATUS_PUBLISH_FAILED => '发布失败'
        );
	}
    
   /**
    * 获取消息发送的状态<br>
    * 当标记为发布成功时需要进一步查询消息的推送状态
    * @return multitype:string
    */
	public function getSendingStatusMap(){
		return array(
            self::STATUS_PUSH_CREATE => '分析中',
            self::STATUS_PUSH_NEW => '分析中',
            self::STATUS_PUSH_ANALYSIS => '分析中',
            self::STATUS_PUSH_ANALYSIS_FAILED => '发送失败',
            self::STATUS_PUSH_PENDING_SEND => '待发送',
            self::STATUS_PUSH_SENDING => '发送中',
            self::STATUS_PUSH_COMPLETE => '已发送'
        );
	}
    
	/**
	 * 通过状态查询消息数目
	 * 如果enterprise_id 为0，则不查enterprise_id
	 */
	public function getNumberBystatus($status, $type='notice', $enterprise_id = 1) { 
		$sql = "SELECT COUNT(1) AS number FROM  {$this->_table}  WHERE message_type='{$type}' AND status = ?";
		if($enterprise_id > 0) {
			$sql = "{$sql} AND enterprise_id='{$enterprise_id}'";
		}
		$sql = $this->get_db()->quoteInto($sql, $status);
		return $this->get_db()->fetchOne($sql);
	}
	
	public function getTotal($query = "")
	{
		if(empty($query)){
			$sql = "SELECT count(*) AS total FROM `{$this->_table}` LIMIT 1";
		}else{
			$sql = "SELECT count(*) AS total FROM {$this->_table} as t where {$query} LIMIT 1";
		}
		return $this->get_db()->fetchOne($sql);
	}
}