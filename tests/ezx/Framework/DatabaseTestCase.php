<?php
/**
 * oss系统
 * 数据库测试基类
 * @author zhouxh
 *
 */
abstract class EZX_Framework_DatabaseTestCase extends Zend_Test_PHPUnit_DatabaseTestCase {
	
	private $conn = null;
	
	final public function getConnection() {
		if ($this->conn === null) {
			$dbObj = new ZendX_Db(APPLICATION_ENV);
			$dbBase = $dbObj->get_db();
			$pdo = $dbBase->getConnection();
			$pdo->exec("set foreign_key_checks=0");
			$this->conn = $this->createZendDbConnection($dbBase, '');
		}
		return $this->conn;
	}
	
	
}