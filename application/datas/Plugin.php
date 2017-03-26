<?php
/**
 * 官网插件 模块的数据提取类
 * @author liujd
 * @time 2014-04-30
 */
class Datas_Plugin extends Cms_Data
{
	const DEVICE_STATUS_PUBLISH = 'enable';
	const DEVICE_STATUS_DISABLE = 'disable';
	
	const PLUGIN_STATUS_PUBLISH = 'enable';
	const PLUGIN_STATUS_DISABLE = 'disable';
	
	const CATEGORY_STATUS_DISABLE = 'disable';
	const CATEGORY_STATUS_ENABLE = 'enable';
	
	public function __construct() {
		$this->db = Cms_Db::getConnection();
		$this->web_db = Cms_Db::getConnection('core_master');
	}
	
	/**
	 * 获取适配的设备列表(数组) ，根据插件详情页里的插件id，找出所有设备列表
	 * {ad::list_plugin ad limit="0,5" /}
	 * @param array $params
	 */
	public function  list_device($params) {
	    extract($params);
	   	$cfg_obj = Cms_Func::getConfig('template/template', 'plugin');
	    $table_name = $cfg_obj->table;
	    
	    $arr_upload_cfg = Cms_Func::getConfig('upload', 'app_plugin');
	    $upload_cfg = $arr_upload_cfg->upload->params->toArray();
	    $download_cfg = Cms_Func::getConfig('upload', 'download');
	    $download_url = strpos($download_cfg->domain, 'http://') !==false ? $download_cfg->domain : "http://{$download_cfg->domain}";
	    
	    $result = array();
	    $sql = "SELECT plugin_id as device_id,url FROM {$table_name}";
	    $rows = $this->db->fetchAll($sql);
	    if(empty($rows)) return false;
	    $id_ary = array();
	    $url_map = array();
	    foreach($rows as $row) {
	    	$url_map[$row['device_id']] = $row['url'];
	    	$id_ary[] = $row['device_id'];
	    }
	    $id_ary = array_unique($id_ary);
	    $ids = implode(',',$id_ary);
	    $sql = "SELECT b.id as device_id,b.icon as device_icon,b.name as device_name,b.description as device_description,
	            c.id as plugin_id,c.description,c.name as plugin_name,c.version,
    			c.platform,c.app_version,c.icon as plugin_icon,c.sort as plugin_sort,
    			c.download as plugin_download,c.whats_new,c.check_sum,c.score as plugin_score
    	FROM `SM_APP_PLUGIN_DEVICE_MAP` a INNER JOIN `SM_APP_ADAPT_DEVICE` b ON a.device_id = b.id
		INNER JOIN SM_APP_PLUGIN c ON a.plugin_id = c.id WHERE a.device_id IN ({$ids})  AND c.status = '".self::PLUGIN_STATUS_PUBLISH."'
		GROUP BY a.device_id ORDER BY c.`version` DESC";
	    $row = $this->web_db->fetchAll($sql);
	    if(empty($row)) {
	        return array();
    	}
    	foreach($row as $k => &$val) {
    		$val['url'] = $url_map[$val['device_id']];
    		$val['device_icon'] = "{$download_url}{$val['device_icon']}";
    		$val['plugin_icon'] = "{$download_url}{$val['plugin_icon']}";
    		$val['plugin_download'] = "{$download_url}{$val['plugin_download']}";
    	}
    	return $row;
	}
    
	/**
	 * 获取设备的详细信息(一维数组)
	 * 		{plugin::detail device_id="112" /}
	 * 		{=$r['content']}
	 * 
	 * 页面参数   plugin_id     插件ID
	 * @param array $params
	 */
	public function detail($params) {
	    extract($params);
	    if(empty($device_id)) {
	        echo '没有发现 设备ID';
	        return array();
	    }
	    
	    $arr_upload_cfg = Cms_Func::getConfig('upload', 'app_plugin');
	    $upload_cfg = $arr_upload_cfg->upload->params->toArray();
	    
	    //获得下载domain的配置
	    $download_cfg = Cms_Func::getConfig('upload', 'download');
	    $download_url = strpos($download_cfg->domain, 'http://') !==false ? $download_cfg->domain : "http://{$download_cfg->domain}";
	    
	    $device_id = intval($device_id);
	    $sql = "SELECT b.id as device_id,b.icon as device_icon,b.name as device_name,b.description as device_description,
	    	    c.id as plugin_id,c.description,c.name as plugin_name,c.version,
    			c.platform,c.app_version,c.icon as plugin_icon,c.sort as plugin_sort,
    			c.download as plugin_download,c.whats_new,c.check_sum,c.score as plugin_score
    	FROM `SM_APP_PLUGIN_DEVICE_MAP` a  INNER JOIN SM_APP_PLUGIN c ON a.plugin_id = c.id 
    	INNER JOIN `SM_APP_ADAPT_DEVICE` b ON a.device_id = b.id
    	WHERE a.device_id = '{$device_id}'  AND c.status = '".self::PLUGIN_STATUS_PUBLISH."' 
        ORDER BY c.`version` DESC LIMIT 1";
	    $row = $this->web_db->fetchRow($sql);
	    if(empty($row)) {
	        return array();
    	}
    	$row['description'] = nl2br($row['description']);
    	//$row['whats_new'] = nl2br($row['whats_new']);
    	$row['device_icon'] = "{$download_url}{$row['device_icon']}";
    	$row['plugin_icon'] = "{$download_url}{$row['plugin_icon']}";
    	$row['plugin_download'] = "{$download_url}{$row['plugin_download']}";
    	return $row;
	}
}