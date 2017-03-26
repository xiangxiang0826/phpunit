<?php
/**
 * 配置内容获取扩展类
 * @author lvyz@wondershare
 *
 */
class ZendX_Config
{
	public static function get($filename, $section = null) {
		$filename = APPLICATION_PATH . DS . 'configs' . DS . $filename . '.ini';
		$config = new Zend_Config_Ini($filename, $section);
		if(!empty($config)) {
			$config = $config->toArray();
		}
		return $config;
	}
}