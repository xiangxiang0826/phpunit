<?php
/**
 * Disqus库封装
 * 
 * @author 刘通
 * @since 2012-08-07
 */
defined('CMS_LIB_ROOT') OR define('CMS_LIB_ROOT', dirname(__FILE__) . '/Libs');

include_once CMS_LIB_ROOT . '/disqus/disqus.php';

class Cms_Disqus
{
	private $_disqus = null;
	
	function __construct($api_key=null)
	{
		if($api_key)
		{
			$this->init($api_key);
		}
	}
	
	function init($api_key)
	{
		$this->_disqus = new DisqusAPI(null, $api_key);
	}
	
	function get_forum_posts($start_id, $limit=200)
	{
		if(null === $this->_disqus)
		{
			return array();
		}
		
		$args = array(
				'filter' => 'approved',
				'start_id' => $start_id,
				'limit' => $limit,
				'order' => 'asc'
		);
		$data = $this->_disqus->get_forum_posts(null, $args);
		return $data;
	}
	
	/**
	 * 同步删除评论
	 */
	function delete($disqus_id)
	{
		$disqus_cfg = Cms_Func::getConfig('disqus', 'global')->toArray();
		$url = 'http://disqus.com/api/3.0/posts/remove.json?access_token='.$disqus_cfg['access_token'].'&api_key='.$disqus_cfg['api_key'].'&api_secret='.$disqus_cfg['api_secret'];
		
		$client = new Zend_Http_Client($url);
		$client->setParameterPost('post', $disqus_id);
		
		$response = $client->request('POST');
		return true;
	}
}