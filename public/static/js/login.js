
/**
 * 登录
 */
$(function(){
	check_browser();
	 $('#login_form').validate({
			rules: {
				'user_name': {
					required: true,
					minlength : 3,
					maxlength: 32,
				},
				'password': {
					required: true,
					minlength : 6,
                	maxlength : 16
				}
			},
		   submitHandler:function() {
			   var param = $('#login_form').serialize();
			   var tips = $('#login_tips');
			   tips.html('');
			   $.post('/login/index/login', param, function(data){
					if(!data){
						tips.html('服务器没有响应');
					} else if(data.state == 0){
						location.href = '/';
					} else{
						tips.html(data.msg);
					} 
					tips.show();
			    }, 'json');
			    return false;
		   },
		   errorClass : 'login_tips',
		   errorLabelContainer : '#login_tips',
			messages : {
                  'user_name' : {
                	  required : '用户名不能为空',
                	  minlength : '用户名必须为3-32个字符',
                	  maxlength : '用户名必须为3-32个字符'
                  },
                  'password' : {
                	  required : '密码不能为空',
                	  minlength : '密码必须为6-16个字符',
                	  maxlength : '密码必须为6-16个字符'
                  }
			},
			showErrors : function(errorMap, errorList){
				if(errorList.length) {
					this.lastActive = errorList[0];
					$("#login_tips").html(errorList[0].message);
				}
			}
	});	
    $('#submit').click(function(){
 	   $('#login_form').submit();      
    });
    
	
	$('body').keydown(function(e){
		if(e.keyCode == 13){
			if($('.messager-window').size()) {
				
			} else {
				$('#submit').click();
			}
		}
	});
	 var $login_button = $(".login_submit");
     $login_button.on("mousedown", function () {
         $login_button.addClass("login_submit_active");
     }).on("mouseup", function () {
         $login_button.removeClass("login_submit_active");
     })
     
	function check_browser(){
		var _userAgent = navigator.userAgent.toLowerCase();
		if(/msie/.test(_userAgent)) {
			var rs = /msie ([\d]+)\.[\d]*;/.exec(_userAgent);
			if(rs[1] < 8) {
				document.write('<div style="text-align:center; width:100%; color:red;">IE版本不能低于8.0</div>');
				return false;
			}
		}
		
		if(typeof localStorage.setItem != 'function') {
			$('body').prepend('<div style="text-align:center; margin-left:20%; margin-right:20%; color:#F90;">你的浏览器对html5支持有限，可能会影响部分功能，建议用chrome浏览器</div>');
		}
	}
});