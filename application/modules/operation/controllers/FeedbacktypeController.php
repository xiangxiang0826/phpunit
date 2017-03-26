<?php
/**
 * 问题类别管理
 * @author zhouxh
 *
 */
class Operation_FeedbacktypeController extends ZendX_Controller_Action 
{
	/**
	 * 问题列表查询
	 */
	public function indexAction(){
		$category = new Model_FeedbackType();
		$this->view->category = $category->dumpTree();
	}
	
	/**
	 * 异步加载数据
	 */
	public function treedataAction() {
		$category = new Model_FeedbackType();
        $list = $category->getListData();
        $feedback = new Model_Feedback();
        $listNumber = $feedback = $feedback->getAllCountInfo();
        $listAdd = array();
        foreach($listNumber as $item){
            $listAdd[$item['id']] = $item['number'];
        }
        //处理分类包含的问题
        foreach($list as $k => $v){
            $number = 0;
            if(isset($listAdd[$v['id']])){
                $number = $listAdd[$v['id']];
            }
            $list[$k]['number'] = $number;
        }
        $listArr = $this->_getDataTree($list);
        foreach($list as $k => $v){
            $list[$k]['number'] = $this->_getDataCount($listArr, $v['id']);
        }
		return Common_Protocols::generate_json_response($list);
	}
    
    private function _getDataTree($rows, $id='id', $pid = 'parent_id', $child = 'child', $root=0){      
        $tree = array(); // 树  
        if(is_array($rows)){
            $array = array();
            foreach ($rows as $key => $item){
               $array[$item[$id]] = &$rows[$key];
            }
            foreach($rows as $key => $item){
                $parentId = $item[$pid];
                if($root == $parentId){
                    $tree[] = &$rows[$key];
                }else{
                    if(isset($array[$parentId])){
                        $parent = &$array[$parentId];
                        $parent[$child][] = &$rows[$key];
                    }
                }
            }
        }
        return $tree;
   }
   
   /**
    * 查找数据中存在指定id的数目
    * 
    * @param array $rows 待搜索的数组
    * @param type $value 待搜索的值
    * @param type $id 待搜索的id键值
    * @param type $child 待搜索的child键值
    * @return int
    */
   private function _getDataCount($rows, $value = -1, $id = 'id', $child = 'child', $is_skip_all = TRUE) {
        $count = 0;
        if(is_array($rows)){
            if($is_skip_all){
                foreach ($rows as $key => $item){
                    if(isset($item[$id]) && ($item[$id] == $value)){
                        $count += $this->_getDataCount($item, $value, $id , $child, FALSE);
                    } else {
                        $count += $this->_getDataCount($item, $value, $id , $child, TRUE);
                    }
                }
            }else{
                $item = $rows;
                $count += $item['number'];
                if(isset($item[$child]) && is_array($item[$child])){
                    foreach($item[$child] as $k => $v){
                        $count += $this->_getDataCount($v, $value, $id , $child, FALSE);
                    }
                }
            }
        }
        return $count;
    }
	
	/**
	 * 添加类别
	 */
	public function addAction(){
		$post = ZendX_Validate::factory($_POST);
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout()->disableLayout();
		$post->rules(
						'name',
						array(
										array('not_empty'),
										array('max_length', array(':value', 20))
						)
		);
		$post->rules(
						'parent_id',
						array(
										array('not_empty'),
										array('digit')
						)
		);
		$post->rules(
						'description',
						array(
										array('max_length',array(':value',100))
						)
		);
		if($post->check()) {
			$id = $this->_request->getParam('id');
			$model = new Model_FeedbackType();
			if(empty($id)) {
				$model->addCategory($_POST);
				return Common_Protocols::generate_json_response();
			} else {
				$model->editCategory($_POST, $id);
				return Common_Protocols::generate_json_response();
			}
		} else {
			return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED);
		}
	}
	
	/**
	 * 删除类别
	 */
	
	public function delAction(){
		$id =$this->_request->getParam('id');
		$id = intval($id);
		if($id) {
			$product = new Model_FeedbackType();
			$number = $product->categoryHasIssue($id);
			if($number == 0) {
				if($product->delCategoryById($id)) {
					$info = 'del_success';
				}
			} else {
				$info = 'del_failed';
			}
			return Common_Protocols::generate_json_response($info);
		} else {
			return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED);
		}
	}
	/**
	 * 查询类别信息
	 */
	public function infoAction(){
		$id =$this->_request->getParam('id');
		$id = intval($id);
		if($id) {
			$product = new Model_FeedbackType();
			$info = $product->getInfoById($id);
			return Common_Protocols::generate_json_response($info);
		} else {
			return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED);
		}
	}
		
	
}