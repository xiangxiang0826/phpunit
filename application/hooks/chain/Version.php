<?php
/**
 * 更新upgrade_type中version字段
 * @author liujd@wondershare.cn
 */
class Hooks_Chain_Version
{
	function update_latest_version($data) {
		$upgrade_type_id = isset($_POST['upgrade_type_id']) ? (int)$_POST['upgrade_type_id'] : 0;
		$firmware_id = isset($_POST['firmware_id']) ? (int)$_POST['firmware_id'] : 0;
		if(!$upgrade_type_id || !$firmware_id) return false;
		$model_upgrade = new Model_Upgrade();
		$versions = $model_upgrade->GetMaxVersionByIds(array($upgrade_type_id));
		$max_version = current($versions);
		$model_upgrade->update(array('version'=>$max_version['version']),array("id = '{$upgrade_type_id}'")); // 更新最新版本
		// 更新EZ_PRODUCT_FIRMWARE_MAP  中status为（IGNORE  test）   ==> ENABLE ，且firmware_comm_id = $firmware_id   
		$model_firmware = new Model_FirmwareComm(); //firmware_comm_id
		$model_firmware->UpdateProductMap(array('status'=>'enabled'), array("status IN ('test','ignore') AND firmware_comm_id = '{$firmware_id}'"));
		return true;
	}
	
	
	function update_version_by_status($data) {
		$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
		if(!$id) return false;
		$upgrade_model = new Model_Upgrade();
		$version_info = $upgrade_model->getVersionByField(array('id','upgrade_type_id','device_type','status'),array('id'=>$id));
		$upgrade_type_id = $version_info['upgrade_type_id'];
		if(!$upgrade_type_id) return false;
		$versions = $upgrade_model->GetMaxVersionByIds(array($upgrade_type_id));
		$max_version = current($versions);
		if(!$max_version) return false;
		$upgrade_model->update(array('version'=>$max_version['version']),array("id = '{$upgrade_type_id}'")); // 更新最新版本
		return true;
	}
	
	public function update_ezapp_version($data) {
		$model = new Model_EnterpriseApp();
		$result = $model->getRowByField(array('upgrade_type_id','id'), array('platform' => $_POST['app']['platform'], 'is_ezapp' => 1));
		if(!$result) return false;
		$upgrade_type_id = $result['upgrade_type_id'];
		$upgrade_model = new Model_Upgrade();
		$versions = $upgrade_model->GetMaxVersionByIds(array($upgrade_type_id));
		$max_version = current($versions);
		if(!$max_version) return false;
		$upgrade_model->update(array('version'=>$max_version['version']),array("id = '{$upgrade_type_id}'")); // 更新最新版本
		return true;
	}
	
}