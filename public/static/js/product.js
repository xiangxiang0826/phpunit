$(document).ready(function(){
	$('#change_tab').click(function(e){
		var target = $(e.target);
		var id = target.attr('id');
		if(id =='publish') {
			 location.href= "/product/index/list";
		} else if(id == 'pending') {
			 location.href= "/product/index/pending";
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
    
	/**
	 * 产品列表控制
	 */
	$('.product_ctrl').click(function(e){
		var target = $(e.target);
		var id = target.attr('id');
		if(id) {
			var id_arr = id.split('_');
			var action = id_arr[0];
		}
		switch(action) {
		     case 'view' : {
		    	 location.href= "/product/index/showinfo?id=" + id_arr[1];
		    	 break;
		     }
		     case 'report': {
		    	 
		    	 break;
		     }
		     case 'manager': {
		    	 if(id) {
                     // 设置初始 是否在e族APP发布的单选框为可选
                     window.is_ez_app_reset = true;
		    		 $.get('/product/index/getinfo', {id:id_arr[1]}, function(e){
		    			 $('#manage_form')[0].reset();
		    			 if(e.status == 200) {
		    				 var result = e.result;
		    				 var manger_div =$( "#manager_div");
			    			 manger_div.find('#product_name').text(result.name);
			    			 manger_div.find('#product_enterprise').text(result.e_name);
			    			 manger_div.find('#product_id').val(result.id);
                             
                            // 初始化选择框 add by etong <zhoufeng@wondershare.cn> 2014/08/04 17:20
                            // --start
                            manger_div.find('input[name="is_ez_app"]:first').prop('checked', true);
                            // manger_div.find('input[name="control"]:first').prop('checked', true);
                            // manger_div.find('textarea[name="remark"]').val('');
                            manger_div.find('input[name="control"]:first').click();
                            // --end
                             
			    			 //选择上radio
			    		     manger_div.find('input[name="is_ez_app"]').each(function(){
			    					  if($(this).val() == result.into_ezapp) {
			    						  $(this).prop('checked', 'checked');
			    					  } 
			    			});
			    		    if(result.status =='disable') {
			    		    	// manger_div.find('input[name="control"][value="0"]').prop('checked', 'checked');
                                // 初始化停用的逻辑
                                manger_div.find('input[name="control"][value="0"]').click();
			    		    } else if(result.status =='enable') {
			    		    	manger_div.find('input[name="control"][value="1"]').prop('checked', 'checked');
			    		    }
                            // 默认不选择发送消息 add by etong <zhoufeng@wondershare.cn> 2014/08/04 17:15
                            // --start
                            manger_div.find('input:checkbox[name="message[]"]').prop('checked', false);
                            // --end
			  				 $.get('/enterprise/index/hasselfapp', {id:result.enterprise_id}, function(response){
                                 
                                 
		    					 if(response.status == 200) {
		    						 if(response.result == 'no') {
                                         // 是否在e族APP发布的单选框不可选，默认选中，然后置 是否重置该单选框的标志位 = false;
		    							 // manger_div.find('input[name="is_ez_app"][value="0"]').prop('disabled', true);
                                         manger_div.find('input[name="is_ez_app"][value="1"]').prop('checked', true);
                                         manger_div.find('input[name="is_ez_app"]').prop('disabled', true);
                                         is_ez_app_reset = false;
		    						 }
		    						 $( "#manager_div").dialog( "open" );
		    					 }else{
                                     $('input[name="is_ez_app"]').prop('disabled', true);
                                 }
		    				 }, 'json'); 
		    			 }
		    		 },'json');
		    	 }
		    	 break;
		     }
		     case 'alerView': {
		    	 $.get('/product/index/getinfo', {id:id_arr[1]}, function(e){
	    			 if(e.status == 200) {
	    				 var result = e.result;
	    				 var check_div =$( "#view_check_div");
	    				 if(result.status == 'audit_success') {
	    					 var chek_msg = '审核成功';
	    					 if(result.into_ezapp == '1') {
	    						 var cover_msg = '发布到全部';
	    					 } else {
	    						 var cover_msg = '厂商APP';
	    					 }
	    					 check_div.find('#product_cover').text(cover_msg);
	    					 check_div.find('#product_cover').parent().show();
	    				 } else if(result.status == 'audit_failed') {
	    					 var chek_msg = '审核失败';
	    			     } else if(result.status == 'publish_failed'){
                             var chek_msg = '审核成功';
                         }
	    				 check_div.find('#product_pass').text(chek_msg);
	    				 check_div.find('#product_opinion').text(result.remark);
	    				 $( "#view_check_div").dialog( "open" );
	    			 }
	    		 },'json');
		    	 break;
		     }
		     case 'check': {
		    	 location.href= "/product/index/recheck/id/" + id_arr[1];
		    	 break;
		    	 $.get('/product/index/getinfo', {id:id_arr[1]}, function(e){
	    			 if(e.status == 200) {
	    				 var result = e.result;
	    				 var check_div =$( "#check_div");
	    				 check_div.find('#product_name').text(result.name);
	    				 check_div.find('#product_enterprise').text(result.e_name);
	    				 check_div.find('#product_id').val(result.id);
                        // 默认全选发送消息 add by etong <zhoufeng@wondershare.cn> 2014/08/04 19:47
                        // --start
                        check_div.find('input[name="is_ez_app"]:first').prop('checked', true);
                        // check_div.find('input[name="pass"]:first').prop('checked', true);
                        check_div.find('input:checkbox[name="message[]"]').prop('checked', true);
                        // check_div.find('textarea[name="remark"]').val('');
                        // $('#check_div input[name="pass"][checked="checked"]').click();
                        // --end
	    				 if(result.type == 'edit') {
	    					 check_div.find('#product_check_type').text('修改');	 
	    				 } else {
	    					 check_div.find('#product_check_type').text('新发布');
	    				 }	 
		    			 //选择上radio
	    				 check_div.find('input[name="is_ez_app"]').each(function(){
		    					  if($(this).val() == result.into_ezapp) {
		    						  $(this).prop('checked', 'checked');
		    					  } 
		    			});
	    				 $.get('/enterprise/index/hasselfapp', {id:result.enterprise_id}, function(response){
	    					 if(response.status == 200) {
	    						 if(response.result == 'no') {
	    							 check_div.find('input[name="is_ez_app"][value="0"]').prop('disabled', true);
	    						 }
	    						 $('#check_div input[name="pass"][checked="checked"]').click();
	    						 $( "#check_div").dialog( "open" ); 
	    					 }
	    				 }, 'json');
	    			 }
	    		 },'json');
		    	 break;
		     }
		    case 'republish': {
		    	//重新发布
		    	$.post('/product/index/republish', {id:id_arr[1]}, function(e){
		    		  if(e.status == 200) {
		    			  location.reload();
		    		  } else {
		    			  $('#message_dialog .message_text').text(e.msg);
					      $( "#message_dialog" ).dialog( "open" );
		    		  }
		    	}, 'json');
		    }
		}
		
	});
	$('#manage_form').validate({
		rules: {
			'remark': {
				maxlength:100
			}
		}
	});	
	//管理窗口
	$( "#manager_div" ).dialog({
        autoOpen: false,
        width: 655,
        dialogClass: "my-dialog",
        modal: true,
        show: {
            effect: "blind",
            duration: 300
        },
        modal: true,
        hide: {
            effect: null,
            duration: 500
        },
        buttons: {
            "取消": function() {
                $( this ).dialog( "close" );
            },
            "确定": function() {
                // 去掉disabled的属性，方便能正常提交数据。
                $('#is_ez_app_div').find('input').removeAttr('disabled');
                $('#manage_form').submit();
            }
        }
    });
	//审核窗口
	$('#check_form').validate({
		rules: {
			'remark': {
				maxlength:60
			}
		},
		submitHandler : function(){
			 var param = $('#check_form').serialize(); 
			 
			   $.post('/product/index/check', param, function(result){
					if(result.status == 200) {
                        location.reload();
					} else if(result.status == 500) {
						$('#message_dialog .error').text(result.msg);
					} else if(result.status == 403) {
						$('#message_dialog .error').text('数据提交有误！');
					}
			    }, 'json');
			 return false;
		}
	});	
    $( "#operate_dialog" ).dialog({
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
 
    $("#close_operate").on("click",function() {
        $( "#operate_dialog" ).dialog( "close" );
        location.reload();
    });
	$( "#check_div" ).dialog({
        autoOpen: false,
        width: 655,
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
                // 去掉disabled的属性，方便能正常提交数据。
                $('#is_ez_app_div').find('input').removeAttr('disabled');
                $('#check_form').submit();
            }
        }
    });
	//查看审核
	$( "#view_check_div" ).dialog({
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
            "返回": function() {
                $( this ).dialog( "close" );
            }
        }
    });

    /**
     * 审核
     */
    $('#check_div input[name="pass"]').click(function(){
    	 var val = $(this).val();
    	 var remark_div = $('#check_div').find('#remark');
    	 var is_ez_app = $('#check_div').find('input[name="is_ez_app"]').filter(':checked').val();
    	 var id = $('#check_div').find('#product_id').val();
		 window.location = '/product/index/recheck/id/'+id;
		 return;
    	 var trObj = $('#tr_' + id);
    	 var product_name = trObj.find('td').eq(1).text();
    	 var company_name = trObj.find('td').eq(3).text();
    	 var msg = '亲爱的' + company_name +',您提交的"' + product_name+'"' 
    	 if(val == 1) {
    		 msg += '已通过审核。请登录e族企业云平台查看详情';
    		 //通过
    		 remark_div.val(msg);
    	 } else if(val == 0) {
    		 //不通过
    		 msg += '没有通过审核。请登录e族企业云平台查看详情';
    		 remark_div.val(msg);
    	 }
    });
	
    /**
     * 管理
     */
    $('#manager_div input[name="control"]').click(function(){
    	 var val = $(this).val();
    	 var remark_div = $('#manager_div').find('#remark');
    	 var id = $('#manager_div').find('#product_id').val();
    	 var trObj = $('#tr_' + id);
    	 var product_name = trObj.find('td').eq(1).text();
    	 var msg = '您的产品"' + product_name+'"' 
    	 if(val == 1) {
             if(is_ez_app_reset){
                 $('#is_ez_app_div').find('input').removeAttr('disabled');
             }
    		 
    		 msg += '已被启用，请登录e族企业云平台查看详情';
    		 //启用
    		 remark_div.val(msg);
    	 } else if(val == 0) {
             if(is_ez_app_reset){
                 $('#is_ez_app_div').find('input').prop('disabled', true);
             }
    		 
    		 //停用
    		 msg += '已被停用。请登录e族企业云平台查看详情';
    		 remark_div.val(msg);
    	 }
    });

    $("input[type=radio]").click(function(){
        var tar_width = $(this).attr('data-width');
        var target_id = $('.check_more').attr('data-target-id');
        var tar_logo = $('.check_more').attr('data-logo');
        var link_url = '/product/ezapp/downqcode?width=' + tar_width + '&ez_pro_id=' + target_id + '&logo=' + tar_logo;
        $('.check_more').attr('href',link_url)
    });

    $(".check_code").attr("v", "h");
    function get_pos(el, t, l) {
        el.css({
            "top": t,
            "left": l,
            "display": "block"
        });
    }

    $(document).on("click", function (event) {
        var $qr_pop = $(event.target).closest(".qr_pop"),
            $qr_pop2 = $(".qr_pop"),
            $qr_more = $(event.target).closest(".check_code");
        if ($qr_more.length && $qr_more.attr("v") == "h") {
            $('#show_img').attr('src','');
            var urlcode = $qr_more.attr('data-urlcode');
            var logo = $qr_more.attr('data-this-logo');
            var pro_id = $qr_more.attr('data-proid');
            var img_url = 'http://api.1719.com/intranet/generate_qr_code/?width=138&height=138&content=' + urlcode + '&logo=' + logo;
            var img_url_err = 'http://api.1719.com/intranet/generate_qr_code/?width=138&height=138&content=' + urlcode;
            $('#show_img').attr('src',img_url).attr('onerror',"javascript:this.src='"+ img_url_err +"';");
            $('.check_more').attr('data-content',urlcode).attr('data-logo',logo).attr('data-target-id',pro_id);
            $('input[type=radio]:first').trigger("click");

            var t = $qr_more.offset().top + $qr_more.height() - 78,
                l = $qr_more.offset().left - ($qr_pop2.width() - $qr_more.width()*2 + 10)-200;
            get_pos($qr_pop2, t, l);
            $qr_more.attr("v", "s");
        } else if (!$qr_pop.length) {
            $qr_pop2.css("display", "none");
            $('.check_code').attr("v", "h");
        }
    });
});