<?php
// Copyright 2012 @Wondershare.com
// $Id: file.class.php 25080 2012-09-13 09:04:23Z liutong $

/*
 |---------------------------------------------------
 |	系统的cache类
 |---------------------------------------------------
 |	author：liutong
 */
class Cms_Cache_File extends Cms_Cache_Base
{
	private $_cache_dir;
	
	function __construct($cache_cfg)
	{
		$this->_cache_dir = $cache_cfg->cache_dir;
		if(!is_dir($this->_cache_dir))
		{
			mkdir($this->_cache_dir, 0755, true);
		}
	}
	
	function get($key)
	{
		$cache_file = $this->_cache_dir . $key;
		if(!file_exists($cache_file))
		{
			return false;
		}
		
		$cache_time = filemtime($cache_file);
		if(time() - $cache_time > $this->ttl)
		{
			@unlink($cache_file);
			return false;
		}
		
		return unserialize(file_get_contents($cache_file));
	}
	
	function set($key, $value)
	{
		$cache_file = $this->_cache_dir . $key;
		$value = serialize($value);
		return file_put_contents($cache_file, $value);
	}
	
	function delete($key)
	{
		$cache_file = $this->_cache_dir . $key;
		return @unlink($cache_file);
	}
	
	function deleteAll()
	{
		$dir = dir($this->_cache_dir);
		
		while(false !== ($entry = $dir->read()))
		{
			$entry = $this->_cache_dir . $entry;
			if(is_dir($entry)) continue;
			@unlink($entry);
		}
		
		$dir->close();
	}
}