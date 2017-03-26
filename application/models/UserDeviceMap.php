<?php
/**
 * APP相关图片资源
 * @author lvyz@wondershare.cn
 *
 */
class Model_UserDeviceMap extends ZendX_Model_Base
{
	protected $_schema = 'cloud';
	protected $_table = 'EZ_USER_DEVICE_MAP';
	
	
	/**
	 * 根据产品id查询对应设备之uid详细信息列表
	 * @param array() $productids
	 * @return mixed
	 */
	public function getUser($productids,$where = array())
	{
		$select = $this->select()->from(array('m'=>$this->_table),array('user_id'));
		$select->join(array('d'=>'EZ_DEVICE'), 'm.device_id=d.id',array('did'=>'id','pid'=>'product_id'));
		$select->join(array('u'=>'EZ_USER'), 'm.user_id=u.id',array('email','name','phone','status','reg_time','last_login_time'));
		// 产品列表内
		if(is_array($productids)){
			$select->where("d.product_id IN (?)", $productids);
		}else{
			$select->where("d.product_id =?", $productids);
		}
		
		// 会员过滤
		if(is_array($where) && !empty($where)){
			foreach($where as $k=>$v){
				$select->where("u.{$k} =?", $v);
			}
		}
		
		$select->group('m.user_id');// 分组去重
		$select->order('u.reg_time DESC');
		return $select;
	}
	
	/**
	 * 根据产品id查询对应设备之uid列表
	 * @param array() $productids
	 * @return mixed
	 */
	public function getUserList($productids)
	{
		$select = $this->select()->from(array('m'=>$this->_table),array('user_id'));
		$select->join(array('d'=>'EZ_DEVICE'), 'm.device_id=d.id',array('did'=>'id','pid'=>'product_id'));
		// 产品列表内
		if(is_array($productids)){
			$select->where("d.product_id IN (?)", $productids);
		}else{
			$select->where("d.product_id =?", $productids);
		}
		$select->group('m.user_id');

		return $this->getDb()->fetchAll($select);
	}
	
	/**
	 * 根据产品userid查询对应设备列表
	 * @param int $userid
	 * @return mixed
	 */
	public function getDeviceList($userid)
	{
		$select = $this->select()->from(array('m'=>$this->_table),array('user_id'));
		$select->join(array('d'=>'EZ_DEVICE'), 'm.device_id=d.id');
		$select->join(array('u'=>'EZ_USER'), 'm.user_id=u.id',array('uid'=>'id'));
	
		$select->where("m.user_id =?",$userid);
		$select->where("d.name !=?",'');//设备名称为空确认
		$select->group('d.id');
		return $this->get_db()->fetchAll($select);
	}
}