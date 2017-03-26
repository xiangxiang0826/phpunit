<?php
/**
 * Model 基类
 * @author lvyz@wondershare.cn
 *
 */
class ZendX_Model_Base
{
	/**
	 * DB连接池
	 * @var Zend_Db_Adapter_Abstract
	 */
	private static $db_pool = array();
	
	/**
	 * 当前连接句柄
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $_db = null;
	
	/**
	 * 数据库名
	 * @var string
	 */
	protected $_schema = null;
	
	/**
	 * 数据库表名
	 * @var string
	 */
	protected $_table = null;
	
	/**
	 * 初始化数据库及相关表配置
	 * @param string $schema
	 * @param string $table
	 * @throws Zend_Db_Exception
	 */
	public function __construct($schema = null, $table = null)
	{
		if(!isset($schema)) {
			if(isset($this->_schema)) {
				$schema = $this->_schema;
			} else {
				$schema = 'cloud';
			}
		}
		$this->_schema = $schema;
		if(APPLICATION_ENV === 'testing') {
			$this->_schema = 'testing';
		}
		if(!isset($table) && !isset($this->_table)) {
			throw new Zend_Db_Exception("Missing table name");
		}
	}
	
	/**
	 * 获取DB连接
	 * @return Zend_Db_Adapter_Abstract
	 */
	public function get_db()
	{
		if(isset(self::$db_pool[$this->_schema])) {
			return self::$db_pool[$this->_schema];
		}
		$db = new ZendX_Db($this->_schema);
		$this->_db = $db->get_db();
		self::$db_pool[$this->_schema] = $this->_db;
		return $this->_db;
	}
	

	/**
	 * 获取构造select()对象
	 * @return Zend_Db_Select
	 */
	public function select()
	{
		return $this->get_db()->select();
	}
	
	/**
	 * 插入记录
	 * @param array $data
	 * @return bool
	 */
	public function insert($data)
	{
		$ret = $this->get_db()->insert($this->_table, $data);
		return $ret ? $this->get_db()->lastInsertId($this->_table) : false;
	}
	
	/**
	 * 更新记录
	 */
	public function update($data, $where)
	{
		return $this->get_db()->update($this->_table, $data, $where);
	}
	
	/**
	 * 转义特殊字符
	 * @param string $string
	 */
	public function quote($string)
	{
		return $this->get_db()->quote($string);
	}
	
	/**
	 * 获取表中所有记录
	 */
	public function fetch_all()
	{
		$select = $this->select()->from($this->_table);
		return $this->get_db()->fetchAll($select);
	}
	
	/**
	 * 开始处理事务
	 */
	public function begin_transaction()
	{
		return $this->get_db()->beginTransaction();
	}
	
	/**
	 * 提交事务
	 */
	
	public function commit()
	{
		return $this->get_db()->commit();
	}
	
	/**
	 * 回滚事务
	 */
	
	public function rollback()
	{
		return $this->get_db()->rollBack();
	}
	
	/**
	 * 查询字段信息
	 * @param string|array $fields  field1,field2
	 * @param int $id
	 * @return mixed
	 */
	public function getFieldsById($fields, $id) {
		$select = $this->select();
		$select->from($this->_table, $fields);
		$select->where('id = ?', $id);
		$result = $this->get_db()->fetchRow($select);
		return $result;
	}
	
	/* 根据key=>value找记录 */
	public function getRowByField($fields, $params) {
		$select = $this->select();
		$select->from($this->_table, $fields);
		foreach($params as $field => $value) {
			$select->where("`{$field}` = ?", $value);
		}
		$result = $this->get_db()->fetchRow($select);
		return $result;
	}
}