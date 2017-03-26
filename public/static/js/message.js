$(document).ready(function(){
	//列表切换
	$('#change_tab').click(function(e){
		var target = $(e.target);
		var id = target.attr('id');
		if(id =='publish') {
			 location.href= "/operation/message/index";
		} else if(id == 'pending') {
			 location.href= "/operation/message/pending";
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
                effect: null,
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
                effect: null,
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
					maxlength:100
				}
			},
		   submitHandler:function() {
			   var param = $('#check_form').serialize(); 
			   var send_time = $('#send_time').text();
			   var current_date = new Date();
			   var pass = $('input[type=radio]').filter(':checked').val();
			   if(send_time && pass ==1) {
				   var send_time_smap = Date.parse(send_time.replace(/-/g,"/"));
				   if (send_time_smap < current_date && send_time_smap > Date.parse('2000/01/01 23:59:59')) {
					   $('#message_dialog .message_text').text('发送时间设置错误,请设置为不通过！');
					   $( "#message_dialog" ).dialog( "open" );
					   return;
				   }
			   }
				$.post('/operation/message/check/id/'+$('#message_id').val(), param, function(result){
					if(result.status == 200) {
						$('#message_dialog .message_text').text('保存成功！');
						$( "#message_dialog" ).dialog( "open" );
						$('#close_message').unbind('click');
						$('#close_message').click(function(){
							location.href="/operation/message/pending";
						});
						
					} else if(result.status == 500) {
						$('#message_dialog .message_text').text('保存失败！');
						$( "#message_dialog" ).dialog( "open" );	
					} else if(result.msg == 505) {
						$('#message_dialog .message_text').text('亲，你没有这个操作的权限！');
						$( "#message_dialog" ).dialog( "open" );	
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
				'message[title]': {
					required: true,
					maxlength : 20
				},
				'message[type]': {
					required: true
				},
				'message[content]': {
					required: true,
					maxlength : 100
				},
				'message[url]': {
					url: true
				},
				'message[receive_type]': {
					required: true
				},
				'message[send_time]': {
					required: true
				}
			},
		   submitHandler:function() {
			   var param = $('#message_form').serialize(); 
			   var send_time = $('#send_time_input').val();
			   if($("input[name='message[receive_type]']:checked").val()=='special' && $("#products_hidden").val().length==0 ){
                   $("#select_product").after('<label id="send_time_input-error" class="error" for="send_time_input">此项必须填写</label>');
                   return;
               }
			   if(send_time) {
				   var send_time_smap = Date.parse(send_time.replace(/-/g,"/"));
				   var current_date = new Date();
				   if (send_time_smap < current_date) {
					   $('#message_dialog .message_text').text('计划发送时间必须大于当前时间！');
					   $( "#message_dialog" ).dialog( "open" );
					   return;
				   }
			   }
			   $.post(location.href, param, function(result){
					if(result.status == 200) {
						$('#message_dialog .message_text').text('保存成功！');
						$( "#message_dialog" ).dialog( "open" );
						$('#close_message').unbind('click');
						$('#close_message').click(function(){
							 window.history.go(-1); 
						});
						
					} else if(result.status == 500) {
						$('#message_dialog .message_text').text('保存失败！');
						$( "#message_dialog" ).dialog( "open" );	
					} else if(result.status == 403) {
						$('#message_dialog .message_text').text('验证失败！');
						$( "#message_dialog" ).dialog( "open" );	
					} else if(result.msg == 505) {
						$('#message_dialog .message_text').text('亲，你没有这个操作的权限！');
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
		 $( "#product_list_div").load('/operation/message/products', function(){
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
                effect: null,
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
			 })
			 $( "#test_message").dialog('open');	 
		 } 
	 })
	 
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
			   $.post('/operation/message/checktestuser', param, function(result){
					if(result.status == 200) {
						$('#test_msg_form').hide();
						var title = $("#title_input").val();
						var content = $("#remark").val();
						var linkurl = $("#message_url").val();
						var type = $("#cang_assort").val();
	                    $('#test_message').find('.message_text').text('正在发送...');
						$.post('/operation/message/dotesttrans',{'title': title, 'content':content, 'linkurl':linkurl, 'type': type}, function(response){
							 if(response.status == 200) {
								 $('#test_message').find('.message_text').text('发送成功！请查收确认！');
								 $('#true_btn').unbind('click');
								 $('#true_btn').click(function(){
									 $('#test_message').dialog( "close" );
									 history.go(-1);
								 })
							 } else if(response.status == 403) {
								 $('#test_message').find('.message_text').text(response.msg);
							 } else if(result.msg == 505) {
									$('#message_dialog .message_text').text('亲，你没有这个操作的权限！');
									$( "#message_dialog" ).dialog( "open" );	
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
                effect: null,
                duration: 500
            },
            buttons: {
                "取消": function() {
                    $( this ).dialog( "close" );
                },
                "确定": function() {
                	if(msg_id) {
	                	$.post('/operation/message/delete/', {id:msg_id}, function(e) {
	                        location.reload();
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
	 
});