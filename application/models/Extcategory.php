<?php

/**
 * 扩展分类Model
 * 
 * @author liujd<liujd@wondershare.cn>
 * @date 2014-07-04
 */
class Model_Extcategory extends ZendX_Model_Base
{
    /**
     * 设置是否允许删除
     * 
     * @var boolean
     */
    public $isDelete = TRUE;
    
    protected $_schema = 'oss';
    protected $_table = 'SYSTEM_USER';
    protected $pk = 'id';
    
	function __construct()
	{
		parent::__construct();
		$this->table = 'system_ext_menu';
		$this->pk = 'id';
	}
	
	/**
	 * 获得所有的数据总记录
	 */
	public function getTotal()
	{
		$sql = "SELECT count(*) AS total FROM `{$this->table}`";	
		return $this->get_db()->fetchAll($sql);
	}
	
	/**
	 * 获取所有分类
	 * 
	 * @param integer $site_id
	 */
	public function getList($offset,$rows)
	{
		$sql = "SELECT * FROM `{$this->table}` ORDER BY `sort` ASC, `id` ASC LIMIT $offset,$rows"; 
		return $this->get_db()->fetchAll($sql);
	}
	
	/** 获取所有分类
	 * 
	 * @param integer $site_id
	 */
	public function getAllList()
	{
		$sql = "SELECT `id`, `name`,
				FROM `{$this->table}` 
				ORDER BY `sort` ASC, `id` ASC";
		return $this->get_db()->fetchAll($sql);
	}
	
	/**
	 * get_where
	 * 
	 * @param array $where
	 */
	public function get_where($site_id,$id)
	{
		$sql = 	"SELECT * FROM `{$this->table}` WHERE `site_id` = '{$site_id}' AND `id` = '{$id}' LIMIT 1";
		return $this->get_db()->fetchRow($sql);
	}
	
	/**
	 * 根据站点ID获得站点列表
	 */
	public function getListBySiteID($site_id)
	{
		$sql = "SELECT `id`, `name`,`site_id`
				FROM `{$this->table}` 
				WHERE `site_id` = '{$site_id}'					  
				ORDER BY `id` ASC";
		return $this->get_db()->fetchAll($sql);
	}
	
	/**
	 * 根据id获得类别信息
	 * 
	 * @param int $id
	 */
	function getCategoryInfoById($id)
	{		
		return $this->get_db()->fetchRow("SELECT * FROM `{$this->table}` WHERE `id`='{$id}' LIMIT 1");
	}
	
	/**
	 * 检查数据项是否已存在
	 *
	 * @param array $data
	 */
	public function isExists($data)
	{		
		$sql = "SELECT * FROM `{$this->table}` WHERE `name` = '{$data['name']}' LIMIT 1";		
		$rs = $this->get_db()->fetchRow($sql);		
		return $rs === false ? false : true;
	}
	
	/**
	 * 保存分类
	 * 
	 * @param array $data
	 * @return number
	 */
	function save($data)
	{
		$id = (int) $data['id'];
		// $data = $this->data($data);
		
		if($id)
		{
			$this->update($data, "`id`={$id}");
		}
		else
		{
			$this->get_db()->insert($this->table, $data);
			$id = $this->get_db()->lastInsertId();
		}
		
		return $id;
	}
	
	/**
	 * 编辑分类
	 * 
	 * @param array $data
	 * @param string $where
	 */
	function update($data, $where)
	{
		return $this->get_db()->update($this->table, $data, $where);
	}
	
	/**
	 * 删除分类
	 * 
	 * @param integer $id
	 */
	function delete($id)
	{
        if($this->isDelete == FALSE){
            return FALSE;
        }
        $result = parent::realDelete("`id`={$id}");
		return $result;
	}
	
	/**
	 * 根据模块ID获得扩展分类的默认id，sort为0的为默认
	 */
	public function getDefaultByModuleID($module_id)
	{
		$sql = "SELECT `id`, `name`,`site_id`
		FROM `{$this->table}`
		WHERE `site_id` = '{$module_id}' AND `sort` = 0
		ORDER BY `id` ASC";
		return $this->get_db()->fetchRow($sql);
	}
}