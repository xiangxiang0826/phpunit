<?php
/**
 * 新闻管理模块
 * @author xiangxiang
 *
 */
class Website_NewsController extends ZendX_Controller_Action
{
	/* 新闻列表页 */
	public function indexAction() {
		$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$rows = isset($_GET['rows']) ? intval($_GET['rows']) : $this->page_size;
		$offset = ($page-1)*$rows;
		
		$where = ' AND a.status <> \'delete\'';
		$search = isset($_GET['search'])?$_GET['search']:'';
		if(!empty($search['category_id'])) $where .= " AND a.category_id=".$_GET['search']['category_id'];
		if(!empty($search['title'])) $where .= " AND a.title like '%".trim($search['title'])."%'";
		$newsModel = new Model_News();
		$newsCatModel = new Model_NewsCategory();
		$result['list'] = $newsModel->getList($where,$offset,$rows);
		foreach($result['list'] as $k=>$v){
			$result['list'][$k]['banner_info'] = $v['is_banner'] == 1 ? '是' : '否';
		}
		$search['tree'] = $newsCatModel->dumpTree();
		$row = $newsModel->getTotal($where);
		$result["total"] = $row;
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($result["total"]));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($rows);
		
		if(empty($search['category_id'])) $search['category_id'] = 0;
		$this->view->results =  $result;
		$this->view->search = $search;
		$this->view->pagenation = $pagenation;		
	}
	
	/* 新闻编辑页 */
	public function editAction(){
		$newsModel = new Model_News();
		$newsCatModel = new Model_NewsCategory();
		$newsID = $this->getRequest()->getParam('id');
		$settings = ZendX_Config::get('application', 'settings');
		$this->view->static_domain = "http://{$settings['download_domain']}";
		if($newsID){
			$newsID = $this->getRequest()->getParam('id');
			$info = $newsModel->getFieldsById('*', $newsID);
			$info['tree'] = $newsCatModel->dumpTree();
			$this->view->info = $info;
		}
	}
	
	/* 新闻添加页 */
	public function addAction(){
		if($this->getRequest()->isPost()) {
			$data = $_POST['info'];
			$validate = $this->validateMessage($data);
			if($validate !== true) {
				return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED, $validate);
			}
			$data['is_banner'] = isset($data['is_banner']) ? 1: 0;
			$data['is_top'] = isset($data['is_top']) ? 1 : 0;
			$data['author'] = $this->view->user_info['user_name'];
			if(!isset($data['id']))
				$data['ctime'] = date('Y-m-d H:i:s');
			
			$config = ZendX_Config::get('application');
			$newsModel = new Model_News();
			$id = $newsModel->save($data);
			if($id){
				return Common_Protocols::generate_json_response();
			}
			return Common_Protocols::generate_json_response('',406,'数据存在错误');
		}
		$newsCatModel = new Model_NewsCategory();
		$result['tree'] = $newsCatModel->dumpTree();
		$this->view->results =  $result;
	}
	
	/* ajax改变banner */
	public function bannerAction(){
		$newsModel = new Model_News();
		$data['id'] = $_POST['id'];
		$data['is_banner'] = $_POST['banner'] == '0' ? '1' : '0';
		$id = $newsModel->save($data);
		if($id){
			return Common_Protocols::generate_json_response();
		}
		return Common_Protocols::generate_json_response('',406,'数据存在错误');
	}
	
	public function delAction(){
		$id =  $this->getRequest()->getPost('id');
		$newsModel = new Model_News();
		$newsModel->delete($id);
		return Common_Protocols::generate_json_response();
		
	}
    
	/**
     * 类型列表页
     */
	public function categoryAction(){
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$rows = isset($_GET['rows']) ? intval($_GET['rows']) : $this->page_size;
		$offset = ($page-1)*$rows;
		$query = array();
		$result = array();
		$categoryModel = new Model_NewsCategory();
		$row = $categoryModel->getTotal();
        
        $categoryInfo = $categoryModel->getTotalByGroup();
        // print_r($groupInfo);exit;
        $categoryMap = array();
        foreach($categoryInfo as $item){
            $categoryMap[$item['cid']] = $item['total'];
        }
        $result = $categoryModel->getList($offset, $rows);
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($result["total"]));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($rows);
		$this->view->results =  $result;
        $this->view->category_map =  $categoryMap;
		$this->view->pagenation = $pagenation;	
	}
    
	/**
     * 添加用户组类型
     * @return type
     */
	public function addcategoryAction() {
		// post方法提交，则保存数据 
		if($this->getRequest()->isPost()) {
            $name = $this->getRequest()->getPost('name');
            $typeData = array('name' => $name);
			if(!$this->checkTypePost($typeData)) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
			$upgradeModel = new Model_NewsCategory();
            $where = array('name'=>$typeData['name'], 'status' => 'enable');
            $whereWithout = array();
            $cid = $this->getRequest()->get('cid');
            if($cid){
                $whereWithout = array('id' => $cid);
            }
			$row = $upgradeModel->getCategoryRowByField(array('id'), $where, $whereWithout);
			if($row) {
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('news_category_name_exists'));
			}
            $date = date('Y-m-d H:i:s');
            if($cid){
                // print_r($cid);exit;
                
                $typeDataAdd = array(
                    'mtime' => $date
                );
                $typeData = array_merge($typeData, $typeDataAdd);
                $upgradeModel->updateCategory($typeData, array('`id` = "'.$cid.'"'));
                
            }else{
                
               $typeDataAdd = array(
                    'ctime' => $date,
                    'mtime' => $date,
                    'creator' => $this->view->user_info['user_name'] // 获取用户名称并保存
                );
                $typeData = array_merge($typeData, $typeDataAdd);
                $upgradeModel->insertCategory($typeData);
            }
			
			return Common_Protocols::generate_json_response();
		}
        $cid = $typeData = $this->getRequest()->get('cid');
        $info = array(
            'name' => '',
            'layer' => '',
            'sort' => ''
        );
        if($cid){
           $this->view->cid = $cid;
           $upgradeModel = new Model_NewsCategory();
           $info = $upgradeModel->getCategoryRowByField(array('name', 'layer', 'sort'), array('id' => $cid));
        }
        $this->view->info = $info;
	}

	/**
     * 添加用户组类型
     * @return type
     */
	public function getcategoryAction() {
        $cid = $typeData = $this->getRequest()->get('cid');
        if(!$cid) {
            return Common_Protocols::generate_json_response(NULL,Common_Protocols::EXISTS_ERROR, $this->view->getHelper('t')->t('news_category_name_not_exist'));
        }
        $info = array(
            'name' => ''
        );
        $upgradeModel = new Model_NewsCategory();
        $info = $upgradeModel->getCategoryRowByField(array('id','name'), array('id' => $cid));
        return Common_Protocols::generate_json_response($info);
	}
    
	/**
     * 更新权限组状态
     * @return type
     */
	public function updatecategoryAction() {
		// post方法提交，则保存数据 
		if($this->getRequest()->isPost()) {
            $typeId = $this->getRequest()->get('cid');
            if(is_integer($typeId) && $typeId) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
			$upgradeModel = new Model_Group();
			try {
                $upgradeModel->begin_transaction();
                $status = $this->getRequest()->get('status');
                if($status == 'deleted'){
                    //删除之前要判断是否该类型下存在相关的类型
                     $categoryInfo = $upgradeModel->getMapList(0, 10, array('user_group_id = "'.$typeId.'"'));
                     // print_r($categoryInfo);exit;
                     if(isset($categoryInfo['total']) && $categoryInfo['total'] > 0 ){
                         return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('user_group_remove_notice'));
                     }
                }

				$upgradeModel->updateCategory(array('status' => $status), 'id="'.$typeId.'"');
				$upgradeModel->commit();
			} catch(Exception $e) {
				$upgradeModel->rollback();
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::ERROR, $e->getMessage());
			}
			return Common_Protocols::generate_json_response();
		}
        return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
	}
    
	/**
     * 删除新闻分类
     * @return type
     */
	public function deletecategoryAction() {
		// post方法提交，则保存数据 
		if($this->getRequest()->isPost()) {
            $typeId = $this->getRequest()->getPost('cid');
            if(is_integer($typeId) && $typeId) {
				return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
			}
			$upgradeModel = new Model_NewsCategory();
			try {
                $upgradeModel->begin_transaction();
                //删除之前要判断是否该类型下存在相关的类型
                $categoryInfo = $upgradeModel->getMapList(0, 10, array('category_id = "'.$typeId.'"'));
                // print_r($categoryInfo);exit;
                if(isset($categoryInfo['total']) && $categoryInfo['total'] > 0 ){
                    return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('news_category_remove_notice'));
                }
				$upgradeModel->deleteCategory('id="'.$typeId.'"');
				$upgradeModel->commit();
			} catch(Exception $e) {
				$upgradeModel->rollback();
				return Common_Protocols::generate_json_response(NULL,Common_Protocols::ERROR, $e->getMessage());
			}
			return Common_Protocols::generate_json_response();
		}
        return Common_Protocols::generate_json_response(NULL, Common_Protocols::VALIDATE_FAILED, $this->view->getHelper('t')->t('system_busy'));
	}
    

	/* 增加、更新控件类型是输入检测 */
	private function checkTypePost($data) {
		$post = ZendX_Validate::factory($data)->labels(array(
				'name' => 'name'
		));
		$post->rules(
				'name',
				array(
						array('not_empty'),
						array('min_length',array(':value', 2)),
						array('max_length',array(':value', 20))
				)
		);
		return $post->check();
	}
	
	/* 新闻列表添加的后台验证*/
	private function validateMessage($data){
		if(isset($data['is_banner']) && empty($data['list_img'])) return '设置为Banner需要上传列表图';
		$data['content'] = trim($data['content']);
		if(empty($data['content'])) return "新闻内容不能为空";
		if(empty($data['list_img'])) return '需要上传新闻导图';
		$post = ZendX_Validate::factory($data)->labels(array(
				'title' => 'title',
				'content' => 'content',
				'sort' => 'sort'
		));
		$post->rules(
				'title',
				array(
						array('not_empty'),
						array('max_length',array(':value',100))
				)
		);
		$post->rules(
				'content',
				array(
						array('not_empty')
				)
		);
		
		if($post->check()){
			return true;
		}
		return current($post->errors('validate'));
		
	}
	
	
}