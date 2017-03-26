$(function() {
	var idstr = "";
	selInit(selInitDefault);

	$("#close_message").on("click",function() {
        if($('.message_text').text() == '保存成功！'){
            return location.href = $('#back_url').val();
        }
		$( "#message_dialog" ).dialog( "close" );
	});

	$('#form_add_action').validate({
		rules: {
			'name': {
				required: true,
				minlength: 2,
				maxlength:16
			},
			'uri': {
				required: true,
				minlength: 2,
				maxlength:128
			},
			'status': {
				required: true
			}
		},
		messages: {   //自定义提示信息
			
		}
	});

	$('#save_action').click(function () {
		if($('#form_add_action').valid()) {
			$('#form_add_action').ajaxSubmit({
				url:$('#form_add_action').attr('action'), //提交给哪个执行
				type:'POST',
				dataType: 'json',
				success: function (result) {
					if(result.status == 200) {
						$( "#message_dialog" ).dialog( "open" );
					} else {
						$('.message_text').text(result.msg);
						$( "#message_dialog" ).dialog( "open" );
					}
				}
			});
			return false;
		}
	});	
});