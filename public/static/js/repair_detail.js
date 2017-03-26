var help_info = {
		'process' : {
			'tips' : '填写处理进度的备注，供用户在APP上查看。',
			'content_title': '处理内容：',
			'data' : ['您的故障请求已经受理，转入人工处理流程。',
						'请将设备按照售后单要求寄回我们的售后地址，我们将对设备进行维修。',
						'机器已经收到，正在为你解决故障。',
						'根据您的问题描述，我们将为您更换新机，请将机器按照售后单要求寄回我们的售后地址。',
						'机器已经寄回，请注意查收，快递单号：',
						'更换的新机已经寄出，请注意查收，快递单号：']
		},
		'finish' : {
			'tips' : '填写已完成的备注信息，供前后端用户查看。',
			'content_title': '完成说明：',
			'data' : ['问题已经解决，祝您生活愉快。',
						'设备维修完成，用户确认接收。',
						'已经更换新机，祝您生活愉快。']
		},
		'closed' : {
			'tips' : '填写不受理的原因，供前后端用户查看。',
			'content_title' : '关闭原因：',
			'data' : ['重复提交的故障单，认定为无效单。',
						'无效的故障描述，如确有设备故障，请重新提交。',
						'长时间未处理，人工关闭，如有疑问请重新提交。'],
		}
		
};
$("#comfirm_dialog").dialog({
    autoOpen: false,
    width: 555,
    dialogClass: "my-dialog",
    modal: true,
    show: {
        effect: "blind",
        duration: 300
    },
    hide: {
        effect: null,
        duration: 500
    },
    buttons: {
        "取消": function() {
            $( this ).dialog( "close" );
        },
        "确定": function() {
        	 var param = $('#deal_form').serialize(); 
			 $.post('/operation/repair/deal', param, function(result){
				    window.location.replace(document.referrer);
			    }, 'json');
        }
    }
});
var repair_detail = {
		repair_id : '',
		init_page : function(){
			this.repair_id = $('#repair_id').val();
			//历史记录查询
			$('#history_tab').click(function(e){
				var target = $(e.target);
				var id = target.attr('id');
				var action = id.split('_')[0];
				var tbody = $('#history_body');
				if(action == 'show') {
					tbody.html('');
					$.get('/operation/repair/history/id/' + repair_detail.repair_id,repair_detail.history_get_back,'json');
				} else if(action == 'hide') {
					tbody.html('');
					target.hide();
					target.siblings().show();
				}
			});
			this.deal_form();
			this.help_event();
		},
		history_get_back : function(data){
			var tbody = $('#history_body');
			if(data.status == 200) {
				tbody.html('');
				for(var m in data.result) {
					var row = data.result[m];
					if(row.id != repair_detail.repair_id) {
						var html = '<tr>' +
                        '<td style="width:150px;"><a href="/operation/repair/detail/id/' + row.id + '" target="_blank">'+ row.number + '</a></td>' +
                        '<td class="inline" title="'+ row.content + '">'+ row.content + '</td>' +
                        '<td style="width:130px;">' + row.ctime + '</td>' +
                        '<td style="text-align:right;width:60px;">'+ repair_detail.show_status(row.status) + '</td>' +
                        '</tr>';
						tbody.append(html);
					}
				}
				if(tbody.html() == '') {
					tbody.append('<tr><td colspan="4" style="text-align:center;">暂无记录</td></tr>');
				}
				var btn = $('#show_content'); 
				btn.siblings().show();
				btn.hide();
			}
		},
		show_status : function(status){
			var return_str = '';
			switch(status){
			    case 'new': {
			    	return_str = '新的';
			    	break;
			    }
			    case 'process': {
			    	return_str = '正在处理';
			    	break;
			    }
			    case 'finish': {
			    	return_str = '已完成';
			    	break;
			    }
			    case 'cancel': {
			    	return_str = '已取消';
			    	break;
			    }
			    case 'closed': {
			    	return_str = '已关闭';
			    	break;
			    }
			}
			return return_str;
		},
		deal_form : function(){
			 $('#deal_form').validate({
					rules: {
						'status': {
							required: true
						},
						'content': {
							required: true,
							maxlength:200
						}
					},
				   submitHandler:function() {
					   var radio = $('.radio_group input').filter(':checked');
					   $('#dialog_status').text(radio.attr('title'));
					    $("#comfirm_dialog").dialog( "open");
					    return false;
				   },
					errorPlacement : function(error, element) {
						var type = element.attr('type');
						if(type == 'radio') {
							error.appendTo(element.parents('.middle').next());
						} else {
							error.appendTo(element.parent().next());		
						}
					}
					
			});	
		},
		help_event : function(){
			var content = $('#content');
			var help_drop = $('#help_drop');
			$('.radio_group input').click(function(){
				var value = $(this).val();
				var msg = help_info[value];
				if(msg) {
					content.attr('placeholder', msg['tips']);
					content.parent().prev().html(msg.content_title);
					help_drop.html('');
					var option = "<option>快速选择常用的备注语句</option>";
					for(var m in msg['data']) {
						var data = msg['data'][m];
						option += '<option>' + data + '</option>';
					}
					help_drop.html(option);
				}
			});
			help_drop.change(function(){
				var value = $(this).val();
				content.val(value);
			});
			var radio = $('.radio_group input');
			if(radio.length >0) {
				radio.get(0).click();
			}
		}
		
};
$(document).ready(function(){
	repair_detail.init_page();	
})