<?php

/**
 * memcached缓存类
 * 
 * @author 刘通
 */
class Cms_Cache_Memcached extends Cms_Cache_Base
{
	private $_memcached;
	
	function __construct($cache_cfg)
	{
		$this->_memcached = new Memcache();
		
		if(!@$this->_memcached->connect($cache_cfg->host, $cache_cfg->port))
		{
			Cms_Func::response(Cms_L::_('memcached_connect_error'));
		}
	}
	
	function get($key)
	{
		$key = $this->getKey($key);
		return $this->_memcached->get($key);
	}
	
	function set($key, $value)
	{
		$key = $this->getKey($key);
		return $this->_memcached->set($key, $value, 0, $this->ttl);
	}
	
	function delete($key)
	{
		$key = $this->getKey($key);
		return $this->_memcached->delete($key);
	}
	
	function deleteAll()
	{
		return $this->_memcached->flush();
	}
	
	function getKey($key)
	{
		return '_cms_' . $key;
	}
}