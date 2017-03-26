<?php
/**
 * 编辑器
 *
 * @author LiuTong
 * @since: 2012年5月7日
 */
class Cms_Editor
{
	
	//construct
	function __construct()
	{		
		
	}
	
	/**
	 * 返回编辑代码
	 * 
	 * @param [array | string] $name 	编辑框CSS选择符
	 * @param [array | string] $content	编辑框中对应的内容
	 * @param integer $width
	 * @param integer $height
	 */
	function editor($name='content', $content='', $width=700, $height=300, $editor = 'Kindeditor')
	{
		$class_name = "Cms_Editors_{$editor}";
		if(class_exists($class_name))
		{
			return call_user_func(array($class_name, 'editor'), $name, $content, $width, $height);
		}
		return false;
	}
}