<?php
/**
 * 资讯频道
 * @author zhouxh
 *
 */
class Operation_ChannelController extends ZendX_Controller_Action {
	public $page_size = 20;
	/**
	 * 发现频道模型
	 * @var Model_ChannelContent
	 */
	protected $modelChannelContent;
	
	function init() {
		parent::init();
		$this->modelChannelContent = $this->getModel('Model_ChannelContent');
	}
	
	/**
	 * 资讯列表
	 */
	public function indexAction(){
		$page = $this->_request->get("page");
		$page = !empty($page) ? $page : 1;
		$offset = ($page-1) * $this->page_size;
		$select = $this->modelChannelContent->createSelect();
		$paginator = Zend_Paginator::factory($select);
		$paginator->setItemCountPerPage($this->page_size);
		$paginator->setCurrentPageNumber($page);
		$favNumber = $this->modelChannelContent->findAllFavNumber();
		$this->view->favMap = $favNumber;
		$this->view->pagination = $paginator;
	}
	
	/**
	 * 添加资讯
	 */
	public function addAction(){
		if($this->getRequest()->isPost()) {
			$content = $_POST['content'];
			$_POST['content'] = preg_replace('/\s/', '', $_POST['content']);
			if(!$this->validateData($_POST)) {
				return Common_Protocols::generate_json_response('', Common_Protocols::VALIDATE_FAILED, '数据验证失败');
			}
			if($this->modelChannelContent->addInfo($_POST['title'], $content, $_POST['source'], $_POST['url'], $_POST['image'])) {
				return Common_Protocols::generate_json_response();
			}
			return Common_Protocols::generate_json_response('', Common_Protocols::ERROR);
		}
	}
	
	/**
	 * 编辑资讯
	 * @throws Zend_Exception
	 */
	public function editAction(){
		$id = $this->getRequest()->getParam('id');
		if($this->getRequest()->isPost() && $id) {
			$content = $_POST['content'];
			$_POST['content'] = preg_replace('/\s/', '', $_POST['content']);
			if(!$this->validateData($_POST)) {
				return Common_Protocols::generate_json_response('', Common_Protocols::VALIDATE_FAILED, '数据验证失败');
			}
			if($this->modelChannelContent->editInfo($_POST['title'], $content, $_POST['source'], $_POST['url'], $_POST['image'], $id)) {
				return Common_Protocols::generate_json_response();
			}
			return Common_Protocols::generate_json_response();
		} else {
			if($id) {
				$result = $this->modelChannelContent->getFieldsById('*', $id);
			}
			if(empty($result)) {
				throw new Zend_Exception('页面不存在', 404);
			}
			$this->view->result = $result;
			$this->view->id = $id;
			$this->_helper->viewRenderer->setRender('add');
		}
	}
	
	/**
	 * 验证表单数据
	 * @param array $data
	 * @return boolean
	 */
	protected function validateData($data){
		$post = ZendX_Validate::factory($data)->labels(array(
				'content'=>'内容',
				'image' => '图片',
				'title'=> '标题',
				'source' => '来源站点',
				'url' => '链接'
		));
		$post->rules('title', array(array('not_empty'),array('min_length',array(':value',1)), array('max_length',array(':value',50))));
		$post->rules('image', array(array('max_length',array(':value',255))));
		$post->rules('content', array(array('not_empty'),array('min_length',array(':value',1)),array('max_length',array(':value',240))));
		$post->rules('source', array(array('not_empty'),array('max_length',array(':value',255))));
		$post->rules('url', array(array('url'),array('not_empty'),array('max_length',array(':value',255))));
		return $post->check();
	}
	
	/**
	 * 发布资讯
	 */
	public function publishAction(){
		$id = $this->getRequest()->getParam('id');
		if($id) {
			$data = array('status' => Model_ChannelContent::STATUS_ENABLE);
			if($this->modelChannelContent->update($data, array('id =?' => $id))) {
				return Common_Protocols::generate_json_response();
			}
		}
		return Common_Protocols::generate_json_response('', Common_Protocols::ERROR);
	}
	
	/**
	 * 资讯删除
	 */
	public function delAction(){
		$id = $this->getRequest()->getParam('id');
		if($id) {
			if($this->modelChannelContent->deleteInfo($id)) {
				return Common_Protocols::generate_json_response();
			}
		}
		return Common_Protocols::generate_json_response('', Common_Protocols::ERROR);
	}
	
	
	
}