<?php
/**
 * 设备管理
 * @author zhouxh
 *
 */
class Model_Feedback extends ZendX_Model_Base
{
	protected $_schema = 'cloud';
	protected $_table = 'EZ_FEEDBACK';
	const STATUS_RESOLVED = 'resolved';
	const STATUS_PROCESSING = 'processing';
	const STATUS_NEW = 'new';
	const STATUS_UNSOLVED = 'unsolved';
	const STATUS_NOTDO = 'notdo'; //不予处理
	/**
	 * 返回状态数组列表
	 * @return array
	 */
    public function getStatusMap(){
    	return array(
    			     self::STATUS_NEW => self::STATUS_NEW,
    			     self::STATUS_NOTDO => self::STATUS_NOTDO,
    			     self::STATUS_PROCESSING => self::STATUS_PROCESSING,
    			     self::STATUS_UNSOLVED => self::STATUS_UNSOLVED,
    			     self::STATUS_RESOLVED => self::STATUS_RESOLVED,
    			);		
	}
	/**
	 * 查看反馈信息
	 */
	public function getDetailInfo($id){
		$select = $this->get_db()->select();
		$select->from(array('F' => $this->_table));
		$select->joinLeft(array('T' => 'EZ_FEEDBACK_TYPE'), 'T.id=F.feedback_type_id', array('t_name' => 'name'));
		$select->joinLeft(array('D' => 'EZ_DEVICE'), 'D.id=F.device_id', array('d_id' => 'id', 'user_activate_time'));
		$select->joinLeft(array('P' => 'EZ_PRODUCT'), 'P.id=D.product_id', array('p_name' => 'name'));
		$select->where("F.id = ?", $id);
		$result = $this->get_db()->fetchRow($select);
		if($result) {
			//查询用户信息
			$userModel = new Model_Member();
			$userInfo = $userModel->getInfoByUid($result['user_id']);
			if($userInfo) {
			    $result['user_info'] = $userInfo;
			    $deviceModel = new Model_Device();
			    $devices = $deviceModel->getDeviceByUid($result['user_id']);
			    $result['user_info']['devices'] = array();
			    if($devices) {
			    	foreach ($devices as $device) {
			    		$result['user_info']['devices'][] = $device['name'];
			    	}
			    }
			} else {
				$result['user_info'] = array();
			}
			//查询该用户的反馈列表
			$result['user_feedback_list'] = array();
			if(!empty($result['user_id'])) {
				$select->reset();
				$select->from(array('F' => $this->_table));
				$select->joinLeft(array('D' => 'EZ_DEVICE'), 'D.id=F.device_id', array('d_id' => 'id'));
				$select->joinLeft(array('P' => 'EZ_PRODUCT'), 'D.product_id=P.id', array('p_name' => 'name'));
				$select->where("F.user_id = ?", $result['user_id']);
				$select->group('F.id');
				$result['user_feedback_list'] = $this->get_db()->fetchAll($select);
			}
		}
		return $result;
	}
	
	/**
	 * 根据 时间节点 查询反馈总数
	 * @param time $starttime $endtime
	 * @return mixed
	 */
	public function getCountInDay($starttime,$endtime)
	{
		$select = $this->select()->from($this->_table,array('total'=>'count(*)'));
		$select->where('ctime > ?', $starttime);
		$select->where('ctime < ?', $endtime);
		$select->where('device_id != ?', '');
		return $this->get_db()->fetchRow($select);
	}
	
	/**
	 * 根据时间节点 查询反馈总数并以deviceid分组
	 * @param time $starttime $endtime
	 * @return mixed
	 */
	public function getCountByGroupDeviceId($starttime,$endtime)
	{
	
		$select = $this->select()->from(array('f'=>$this->_table),array('total'=>'count(*)','*'));
		$select->joinInner(array('de'=>'EZ_DEVICE'),'f.device_id=de.id',array('c_name'=>'name'));
		$select->joinInner(array('p'=>'EZ_PRODUCT'),'de.product_id=p.id',array('p_name'=>'name','pid'=>'id'));
		$select->where('f.ctime > ?', $starttime);
		$select->where('f.ctime < ?', $endtime);
		$select->group('de.product_id');
		//echo $select.'<br/>';
		return $this->get_db()->fetchAll($select);
	}
    
	/**
	 * 查询反馈总数列表
	 * 
	 * @return array
	 */
	public function getAllCountInfo()
	{
		$sql = "SELECT feedback_type_id as id, count(*) as number FROM `{$this->_table}` where 1 GROUP BY feedback_type_id  ORDER BY ctime DESC";
		$category = $this->get_db()->fetchAll($sql);
		$treeArr = $category;
		return $treeArr;
	}
}