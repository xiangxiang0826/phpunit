<?php
/**
 * CMS专用父类Model
 * 
 * @author liujd<liujd@wondershare.cn>
 * @date 2014-07-31
 */
class ZendX_Cms_Model extends ZendX_Model_Base {
    protected $pk = 'id';
    protected $_schema = 'oss';
	/**
	 * 正常记录条件
	 */
	protected $able_condition = "`status`='enable'";
	
	/**
	 * 根据ID获取信息
	 * 
	 * @param integer $id
	 */
	public function getInfoById($id)
	{
		$id = (int) $id;
		return $this->get_db()->fetchRow("SELECT * FROM `{$this->_table}` WHERE `{$this->pk}`={$id}");
	}
    
	/**
	 * 根据name和cat_id获取信息
	 * 
	 * @param integer $id
	 */
	function getInfoByCid($name, $cat_id)
	{
		$name = trim($name);
        $cat_id = (int) $cat_id;
		return $this->get_db()->fetchRow("SELECT * FROM `{$this->_table}` WHERE `name`='{$name}' AND `category_id` = {$cat_id}");
	}
	
	/**
	 * 处理入库数据的公共字段
	 * 
	 * @param array		$data
	 * @param string 	$pk
	 */
	function data($data, $pk=null)
	{
		$user_info = Common_Auth::getUserInfo();
		$append_data = array(
				'user_id_e' => $user_info['user_id'],
				'edit_time' => date('Y-m-d H:i:s'),
				'user_name' => $user_info['user_name']
			);
		
		$pk = $pk ? $pk : $this->pk;
		if(empty($data[$pk]))
		{
			$append_data['user_id_a'] = $user_info['user_id'];
			$append_data['add_time'] = date('Y-m-d H:i:s');
		}
		
		return array_merge($data, $append_data);
	}
    
    /**
	 * 删除功能
	 * @param string $where
	 */
    function realDelete($where){
        return $this->get_db()->delete($this->_table, $where);
    }


    /**
	 * 删除功能
	 * @param string $where
	 */
	function deleted($where)
	{
		$user_info = Common_Auth::getUserInfo();
		$data = array(
					'user_id_e' => $user_info['user_id'],
					'edit_time' => date('Y-m-d H:i:s'),
					'user_name' => $user_info['user_name'],
					'status'=>'deleted'
				);
		return $this->get_db()->update($this->_table, $data, $where);
	}
	
	/**
	 * 禁用功能
	 * @param string $where
	 */
	function disable($where)
	{
		$user_info = Common_Auth::getUserInfo();
		$data = array(
					'user_id_e' => $user_info['user_id'],
					'edit_time' => date('Y-m-d H:i:s'),
					'user_name' => $user_info['user_name'],
					'status'=>'disable'
				);
		return $this->get_db()->update($this->_table, $data, $where);
	}
	
	/**
	 * 启用功能
	 * @param string $where
	 */
	function able($where) {
		$user_info = Common_Auth::getUserInfo();
		$data = array(
					'user_id_e' => $user_info['user_id'],
					'edit_time' => date('Y-m-d H:i:s'),
					'user_name' => $user_info['user_name'],
					'status'=>'enable'
				);
		return $this->get_db()->update($this->_table, $data, $where);
	}
	
	// 返回当前model用到的db句柄
	public function db() {
		return $this->get_db();
	}
}