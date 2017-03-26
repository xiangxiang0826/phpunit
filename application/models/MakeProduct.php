<?php
/**
 * 制作产品模型
 * @author zhouxh
 *
 */
class Model_MakeProduct extends ZendX_Model_Base
{
	protected $_schema = 'cloud';
	protected $_table = 'EZ_MAKE_PRODUCT';
	CONST STATUS_DRAFT = 'draft';
	CONST STATUS_PENDING = 'pending';
	CONST STATUS_AUDIT_SUCESS= 'audit_success';
	CONST STATUS_PUBLISHED = 'published';
	CONST STATUS_AUDIT_FAILED= 'audit_failed';
	CONST STATUS_PUBLISH_FAILED= 'publish_failed';
	
	/**
	 * 获得总记录数
	 */
	public function getTotal($query = "")
	{
		$join = ' LEFT JOIN EZ_PRODUCT_CATEGORY c ON t.category_id=c.id  LEFT JOIN EZ_ENTERPRISE e ON t.enterprise_id=e.id LEFT JOIN EZ_PRODUCT AS d ON t.id=d.id';
		if(empty($query)){
			$sql = "SELECT count(*) AS total FROM `{$this->_table}` AS t" . $join;
		}else{
			$sql = "SELECT count(*) AS total FROM `{$this->_table}` AS t {$join} WHERE ".$query;
		}
		return $this->get_db()->fetchOne($sql);
	}
	
	/**
	 * 获取站点所有制作的产品
	 */
	public  function getList($query = "", $offset, $rows, $orderField='t.id', $sort= 'DESC')
	{
		if(empty($query)){
			$sql = "SELECT distinct t.*,d.id as is_post, c.name AS c_name,e.company_name AS e_name,d.status AS p_status,d.ctime AS p_time FROM `{$this->_table}` AS t LEFT JOIN EZ_PRODUCT_CATEGORY c ON t.category_id=c.id  LEFT JOIN EZ_ENTERPRISE e ON t.enterprise_id=e.id 
			            LEFT JOIN EZ_PRODUCT AS d ON t.id=d.id  ORDER BY $orderField $sort limit :offset,:limit";
		}else{
			$sql = "SELECT  distinct t.*,c.name AS c_name,e.company_name AS e_name,d.status AS p_status,d.ctime AS p_time FROM `{$this->_table}` AS t LEFT JOIN EZ_PRODUCT_CATEGORY c ON t.category_id=c.id  LEFT JOIN EZ_ENTERPRISE e ON t.enterprise_id=e.id LEFT JOIN EZ_PRODUCT AS d ON t.id=d.id 
			             WHERE {$query} ORDER BY $orderField $sort limit :offset,:limit";
		}
		$smtp = $this->get_db()->prepare($sql);
		// $smtp->bindParam(':orderby', $orderField);
		// $smtp->bindParam(':sort', $sort);
		$smtp->bindParam(':offset', $offset, PDO::PARAM_INT);
		$smtp->bindParam(':limit', $rows, PDO::PARAM_INT);
		$smtp->execute();
		return $smtp->fetchAll();	
	}
	
	/**
	 * 查询产品信息
	 * @param int $productId 产品Id
	 */
	public function queryProductInfo($productId){
		if($productId) {
			$sql = "SELECT  distinct t.*,c.name AS c_name,e.company_name AS e_name FROM `{$this->_table}` AS t LEFT JOIN EZ_PRODUCT_CATEGORY c ON t.category_id=c.id  LEFT JOIN EZ_ENTERPRISE e ON t.enterprise_id=e.id   WHERE t.id=?";
			$result = $this->get_db()->fetchRow($sql, $productId);
			//已经发布的产品从发布表里面查询产品
			if($result['status'] == Model_MakeProduct::STATUS_PUBLISHED) {
				$publishProduct = new Model_Product();
				$publishInfo = $publishProduct->getFieldsById(array('status','into_ezapp', 'ctime', 'mtime', 'remark'), $productId);
                // print_r($publishInfo);exit(',ln='.__line__);
				if($publishInfo) {
					$result['status'] = $publishInfo['status'];
					$result['into_ezapp'] = $publishInfo['into_ezapp'];
					$result['publish_ctime'] = $publishInfo['ctime'];
					$result['publish_mtime'] = $publishInfo['mtime'];
					// $result['remark'] = $publishInfo['remark'];
				}
            }else{
                $publishProduct = new Model_Product();
				$publishInfo = $publishProduct->getFieldsById(array('status','into_ezapp', 'ctime', 'mtime', 'remark'), $productId);
                // print_r($publishInfo);exit(',ln='.__line__);
				if($publishInfo) {
                    $result['into_ezapp'] = $publishInfo['into_ezapp'];
					$result['publish_ctime'] = $publishInfo['ctime'];
                    $result['publish_mtime'] = $publishInfo['mtime'];
                }
            }
			return $result;
		}
		return false;
	}
	
	/**
	 * 查询设备激活数
	 */
	public function queryActiveNumber($id){
		if(is_int($id)) {
			$idArr = array($id);
		} elseif(is_array($id)) {
			$idArr = $id;
		} else {
			return false;
		}
		$sql = 'SELECT product_id, COUNT(*) AS number FROM EZ_DEVICE WHERE product_id IN('. implode(',', $idArr) . ") AND (status='". Model_Device::STATUS_USER_ACTIVATE . "' OR status='". Model_Device::STATUS_FACT_ACTIVATE. "' ) GROUP BY product_id";
		return $this->get_db()->fetchPairs($sql);
	}
	/**
	 * 待审核产品数目
	 */
	public function needCheckProduct(){
		$sql = "SELECT COUNT(*) FROM " . $this->_table . " WHERE status in ('".self::STATUS_PUBLISH_FAILED."', '".self::STATUS_PENDING."')";
		$number = $this->get_db()->fetchOne($sql);
		return $number;
	}
}