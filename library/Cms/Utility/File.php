<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of File
 *
 * @author 刘通
 */
class Cms_Utility_File {
	
	/**
	 * 获取指定文件夹下的文件夹，过滤掉[.|..|.svn]
	 *  
	 * @param string $dir Path to the directory
	 * @return array
	 */
	public static function getSubDir($dir) 
	{
		if (!file_exists($dir)) 
		{
			return array();
		}
		
		$subDirs 	 = array();
		$dirIterator = new DirectoryIterator($dir);
		foreach ($dirIterator as $dir) 
		{
			if ($dir->isDot() || !$dir->isDir()) 
			{
				continue;
			}
			$dir = $dir->getFilename();
			if ($dir == '.svn') 
			{
				continue;
			}
			$subDirs[] = $dir;
		}
		return $subDirs;
	}
	
	/**
	 * @param string $dir
	 * @return boolean
	 */
	public static function deleteRescursiveDir($dir) 
	{
		if (is_dir($dir)) 
		{	        	
			$dir 	 = (substr($dir, -1) != DS) ? $dir.DS : $dir;
			$openDir = opendir($dir);
			while ($file = readdir($openDir)) 
			{
				if (!in_array($file, array(".", ".."))) 
				{
					if (!is_dir($dir.$file)) 
					{
						@unlink($dir.$file);
					} else {
						self::deleteRescursiveDir($dir.$file);
					}
				}
			}
			closedir($openDir);
			@rmdir($dir);
		}
        
		return true;
	}
	
	/**
	 * @param string $source
	 * @param string $dest
	 * @return boolean
	 */
	public static function copyRescursiveDir($source, $dest) 
	{
		$openDir = opendir($source);
		if (!file_exists($dest)) 
		{
			@mkdir($dest);
		}
		while ($file = readdir($openDir)) 
		{
			if (!in_array($file, array(".", ".."))) 
			{
				if (is_dir($source . DS . $file)) 
				{ 
					self::copyRescursiveDir($source . DS . $file, $dest . DS . $file); 
				} else { 
					copy($source . DS . $file, $dest . DS . $file); 
				} 
			}
		}
		closedir($openDir);
		
		return true;
	}
	
	/**
	 * 删除一个文件，并向后删除非空文件夹，主要是给删除页面时用
	 * 		
	 * @param string	$path
	 * @param enum		$is_list {Y, N}
	 */
	static function deleteNodeBackTrace($path, $is_list='N')
	{
		$url_arr = array();
		if(is_file($path))
		{
			$url_arr[] = $path;
			unlink($path);
			$path = dirname($path);
			
			if($is_list == 'Y')
			{
				$url_arr = array_merge($url_arr, self::getPageFiles($path, true));
			}
		}
		
		while(true) {
			if(!@rmdir($path))
			{
				break;
			}
			$url_arr[] = $path;
			$path = dirname($path);
		}
		
		//发送任务
		Cms_Task::getInstance()->send($url_arr, 'delete'); ////Cms_Task::send($url_arr, 'delete');
		return true;
	}
	
	/**
	 * 获取一个目录下的分页列表
	 * 		说明：用glob()好像也要进行二次处理，这里先用dir()
	 */
	static function getPageFiles($dirname, $is_del=false)
	{
		if(!is_dir($dirname))
		{
			return array();
		}
		
		$dir = dir($dirname);
		$files = array();
		while (false !== ($entry = $dir->read()))
		{
			if(preg_match('/^\d+\.html$/', $entry))
			{
				$file = $dirname . '/' . $entry;
				$files[] = $file;
				$is_del && unlink($file);
			}
		}
		$dir->close();
		
		return $files;
	}
}