<?php
/* 
 * 系统模块
 *  */
class System_IndexController extends ZendX_Controller_Action
{
	
	public function indexAction() {
        $this->redirect('/system/user/account');
		// return ;
	}
}