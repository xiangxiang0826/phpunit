<?php

/**
 * 扩展分类Model
 *
 * @author xiangxiang<xiangxiang@wondershare.cn>
 * @date 2014-10-10
 */
class Model_News extends ZendX_Model_Base
{
	/**
	 * 设置是否允许删除
	 *
	 * @var boolean
	 */
	public $isDelete = TRUE;

	protected $_schema = 'website';
	protected $_table = 'EZ_NEWS';
	protected $pk = 'id';
	function __construct()
	{
		parent::__construct('website');
	}


	/**
	 * 保存分类
	 *
	 * @param array $data
	 * @return number
	 */
	function save($data)
	{	
		$id = isset($data['id']) ? (int) $data['id'] : 0;
		// $data = $this->data($data);

		if($id)
		{
			$this->update($data, "`id`={$id}");
		}
		else
		{
			$this->get_db()->insert($this->_table, $data);
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
		return $this->get_db()->update($this->_table, $data, $where);
	}

	/**
	 * 删除分类
	 *
	 * @param integer $id
	 */
	function delete($id)
	{
		$data['status'] = 'delete';
		return $this->get_db()->update($this->_table, $data, array("id = '{$id}'"));
	}

	function getList($where, $offset, $rows){
		$sql = "SELECT a.id,a.title,a.author,a.ctime,a.is_banner,a.list_img,b.name FROM EZ_NEWS a LEFT JOIN EZ_NEWS_CATEGORY b ON a.category_id=b.id WHERE 1=1 $where ORDER BY a.ctime DESC LIMIT $offset,$rows";
		//echo $sql;exit;
		return $this->get_db()->fetchAll($sql);
	}
	
	/* 获得所有的数据总记录	 */
	public function getTotal($query = "")
	{		
		$sql = "SELECT count(*) AS total FROM {$this->_table} as a where 1=1 {$query} LIMIT 1";
		return $this->get_db()->fetchOne($sql);
	}
}