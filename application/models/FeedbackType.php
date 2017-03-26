<?php
/**
 * 设备管理
 * @author zhouxh
 *
 */
class Model_FeedbackType extends ZendX_Model_Base
{
	protected $_schema = 'cloud';
	protected $_table = 'EZ_FEEDBACK_TYPE';

	/**
	 * 树显示类别
	 */
	public function dumpTree() {
		$sql = "SELECT id,name,parent_id FROM `{$this->_table}`  ORDER BY ctime DESC";
		$category = $this->get_db()->fetchAll($sql);
		$treeArr = ZendX_Array::array_to_tree($category, 'id', 'parent_id');
		return  ZendX_Array::dumpArrayTree($treeArr);
	}

	/**
	 * 查询列表
	 */
	public function getListData(){
		$sql = "SELECT * FROM `{$this->_table}`  ORDER BY ctime DESC";
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
			$sql = "SELECT id,name,parent_id FROM  $this->_table";
			$arr = $this->get_db()->fetchAll($sql);
		}
		$subTree = ZendX_Array::array_to_tree($arr, 'id', 'parent_id', 'childrens', $departId, true);
		if (!isset($subTree['childrens'])) {
			return false;
		} else {
			$children = array();
			Model_ProductCate::getHasChild($subTree['childrens'], $children);
			return $children;
		}
	}
	/**
	 * 添加类别
	 * @param unknown_type $data
	 * @return boolean|number
	 */
	public function addCategory($data){
		if(empty($data)) {
			return false;
		}
		$data['mtime'] = date('Y-m-d H:i:s', time());
		$res = $this->get_db()->insert($this->_table, $data);
		return $res;
	}
	/**
	 * 更新类别
	 * @param array $data
	 * @param int $id
	 */
	public function editCategory($data, $id) {
		$data['mtime'] = date('Y-m-d H:i:s', time());
		$where = $this->get_db()->quoteInto('id = ?', $id);
		return $this->update($data, $where);
	}
	/**
	 * 删除类别
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
        return $this->get_db()->delete($this->_table, $where);
	}
	/**
	 * 查询分类下是否有问题
	 * @param int $id
	 * @param int 返回数目
	 */
	public function categoryHasIssue($id, $list = array()) {
		$allCategory = $this->getAllChildrenById($id, $list);
		if($allCategory == false) {
			$allCategory = array($id);
		} else {
			$allCategory[] = $id;
		}
		$sql = 'SELECT COUNT(*) AS number FROM EZ_FEEDBACK WHERE feedback_type_id IN('. implode(',', $allCategory) . ")";
		return $this->get_db()->fetchOne($sql);
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

	/* 统计各分类ID的反馈总数 */
	public function CountForGroup() {
		$sql = "SELECT a.feedback_type_id ,COUNT(a.id) AS counts  FROM EZ_FEEDBACK a INNER JOIN  EZ_FEEDBACK_TYPE b ON a.feedback_type_id  = b.id GROUP BY a.feedback_type_id ";
		$data =  $this->get_db()->fetchPairs($sql);
		return $data ? $data : array();
	}
}