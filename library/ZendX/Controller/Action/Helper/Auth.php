<?php
/*  
 * 用户权限控制。
 * 实现逻辑：
 * 1、未登录状态下，除了module是login的情况下直接返回外，其他情况都直接跳到login进行登录。
 * 2、后台设置用户的权限都是url的形式，无论是菜单，还是action。用户登录时，根据用户组权限，找出所有权限的url，并根据url的类型是menu Or action，分别赋值给session的perm_menu、perm_action。
 * 3、每次用户访问页面，首先根据module、controller、action组合成url，然后在用户的url里判断，是否存在，不存在则返回。
 * 4、根据当前组合的url，找出对应的菜单id和module_id，选中当前的菜单和module。
 * 
 * 潜规则：
 * 1、所有菜单必须配置到一个module下。
 * 2、所有菜单的url必须独立使用，不能通过参数的传递来区分不同业务。
 * */
class ZendX_Controller_Action_Helper_Auth extends Zend_Controller_Action_Helper_Abstract {
	protected $request = '';
	protected $controller = '';
	protected $moduleName = '';
	protected $controllerName = '';
	protected $actionName = '';
	public function init() { //初始化，获取request,controller等信息
		parent::init();
		$this->request = $this->getRequest();
		$this->controller = $this->getActionController();
		$this->moduleName = $this->request->getModuleName();
        
		$this->controllerName = $this->request->getControllerName();
		$this->actionName = $this->request->getActionName();
	}
	public function preDispatch() {
		$module_model = new Model_Module();
		$default_config = $this->controller->getInvokeArg('bootstrap')->getOption('default');
		$visit_url = "{$this->moduleName}/{$this->controllerName}/{$this->actionName}";
		if($this->filter($visit_url)) return true; //符合需要过滤的地址，则直接过滤掉  
		if($this->moduleName == $default_config['default_module']) { // 是默认的module，则进入界面初始化为配置的module
			$this->moduleName = $default_config['init_module'];
		}  
		$current_module = $module_model->FindByLabel($this->moduleName);
		if(empty($current_module)) {
			$current_module = $module_model->FindByUrl($visit_url);
		}
		$module_id =  $current_module ? $current_module['id'] : $default_config['module_id'];
		$user_info = Common_Auth::getUserInfo(); // 获取用户登录态
		if(empty($user_info)) {
			if($this->moduleName =='login') { //
				return true;
			}
			return $this->controller->getHelper('Redirector')->gotoUrl("/login");
		}
		$modules = $module_model->GetUserModules();
		$group_permission_model = new Model_GroupPermission();
		$menus = $group_permission_model->GetMenuByModuleId($module_id);
		$this->controller->view->current_module = $current_module;
		$this->controller->view->visit_url = "/{$visit_url}";
		$this->controller->view->modules = $modules ? $modules : array();
		$this->controller->view->user_info = $user_info;
		$this->controller->view->menus = Common_Func::arrayToTree($menus);
        // 添加导航菜单相关的代码
        $this->controller->view->menuArray = $menus;
		$this->controller->view->module_id = $module_id;
		/*
		 * 以下代码主要实现 通过登录的用户信息，获取此用户的权限列表，包括可见的菜单、可见的模块、不可见的action
		*/
		if(!Common_Auth::auth($visit_url)) { // 根据请求的uri验证权限
			if($this->request->isXmlHttpRequest()) {
				Common_Protocols::generate_json_response(NULL,NULL,Common_Protocols::USER_VERIFY_FAILED); // ajax请求，则直接返回json
				exit;
			}
			//否则显示无权限提示的页面
			echo $this->controller->view->Render('error_nopermission.phtml');
			exit;
		}
	}
	
	
	/* 针对api的地址过滤 */
	private function filter($url) {
		$filter_url = array('api/upload/index','login/index/logout'); // 需要过滤的地址
		if(in_array($url, $filter_url)) {
			return true;
		}
	}
	
	/* 请求完成后的回调*/
	public function postDispatch() {
		//print $this->moduleName.'==='.$this->controllerName.'====='.$this->actionName;
		$this->triger_hook();
		Cms_Log::log(); // 打印日志
	}
	
	private function triger_hook() { // 根据配置文件执行相应的hooks
		$hooks_config = ZendX_Config::get('hooks','hooks');
		if(!$hooks_config) {
			return false;
		}
		// 构造url
		$visit_url = "{$this->moduleName}/{$this->controllerName}/{$this->actionName}";
		$hook_class = '';
		if(isset($hooks_config['post']) && $this->request->isPost()) {
			if(isset($hooks_config['post'][$visit_url])) {
				$hook_class = $hooks_config['post'][$visit_url];
			}
		}
		if(isset($hooks_config['get']) && $this->request->isGet()) {
			if(isset($hooks_config['get'][$visit_url])) {
				$hook_class = $hooks_config['get'][$visit_url];
			}
		}
		if($hook_class) { //  Chain_Version.update_latest_version ->  $obj =  new Hooks_Chain_Version() -> $obj->update_latest_version();
			$class_ary = explode('.',$hook_class);
			$hook_class = "Hooks_{$class_ary[0]}";
			if(!class_exists($hook_class)) return false;
			$hook_obj = new $hook_class();
			if(!is_callable(array($hook_obj,$class_ary[1]))) return false;
			return $hook_obj->$class_ary[1]($visit_url);
		}
	}
}
