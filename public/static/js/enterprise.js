$(document).ready(function(){
	$('#change_tab').click(function(e){
		var target = $(e.target);
		var id = target.attr('id');
		if(id =='publish') {
			 location.href= "/enterprise/index/index";
		} else if(id == 'pending') {
			 location.href= "/enterprise/index/pending";
		}
	});
	
	//审核窗口
	$( "#check_div" ).dialog({
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
            duration: 10
        },
        buttons: {
            "取消": function() {
                $( this ).dialog( "close" );
            },
            "确定": function() {
                $('#check_form').submit();
            }
        }
    });
	$('#check_form').validate({
		rules: {
			'check_opinion': {
				maxlength:500
			},
	'label': {
		isString : true,
		maxlength:500,
		required : true,
		remote : { // 异步验证
			 url: "/enterprise/index/labelexists",
			 type: "post",               //数据发送方式
			 dataType: "json"          //接受数据格式
		}
	}
		},
		submitHandler : function(){
			 var param = $('#check_form').serialize(); 
			   $.post('/enterprise/index/check', param, function(result){
					if(result.status == 200) {
						location.reload();
					} else if(result.status == 500) {
						$('#check_div .error').text('内部错误！');
					} else if(result.status == 403) {
						$('#check_div .error').text('数据提交有误！');
					} 
			    }, 'json');
			 return false;
		},
		messages : {
			label : {
				'remote' : '该企业标识已经存在'
			}
		}
	});	
    
	$( "#manager_div" ).dialog({
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
                $('#manage_form').submit();
            }
        }
    });
	$('#manage_form').validate({
		rules: {
			'remark': {
				maxlength:350
			}
		}
	});	
    //管理
    $( ".manager_detail" ).click(function(e) {
    	 var target = $(e.target);
         var id = target.attr('id').split('_')[1];
         if (id) {
            // 清除表单的记忆
            $( "#manage_form")[0].reset();
            var remark_div = $('#manager_div').find('#remark');
            remark_div.val('');
            // 根据目前的状态来初始化选择界面
             var status_string = target.parent('td').prev('td').html();
             if(status_string.indexOf('冻结') != -1){
                 $('#manager_div input[name="pass"][value="1"]').click();
             }else if(status_string.indexOf("待启用") != -1){
                 $('#manager_div input[name="pass"]').prop('checked', false);
             }else if(status_string.indexOf("启用") != -1){
                 $('#manager_div input[name="pass"]:first').click(); 
             }else{
                 $('#manager_div input[name="pass"]:first').click(); 
             }
         	 $( "#manager_div" ).dialog( "open" );
             
             // $('#manager_div input[name="pass"][checked="checked"]').click();
            
             var manager_div = $('#manager_div');
             manager_div.find('#current_status').text($('#status_' + id).text());
             manager_div.find('#enterprise_id').val(id); 
         }
    });
    //审核
    $('.check_detail').click(function(e){
        var target = $(e.target);
        var id = target.attr('id').split('_')[1];
        if (id) {
            // 清除表单的记忆
            $( "#check_form")[0].reset();
            var check_div = $('#check_div');
            check_div.find('.cpy_name').val('');
            check_div.find('#enterprise_id').val(id);
            check_div.find('input[name="pass"][value="1"]').click();
            $( "#check_div" ).dialog( "open" );
        }
    });
    
  //查看审核不通过信息
	$( "#view_div" ).dialog({
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
            	 $( this ).dialog( "close" );
            }
        }
    });
	//查看审核信息
	//管理
    $( ".view_detail" ).click(function(e) {
    	 var target = $(e.target);
         var id = target.attr('id').split('_')[1];
         if (id) {
        	  $.get('/enterprise/index/nopassinfo',{'id':id}, function(response){
        	    	if(response.status = 200) {
        	    		var result = response.result;
        	    		var current_status_str;
        	    		if(result.status == 'audit_failed') {
        	    			current_status_str = '审核不通过';
        	    		} else {
        	    			current_status_str = '审核不通过';
        	    		}
        	    		$( "#view_div" ).find('#current_status').text(current_status_str);
        	    		$( "#view_div" ).find('#check_opinion').text(result.check_opinion);
        	    		$( "#view_div" ).dialog( "open" );   
        	    	}
        	    },'json');
         }
    });
    /**
     * 启用冻结
     */
    $('#manager_div input[name="pass"]').click(function(){
    	 var val = $(this).val();
    	 var remark_div = $('#manager_div').find('#remark');
    	 if(val == 1) {
    		 //冻结
    		 remark_div.val('您的账号已被冻结，请联系我们了解详细情况。（遥控e族企业云平台）');
    	 } else if(val == 0) {
    		 //启用
    		 remark_div.val('您的账号已启用，请登录企业云平台查看产品数据信息。（遥控e族企业云平台）');
    	 }
    });
    
    /**
     * 审核
     */
    $('#check_div input[name="pass"]').click(function(){
    	 var val = $(this).val();
    	 var remark_div = $('#check_div').find('#remark');
    	 if(val == 1) {
    		 //通过
    		 remark_div.text('您提交的资料已通过审核，请登录企业云平台创建产品。（遥控 e 族企业云平台）');
    	 } else if(val == 0) {
    		 //不通过
    		 remark_div.text('您提交的资料没有通过审核，请联系我们了解详细情况。（遥控 e 族企业云平台）');
    	 }
    });
   
})