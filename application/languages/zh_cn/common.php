<?php
return array(
	'site_name' => '遥控e族OSS后台',	
	'ez' => '遥控e族',
	'help' => '帮助',
	'logout' => '退出',
	//--user
	'user_name'	=> '用户名',
	'real_name'	=> '真实姓名',
	'new_password'	=> '新密码',
	'curr_password'	=> '当前密码',
	'last_login_time'	=> '上次登录时间',
	
	//--user save
	'username_pwd_error'=>'用户名或密码错误',
	'password_wrong'		=> '密码错误',
	'pwd_edited_relogin'	=> '密码已修改，需要重新登录',
	'username_error'=>'用户名错误',
	'password_error'=>'密码错误',
	'group_error'=>'用户组错误',
	'status_error'=>'您的账户已被锁定，请联系管理员',
	'login_sucess'=>'恭喜，登录成功',
    '500_error' =>'服务器内部错误',
    'enterprise_manager' => '厂商管理',
    'enterprise_list' => '厂商列表',
	'status_error'=>'您的账户已被锁定，请联系管理员',
	'login_sucess'=>'恭喜，登录成功',
	'zh-cn' => '中文简体',
	'zh-tw' => '中文繁体',
    'en-us' => '英语',
    'de' => '德语',
	'ja' => '日语',
	'ar' => '阿拉伯语',
	'add_ok'	=> '添加成功',		//添加记录时
	'add_fail'	=> '添加失败',
	'edit_ok'	=> '修改成功',		//修改记录时
	'edit_fail' => '修改失败',
	'action_ok' => '操作成功',		//添加和删除
	'delete_selected' => '删除选中',
	'delete_ok'	=> '删除成功',		//删除记录时
	'delete_faile'	=> '删除失败',
	'create_selected' => '生成选中',	//生成时
	'creating'	=> '正在生成...',
	'create_ok'	=> '生成成功',
	'operate_ok' => '操作成功',
	
	//模块列表
	'add_time'	=> '添加时间',
	'edit_time'	=> '编辑时间',
	'action_time' => '操作时间',
	'editor'	=> '编辑者',
	'actor'		=> '操作者',
	'search'	=> '搜索',
	'add'		=> '添加',
	'sort'		=> '排序',
	'state'		=> '状态',
	'action'	=> '操作',
	'add_page'	=> '添加页面',			//由此向下三项在公共语言中已包含
	'edit_page'	=> '编辑页面',
	'show_page'	=> '查看页面',
	'page_state'=> '页面状态',
	'create'	=> '生成',
	'created'	=> '已生成',
	'not_created' => '未生成',
	'modified'	=> '已修改',
	'new'			=> '未添加',
	'added'			=> '已添加',
	'name'		=> '名称',
	'edit'		=> '编辑',
	'preview'	=> '预览',
	'create'	=> '生成',
	'delete'	=> '删除',
	'disable'	=> '禁用',
	'able'		=> '启用',
	'open' 		=> '开',
	'close' 	=> '关',
	'delete_confirm' => '确认删除',
	'no_item'	=> '没有选择记录',
	'save'		=> '保存',
	'submit'	=> '提交',
	'yes'       => '是',
	'no'        => '否',
	'seo_info'	=> 'SEO信息',
	'base_information'	=> '基本信息',
	'deleted'	=> '已删除',
	
	//--
	'without_permission'	=> '没有权限',
	'has_existed'	=> '记录已存在',
	'name_dup'		=> '该名称已被占用，请重新填入',
	'memcached_connect_error' => 'Memecached服务器连接错误，请联系技术',
	
	//--
	'tip'		=> '提示',			//弹出对话框的提示符
	
	//--login
	'relogin'	=> '你已经退出系统，请重新登录',
	
	//validate
	'require' => '是必填项',
	'number' => '必须是数字',
	'invalid_format' => '格式错误',
	'maxlen' => '最多{x}个字符',
	'minlen' => '最少{x}个字符',
	'max' => '不能大于{x}',
	'min' => '不能小于{x}',
	
	//check tag
	'check_tag_prompt' =>  '检查HTML标签错误,以下行数仅供参考,放在DW里面行数可能正确一点',
	'the_line_tag_error_1' => '<p class="tag_error_prompt">第{line}行,标签{tag}没有关闭</p>',
	'the_line_tag_error_2' => '<p class="tag_error_prompt">第{line}行,标签{tag}关闭有错</p>',
	
	//Copyscape
	'response_format_error' => '响应类型错误',
	'response_failure' => '请求失败',
	
	//添加页面时的错误信息，因为自动生成时，各个模块都可能用到，所以放到全局中
	//但是手动添加页面的语言项还是在template模块下的语言包中
	'title_url_dup'	=> '[{name}] 添加失败<br />因为title或url有重复，请修改自动生成规则',
	'title_dup'		=> '[{name}] 添加失败<br />因为title有重复，请修改自动生成规则',
	'url_dup'		=> '[{name}] 添加失败<br />因为url有重复，请修改自动生成规则',
	'list_page_url_rule' => '[{name}]模版<br />URL，必须以/index.html结尾，请修改自动生成规则',
	'e_1719'=>'遥控e族',
	'div_page' => '页',
);