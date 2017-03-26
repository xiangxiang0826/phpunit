var idstr = "";
$(function () {
	selInit(selInitDefault);
	$('#save').click(function () {
        if($('#form_template').valid()) {
            idstr = "";
            $('input:checkbox[name="ids[]"]').each(function(){ 
                if($(this).prop("checked")){
					if($(this).val()){
						idstr += $(this).val()+","
					}
                }
            });
            $.post($('#form_template').attr('action'),{id:$('#id').val(), ids:idstr, name:$('#name').val(), label:$('#label').val()},function (result) {
                if(result.status == 200) {
                    $('.message_text').text('保存成功！');
                    return $( "#message_dialog" ).dialog( "open" );
                }
                if(result.status == 406) {
                    $('.message_text').text(result.msg);
                    return $( "#message_dialog" ).dialog( "open" );
                }
                $('.message_text').text('服务器忙，请重试！');
                return $( "#message_dialog" ).dialog( "open" );
            },'json');
        }
	});
	
	$("#close_message").on("click", function() {
        if($('.message_text').text() == '保存成功！'){
            location.href = $('#back_url').val();
            return;
        }
        $( "#message_dialog" ).dialog( "close" );
    });
	
    $('#form_template').validate({
    		rules: {
    			'name': {
    				maxlength:32,
    				minlength:2,
    				required: true,
    			},
    			'label': {
    				required: true,
                    minlength: 2,
    				maxlength:16
    			}
    		},
    		messages: {
    		}
    });	
});