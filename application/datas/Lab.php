<?php
/**
 * 沙盒模块的数据提取类
 * @author hcb
 * @time 2013-09-24
 */
class Datas_Lab extends Cms_Data
{
	private $_end_condition = " `tpl_id` > 0 AND `state` = 'able'";
	
	public function __construct()
	{
		parent::__construct();
		date_default_timezone_set('Etc/GMT+8');
	}
	
	
	/**
	 * 项目信息
	 * */
	public function info( $params )
	{
		if(empty($params['lab_id']))
		{
		    echo '没有发现项目ID';
			return array();
		}
		
		$lab_id = intval($params['lab_id']);
		
		$sql = "SELECT *, page_url AS url FROM `lab` WHERE `lab_id`={$lab_id}";
		$data = $this->fetchRow($sql);

		$data['date_s_f'] = date('m/d/Y', strtotime($data['date_s']));
		$data['date_e_f'] = date('m/d/Y', strtotime($data['date_e']));
		
		$survey_info = Cms_Template::getListByModuleType($params['site_id'], 'lab', 'survey');
		
		$sql = "SELECT `url` FROM {$survey_info[0]['page_table']} WHERE entity_id = {$lab_id}";
		$sur = $this->fetchRow($sql);
		$data['survey_url'] = $sur['url'];
		
		return $data;
	}
	
	
	/**
	 * 项目列表
	 * */
	public function lists( $params )
	{
		//条件
		$condition = array();
		
		if( !empty($params['lab_ids']) ){
			$condition[] = "`lab_id` IN ({$params['lab_ids']})";
		}
		
		$limit = 0;
		$where_limit = '';
		if(isset($params['num']))
		{
			$limit = intval($params['num']);
			$limit > 0 && $where_limit = " LIMIT {$limit}";
		}
		 
		if(empty($params['sort']))
		{
			$params['sort'] = 'date_s';
		}
		
		if(empty($params['order']) || strtolower($params['order']) != 'asc')
		{
			$params['order'] = 'DESC';
		}
		

		$condition[] = '`date_e` > '.date('Y-m-d');
		$condition = implode(' AND ', $condition);
		
		$condition = "WHERE `site_id` = {$params['site_id']} AND {$condition} AND {$this->_end_condition} ORDER BY `{$params['sort']}` {$params['order']} {$where_limit}";
		$sql = "SELECT `lab_id`, `name`, `custom_name`, `desc_short`, `join_num`, `date_s`, `date_e`, `img`, `page_url` AS `url` FROM `lab` {$condition}";
		$data = $this->fetchAll($sql);
		return $data;	
	}
	
	
	/**
	 * Feature列表
	 * {lab::feature_list lab_id="33" type="key" /}
	 * */
	public function feature_list( $params )
	{
		if(empty($params['lab_id']))
		{
			echo '没有发现项目ID';
			return array();
		}
		
		$lab_id = (int)$params['lab_id'];
		
		if( empty( $params['type'] ) )
		{
			$params['type'] = 'key';
		}
		
		$sql = "SELECT * FROM `lab_features` WHERE `lab_id` = {$lab_id} AND `type` = '{$params['type']}' AND `state` = 'able'";
		$data= $this->fetchAll($sql);
		return $data;
	}
	
	
}
?>