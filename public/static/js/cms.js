/**
 * cms用到的jquery扩展等公用js
 * 
 * @author 刘通
 */
(function($){
	
	/**
	 * 解决jQuery 1.9移除$.browse的问题
	 */
	var _userAgent = navigator.userAgent.toLowerCase();
	
	$.browser = {
			mozilla : /firefox/.test(_userAgent),
			firefox : /firefox/.test(_userAgent),
			webkit	: /webkit/.test(_userAgent),
			opera	: /opera/.test(_userAgent),
			msie	: /msie/.test(_userAgent),
			chrome	: /chrome/.test(_userAgent),
			safari	: /safari/.test(_userAgent)
	};
	
    /**
	 * 遮罩层
	 */
	$.overlay = {
		dialog : null,
		open : function(options){
			var opts = $.extend({
				id : 'create-overlay-dialog',
				width : '200px',
				height : '50px',
				content : 'Working...'
			}, options || {});
			
			$.overlay.dialog = $('<div id="' + opts.id + '"></div>');
			
			$.overlay.dialog.dialog({
				noheader : true,
				width : opts.width,
				height : opts.height,
				modal : true,
				content : '<div style="margin:10px;">' + opts.content + '</div>' 
			});
		},
		
		set_content : function(content){
			this.dialog.dialog({content:content});
		},
		
		close : function(){
			if($.overlay.dialog !== null){
				$.overlay.dialog.dialog('destroy');
			}
		}
	};
	
	/**
	 * 打开对话框
	 */
	$.dialog = {
		dialog : null,
		open : function(options){
			var opts = $.extend({
				title : 'Dialog',
				width : '500px',
				heidht : '400px',
				noheader : false,
				content : 'Loading...',
				resizable : false,
				onClose : null
			}, options || {}), obj = this;
			
			this.dialog = $('<div></div>');
			
			this.dialog.dialog({
				title : opts.title,
				width : opts.width,
				height: opts.height,
				noheader : opts.noheader,
				modal : true,
				content : '<div style="margin:10px;">' + opts.content + '</div>',
				buttons : opts.buttons || null,
				resizable : opts.resizable,
				onClose : function(){obj.close(opts.onClose);},
				onMove : function(left, top) {
					var _height = $(window).height() - parseInt(opts.height);
					
					if(_height > 0 && top > _height) {
						obj.dialog.dialog('move', {top : _height});
					}
					
					var _width = $(window).width() - parseInt(opts.width);
					
					if(_width > 0 && left > _width) {
						obj.dialog.dialog('move', {left : _width});
					}
					
					if(top < 0) {
						obj.dialog.dialog('move', {top : 0});
					}
					
					if(left < 0) {
						obj.dialog.dialog('move', {left : 0});
					}
				}
			});
		},
		
		set_content : function(content){
			this.dialog.dialog({content:content});
		},
		
		close : function(onclose){
			if(this.dialog !== null){
				this.dialog.dialog('destroy');
			}
			
			if(typeof onclose === 'function'){
				onclose();
			}
		}
	};
	
	/**
	 * 封装datagrid
	 */
	$.datagrid = {
		getSelectItems : function(field, datagrid){
			datagrid || (datagrid = $datagrid);
			var selections = datagrid.datagrid('getSelections'),
				ret=[];
			if(selections.length == 0){
				return false;
			}
			
			for(var i in selections){
				ret.push(selections[i][field]);
			}
			
			return ret.join(',');
		}
	}
	
	/**
	 * 打开新窗口
	 */
	$.newwin = {
		open : function (url, target, options){
			var opts = $.extend({
				width		: screen.width - 10,
				height		: screen.height - 100,
				toolbar		: 'no',
				menubar		: 'no',
				scrollbars	: 'yes',
				resizable	: 'yes',
				location	: 'no',
				status		: 'no',
				alwaysRaised: 'yes'
			}, options || {});
			
			var param = [];
			$.each(opts, function(i,n){
				param.push(i + '=' + n);
			});
			param = param.join(',');
			
			window.open(url,target,param);
		}
	};
	
	/**
	 * 添加导航标签
	 */
	$.nav_tab = {
		add : function(title, url, args){
			if(typeof args === 'object'){
				var sep = '?';
				
				if(url.indexOf(sep) > 0) {			//url中包含?，追加时就用&
					sep = '&';
				}
				
				url += sep;
				url += this.asm_url(args);
			}
			location.href = url;
		},
		asm_url : function(args){
			var rt = [];
			for(var i in args){
				rt.push(i + '=' + encodeURIComponent(args[i]));
			}
			return rt.join('&');
		},
		parent_frame : function() {
			if(typeof parent_frame != 'undefined') {
				return top.frames[parent_frame];
			}
			return null;
		},
		reload_parent : function(fn) {				//刷新父标签的grid
			if($.browser.firefox) {
				return false;
			}
			
			if($.browser.msie) {
				return false;
			}
			
			if(typeof fn == 'function') {
				fn();
			} else {
				var parent_frame = this.parent_frame();
				if(parent_frame) {
					if(typeof parent_frame.$datagrid != 'undefined') {
						parent_frame.$datagrid.datagrid('reload');
					} else if(typeof parent_frame.$treegrid != 'undefined') {
						parent_frame.$treegrid.treegrid('reload');
					}
				}
			}
		}
	};
	
	/**
	 * 提交按钮的封装
	 */
	$.form_submit = function(options) {
		var opts = $.extend({
			form	: 'form',
			button	: 'submitButton',
			check	: null,
			success	: null,
			error	: null,
			pk		: 'id'			//编辑成功后修改的pk值
		}, options || {});
		
		$('#' + opts.button).click(function(){
			/**
			 * 验证表单
			 */
			var pass=true, msg='', obj=null;
			$('#' + opts['form'] + ' :input').each(function(idx, elem){
				var $obj = $(elem), data = $obj.data('validate'), cfg, 
					require=true, condition=true, field_label;
				if(data) {
					cfg = (new Function('return ' + data))();
					for(var i in cfg){				//先查看是否有必填项，默认都是必填项，如果有格式验证但是又不是必填项可以添加requre:[false]
						if(i == 'require') {
							require = cfg[i][0];
						}
					}
					
					if(!require) {					//非必填项如果为空则退出验证
						if($.trim($obj.val()) == '') return true;
					}
					
					for(var i in cfg){
						switch(i){
						case 'require':
							condition = $.trim($obj.val());
							break;
						case 'number':
							condition = /^[\d]+$/.test($obj.val());
							break;
						case 'min':
							condition = cfg[i][0] <= parseInt($obj.val());
							break;
						case 'max':
							condition = cfg[i][0] >= parseInt($obj.val()); 
							break;
						case 'minlen':
							condition = cfg[i][0] <= $obj.val().length;
							break;
						case 'maxlen':
							condition = cfg[i][0] >= $obj.val().length;
							break;
						case 'url':
							condition = /^http:\/\//i.test($obj.val());
							break;
						case 'regexp':
							condition = cfg[i][0].test($obj.val());
							break;
						}
						
						if(!condition){
							field_label = $('td:first', $(this).closest('tr')).text();
							msg = $.trim(field_label) + cfg[i][1];
							pass = false;
							obj = $obj;
							return false;
						}
					}
				}
			});
			
			if(!pass) {
				$.messager.alert('Tip', msg, 'error', function(){
					obj.focus();
				});
				return false;
			}
			if(typeof opts.check == 'function'){
				var rs = opts.check();
				if(rs === false) {
					return false;
				}
			}
			
			var post_data = $('#' + opts.form).serialize(),
				self = this;
			
			$(this).attr('disabled', 'disabled');
			$.ajax({
				type	: 'POST',
				url		: opts.url,
				data	: post_data,
				dataType: 'json',
				success: function(data) {
					if(data.state == 'ok') {
						$('#' + opts.pk).val(data.id);
					}
					
					if(typeof opts.success == 'function') {
						opts.success(data);
					} else {
						$.messager.alert('Tip', data.msg, data.state);
					}
					
					$(self).removeAttr('disabled');
				},
				error: function() {
					if(typeof opts.error == 'function') {
						opts.error();
					}
					$(self).removeAttr('disabled');
				}
			});
		});
	}
	
	/**
	 * 打开编辑模版页面
	 */
	$.template_page = {
		url : '/template/page/edit',
		add : function(tpl_id, entity_id, title, /*附加的信息*/opts) {
			var param = [];
			if(tpl_id < 1) {
				return false;
			}
			
			if(opts) {
				for(var i in opts) {
					param.push('data[' + i + ']=' + opts[i]); 
				}
				param = '?' + param.join('&');
			} else {
				param = '';
			}
			
			//先判断有无添加
			$.get('ajax/common/exists/' + param, {tpl_id:tpl_id, entity_id:entity_id}, function(data){
				if(data.state == 'error') {
					$.messager.alert('Tip', data.msg, 'warning', function(){
						$.template_page.edit(data.tpl_id, data.page_id, data.title);
					});
				} else {
					$.nav_tab.add(title + ' - [' + tpl_id + ']', $.template_page.url + param, {site_id:site_id, tpl_id:tpl_id, entity_id:entity_id});
				}
			}, 'json');
		},
		edit : function(tpl_id, page_id, title) {
			if(tpl_id < 1) {
				return false;
			}
			
			$.nav_tab.add(title + ' - [' + tpl_id + '][' + page_id + ']', this.url, {site_id:site_id, tpl_id:tpl_id, page_id:page_id});
		}
	};

	/**************************生成*****************************/
	
	$.create_html = {
		start_time : undefined,
		template : function(site_id, tpl_id){						//生成一个模版下所有的页面
			var url = '/template/create/template/site_id/' + site_id,
				opts = {tpl_id:tpl_id};
			this.begin();
			this.create(url, opts);
		},
		page : function(site_id, tpl_id, page_id){					//生成一个页面
			var url = '/template/create/page/site_id/' + site_id,
				opts = {tpl_id:tpl_id, page_id:page_id};
			this.begin();
			this.create(url, opts);
		},
		multi_page : function(site_id, tpl_id, page_ids){			//生成一个模版下的多个页面
			var url = '/template/create/multipage/site_id/' + site_id,
				opts = {tpl_id:tpl_id, page_ids:page_ids};
			this.begin();
			this.create(url, opts);
		},
		multi_tpl_page : function (site_id, datagrid){				//在模块列表中，批量选择生成时
			var url = '/template/create/page/site_id/' + site_id,
				selections = datagrid.datagrid('getSelections'),
				ret=[], page_infos=[];
			if(selections.length == 0){
				return false;
			}
			
			for(var i in selections){
				if(parseInt(selections[i]['tpl_id']) > 0 
					&& parseInt(selections[i]['page_id']) > 0){
					page_infos.push({tpl_id:selections[i]['tpl_id'], page_id:selections[i]['page_id']});
				}
			}
			
			if(page_infos.length == 0){
				return false;
			}
			
			this.begin();
			
			(function create(i){
				$.ajax({
					url		: url,
					data	: page_infos[i],
					dataType: 'json',
					success	: function(data){
						if(++i < page_infos.length){
							$.overlay.set_content('<span class="create-state"><span class="create-done">' + i + '</span>/' + page_infos.length + '</span>');
							create(i);
						} else {
							$.messager.alert('Tip', $.create_html.end() + ' Seconds');
							$.overlay.close();
						}
					},
					error	: function() {
						$.messager.alert('Tip', '返回数据错误，可能是模版标签有误', 'error');
						$.overlay.close();
						return false;
					}
				});
			})(0);
			
		},
		create : function(url, opts, start){
			opts['start'] = (start || (start = 0));
			
			$.ajax({
				url		: url,
				data	: opts,
				dataType: 'json',
				success : function(data) {
					if(data.state == 'ok' && data.done < data.total) {
						$.overlay.set_content('<span class="create-state"><span class="create-done">' + data.done + '</span>/' + data.total + '</span>');
						$.create_html.create(url, opts, data.done);
					} else {
						$.overlay.close();
						if(data.state == 'ok'){
							data.msg = $.create_html.end() + ' Seconds';
						}
						$.messager.alert('Tip', data.msg, data.state);
					}
				},
				error	: function() {
					$.messager.alert('Tip', '返回数据错误，可能是模版标签有误', 'error');
					$.overlay.close();
					return false;
				}
			});
		},
		begin : function(){
			$.overlay.open({width:150});
			this.start_time = new Date();
		},
		end : function(){
			var end_time = new Date();
			end_time = end_time.getTime() - this.start_time.getTime();
			
			if(typeof $datagrid != 'undefined') {
				$datagrid.datagrid('reload');
			} else if(typeof $treegrid != 'undefined') {
				$treegrid.treegrid('reload');
			}
			
			return end_time / 1000;
		}
	}
	// 增加数组支持indexOf，解决ie下报错  modify by Liu jinde
	if(!Array.indexOf)
	{
	    Array.prototype.indexOf = function(obj)
	    {               
	        for(var i=0; i<this.length; i++)
	        {
	            if(this[i]==obj)
	            {
	                return i;
	            }
	        }
	        return -1;
	    }
	}
})(jQuery);