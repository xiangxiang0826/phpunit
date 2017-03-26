<?php
/**
 * 产品类别管理
 * @author zhouxh
 *
 */
class Product_CategoryController extends ZendX_Controller_Action
{
	/**
	 * 分类列表
	 */
	public function indexAction() {
		$category = new Model_ProductCate();
		$this->view->category = $category->dumpTree();
	}
	/**
	 * 异步加载数据
	 */
	public function treedataAction(){
		$category = new Model_ProductCate();
		$list = $category->getListData();
		//查询所有类别的激活数目
		$categoryActive = $category->getActiveNumber();
		$model_product = new Model_Product();
		foreach ($list as $key=> $row) {
			//计算子类
			$child = $category->getAllChildrenById($row['id'], $list);
			if($child == false) {
				$child = array($row['id']);
			} else {
				$child[] = $row['id'];
			}
			$totalActive = 0;
			$hasProduct = 0;
			//计算激活数目
			foreach ($child as $id) {
				if(isset($categoryActive[$id])) {
					$totalActive += $categoryActive[$id];
				}
			}
			//计算产品数目
			$hasProductArr = $model_product->queryNumsByCid($child);
			$hasProduct = array_sum($hasProductArr);
			$list[$key]['active_num'] = $totalActive;
			$list[$key]['has_product'] = $hasProduct;
		}
		return Common_Protocols::generate_json_response($list);
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
    					array('max_length', array(':value', 128))
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
    					array('max_length',array(':value',512))
    			)
    	);
    	$post->rules(
    			'sort',
    			array(
    				    array('not_empty'),
    				   array('max_length',array(':value',5)),
    					array('numeric')
    			)
    	);
    	if($post->check()) {
    		$id = $this->_request->getParam('id');
    		$model = new Model_ProductCate();
    		if(empty($id)) {
    			$model->addCategory($_POST);
    		} else {
    			$model->editCategory($_POST, $id);
    		}
    	}
    	header("location:" . $_SERVER['HTTP_REFERER']);
    	exit;
    }

    
    /**
     * 删除类别
     */
    
    public function delAction(){
    	$id =$this->_request->getParam('id');
    	$id = intval($id);
    	if($id) {
    		$product = new Model_ProductCate();
    		$number = $product->categoryHasProduct($id);
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
    		$product = new Model_ProductCate();
    		$info = $product->getInfoById($id);
    		return Common_Protocols::generate_json_response($info);
    	} else {
    		return Common_Protocols::generate_json_response(null, Common_Protocols::VALIDATE_FAILED);
    	}
    }
}