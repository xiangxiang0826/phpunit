$(document).ready(function(){
	//列表切换
	$('#change_tab').click(function(e){
		var target = $(e.target);
		var id = target.attr('id');
		if(id =='official') {
			 location.href= "/operation/custom/index";
		} else if(id == 'enterprise') {
			 location.href= "/operation/custom/enterprise";
		}
	});
	  $( "#message_dialog" ).dialog({
  		  autoOpen: false,
            width: 540,
            height: 240,
            show: {
                effect: "blind",
                duration: 300
            },
            hide: {
                effect: "explode",
                duration: 500
            },
            dialogClass: "my_message_dialog",
            modal: true
  	}); 
	
	  $( "#product_list_div" ).dialog({
  		  autoOpen: false,
            width: 940,
            height: 600,
            show: {
                effect: "blind",
                duration: 300
            },
            hide: {
                effect: "explode",
                duration: 500
            },
			dialogClass: "my_message_dialog",
            modal: true,
            buttons: {
                "取消": function() {
                    $( this ).dialog( "close" );
                },
                "确定": function() {
                	if(products_map.size) {
                		var products = products_map.keys();
                		var products_name = products_map.values();
                		$('#products_hidden').val(products.join(','));
                		var products_div = $('#products_div');
                		products_div.text(products_name.join('，'));
                	}
                	 $( this ).dialog( "close" );
                }
            }
  	});  
	 $('#close_message').click(function() {
			$( "#message_dialog" ).dialog( "close" );
	});
	 $('#cancel_btn').click(function(){
  	   window.history.go(-1); 
      });
     
	 //审核
	 $('#check_form').validate({
			rules: {
				'opinion[pass]': {
					required: true
				},
				'opinion[comments]': {
                    required: false,
                    minlength:2,
					maxlength:50
				}
			},
		   submitHandler:function() {
			   var param = $('#check_form').serialize(); 
			   var pass = $('input[type=radio]').filter(':checked').val();
				$.post('/operation/custom/check/id/'+$('#message_id').val(), param, function(result){
					if(result.status == 200) {
						$('#message_dialog .message_text').text('保存成功！');
						$( "#message_dialog" ).dialog( "open" );
						$('#close_message').unbind('click');
						$('#close_message').click(function(){
							location.href="/operation/custom/index?s="+Math.random();
						});
						
					} else if(result.status == 500) {
						$('#message_dialog .message_text').text('保存失败！');
						$( "#message_dialog" ).dialog( "open" );	
					} else if(result.msg == 505) {
						$('#message_dialog .message_text').text('亲，你没有这个操作的权限！');
						$( "#message_dialog" ).dialog( "open" );	
					}else {
                        if(result.msg){
                            $('#message_dialog .message_text').text(result.msg);
                            $( "#message_dialog" ).dialog( "open" );	
                        }
                    }
			    }, 'json');
			     return false;
		   },
			errorPlacement : function(error, element) {
		    	var id = element.attr('id');
				if (id == 'pass_radio') {
					error.appendTo(element.parent());
				} else {
					element.after(error);
				}		
			}
			
	});	
     $('#save_btn').click(function(){
    	 $('#check_form').submit();      
     });
	
   //接收类型
   $('.receive_type').click(function(){
	   var current = $(this);
	   var val = current.val();
	   var tr_arr = $('#area_list_tr,#products_list_tr');
	   tr_arr.hide();
	   if(val == 'all') {
		   tr_arr.hide();
	   } else if(val == 'special') {
		   tr_arr.show();
	   }
	});
    
    //编辑消息
    var message_form =  $('#message_form').validate({
			rules: {
				'message[original_message][title]': {
					required: true,
                    minlength:2,
					maxlength : 20
				},
				'message[original_message][text]': {
					required: true,
                    minlength:2,
					maxlength : 50
				},
				'message[send_type]': {
					required: true
				},
				'message[is_offline]': {
					required: true
				}
			},
		   submitHandler:function() {
			   var param = $('#message_form').serialize();
			   var start_time = $('#start_time_input').val();
			   if($("input[name='message[user_type]']:checked").val()=='special'){
                    var num = $('#weidu').find('li').length;
                    for(var i=0; i<num; i++){
                        var obj = $('#weidu').find('li').eq(i);
                        if(obj.find('select:first').val() == '0' || obj.find('input:first').val() == ''){
                            $(obj).after('<label id="weidu_'+i+'_input-error" class="error" for="weidu_'+i+'_input">此项必须填写</label>');
                            return;
                        }else{
                            $(obj).find('label').remove();
                        }
                    }
               }
                var num = $('#extend').find('li').length;
                if(num == 1){
                    // 要么都没有，要么都设置了
                    var i = 0;
                    var obj = $('#extend').find('li').eq(i);
                    var key = $("input[name='message[original_message][extend]["+i+"][key]']").val();
                    var value = $("input[name='message[original_message][extend]["+i+"][value]']").val();
                    if((key && !value) || (!key && value)){
                        $(obj).after('<label id="extend_'+i+'_input-error" class="error" for="extend_'+i+'_input">以上项可选择填写，如果需填写请同时填写2项</label>');
                        return;
                    }else{
                        $(obj).find('label').remove();
                    }
                }else{
                    for(var i=0; i<num; i++){
                        var key = $("input[name='message[original_message][extend]["+i+"][key]']").val();
                        var value = $("input[name='message[original_message][extend]["+i+"][value]']").val();
                        var obj = $('#extend').find('li').eq(i);
                        if(!key || !value){
                            $(obj).after('<label id="extend_'+i+'_input-error" class="error" for="extend_'+i+'_input">以上项必须填写</label>');
                            return;
                        }else{
                            $(obj).find('label').remove();
                        }
                    }
                }
               if($("input[name='message[send_type]']:checked").val() == 'regular_time'){
                    if(start_time) {
                        var send_time_smap = Date.parse(start_time.replace(/-/g,"/"));
                        var current_date = new Date();
                        if (send_time_smap < current_date) {
                            $('#message_dialog .message_text').text('计划发送时间必须大于当前时间！');
                            $( "#message_dialog" ).dialog( "open" );
                            return;
                        }
                        $('#start_time').find('label').remove();
                    }else{
                        $('#start_time').append('<label id="start_time-error" class="error" for="start_time">请填写正确的时间</label>');
                        return;
                    }
               }
               if($("input[name='message[is_offline]']:checked").val() == '1'){
                    if($("#expire_time_value").val() == '' || parseInt($("#expire_time_value").val()) == 0  || /^\d+$/.test($("#expire_time_value").val()) == false) {
                        $('#expire_time').append('<label id="expire_time-error" class="error" for="expire_time">请填写正确数字</label>');
                        return;
                    }else{
                        if(parseInt($("#expire_time_value").val()) > 999){
                            $('#expire_time').append('<label id="expire_time-error" class="error" for="expire_time">填写的数字应该在1~999之间</label>');
                            return;
                        }else{
                            $('#expire_time').find('label').remove();
                        }
                    }
               }
			   $.post(location.href, param, function(result){
					if(result.status == 200) {
						$('#message_dialog .message_text').text('保存成功！');
						$( "#message_dialog" ).dialog( "open" );
						$('#close_message').unbind('click');
						$('#close_message').click(function(){
							 location.href="/operation/custom/index?s="+Math.random();
						});
						
					} else if(result.status == 500) {
						$('#message_dialog .message_text').text('保存失败！');
						$( "#message_dialog" ).dialog( "open" );	
					} else if(result.status == 403) {
						$('#message_dialog .message_text').text('验证失败！');
						$( "#message_dialog" ).dialog( "open" );	
					}
			    }, 'json');
			     return false;
		   }	
	});	
	 
	 $('#edit_btn').click(function(){
		 if(!$('#message_form').valid()) return false;
		 $('#message_form').submit();
	 });
   //产品选择
	 $('#select_product').click(function(){
		 $( "#product_list_div").load('/operation/custom/products', function(){
			 $( "#product_list_div").dialog('open');	 
		 })	 
	 })
	 
	 //测试发送
	 $( "#test_message" ).dialog({
  		  autoOpen: false,
            width: 540,
            height: 400,
            show: {
                effect: "blind",
                duration: 300
            },
            hide: {
                effect: "explode",
                duration: 500
            },
            dialogClass: "my_message_dialog",
            modal: true
  	}); 
	 $('#cancle_btn').click(function(){
		 $('#test_message').dialog( "close" );
	 });
	 $('#test_send_btn').click(function(){
		 if(message_form.form()) {
			 $('#test_msg_form').show();
			 $('#test_message').find('.message_text').text('');
			 $('#true_btn').unbind('click');
			 $('#true_btn').click(function(){
			      $('#test_msg_form').submit();
			 });
			 $( "#test_message").dialog('open');	 
		 }
	 });
	 
	 //测试发送消息
	 $('#test_msg_form').validate({
			rules: {
				'user_name': {
					required: true
				},
				'password': {
					required: true
				}
			},
		   submitHandler:function() {
			   var param = $('#test_msg_form').serialize(); 
			   $.post('/operation/custom/checktestuser', param, function(result){
					if(result.status == 200) {
						$('#test_msg_form').hide();
						var title = $("#title_input").val();
						var content = $("#remark").val();
						var linkurl = $("#message_url").val();
						var type = $("#cang_assort").val();
	                    $('#test_message').find('.message_text').text('正在发送...');
                        param_form = $('#message_form').serialize(); 
						$.post('/operation/custom/dotesttrans',param_form, function(response){
							 if(response.status == 200) {
								 $('#test_message').find('.message_text').text('发送成功！请查收确认！');
								 $('#true_btn').unbind('click');
								 $('#true_btn').click(function(){
									 $('#test_message').dialog( "close" );
									 // history.go(-1);
								 })
							 } else if(response.status == 403) {
								 $('#test_message').find('.message_text').text(response.msg);
							 }  else if(response.msg == 505) {
								 $('#test_message').find('.message_text').text('亲，你没有这个操作的权限！');
								}
						} , 'json');
					} else if(result.status == 403) {
						$('#test_message').find('.message_text').text(result.msg);
					}
			    }, 'json');
			     return false;
		   }	
	});	
	
	    var msg_id = 0;
		$( "#confirm_dialog" ).dialog({
            autoOpen: false,
            width: 555,
            dialogClass: "my-dialog",
            modal: true,
            show: {
                effect: "blind",
                duration: 300
            },
            hide: {
                effect: "explode",
                duration: 500
            },
            buttons: {
                "取消": function() {
                    $( this ).dialog( "close" );
                },
                "确定": function() {
                	if(msg_id) {
	                	$.post('/operation/custom/delete/', {id:msg_id}, function(e) {
                            location.reload();
                            // location.href="/operation/custom/index?s="+Math.random();
	        			},'json');
                    }
                }
            }
        });
		$('.del_msg').click(function(e){
			var target = $(e.target);
			var id = target.attr('id').split('_')[1];
			msg_id = id;
			$( "#confirm_dialog" ).dialog('open');
		});
	 
		$('.push_msg').click(function(e){
			var target = $(e.target);
			var id = target.attr('id').split('_')[2];
			msg_id = id;
            $.post('/operation/custom/push/', {id:msg_id}, function(e) {
                location.reload();
            },'json');
            return false;
		});
});