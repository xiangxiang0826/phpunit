<?php

/**
 * @author 刘通
 */
return array(
	//--template list
	'module'		=> '模块',
	'template_list'	=> '模版列表',
	'template'		=> '模版',
	'template_name'	=> '模版名称',
	'page_table'	=> '页面表',
	'field_manage'	=> '字段管理',
	'page_list'		=> '页面列表',
	'add_template'	=> '添加模版',
	'edit_template'	=> '编辑模版',
	'has_valid_page'	=> '该模板有有效页面，不允许删除',
    'select_template'    => '选择模板',

	//--edit template
	'template_name_error' => '模版名称不能包含引号',
	'template_name_tip' => '用于在模版列表显示的名称，不能包含引号',
	'template_name_placeholder' => '用于在模版列表显示的名称',
	'template_content' => '模版内容',
	'template_of_module' => '模版所属模块',
	'template_of_module_tip' => '对应的cms系统的模块，用于和模块关联',
	'select_module' => '选择模块',
	'template_type' => '模版类型',
	'template_type_tip'	=> '和模块对应，指定模版是模块先的哪种类型',
	'select_type'		=> '选择类型',
	'is_default'		=> '是否默认',
	'is_default_tip'	=> '比如产品评论模版，选成默认后，添加产品时，自动添加评论页面就添加在该模版',
	'default_dup'		=> '该类型模版已存在默认, [<a href="javascript:;" onclick="$.nav_tab.add(\'编辑模版 - [{name}]\', \'template/template/edit/site_id/{site_id}/tpl_id/{tpl_id}\');">{name}</a>]',
	'host'				=> '对应域名',
	'host_tip'			=> '和主站域名不同时，在这里重新配置，比如：store。一般不用修改',
	'html_path'			=> '文件路径',
	'html_path_tip'		=> '文件路径，如:images.1719.com',
	'html_path_format'	=> '必须：<br /> 字母、数字、圆点、下换线和横线 + /httpdocs/结尾',
	'edit_html_path_error' => '静态文件生成路径，只能由开发人员修改',
	'html_path_dup'		=> '该静态文件生成路径和其它站点的重复',

	//--block list
	'block_list'	=> '块列表',
	'file_path'		=> '文件路径',
	'block'			=> '块',
	'block_name'	=> '块名称',
	'add_block'		=> '添加块',
	'edit_block'	=> '编辑块',

	//--edit block
	'block_content' => '块内容',
	'file_path'		=> '文件路径',
	'file_path_tip'	=> '相对于站点静态文件根目录',
	'file_path_format_error'	=> '格式错误<br />1. 以.html结尾<br />2.其它部分只允许包含英文、数字、\/、_和-',
	'file_path_dup'	=> '文件路径已存在，请修改',
	'blk_html_path_tip'	=> '此处的静态文件存放路径，为服务器上部署的时机路径,如/data/www/vhost/images.1719.com/httpdocs',

	//--page list
	'tpl_id_error' => '模版ID错误，联系开发者修改',
	'html_time'	=> '生成时间',
	'in'		=> '包含',
	'not_in'	=> '不包含',
	'eq'		=> '等于',
	'not_eq'	=> '不等于',
	'gt'		=> '大于',
	'lt'		=> '小于',
	'gt_eq'		=> '大于等于',
	'lt_eq'		=> '小于等于',
	'also'		=> '同时满足',
	'page_list'	=> '页面列表',
	'disabled'	=> '已禁用',
	'page'		=> '页面',
	'in_other_tpl' => '该ID在[{name}]模版中有页面，<a href="javascript:;" onclick="$.template_page.edit({tpl_id}, {page_id}, \'编辑页面\')">查看</a>',

	//--edit page
	'entity_id_tip' => '模版对应实体的ID',
	'page_keyword_tip' => '以逗号分隔开的关键字',
	'without_url_permission' => '你没有修改URL的权限',
	'without_create_directory_permission' => '你没有新建目录的权限',
	'more_char'		=> '还可以输入<span class="more-char"></span>个字符',
	'keyword_rule'	=> '<br />关键字格式：用英文逗号分隔的词语<br />注意：不允许出现中文逗号和换行符',
	'title_url_dup'	=> '标题和URL有重复，点击查看',
	'title_dup'		=> '标题有重复，点击查看',
	'url_dup'		=> 'URL有重复，点击查看',
	'title_dup_page'=> '标题重复页面',
	'url_dup_page'	=> 'URL重复页面',
	'show'			=> '查看',
	'list_page_url_rule'	=> '列表页面的URL必须以 /index.html结尾',
	'keyword_dup_degree'	=> '关键词重复度',
	're_update'		=> '重新查询',
	'keyword_num'	=> '其它<span class="warn">{x}</span>个文档中重复',
	'title_more_than' => '标题超过了{x}个字符',
	'keyword_more_than' => '关键字超过了{x}个字符',
	'url_more_than' => 'URL超过了{x}个字符',
	'desc_more_than' => '描述超过了{x}个字符',
	'is_default_page'	=> '是否默认页面',
	'is_default_page_tip'	=> '注意：一个实体对应多个页面时，选为默认的页面信息会被同步到实体表，请确保一个实体有且仅有一个默认页面',
	'default_page_dup'	=> '默认页面重复，点击查看[<a href="javascript:;" onclick="$.nav_tab.add(\'编辑页面 - [{tpl_id}][{page_id}]\', \'template/page/edit/site_id/{site_id}/tpl_id/{tpl_id}/page_id/{page_id}\');">页面</a>]',
	'submit_confirm' => '确认提交',

	//--field list
	'field'			=> '字段',
	'add_field'		=> '添加字段',
	'edit_field'	=> '编辑字段',
	'field_list'	=> '字段列表',
	'field_name'	=> '字段名称',

	//--edit field
	'input_label'	=> '字段名称',
	'input_name_tip'=> '添加页面界面中显示的名字',
	'input_type'	=> '输入框类型',
	'text_input'	=> '单行输入框',
	'textarea_input'=> '多行输入框',
	'select_input'	=> '下拉选择',
	'fulltext_input'=> '富文本编辑器',
	'input_value'	=> '默认值',
	'input_option'	=> '输入项参数',
	'input_size'	=> '输入框大小',
	'width'			=> '长',
	'height'		=> '高',
	'field_name_db'	=> '表字段名',
	'field_name_db_tip' => '在数据库中表的字段名，由英文字母下划线组成',
	'field_type'	=> '字段类型',
	'field_type_tip'=> '在数据库中的类型',
	'order_edit'	=> '编辑顺序',
	'is_search'		=> '可搜索',
	'is_search_tip'		=> '选择是，可以在页面列表按该字段搜索',
	'is_list'		=> '可显示',
	'is_list_tip'	=> '选择时，可以在页面列表显示',
	'is_unique'		=> '是否唯一',
	'is_unique_tip'	=> '选择是后，该字段会和实体ID共同确定唯一',
	'is_null'		=> '是否可以为空',
	'is_null_tip'	=> '',
	'yes'			=> '是',
	'no'			=> '否',
	'order_edit_tip' => '在添加页面界面的显示顺序',
	'field_name_not_valid' => '数据库字段名不合法',
	'field_name_conflict' => '数据库字段已被占用',
	'field_name_require' => '字段名称是必填项',
	'input_width_number' => '输入框宽度必须是数字',
	'input_height_number' => '输入框高度必须是数字',
	'order_edit_number' => '编辑顺序必须是数字',
	'must_alpha'	=> '必须是数字和下划线',

	/**
	 +----------------------------------------------
	 | 在template.ini中定义的i18n_name
	 +----------------------------------------------
	 */
	'shop'	=> '商城',
	'product'	=> '产品',
	'store'	=> 'Store',
	'other'		=> '其它',
	'lab'		=> '沙盒项目',
	'shop_index' => '首页',
	'list_page'	=> '列表页',
	'list_sub_page'	=> 'sub列表页',
	'tag_page'	=> '标签页',
	'author_page'	=> '作者页',
	'review_list_page' => '评论列表页',
	'content_page'	=> '内容页',
	'switch_page'	=> '切换页',
	'first_category_page'	=> '一级分类页',
	'second_category_page'	=> '二级分类页',
	'overview_page' => 'Overview页',
	'survey_page' => 'Survey页',
	'pro_line'		=> '产品线',
	'sale_index' => '营销首页',
	'faq_refund_list' => 'FAQ退款列表',
	'sale_category_list' => '营销分类列表',
	'pro_comparison_list' => '产品对比列表',
	'pro_help_index' => '产品帮助首页',
	'pro_help_category' => '产品帮助分类',
	'tech_article_list'	=> '技术文章列表',
	'pro_onhelp_list'	=> '产品在线帮助列表',
	'pro_download_list'	=> '产品下载列表',
	'web_map'	=> '网站地图',
	'tech_article'	=> '技术文章',
	'online_article' => '在线帮助文章',
	'software_update_page' => '软件更新页',
	'product_suggest_page' => '产品建议页',
	'product_faq_list_page' => '产品FAQ列表页',
	'product_category_list_page' => '产品分类列表页',
	'product_category_page' => '产品分类页',
	'faq_center_index'    => 'FAQ中心首页',
	'contrast_table'	=> '产品对比表',
	'index' => '首页',
	'store_index' => 'Store首页',
	'store_one_category' => 'Store一级分类',
	'store_two_category' => 'Store二级分类',
	'store_custom_category' => 'Store自定义分类',
	'store_product_buy'     => 'Store产品购买',
	'newsroom_summary_page'       => 'newsroom聚合页',
	'newsroom_article_page'       => 'newsroom文章页',
	'newsroom_listyear_page'       => 'newsroom年份列表',
	
	

	'pro_id'	=> '产品',				//这是在template.ini中配置的entity_id的语言项
	'art_id'	=> '文章',				//我直接这样写可能有点不形象，但是可以省些配置项
	'cat_id'	=> '分类',
	'tag_id'	=> '标签',
	's_art_id'	=> 'Support文章',
	'h_art_id'	=> 'Help文章',
	'id'        => '',
	'lab_id'    => '沙盒项目',
	//template.ini	i18n_name end
);