<?php
/**
 * 产品类别
 * @author zhouxh
 *
 */
class Model_ProductCate extends ZendX_Model_Base
{
	protected $_schema = 'cloud';
	protected $_table = 'EZ_PRODUCT_CATEGORY';
	const STATUS_ENABLE = 'enable';
	const STATUS_DISABLE = 'disable';
	const STATUS_DELETE = 'deleted';
	const ROOT_ID = 1;
	/**
	 * 树显示类别
	 */
	public function dumpTree($all = false) {
			$where = !$all ? "status='" . self::STATUS_ENABLE . "'" : 1;
			$sql = "SELECT id,name,parent_id,layer,sort FROM `{$this->_table}` WHERE {$where} ORDER BY sort DESC";
			$category = $this->get_db()->fetchAll($sql);
			$treeArr = ZendX_Array::array_to_tree($category, 'id', 'parent_id');
			return  ZendX_Array::dumpArrayTree($treeArr, $level=0, $T='├', $L='└', $I='│', $S='&nbsp;');
	}
	/**
	 * 查询列表
	 */
	public function getListData(){
		$sql = "SELECT * FROM `{$this->_table}` WHERE status='" . self::STATUS_ENABLE . "' ORDER BY sort DESC,mtime DESC";
		$category = $this->get_db()->fetchAll($sql);
		$treeArr = $category;
		return $treeArr;
	}
	
	/**
	 * 通过父ID查询该父类下的子类
	 * @param int $parent_id
	 */
	public function getChild($parent_id) {
		$sql = 'SELECT id,depart_name FROM ' . $this->_table . ' WHERE parent_id=:parent';
		$smtp = $this->get_db()->prepare($sql);
		$smtp->bindParam(':parent', $parent_id, PDO::PARAM_INT);
		$smtp->execute();
		$groups = array();
		$list = $smtp->fetchAll();
		foreach ($list as $row) {
			$groups[$row['id']] = $row['name'];
		}
		return $groups;
	}
	
	/**
	 * 查询类别下的所有类别
	 * @param int $departId
	 */
	public function getAllChildrenById($departId, $arr = array()){
		if(empty($arr)) {
		    $sql = 'SELECT id,name,parent_id FROM ' . $this->_table . ' WHERE status=\'' . self::STATUS_ENABLE . "'";
		    $arr = $this->get_db()->fetchAll($sql);
		}
		$subTree = ZendX_Array::array_to_tree($arr, 'id', 'parent_id', 'childrens', $departId, true);
		if (!isset($subTree['childrens'])) {
			return false;
		} else {
			$children = array();
			self::getHasChild($subTree['childrens'], $children);
			return $children;
		}
	}
	
	/**
	 @ $tree array 树数组
	 @ $child array 该树的所有子元素
	 **/
	public static function getHasChild($tree, &$child){
		foreach($tree as $row) {
			if(!isset($row['childrens'])) {
				$child[] = $row['id'];
			} else {
				$child[] = $row['id'];
				$child = self::getHasChild($row['childrens'],$child);
			}
		}
		return $child;
	}
	/**
	 * 添加产品类别
	 * @param unknown_type $data
	 * @return boolean|number
	 */
	public function addCategory($data){
		if(empty($data)) {
			return false;
		} 
		$data['mtime'] = date('Y-m-d H:i:s', time());
		$data['status'] = Model_ProductCate::STATUS_ENABLE;
		$parents = $this->calcuPath($data['parent_id']);
		array_unshift($parents, $data['parent_id']);
		$data['layer'] = implode(',', $parents);
		$res = $this->get_db()->insert($this->_table, $data);
		return $res;
	}
	/**
	 * 更新产品类别
	 * @param array $data
	 * @param int $id
	 */
	public function editCategory($data, $id) {
		$data['mtime'] = date('Y-m-d H:i:s', time());
		if($id == self::ROOT_ID) {
			$data['parent_id'] = 0;
		}
		$data['layer'] = '0';
		if($data['parent_id'] != $id) { // 不能把自己作为自己的父类，modify by liu.jinde
			$parents = $this->calcuPath($data['parent_id']);
			array_unshift($parents, $data['parent_id']);
			$data['layer'] = implode(',', $parents);
		} else {
			$data['parent_id'] = 0;
		}
		$where = $this->get_db()->quoteInto('id = ?', $id);
		return $this->update($data, $where);
	}
	/**
	 * 删除产品类别
	 * @param int $id
	 * @return number
	 */
	public function delCategoryById($id) {
		//查询类别的子类别
		$allIds = $this->getAllChildrenById($id);
		if($allIds) {
			$allIds[] = $id;
		} else {
			$allIds = array($id);
		}
		$where = "id IN(". implode(',', $allIds) .")";
		$data = array();
		$data['mtime'] = date('Y-m-d H:i:s', time());
		$data['status'] = Model_ProductCate::STATUS_DELETE;
		return $this->update($data, $where);
	}
	/**
	 * 查询分类下是否有产品
	 * @param int $id
	 * @param int 返回数目
	 */
	public function categoryHasProduct($id, $list = array()) {
		$allCategory = $this->getAllChildrenById($id, $list);
		if($allCategory == false) {
			$allCategory = array($id);
		} else {
			$allCategory[] = $id;
		}
		$sql = 'SELECT COUNT(*) AS number FROM EZ_PRODUCT WHERE category_id IN('. implode(',', $allCategory) . ")";
		return $this->get_db()->fetchOne($sql);
	}
	/**
	 * 计算父节点
	 * @param unknown_type $id
	 */
	protected function calcuPath($id) {
		$path = array();
		$sql = 'SELECT parent_id FROM ' . $this->_table .' WHERE id=' . $id . " AND status='" . Model_ProductCate::STATUS_ENABLE . "'";
		$parentId = $this->get_db()->fetchOne($sql);
		if($parentId) {
		    $path[] = $parentId;
		}
		while ($parentId) {
			$sql = 'SELECT parent_id FROM ' . $this->_table .' WHERE id=' . $parentId . " AND status='" . Model_ProductCate::STATUS_ENABLE . "'";
			$parentId = $this->get_db()->fetchOne($sql);
			if($parentId) {
			    $path[] = $parentId;
			}
		}
		return $path;
	}
	
	
	/* 获取分类列表 */
	public function getList($query = "") {
		$condition_string = '1';  // 默认是 where 1
		$queryString = array();
		if(!empty($query['parent_id'])) {
			$str =  $this->get_db()->quoteInto("a.`parent_id` = ?", "{$query['parent_id']}");
			$queryString[] = "({$str})";
		}
		if(!empty($queryString)) {
			$condition_string = implode(" AND ", $queryString);
		}
		$sql = "SELECT a.* FROM `{$this->_table}` a  WHERE {$condition_string} ORDER BY a.`ctime` DESC";
		$data = $this->get_db()->fetchAll($sql);
		return $data;
	}
	/**
	 * 查询类别信息
	 * @param int $id
	 * @return mixed
	 */
	public function getInfoById($id){
		$select = $this->select();
		$select->from($this->_table)->where('id=?', $id);
		return $this->get_db()->fetchRow($select);
	}
	/**
	 * 查询激活所有类别的激活数
	 */
	public function getActiveNumber(){
		$sql = "SELECT EC.id,SUM(tmp.d_sum) FROM  (SELECT COUNT(*) AS d_sum, product_id FROM EZ_DEVICE ED WHERE  ED.STATUS IN('". Model_Device::STATUS_USER_ACTIVATE . "', '". Model_Device::STATUS_USER_ACTIVATE ."') GROUP BY  ED.product_id) AS  tmp JOIN EZ_PRODUCT EP ON tmp.product_id = EP.id 
                  JOIN EZ_PRODUCT_CATEGORY EC ON EC.id = EP.category_id GROUP BY EC.id";
		return $this->get_db()->fetchPairs($sql); 
	}
	

	
}