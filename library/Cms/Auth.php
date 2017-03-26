<?php

/**
 * 权限控制
 * 
 * @author 刘通
 * 
 * 1.开发者不受权限控制
 * 2.下列权限只给管理员组
 * 		a) 系统管理
 * 		b) 用户管理
 */
class Cms_Auth
{
	/**
	 * 权限用户组验证
	 * 		管理员不需要验证权限
	 * 
	 * @param integer	$site_id	站点ID
	 * @param enum		$level		验证级别 {C:controller, A:action}	
	 * @param array		$request	array('m'=>'','c'=>'', 'a')
	 */
	static function auth($site_id, $level='C', $request=null)
	{
		return true;
	}
	
	/**
	 * 验证用户权限
	 * 
	 * @param string $prev
	 */
	static function checkUserPrev($prev)
	{
		return true;
	}
	
	/**
	 * 截取菜单URL
	 * 		登录时，用以截取菜单URL
	 * @param string $item
	 */
	static function cutUrl(&$item)
	{
		$part = explode('/', $item, 4);
		if(isset($part[3]))
		{
			unset($part[3]);
			$item = implode('/', $part);
		}
	}
	
	/**
	 * 是否是管理员权限
	 * 		group_id:0	超级用户，相当于Linux系统的root用户，本系统中时admin
	 * 		group_id:1	开发组，除了删除开发组用户和更改开发组用户的用户组，没有其它的权限限制
	 * @param array $group_ids
	 */
	static function isAdmin(array $group_ids)
	{
		return (bool)array_intersect(array(0, 1), $group_ids);
	}
	
	/**
	 * 是否超级管理员
	 * 
	 * @param array $group_ids
	 */
	static function isSuper(array $group_ids)
	{
		return in_array(0, $group_ids);
	}
	
	/**
	 * 获取用户信息
	 */
	static function getUserInfo()
	{
		return Common_Auth::getUserInfo();
	}
}