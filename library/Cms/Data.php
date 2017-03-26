<?php

/**
 * 提取数据的基类
 * 
 * @author 刘通
 */
class Cms_Data
{
	private $db;
	private $cache;
	function __construct()
	{
		//$this->db = Cms_Db::getConnection();
		$this->cache = Cms_Cache::getInstance();
		$this->cache->setTtl(10);
	}
	
	/**
	 * 获取一维数组的方法
	 * 
	 * @param string $sql
	 * @return 一维数组
	 */
	function fetchRow($sql)
	{
		$key = '_row_' . md5($sql);
		$data = $this->cache->get($key);
		if(!$data)
		{
			$data = $this->db->fetchRow($sql);
			$this->cache->set($key, $data);
		}
		
		return $data;
	}
	
	/**
	 * 获取二维数组的方法
	 * 
	 * @param string $sql
	 * @return 二位数组
	 */
	function fetchAll($sql)
	{
		$key = '_all_' . md5($sql);
		$data = $this->cache->get($key);
		if(!$data)
		{
			$data = $this->db->fetchAll($sql);
			$this->cache->set($key, $data);
		}
		
		return $data;
	}
	
	/**
	 * 转义数据
	 */
	function quoteInto($text, $value, $type=null, $count=null)
	{
		return $this->db->quoteInto($text, $value, $type, $count);
	}
	
	/**
	 * 获取模块的列表信息
	 * 		分页中获取列表信息
	 * 
	 * @param array $params
	 * 		example: array('site_id'=>'1', 'order'=>'id', 'sort'=>'desc' 'page'=>'', 'pagesize'=>'')
	 * @return array
	 * 		example: array('total'=>'', 'rows'=>'', 'pagesize'=>'')
	 */
	function pageQuery($params)
	{
		$page = isset($params['page']) ? (int) $params['page'] : 1;
		$pagesize = isset($params['pagesize']) ? (int) $params['pagesize'] : 15;
		$start = ($page - 1) * $pagesize;

		$sql = $params['sql'];
		$data['total'] = $this->db->fetchOne("SELECT COUNT(*) AS `count` ".stristr($sql, 'from'));
		$sql .= " LIMIT {$start}, {$pagesize}";
		$data['rows'] = $this->fetchAll($sql);
		$data['pagesize'] = $pagesize;
		return $data;
	}
	
	/**
	 * 获取扩展字段信息
	 * 
	 * @param integer	$site_id
	 * @param string	$module
	 */
	protected function extField($site_id, $module)
	{
		$key = '_ext_' . md5($site_id . $module);
		$data = $this->cache->get($key);
		
		if(!$data)
		{
			$ext2site_model = new System_Models_Ext2site();
			$tmp = $ext2site_model->getInfoBySiteId($site_id, $module);
			foreach($tmp as $value)
			{
				$data[$value['field_id']] = $value;
			}
			
			$this->cache->set($key, $data);
		}
		
		return $data;
	}
	
	/**
	 * 处理url
	 */
	protected function _formatURL($url)
	{
		$url = Cms_T::formatURL($url);
		
		return $url;
	}
}