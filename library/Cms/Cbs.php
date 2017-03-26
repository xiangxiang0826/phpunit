<?php

/**
 * 同步cbs数据的类
 * 
 * @author 刘通
 */
final class Cms_Cbs
{
	private $_url;
	
	function __construct()
	{
		$cbs_cfg = Cms_Func::getConfig('cbs', 'url')->toArray();
		$this->_url = $cbs_cfg['product'];
	}
	
	/**
	 * 获取cbs数据
	 * 
	 * @param integer $cbs_id
	 * @param array $fields
	 */
	function getData($cbs_id, array $fields=array())
	{
		$cbs_id = intval($cbs_id);
		
		//http://cbs.wondershare.cn/interface.php?m=cms_product&type=product_content&pids=0请求在cbs系统中有问题
		if($cbs_id <= 0)
		{
			Cms_Func::jsonResponse(Cms_L::_('cbs_id_error'));
		}
		
		$json_data = file_get_contents($this->_url . $cbs_id);
		$cbs_data = json_decode($json_data, true);
		
		if(!$cbs_data)
		{
			Cms_Func::jsonResponse(Cms_L::_('cbs_data_error'));
		}
		
		$cbs_data = $cbs_data[$cbs_id];
		
		if($fields)
		{
			$data = array();
			foreach($fields as $key => $field)
			{
				$key = is_string($key) ? $key : $field;
				if(isset($cbs_data[$field]))
				{
					$data[$key] = $cbs_data[$field];
				}
			}
			
			$cbs_data = $data;
		}
		
		return $cbs_data;
	}
}