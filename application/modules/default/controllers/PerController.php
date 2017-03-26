<?php

class PerController extends ZendX_Controller_Action {
	
	public function indexAction() {
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$model_user = new Model_User();
		$modules_dir = APPLICATION_PATH . DS . 'modules' . DS;
		$dir_handle = dir($modules_dir);
		$filter = array('.','..','default');
		while(false !== ($entry = $dir_handle->read())) {
			if(!in_array($entry,$filter)) {
				print "{$entry}====><br>";
				$controller_dir = "{$modules_dir}{$entry}".DS."controllers";
				if(!is_dir($controller_dir)) continue;
				$controller_dir_handle = dir($controller_dir);
				
				while(false !== ($file = $controller_dir_handle->read())) {
					if(!in_array($file,array('.','..'))) {
						preg_match('/(\w+)Controller\.php/i',$file,$controller_name);
						//print_r($controller_name);
						$controller_file = $controller_dir . DS.$file;
						$contents = file_get_contents($controller_file);
						preg_match_all('/function\s*(\w+)Action/i', $contents, $action_array);
						//print_r($action_array);
						
						//print "{$controller_file}<br>";
						
						foreach($action_array[1] as $action) {
							$url = strtolower("{$entry}/{$controller_name[1]}/{$action}");
							$sql = "SELECT id FROM SYSTEM_PERMISSION WHERE url = '{$url}'";
							print "{$sql}<br>";
							$row = $model_user->get_db()->fetchRow($sql);
							if(!$row) { // 如果不存在，则插入此url
								$sql = "INSERT INTO SYSTEM_PERMISSION(`name`,`i18n_name`,`type`,`url`,`parent_id`,`module_id`,`cuser`,`muser`) 
										VALUES('{$entry}','{$controller_name[1]}','action','{$url}','0','0',1,1)"; // 
								print "<font color=red>{$sql}</font><br>";
								$model_user->get_db()->query($sql);
							}
							//print "{$url}<br>";
						}
					}
				}
				
				//print 
				//print "{$controller_dir}<br>";
			}
			
		}
		
		//print 'per';
	}
	
	
	public function createAction() {
		exit;
		set_time_limit(0);
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		$model = new Model_User();
		
		$sql = "SELECT id FROM SYSTEM_PERMISSION WHERE status = 'enable'";
		$permissions = $model->get_db()->fetchAll($sql);
		
		$sql = "SELECT id FROM SYSTEM_MODULE WHERE status = 'enable'";
		$modules = $model->get_db()->fetchAll($sql);
		
		$sql = "SELECT id FROM SYSTEM_USER_GROUP WHERE status = 'enable'";
		$groups = $model->get_db()->fetchAll($sql);
		foreach($groups as $group) {
			foreach($modules as $module) {
				$sql = "INSERT INTO SYSTEM_GROUP_MODULE(group_id,module_id) VALUES('{$group['id']}','{$module['id']}')";
				$model->get_db()->query($sql);
				print "<font color=red>{$sql}</font><br>";
			}
			
			foreach($permissions as $per) {
				$sql = "INSERT INTO SYSTEM_GROUP_PERMISSION(group_id,perm_id) VALUES('{$group['id']}','{$per['id']}')";
				$model->get_db()->query($sql);
				print "<font color=red>{$sql}</font><br>";
			}
		}
		
		
		//print_r($permissions);
	}
}