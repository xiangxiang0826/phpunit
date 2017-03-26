$(function() {
	$("#close_message").on("click",function() {
        if($('.message_text').text() == '保存成功！'){
            return location.href = $('#back_url').val();
        }
		$( "#message_dialog" ).dialog( "close" );
	});
        
        // 添加自定义校验规则,2-64位数字和字母组合
        jQuery.validator.addMethod("isWord", function(value, element) {
            var isWord = /^[0-9A-Za-z]{2,64}$/;
            return this.optional(element) || (isWord.test(value));
        }, "\u53ea\u80fd\u7531\u6570\u5b57\u6216\u82f1\u6587\u5b57\u6bcd\u7ec4\u6210\uff08\u793a\u4f8b\u003a\u0035\u0030\u0030\u0041\u0042\u0043\uff09");
        // 上述提示为：只能由数字或英文字母组成（示例:500ABC）
        
	$('#form_add_action').validate({
		rules: {
			'app_key': {
				required: true,
                                minlength: 2,
				maxlength:32,
                                isWord:true
			},
			'product_id': {
				required: true,
                                digits:true,
				maxlength:11
			},
			'callback_url': {
				required: true,
				url:true,
                minlength: 10,
                // http://1.cn
                maxlength:255
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
                        $('.message_text').text('保存成功！');
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