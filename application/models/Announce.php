<?php

/**
 * 扩展分类Model
 *
 * @author liujd<liujd@wondershare.cn>
 * @date 2014-07-04
 */
class Model_Announce extends ZendX_Model_Base
{
	/**
	 * 设置是否允许删除
	 *
	 * @var boolean
	 */
	public $isDelete = TRUE;

	protected $_schema = 'cloud';
	protected $_table = 'EZ_ANNOUNCE';
	protected $_subtable = 'EZ_ENTERPRISE_ANNOUNCE';
	protected $pk = 'id';

	CONST STATUS_PENDING = 'pending';
	CONST STATUS_SENDING = 'sending';
	CONST STATUS_FINISHED= 'finished';
	CONST STATUS_AUDIT_FAILED = 'audit_failed';
	CONST STATUS_AUDIT_SUCCESS = 'audit_success';
	function __construct($schema = null, $table = null)
	{
		parent::__construct($schema, $table);
		$this->table = 'system_ext_menu';
		$this->pk = 'id';
	}

	/**
	 * 获取状态列表
	 * @return multitype:string
	 */
	public function getStatusMap(){
		return array(
				self::STATUS_FINISHED => '已发送',
				self::STATUS_PENDING => '待审核',
				self::STATUS_AUDIT_FAILED => '审核不通过'
		);
	}

	/**
	 * 获取状态列表
	 * @return multitype:string
	 */
	public function getStatusMapAll(){
		return array(
				self::STATUS_FINISHED => '已发送',
				self::STATUS_PENDING => '待审核',
				self::STATUS_SENDING => '发送中',
				self::STATUS_AUDIT_FAILED => '审核不通过',
				self::STATUS_AUDIT_SUCCESS => '审核成功',
		);
	}

	/**
	 * 获得所有的数据总记录
	 */
	public function getTotal($query = "")
	{
		if(empty($query)){
			$sql = "SELECT count(*) AS total FROM `{$this->_table}` LIMIT 1";
		}else{
			$sql = "SELECT count(*) AS total FROM {$this->_table} as t where {$query} LIMIT 1";
		}
		return $this->get_db()->fetchOne($sql);
	}

	/**
	 * 获取所有分类
	 *
	 * @param integer $site_id
	 */
	public function getList($query,$offset,$rows)
	{

		if(!empty($query)){
			$sql = "SELECT * FROM `{$this->_table}` AS t WHERE $query ORDER BY  `id` DESC LIMIT $offset,$rows";
		}else{
			$sql = "SELECT * FROM `{$this->_table}`  ORDER BY  `id` DESC LIMIT $offset,$rows";
		}

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
		return $this->get_db()->update($this->_table, $data, $where);
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

	/**
	 *获取公司名称
	 */
	public function getEnterpriseName($str){
		$sql = "SELECT company_name from EZ_ENTERPRISE where id in (".$str.")";
		return $this->get_db()->fetchAll($sql);
	}
	/**
	 *获取企业用户ID对应的企业名称
	 */
	public function ids2Name($idStr,$idArr){
		if(empty($idStr)) return '所有厂商';
		$temArr = explode(',',$idStr);
		$resStr = '';
		foreach($temArr as $v){
			$resStr .= isset($idArr[$v])?($idArr[$v].','):'';
		}
		return rtrim($resStr,',');
	}

	/**
	 * 获取对应公告已阅读数和总数
	 */
	public function getPortion($idArr){
		if(empty($idArr)) return array();
		$announceIdStr = join(',',$idArr);
		$sql = "SELECT COUNT(id) alls,COUNT(CASE status WHEN 'readed' THEN id END) as old ,announce_id from EZ_ENTERPRISE_ANNOUNCE WHERE announce_id IN (".$announceIdStr.")   GROUP BY announce_id";
		$portionArr = $this->get_db()->fetchAll($sql);
		$resArr = array();
		if($portionArr){
			foreach($portionArr as $k=>$v){
				$resArr[$v['announce_id']] = $v;
			}
		}
		return $resArr;
	}
	
	/**
	 *  单元测试 修改数据库连接方法
	 */
	public function __set($key,$val){
		$this->$key = $val;
	}
}