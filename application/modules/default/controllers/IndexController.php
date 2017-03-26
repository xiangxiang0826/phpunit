<?php

class IndexController extends ZendX_Controller_Action
{
	
	public function indexAction() {
		
		/* $ret = ZendX_Tool::SendSchedulerTask('product','publish');
		print_r($ret);
		exit; */
		/*
		$download_url = 'source.1719.com';
		$abs_path = '/data/www/vhosts/1719.com/httpdocs/Sites_wondercloud/images.1719.com/www/ezcloud/firmware/2014/07/20140718_183454fafdb0d83d191db03.gif';
		$ret = Cms_Task::getInstance()->send(array($abs_path),'edit',$download_url,true);
		print_r($ret);
		*/
        // 添加菜单的临时解决方案 by etong <zhoufeng@wondershare.cn>
        $this->redirect('/product/about/index');
		// return true;
	}
	
	
	public function helpAction() {
		
		
		
	}
}