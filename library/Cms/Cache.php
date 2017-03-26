<?php

/**
 * 缓存类
 * @author:刘通
 */
class Cms_Cache
{
	private static $instance = null;
	private $_handler;
	
	private function __construct()
	{
		$cache_cfg = Cms_Func::getConfig('cache', 'handler');
		
		$handler = $cache_cfg->name;
		
		$class_name = 'Cms_Cache_' . ucfirst($handler);
		$this->_handler = new $class_name($cache_cfg);
		$this->setTtl($cache_cfg->ttl);
	}
	
	static function getInstance()
	{
		if(null === self::$instance)
		{
			self::$instance = new self();
		}
		
		return self::$instance;
	}
	
	function setTtl($ttl)
	{
		$this->_handler->setTtl($ttl);
	}
	
	function get($key)
	{
		return $this->_handler->get($key);
	}
	
	function set($key, $value)
	{
		return $this->_handler->set($key, $value);
	}
	
	function delete($key)
	{
		return $this->_handler->delete($key);
	}
	
	function deleteAll()
	{
		return $this->_handler->deleteAll();
	}
}