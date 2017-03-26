<?php
/**
 * 用于创建子目录系列
 * 
 * @package libs
 * @author leimengcheng<leimc@spotmau.cn>
 * @date 2014-01-15
 */

class Dir {

	/**
	 * 根据$dir递归创建相应目录
	 * 
	 * @param string $dir
	 */
	public function mkdir($dir)
	{
		if(is_dir($dir)) return true;
		return  mkdir($dir, 0666,true) && chmod($dir, 0755);
	}
	
}
?>