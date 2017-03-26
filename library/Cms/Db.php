<?php

/**
 * 数据操作类
 * 
 * @author 刘通
 */
class Cms_Db
{
	static function getConnection($section = 'master')
	{
		$db = new ZendX_Cms_Model();
		return $db->get_db();
	}
}