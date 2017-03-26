<?php 

class ZendX_App_Version
{
	/**
	 * 生成版本号
	 * @return string
	 */
	public static function make()
	{
		$version = array(
				'major' => 1 + (date('Y') - 2014),
				'minor' => date('m'),
				'revision' => date('d'),
				'build' => date('His'),
		);
		return implode('.', $version);
	}
	
	/**
	 * 检查版本号格式是否合法
	 * @param string $version
	 */
	public static function check($version)
	{
		return preg_match('/^[1-9]\.[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{6}$/', $version);
	}
	
	/**
	 * 比较两个版本号
	 * @param string $version1
	 * @param string $version2
	 * @return mixed
	 */
	public static function compare($version1, $version2)
	{
		if(!ZendX_APP_Version::check($version1) || !ZendX_APP_Version::check($version2))
		{
			return false;
		}
		$version1 = intval(implode('', explode('.', $version1)));
		$version2 = intval(implode('', explode('.', $version2)));
		
		if($version1 == $version2) {
			return 0;
		} else if($version1 < $version2)
		{
			return -1;
		} else {
			return 1;
		}
	}
}
