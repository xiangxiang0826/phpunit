var controller = $('#controller_name').val()?$('#controller_name').val():'usermessage';
var action = $('#action_name').val()?$('#action_name').val():'add';
$(document).ready(function(){
    
	//列表切换
	$('#change_tab').click(function(e){
		var target = $(e.target);
		var id = target.attr('id');
		if(id =='official') {
			 location.href= "/operation/"+controller+"/index";
		} else if(id == 'enterprise') {
			 location.href= "/operation/"+controller+"/enterprise";
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
                	if(typeof products_map.size() != 'undefined') {
                		var products = products_map.keys();
                		var products_name = products_map.values();
                        if(local_click_name){
                            $('input[name="'+local_click_name.replace('[showvalue]', '[value]')+'"]').val(products.join(',')) 
                            $('input[name="'+local_click_name+'"]').val(products_name.join('，')) 
                            var products_hidden = $('input[name="'+local_click_name.replace('[showvalue]', '[value]')+'"]');
                            var json_data = {};
                            for(i=0; i<products_map.size(); i++){
                                json_data[products[i]] = products_name[i];
                            }
                            products_hidden.attr('data-products', JSON.stringify(json_data));
                        }
                	}
                    $( this ).dialog( "close" );
                }
            }
  	});
    
	$( "#app_list_div" ).dialog({
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
                	if(typeof apps_map.size() != 'undefined') {
                		var apps = apps_map.keys();
                		var apps_name = apps_map.values();
                        if(local_click_name){
                            $('input[name="'+local_click_name.replace('[showvalue]', '[value]')+'"]').val(apps.join(',')) 
                            $('input[name="'+local_click_name+'"]').val(apps_name.join('，')) 
                            var apps_hidden = $('input[name="'+local_click_name.replace('[showvalue]', '[value]')+'"]');
                            var json_data = {};
                            for(i=0; i<apps_map.size(); i++){
                                json_data[apps[i]] = apps_name[i];
                            }
                            apps_hidden.attr('data-apps', JSON.stringify(json_data));
                        }
                	}
                    $( this ).dialog( "close" );
                }
            }
  	});

	$("#platform_list_div" ).dialog({
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
                	if(typeof platforms_map.size() != 'undefined') {
                		var platforms = platforms_map.keys();
                		var platforms_name = platforms_map.values();
                        if(local_click_name){
                            $('input[name="'+local_click_name.replace('[showvalue]', '[value]')+'"]').val(platforms.join(',')) 
                            $('input[name="'+local_click_name+'"]').val(platforms_name.join('，')) 
                            var platforms_hidden = $('input[name="'+local_click_name.replace('[showvalue]', '[value]')+'"]');
                            var json_data = {};
                            for(i=0; i<platforms_map.size(); i++){
                                json_data[platforms[i]] = platforms_name[i];
                            }
                            platforms_hidden.attr('data-platforms', JSON.stringify(json_data));
                        }
                	}
                    $( this ).dialog( "close" );
                }
            }
  	});
    
    var changeSel = function(){
        var name = $(this).attr('name');
        // alert('name:'+name);
        var value = $(this).find("option:selected").val();
        // alert('value:'+value);return;
        var showvalue_obj = $('input[name="' + name.replace('[key]', '[showvalue]')+'"]');
        var value_obj = $('input[name="' + name.replace('[key]', '[value]')+'"]');
        var data = new HashMap();
        switch(value){
            case 'product':{
                 // 重新设置所选的值
                 var json_data = value_obj.attr('data-products');
                 if(!json_data){
                     json_data = '[]';
                 }
                 var json_obj = JSON.parse(json_data);
                 for(m in json_obj){
                     data.put(m, json_obj[m]);
                 }
                 showvalue_obj.val(data.values().join(','));
                 value_obj.val(data.keys().join(','));
                 showvalue_obj.attr('placeholder','请点击选择产品');
                 break;
            }
            case 'app_label':{
                 // 重新设置所选的值
                 var json_data = value_obj.attr('data-apps');
                 if(!json_data){
                     json_data = '[]';
                 }
                 var json_obj = JSON.parse(json_data);
                 for(m in json_obj){
                     data.put(m, json_obj[m]);
                 }
                 showvalue_obj.val(data.values().join(','));
                 value_obj.val(data.keys().join(','));
                 showvalue_obj.attr('placeholder','请点击选择APP类型');
                 break;   
            }
            case 'app_platform':{
                 // 重新设置所选的值
                 var json_data = value_obj.attr('data-platforms');
                 if(!json_data){
                     json_data = '[]';
                 }
                 var json_obj = JSON.parse(json_data);
                 for(m in json_obj){
                     data.put(m, json_obj[m]);
                 }
                 showvalue_obj.val(data.values().join(','));
                 value_obj.val(data.keys().join(','));
                 showvalue_obj.attr('placeholder','请点击选择平台类型');
                 break;
            }
            default:{
                showvalue_obj.val('');
                value_obj.val('');
                showvalue_obj.attr('placeholder','请先选择维度');
                break;
            }
        }
    }
    
    
    
    // $(".changeSel").bind('change', changeSel);
    $('[id="assort[push_rule][0][key]"]').bind('change', changeSel);
    
    // 重新初始化选择框
    $(".cang_assort").dropkick();
    
    var showvalue = function(){
        var name = $(this).attr('name');
        var select_name =  name.replace('[showvalue]', '[key]');
        var select_value = $("#message_form").find('select[name="'+select_name+'"]').val();
        switch(select_value){
            case 'product':{
                local_click_name = name;
                var products_hidden = $("#message_form").find('input[name="'+name.replace('[showvalue]', '[value]')+'"]');
                products_map.clear();
                if (products_hidden.val()) {
                    var product_info = products_hidden.attr('data-products');
                    if(product_info){
                        var products = JSON.parse(product_info);
                        for(var m in products) {
                            products_map.put(m, products[m])
                        }
                    }
                }
                $( "#product_list_div").load('/operation/usermessage/products', function(){
                    $( "#product_list_div").dialog('open');	 
                })
                break;
            }
            case 'app_label':{
                local_click_name = name;
                var apps_hidden = $("#message_form").find('input[name="'+name.replace('[showvalue]', '[value]')+'"]');
                apps_map.clear();
                if (apps_hidden.val()) {
                    var app_info = apps_hidden.attr('data-apps');
                    if(app_info){
                        var apps = JSON.parse(app_info);
                        for(var m in apps) {
                            apps_map.put(m, apps[m])
                        }
                    }
                }
                $( "#app_list_div").load('/operation/usermessage/apps', function(){
                    $("#app_list_div").dialog('open');	 
                });
                break;
            }
            case 'app_platform':{
                local_click_name = name;
                var hidden = $("#message_form").find('input[name="'+name.replace('[showvalue]', '[value]')+'"]');
                platforms_map.clear();
                if (hidden.val()) {
                    var info = hidden.attr('data-platforms');
                    if(info){
                        var apps = JSON.parse(info);
                        for(var m in apps) {
                            platforms_map.put(m, apps[m])
                        }
                    }
                }
                $( "#platform_list_div").load('/operation/usermessage/platforms?selectmodel=radio', function(){
                    $("#platform_list_div").dialog('open');	 
                });
                break;
            }
            default:{

            }
        }
    }
        
    $('.showvalue').click(showvalue);
    
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
				$.post('/operation/'+controller+'/check/id/'+$('#message_id').val(), param, function(result){
					if(result.status == 200) {
						$('#message_dialog .message_text').text('保存成功！');
						$( "#message_dialog" ).dialog( "open" );
						$('#close_message').unbind('click');
						$('#close_message').click(function(){
                             return_actioin_name = 'index';
                             if($('#return_action_name').val()){
                                 return_actioin_name = $('#return_action_name').val();
                             }
							 location.href="/operation/"+controller+"/"+return_actioin_name+"/?s="+Math.random();
							//location.href="/operation/"+controller+"/index?s="+Math.random();
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
    
    var check_rules = {};
    switch(controller){
        case 'usermessage':{
            check_rules = {
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
                'message[is_appbox]': {
                    required: true
                },
                'message[original_message][go]': {
                    required: true
                },
                'message[send_type]': {
                    required: true
                },
                'message[is_offline]': {
                    required: true
                }
            }
            break;
        }
        case 'account':{
            check_rules = {
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
				'message[original_message][url]': {
					required: false,
                    url:true
				},
				'message[send_type]': {
					required: true
				}
            }
            break;   
        }
        case 'custom':{
            check_rules = {
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
            }
            break;
        }
        default:{
            break;
        }
    }
    
    //编辑消息
    var message_form =  $('#message_form').validate({
           rules:check_rules,
		   submitHandler:function() {
			   var param = $('#message_form').serialize();
			   var start_time = $('#start_time_input').val();
			   if($("input[name='message[user_type]']:checked").val()=='special'){
                    var num = $('#weidu').find('li').length;
                    for(var i=0; i<num; i++){
                        var obj = $('#weidu').find('li').eq(i);
                        if(obj.find('select:first').val() == '0' || obj.find('input:first').val() == ''){
                            $(obj).after('<label id="weidu_'+i+'_input-error" class="error" for="weidu_'+i+'_input">以上项必须填写</label>');
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
               
               // 检查通知消息的点击行为
               if(controller == 'usermessage'){
                    var go = $("input[name='message[original_message][go]']:checked").val();
                    var passed = true;
                    var error = '请正确填写相应的字段';
                    if(go && go != 'none' &&  go != 'app'){
                        // 检查扩展字段的输入情况
                        if(go == 'activity'){
                            if($("input[name='message[original_message][ex]["+go+"_android]']").val() =='' && $("input[name='message[original_message][ex]["+go+"_ios]']").val() ==''){
                               passed = false;
                            }
                        }else{
                            if(go == 'url'){
                                passed = /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test($("input[name='message[original_message][ex]["+go+"]']").val());
                                error = '请填写正确的URL';
                            }else{
                                 if($("input[name='message[original_message][ex]["+go+"]']").val() ==''){
                                    passed = false;
                                 }
                            }
                        }
                         if(passed == false){
                            $('#span_'+go).append('<label id="span'+go+'-error" class="error" for="span_'+go+'">'+error+'</label>'); 
                            return;
                         }else{
                            $('#span_'+go).find('label').remove();
                         }
                    }
               }
               
			   $.post(location.href, param, function(result){
					if(result.status == 200) {
						$('#message_dialog .message_text').text('保存成功！');
						$( "#message_dialog" ).dialog( "open" );
						$('#close_message').unbind('click');
						$('#close_message').click(function(){
                             return_actioin_name = 'index';
                             if($('#return_action_name').val()){
                                 return_actioin_name = $('#return_action_name').val();
                             }
							 location.href="/operation/"+controller+"/"+return_actioin_name+"/?s="+Math.random();
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
         $( "#product_list_div").load('/operation/'+controller+'/products', function(){
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
              $.post('/operation/'+controller+'/checktestuser', param, function(result){
                   if(result.status == 200) {
                       $('#test_msg_form').hide();
                       var title = $("#title_input").val();
                       var content = $("#remark").val();
                       var linkurl = $("#message_url").val();
                       var type = $("#cang_assort").val();
                       $('#test_message').find('.message_text').text('正在发送...');
                       param_form = $('#message_form').serialize(); 
                       $.post('/operation/'+controller+'/dotesttrans',param_form, function(response){
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
                           }else{
                               if(response.msg){
                                   $('#test_message').find('.message_text').text(response.msg);
                               }else{
                                   $('#test_message').find('.message_text').text('服务器异常，请稍后重试！'); 
                               }
                           }
                       } , 'json');
                   } else if(result.status == 403) {
                       $('#test_message').find('.message_text').text(result.msg);
                   }else{
                       if(result.msg){
                                   $('#test_message').find('.message_text').text(result.msg);
                        }else{
                            $('#test_message').find('.message_text').text('服务器异常，请稍后重试！'); 
                        }
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
                    $.post('/operation/'+controller+'/delete/', {id:msg_id}, function(e) {
                        location.reload();
                        // location.href='/operation/'+controller+'/index?s='+Math.random();
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
        $.post('/operation/'+controller+'/push/', {id:msg_id}, function(e) {
            location.reload();
        },'json');
        return false;
    });

    //处理编辑页面的tab切换
    $('.original_message_go').click(function(){
        var  val = $(this).val();
        $('.original_message_go_item').hide();
        $('#span_'+val).show();
    });
    $('.user_type').click(function(){
        var  val = $(this).val();
        $('.user_type_item').css("visibility", "hidden");
        $('#span_'+val).css("visibility", "visible");
        $('#message_user_type').find('label').remove();
    });
    $('.send_type').click(function(){
        var  val = $(this).val();
        $('.send_type_item').hide();
        $('#span_'+val).show();
        $('#start_time').find('label').remove();
    });

    $('.is_offline').click(function(){
        $('#expire_time').find('label').remove();
    });

    $('#addWeidu').click(function(){
        var num = $('#weidu>li').length;
        if(num >= 5){
            return false;
        }else{
            var html = $('#weiduTemplate').html().replace(/\&lt;/gi, '<').replace(/\&gt;/gi, '>').replace(/\]\[0\]\[/gi, ']['+num+'][');
            $('#weidu').append(html);
            for(var i=0; i<num; i++){
                $('#weidu>li').eq(i).find('.delWeidu').hide();
            }
            // 处理删除的问题
            $('#weidu>li').eq(num).find('.delWeidu').show();
            $('#weidu>li').eq(num).find('.delWeidu').one('click' ,function(){
                var num = $('#weidu>li').length;
                if(num < 2 || /^.*\]\[0\]\[.*$/.test($(this).attr('name'))){
                    return false;
                }else{
                    $(this).parent('div').parent('li').remove();
                    $('#weidu>li').find('.delWeidu').hide();
                    $('#weidu>li').eq(num-2).find('.delWeidu').show();
                }
                $('#weidu>li').eq(0).find('.delWeidu').hide();
            });
            $('.showvalue').click(showvalue);
            for(var i=0; i<num+1; i++){
                $('[id="assort[push_rule]['+i+'][key]"]').bind('change', changeSel);
                //console.log('[id="assort[push_rule]['+i+'][key]"]');
            }
            // 重新初始化选择框
            $(".cang_assort").dropkick();
        }
    });

    $('#addExtend').click(function(){
        var num = $('#extend').find('li').length;
        if(num >= 100){
            return false;
        }else{
            var html = $('#extend').find('li:first').html().replace(/\]\[0\]\[/gi, ']['+num+'][');
            $('#extend').append('<li>'+html+'</li>');
            for(var i=0; i<num; i++){
                $('#extend').find('li').eq(i).find('.delExtend').hide();
            }
            // 处理删除的问题
            $('#extend').find('li').eq(num).find('.delExtend').show();
            $('#extend').find('li').eq(num).find('.delExtend').one('click' ,function(){
                var num = $('#extend').find('li').length;

                if(num < 2 || /^.*\]\[0\]\[.*$/.test($(this).attr('name'))){
                    return false;
                }else{
                    $(this).parent('div').parent('li').remove();
                    $('#extend').find('li').find('.delExtend').hide();
                    $('#extend').find('li').eq(num-2).find('.delExtend').show();
                }
                $('#extend').find('li').eq(0).find('.delExtend').hide();
            });
        }
    });
    
    if(action == 'edit' || action == 'add'){
        // 处理datepicker时间的显示
        var config={
                    showSecond: true,
                    changeMonth: true,
                    changeYear: true,
                    rangeSelect: true,
                    timeFormat: 'HH:mm:ss',
                    yearRange: '-10:+10'
        };
        $("#start_time_input").datetimepicker(config);
    }
    
    // 处理编辑状态默认填充的问题
    if(action == 'edit'){
        // 处理公共部分
        $('.user_type_item').css("visibility", "hidden");
        $("#message_form").find('input[name="message[user_type]"][value="'+user_type+'"]').click();
        $('#span_'+user_type).css("visibility", "visible");
        
        // 处理推送时间
        $("#message_form").find('input[name="message[send_type]"][value="'+send_type+'"]').click();
        $('.send_type_item').hide();
        $('#span_'+send_type).show();
        
        if(start_time){
            $("#start_time_input").datetimepicker('setDate', start_time);
        }
        
        // 处理推送维度的问题
        var count = rules.length;
        if(count != 0){
            $('#weidu>li').remove();
        }
        for(i=0; i<count; i++){
            var html = $('#weiduTemplate').html().replace(/\&lt;/gi, '<').replace(/\&gt;/gi, '>').replace(/\]\[0\]\[/gi, ']['+i+'][');
            $('#weidu').append(html);
        }

        // return;
        for(i=0; i<count; i++){
            var item = rules[i];
            if(typeof item == 'undefined'){
                continue;
            }
            $("#message_form").find('select[name="message[push_rule]['+i+'][key]"] option[value="'+item.key+'"]').prop("selected",true);
            // alert(item.key);return;
            $("#message_form").find('select[name="message[push_rule]['+i+'][condition]"] option[value="'+item.condition+'"]').prop("selected",true);
            $("#message_form").find('input[name="message[push_rule]['+i+'][value]"]').val(item.value);
            $("#message_form").find('input[name="message[push_rule]['+i+'][showvalue]"]').val(item.showvalue);
            
            // 填充对应的data
            var json_data = {};
            var item_value_arr = item.value.split(',');
            var item_showvalue_arr = item.showvalue.split(',');
            var count_arr = Math.min(item_value_arr.length, item_showvalue_arr.length);
            for(j=0; j<count_arr; j++){
                json_data[item_value_arr[j]] = item_showvalue_arr[j];
            }
            var json_string = JSON.stringify(json_data);
            var types = {'product':'products', 'app_label':'apps', 'app_platform':'platforms'};
            var data_name = (typeof types[item.key] != 'undefined')?types[item.key]:'';
            $("#message_form").find('input[name="message[push_rule]['+i+'][value]"]').attr('data-'+data_name, json_string);
            var showvalue_obj = $("#message_form").find('input[name="message[push_rule]['+i+'][showvalue]"]');
            switch(item.key){
                case 'product':{
                     showvalue_obj.attr('placeholder','请点击选择产品');
                     break;
                }
                case 'app_label':{
                     showvalue_obj.attr('placeholder','请点击选择APP类型');
                     break;   
                }
                case 'app_platform':{
                     showvalue_obj.attr('placeholder','请点击选择平台类型');
                     break;
                }
                default:{
                    showvalue_obj.attr('placeholder','请先选择维度');
                    break;
                }
            }
            $('[id="assort[push_rule]['+i+'][key]"]').bind('change', changeSel);
        }
        
        $('.showvalue').click(showvalue);
        // 重新初始化选择框
        $(".cang_assort").dropkick();
        
        // 处理维度删除的问题
        if(count > 1){
            $('#weidu>li').eq(count-1).find('.delWeidu').show();
            for(var i=0; i<count-1; i++){
                $('#weidu>li').eq(i).find('.delWeidu').hide();
            }
        }
        
        // 处理删除的问题
        for(i=0; i<count; i++){
            $('#weidu>li').eq(i).find('.delWeidu').one('click' ,function(){
                var num = $('#weidu>li').length;
                if(num < 2 || /^.*\]\[0\]\[.*$/.test($(this).attr('name'))){
                    return false;
                }else{
                    $(this).parent('div').parent('li').remove();
                    $('#weidu>li').find('.delWeidu').hide();
                    $('#weidu>li').eq(num-2).find('.delWeidu').show();
                }
                $('#weidu>li').eq(0).find('.delWeidu').hide();
            });
        }
        
        // 处理扩展字段的问题
        var count = ex_others.length;
        for(i=1; i<count; i++){
            var html = $('#extend').find('li:first').html().replace(/\]\[0\]\[/gi, ']['+i+'][');
            $('#extend').append('<li>'+html+'</li>');
        }
        for(i=0; i<count; i++){
            var item = ex_others[i];
            $("#message_form").find('input[name="message[original_message][extend]['+i+'][key]"]').val(item.key);
            $("#message_form").find('input[name="message[original_message][extend]['+i+'][value]"]').val(item.value);
        }
        
        // 处理扩展删除的问题
        if(count > 1){
            $('#extend').find('li').eq(count-1).find('.delExtend').show();
            for(var i=0; i<count-1; i++){
                $('#extend').find('li').eq(i).find('.delExtend').hide();
            }
        }
        
        if(controller == 'usermessage'){
            if(go && go != 'app' && go != 'none'){
                if(go == 'activity'){
                    if(ex_arr[go+'_android'] != ''){
                        $("#message_form").find('input[name="message[original_message][ex]['+go+'_android]"]').val(ex_arr[go+'_android']);
                    }
                    if(ex_arr[go+'_ios'] != ''){
                        $("#message_form").find('input[name="message[original_message][ex]['+go+'_ios]"]').val(ex_arr[go+'_ios']);
                    }
                }else{
                    if(ex_arr[go] != ''){
                        $("#message_form").find('input[name="message[original_message][ex]['+go+']"]').val(ex_arr[go]);  
                    }
                }
            }
            $("#message_form").find('input[name="message[original_message][go]"][value="'+go+'"]').click();
            $('.original_message_go_item').hide();
            $('#span_'+go).show();
            
            $("#message_form").find('input[name="message[is_appbox]"][value="'+is_appbox+'"]').click();  
        }
        
        //处理离线时间的判断
        if(controller == 'account'){
            if(url){
                $("#message_form").find('input[name="message[original_message][url]"]').val(url);
            }
        }else{
            $("#message_form").find('input[name="message[is_offline]"][value="'+is_offline+'"]').click();
            if(is_offline == '1'){
                $("#message_form").find('input[name="message[expire_time][value]"]').val(offline_value);
                $("#message_form").find('select[name="message[expire_time][unit]"] option[value="'+offline_unit+'"]').prop("selected",true);
            }
        }
    }
});