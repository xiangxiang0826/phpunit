<?php
// Copyright 2012 @Wondershare.com
// $Id: base.class.php 25080 2012-09-13 09:04:23Z liutong $

/*
 |---------------------------------------------------
 |	cache基类
 |---------------------------------------------------
 |	author：liutong
 */
abstract class Cms_Cache_Base
{
	protected $ttl;
	
	function setTtl($ttl)
	{
		$this->ttl = $ttl;
	}
	
	/**
	 * getter
	 * @param string $key
	 */
	abstract function get($key);
	
	/**
	 * setter
	 * @param string $key
	 * @param mixed $value
	 */
	abstract function set($key, $value);
	
	/**
	 * 删除
	 * @param string $key
	 */
	abstract function delete($key);
	
	/**
	 * 删除所有
	 */
	abstract function deleteAll();
}