<?php
/**
 * 厂商权限表
 * @author zhouxh
 *
 */
class Model_EnterpriseGrant extends ZendX_Model_Base {
	protected $_schema = 'cloud';
	protected $_table = 'EZ_API_ENTERPRISE_GRANT';
	
	/**
	 * 创建授权码
	 * @param int $enterpriseId 企业ID
	 * @param string $name 企业名
	 * @return Ambigous <boolean, string>
	 */
	public function createGrant($enterpriseId, $name){
		$grantData = array(
				'app_key' => md5($enterpriseId),
				'app_salt' => md5(rand()),
				'enterprise_id' => $enterpriseId,
				'status' => 'enable',
				'name' => $name
		);
		$res = $this->insert($grantData);
		return $res;
	}


}