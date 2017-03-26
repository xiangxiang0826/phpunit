/*******************************************************************************
* KindEditor - WYSIWYG HTML Editor for Internet
* Copyright (C) 2006-2011 kindsoft.net
*
* @author Roddy <luolonghao@gmail.com>
* @site http://www.kindsoft.net/
* @licence http://www.kindsoft.net/license.php
*******************************************************************************/

KindEditor.plugin('template', function(K) {
	var self = this, name = 'template', lang = self.lang(name + '.');
	self.clickToolbar(name, function() {
		if(typeof site_id == 'undefined') {
			alert('site_id undefined');
			return false;
		}
		
		var lang = self.lang(name + '.'),
			html = ['<div style="padding:10px 20px;">',
				'<div class="ke-header">',
				// left start
				'<div class="ke-left">',
				lang. selectTemplate + ' <select id="tplSelect">',
				'</select></div>',
				// right start
				'<div class="ke-right">',
				'<input type="checkbox" id="keReplaceFlag" name="replaceFlag" value="1" /> <label for="keReplaceFlag">' + lang.replaceContent + '</label>',
				'</div>',
				'<div class="ke-clearfix"></div>',
				'</div>',
				'<iframe class="ke-textarea" frameborder="0" style="width:458px;height:260px;background-color:#FFF;"></iframe>',
				'</div>'].join('');
		var dialog = self.createDialog({
			name : name,
			width : 500,
			title : self.lang(name),
			body : html,
			yesBtn : {
				name : self.lang('yes'),
				click : function(e) {
					var doc = K.iframeDoc(iframe);
					self[checkbox[0].checked ? 'html' : 'insertHtml'](doc.body.innerHTML).hideDialog().focus();
				}
			}
		});
		
		var selectBox = K('select#tplSelect', dialog.div),
			checkbox = K('[name="replaceFlag"]', dialog.div),
			iframe = K('iframe', dialog.div);
		//获取模版列表，填充下拉框和iframe
		K.ajax('public/editor/tpllist/site_id/' + site_id, function(data){
			var tmp = [];
			K.each(data, function(i, elem){
				tmp.push('<option value="' + elem['id'] + '">' + elem['name'] + '</option>');
			});
			selectBox.html(tmp.join(''));
			iframe.attr('src', 'public/editor/tplcontent/site_id/' + site_id + '/?id=' + selectBox.val());
		});
		
		selectBox.change(function() {
			iframe.attr('src', 'public/editor/tplcontent/site_id/' + site_id + '/?id=' + selectBox.val());
		});
	});
});

/****************************************************************************
 * 把原插件的读写死的模版的功能，改成读我们可以管理的自定义的模版的功能
 *
 * @修改者		刘通
 * @修改时间	2013-09-28
 ***************************************************************************/