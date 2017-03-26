<?php
/**
 * 创建数据库连接类
 * @author lvyz@wondershare.cn
 *
 */
class ZendX_Db
{
	
	/**
	 * 当前连接
	 * @var Zend_Db_Adapter_Abstract
	 */
	private  $_db;
	
	/**
	 * 初始化数据库配置连接
	 * @param string $schema
	 * @return Zend_Db_Adapter_Abstract
	 */
	public function __construct($schema)
	{
		$config = ZendX_Config::get('mysql');
		
		if(!isset($config[$schema])) {
			throw new Zend_Db_Exception("{$schema} is a invalid schema");
		}
		
		$db = Zend_Db::factory($config[$schema]['adapter'], $config[$schema]['params']);
		
		$this->_db = $db;
	}
	
	public function get_db() {
		return $this->_db;
	}
	
	/**
	 * 开始事务
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
	
}