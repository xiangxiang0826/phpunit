<?php
/*
 * 反馈管理
*  */
class Operation_FeedbackController extends ZendX_Controller_Action
{
	/**
	 * 反馈列表
	 */
	public function indexAction() {
		$page = $this->getRequest()->getParam('page', 1);
		$search = $this->_request->getParam("search");
		$feedbackModel = new Model_Feedback();
		$select = $feedbackModel->get_db()->select();
		$select->from(array('F' => 'EZ_FEEDBACK'));
		$select->joinLeft(array('T' => 'EZ_FEEDBACK_TYPE'), 'T.id=F.feedback_type_id', array('t_name' => 'name'));
		$select->joinLeft(array('D' => 'EZ_DEVICE'), 'D.id=F.device_id', array('d_id' => 'id'));
		$select->joinLeft(array('P' => 'EZ_PRODUCT'), 'P.id = D.product_id', array('p_name' => 'name'));
		$select->group('F.id');
        $select->order('F.id DESC');
        
		$condition = array();
		if(!empty($search['status'])) {
			$select->where('F.status = ?', $search['status']);
		}
		$feedbackType = new Model_FeedbackType();
		if(!empty($search['feedback_type'])) {
			$categoryIds = $feedbackType->getAllChildrenById($search['feedback_type']);
			if($categoryIds) {
				$categoryIds[] = $search['feedback_type'];
			} else {
				$categoryIds = array($search['feedback_type']);
			}
			$select->where("F.feedback_type_id IN(". implode(',', $categoryIds) . ")");
		}
		if(!empty($search['enterprise_id'])) {
			$select->where('F.enterprise_id = ?', trim($search['enterprise_id']));
		}
		if(!empty($search['feedback_id'])) {
			$select->where('F.id = ?', trim($search['feedback_id']));
		}
		$mobile = trim($search['mobile']);
		$email = trim($search['email']);
		$user_sql = 'SELECT id FROM  EZ_USER WHERE 1 ';
		$sql_param = false;
		if(!empty($mobile)) {
			$user_sql .= $feedbackModel->get_db()->quoteInto(" AND phone LIKE ?", "%{$mobile}%");
			$sql_param = true;
		}
		if(!empty($email)) {
			$user_sql .= $feedbackModel->get_db()->quoteInto(" AND email LIKE ?", "%{$email}%");
			$sql_param = true;
		}
		if($sql_param) {
			$userIds = $feedbackModel->get_db()->fetchCol($user_sql);
			$userIds = $userIds ? $userIds : array(0);
			$select->where("user_id IN(". implode(',', $userIds) . ")");
		}
		if(!empty($search['start']) && !empty($search['end'])) {
			$select->where("(F.ctime >='{$search['start']} 00:00:00' AND F.ctime <= '{$search['end']} 23:59:59')");
		} else if(!empty($search['start'])) {
			$select->where("(F.ctime >='{$search['start']} 00:00:00')");
		} else if(!empty($search['end'])) {
			$select->where("(F.ctime <='{$search['end']} 23:59:59')");
		}
		$productCate = new Model_ProductCate();
		//品类查询
		if(!empty($search['category'])) {
			$categoryIds = $productCate->getAllChildrenById($search['category']);
			if($categoryIds) {
				$categoryIds[] = $search['category'];
			} else {
				$categoryIds = array($search['category']);
			}
			$select->where("P.category_id IN(". implode(',', $categoryIds) . ")");
		}
		$paginator = Zend_Paginator::factory($select);
		$paginator->setItemCountPerPage($this->page_size);// 测试分页 =1
		$paginator->setCurrentPageNumber($page);
		$enterprise = new Model_Enterprise();
		$this->view->paginator = $paginator;
		$this->view->enterprises =  $enterprise->droupDownData();
		$this->view->search = $search;
		$this->view->category = $productCate->dumpTree();
		$this->view->status = $feedbackModel->getStatusMap();
		$this->view->feedbackType = $feedbackType->dumpTree();
	}
	/**
	 * 查看详情
	 */
	public function detailAction(){
		$id = $this->getRequest()->getParam('id');
		if($id) {
			$feedbackType = new Model_FeedbackType();
			$feedbackModel = new Model_Feedback();
			$detailInfo = $feedbackModel->getDetailInfo($id);
			$this->view->feedbackType = $feedbackType->dumpTree();
			$this->view->status = $feedbackModel->getStatusMap();
			$this->view->info = $detailInfo;
		} else {
			throw new Zend_Exception('参数错误');
		}
	}
	/**
	 * 更新反馈状态
	 */
	public function updateAction(){
		$id = $this->getRequest()->getParam('id');
		if($id) {
			$feedbackModel = new Model_Feedback();
			$data['status'] = $_POST['status'];
			$data['feedback_type_id'] = !empty($_POST['category']) ? $_POST['category'] : NULL;
			$data['remark'] = $this->getRequest()->getPost('remark');
			$feedbackModel->update($data, "id =$id");
			return Common_Protocols::generate_json_response();
		} else {
			return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED);
		}
	}
}