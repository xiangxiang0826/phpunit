<?php
/** 
 * 添加数据表模型
 * 
 * @author etong <zhoufeng@wondershare.cn>
 * @version $v1 2014/07/30 11:10 $
 */
class Model_ApiPermission extends ZendX_Model_Base {
	protected $_schema = 'cloud';
	protected $_table = 'EZ_API_PERMISSION';
	protected $pk = 'id';
	
	/**
	 * 获取所有权限
	 */
	function getList($query = "", $offset, $rows)
	{
		if(empty($query)){
			$sql = "SELECT  t.* FROM `{$this->_table}` AS t   ORDER BY t.id DESC limit $offset,$rows";
		}else{
			$sql = "SELECT  t.* FROM `{$this->_table}` AS t   WHERE ".$query." ORDER BY t.id DESC limit $offset,$rows";
		}
		return $this->get_db()->fetchAll($sql);
	}
    
	/**
	 * 获得总记录数
	 */
	public function getTotal($query = "")
	{
		if(empty($query)){
			$sql = "SELECT count(*) AS total FROM `{$this->_table}` AS t";
		}else{
            $sql = "SELECT count(*) AS total FROM `{$this->_table}` AS t WHERE ".$query;
		}
		return $this->get_db()->fetchOne($sql);
	}
	
	function save($data)
	{
		$menu_id = (int) $data['menu_id'];
		$data = $this->data($data);
		unset($data['menu_id']);
		
		if($menu_id)
		{
			$this->get_db()->update($this->table, $data, "`menu_id`={$menu_id}");
		} else {
			$this->get_db()->insert($this->table, $data);
			$menu_id = $this->get_db()->lastInsertId();
		}
		
		return $menu_id;
	}
    
	/**
	 * 获取权限表和厂商表的详情数组
	 * 
	 * @param array $condition
	 */
	public function getItemsByWhere($condition) {
        $select = $this->get_db()->select();
		$select->from(array('D' => $this->_table));
		if(!empty($condition)) {
			foreach ($condition as $key=>$value) {
                $select->where("$key=?", $value);
			}
		}
        $sql = $select->__toString();
        return $this->get_db()->fetchAll($sql);
	}
    
    public function getGroup(){
        return array(
            'common' => '公共权限',
            'auth' => '可授权权限',
            'intranet' => '内部权限'
        );
    }
}