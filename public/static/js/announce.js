$(document).ready(function() {
	$("#message_dialog").dialog({
		autoOpen : false,
		width : 540,
		height : 240,
		show : {
			effect : "blind",
			duration : 300
		},
		hide : {
			effect : "explode",
			duration : 500
		},
		dialogClass : "my_message_dialog",
		modal : true
	});

	$("#product_list_div").dialog({
		autoOpen : false,
		draggable : false,
		width : 940,
		height : 540,
		show : {
			effect : "blind",
			duration : 300
		},
		hide : {
			effect : "explode",
			duration : 500
		},
		dialogClass : "my_message_dialog",
		modal : true,
		buttons : {
			"取消" : function() {
				$(this).dialog("close");
			},
			"确定" : function() {
				if (products_map.size) {
					var products = products_map
							.keys();
					var products_name = products_map
							.values();
					$('#products_hidden').val(
							products.join(','));
					var products_div = $('#products_div');
					products_div
							.text(products_name
									.join('，'));
				}
				$(this).dialog("close");
			}
		}
	});
	
	$('#close_message').click(function() {
		$("#message_dialog").dialog("close");
	});
	$('#cancel_btn').click(function() {
		window.history.go(-1);
	});

	$('#save_btn').click(function() {
		$('#check_form').submit();
	});

	// 接收类型
	$('.receive_type').click(function() {
		var current = $(this);
		var val = current.val();
		var tr_arr = $('#area_list_tr,#products_list_tr');
		tr_arr.hide();
		if (val == 'all') {
			tr_arr.hide();
		} else if (val == 'special') {
			tr_arr.show();
		}
	});

	$('#edit_btn').click(function() {
		if (!$('#announce_form').valid())
			return false;
		$('#announce_form').submit();
	});
	
	// 编辑消息
	$('#announce_form').validate({
		rules : {
			'announce[content]': {
				required: true
			},
			'announce[title]' : {
				required : true,
				minlength : 2,
				maxlength : 32
			}
		},
		ignore : '',
		submitHandler : function() {
			var param = $('#announce_form').serialize();
			var send_time = $('#send_time_input').val();
			// if special chckbox is checked,one
			// or more enterprise should be
			// selected
			if ($("input[name='send_type']:checked").val() == 'special'	&& $("#products_div").text().length <= 5) {
				$("#select_enterprise").after('<label id="send_time_input-error" class="error" for="send_time_input">此项必须填写</label>');
				return;
			}
			$.post(
					location.href,
					param,
					function(result) {
						if (result.status == 200) {
							$('#message_dialog .message_text').text('保存成功！');
							$("#message_dialog").dialog("open");
							$('#close_message').unbind('click');
							$('#close_message').click(function() {
												location.href = "/enterprise/announce/index";
											});

						} else if (result.status == 500) {
							$('#message_dialog .message_text').text('保存失败！');
							$("#message_dialog").dialog("open");
						} else if (result.status == 403) {
							$('#message_dialog .message_text').text(result.msg);
							$("#message_dialog").dialog("open");
						}
					}, 'json');
			return false;
		}
	});	
	
	// 产品选择
	$('#select_enterprise').click(function() {
				$("#product_list_div").load(
						'/enterprise/announce/enterprise',
						function() {
							$("#product_list_div").dialog('open');
						});
			});
	// 全部厂商或者特定厂商的DIV显示或者隐藏
	if ($('.receive_type:checked').val() == 'special') {
		$('#area_list_tr,#products_list_tr').show();
	}
	
	//ckeditor初始化
	var announce_editor;
	 KindEditor.ready(function(K) {
		 announce_editor = K.create('textarea[name="announce[\'content\']"]', {
	         allowFileManager : true,
	         resizeType:1,
	         afterBlur: function(){this.sync();}
	     });
	 });
	 
});