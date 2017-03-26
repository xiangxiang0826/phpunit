// JavaScript Document
//该页导航激活
(function($){
	 $.fn.navActive=function(options){
			  var defaultVal={
				  oA:$(".nav ul li a"),//导航元素
				  active:"active"//激活样式
			  };
			  var obj=$.extend(defaultVal,options);
			  return this.each(function(){
				  //遍历导航a链接，如果当前a链接的href名，被包含在当期页的url中，那么则让改a链接添加激活样式
				  $.each(obj.oA,function(){
					        var url=window.location.pathname;
							var hrefName=lastName($(this)[0]);
							if(url.indexOf(hrefName)>0){
							   var $parent=$(this).parents(".vertical_nav");
							   $parent.find("a").removeClass(obj.active)
							   $(this).addClass(obj.active)
							}
				  });
				  function lastName(obj){
						   var oHref=obj.href;
						   var oIndex=oHref.lastIndexOf("/")+1;
						   var hrefLast=oHref.substring(oIndex);
						   return hrefLast;
				  }
			  });  
	 }
})(jQuery);

// 添加处理菜单定位的方法
jQuery.extend({
      // navLocation
      navLocation:function (name) {
          //alert(name);
          jQuery("a[href='"+name+"']").each(function(){
              if($(this).parent('li').parent('ul').hasClass('vertical_nav_child')){
                  $(this).addClass('current');
              }
          });
      }
});

//执行部分
$(function(){
   //侧导航伸缩
   (function(){
		 function Tree(obj,oUl,expanded,collapsed){
				  this.obj=obj;	
				  this.oUl=oUl;
				  this.expanded=expanded;
				  this.collapsed=collapsed;
		 };
		 Tree.prototype={
				  init:function(){
					  var _this=this;
					  this.obj.on("click",function(){
						  var $oUl=$(this).siblings(this.oUl)
						  if($oUl.is(":visible")){
							  _this.toExpand($(this));
							  $oUl.slideUp("fast");
						  }else{
							  _this.toCollapse($(this));
							  $oUl.slideDown("fast");
						  };
					  })
				  },
				  toExpand:function(opt){
					  opt.attr("class",this.expanded);
				  },
				  toCollapse:function(opt){
					  opt.attr("class",this.collapsed);
				  }
		 };
		 var $obj=$(".vertical_nav>li>span");
		 var verticalTree=new Tree($obj,"ul","expanded","collapse");
		 verticalTree.init();
   })();
   /*
   //表格隔行变色和其他规则颜色
   (function(){
         $(".table_s tbody tr:odd").css("background","#eef0f2");
		 $(".table_s tbody tr").each(function(){
		    $this=$(this)
			$this.find("td:first").css("min-width","96px");
		 });
		 $(".table_s tbody tr").each(function(){
		    $this=$(this)
			//$this.find("td:last").css("padding","0 10px");
		 });
   })();
   */
   //鼠标悬停按钮，相应图标转动
   (function(){
		 function Rotate(obj,icon,xdeg){
				this.oParent=$(obj);
				this.oSon=this.oParent.find(icon);
				this.xdeg=xdeg;
		 };
		 Rotate.prototype={
				init:function(){
					 var _this=this;
					 this.oParent.hover(function(){
						 _this.cssTransformToXdeg();
						 _this.cssTransition();
					 },function(){
						 _this.cssTransformToZero();
						 _this.cssTransition();
					 });   
				},
				cssTransformToXdeg:function(){
				   this.oSon.css("transform","rotate("+this.xdeg+"deg)");
				},
				cssTransformToZero:function(){
				   this.oSon.css("transform","rotate(0deg)");
				},
				cssTransition:function(){
				   this.oSon.css("transition","all 520ms ease 0s");
				}
			 
		 }
		 var add_icon=new Rotate("a.add_btn",".plus_icon",90);
		 var search_icon=new Rotate("a.search_btn",".find_icon",90);
		 add_icon.init();
		 search_icon.init();
   })();
   
   //做导航高度
   function adjustH(){
      var winH=$(window).height();
	  $("#aside-menu").height(winH)
   };
   //adjustH();
   $(window).resize(function(){
        //adjustH();
   })
   /**
    * 查询
    */
   $('#search_submit').click(function(){
		$('#search_form').submit();
	});
    
    function menuLocation(){
        var check_url = window.location.href;
        var li = $('.vertical_nav_child a[href="'+ real_uri +'"]');
        $.get(check_url, function(response) {
            if(response.status = 200 && typeof(response.result) != 'undefined') {
                li.html(li.text()+'(<font color=red>'+response.result+'</font>)');
                if(typeof(callback) == 'function') {
                	callback(response);
                }
            }
         },'json');
    }  
	
    //左侧导航添加数字标识的问题
    //需要审核的数量
   bindNotice('产品', '/product/index/list', '/product/index/needchecknum', function (response) {
	   var li = $('#pending');
   	   if(response.result > 0) {
   		   li.html('待审核(<font>' + response.result +'</font>)');
   		   li.find('font').css('color', 'red');	
   	   } 
	   
   });
   bindNotice('运营', '/operation/message/index', '/operation/message/needcheck', function (response) {
	   var li = $('#pending');
	   if(response.result > 0) {
		   li.html('待审核(<font style="color:red;">' + response.result +'</font>)');
   	       li.find('font').css('color', 'red');	
   	   } 
   });
   bindNotice('运营', '/operation/usermessage/index', '/operation/usermessage/needcheck', function (response) {
	   var li = $('#pending');
	   if(response.result > 0) {
		   li.html('待审核(<font style="color:red;">' + response.result +'</font>)');   
	   }
   });
   bindNotice('运营', '/operation/account/index', '/operation/account/needcheck', function (response) {
	   var li = $('#pending');
	   li.html('待审核(<font style="color:red;">' + response.result +'</font>)');
   });
   bindNotice('运营', '/operation/custom/index', '/operation/custom/needcheck', function (response) {
	   var li = $('#pending');
	   if(response.result > 0) {
		   li.html('待审核(<font style="color:red;">' + response.result +'</font>)');   
	   }
   });
   bindNotice('厂商', '/enterprise/index/index', '/enterprise/index/needcheck',function (response) {
	   var li = $('#pending');
	   if(response.result > 0) {
		   li.html('待处理(<font style="color:red;">' + response.result +'</font>)');
   	       li.find('font').css('color', 'red');	
   	   }
   });
   bindNotice('厂商', '/enterprise/announce/index', '/enterprise/announce/needchecknum');  
   bindNotice('运营', '/operation/onlinefeedback/index', '/operation/onlinefeedback/needchecknum');
   bindNotice('运营', '/operation/clientfeedback/index', '/operation/clientfeedback/needchecknum');
   function bindNotice(module, real_uri, check_url, callback){
        if($('#nav .active').text() != module){
            return;
        }
        var li = $('.vertical_nav_child a[href="'+ real_uri +'"]');
        $.get(check_url, function(response) {
            if(response.status = 200 && typeof(response.result) != 'undefined') {
            	if(response.result > 0) {
            		var color = 'red';
            		li.html(li.text()+'(<font color='+ color +'>'+response.result+'</font>)');
            	} else {
            		li.html(li.text());
            	}
                if(typeof(callback) == 'function') {
                	callback(response);
                }
            }
         },'json');
    }  
});
/**
 * 计算两日期时间差
 * @param   interval 计算类型：D是按照天、H是按照小时、M是按照分钟、S是按照秒、T是按照毫秒
 * @param  date1 起始日期  格式为年月格式 为2012-06-20
 * @param  date2 结束日期
 * @return 
 */
function countTimeLength(interval, date1, date2) {
    var objInterval = {'D' : 1000 * 60 * 60 * 24, 'H' : 1000 * 60 * 60, 'M' : 1000 * 60, 'S' : 1000, 'T' : 1};
    interval = interval.toUpperCase();
    var dt1 = Date.parse(date1.replace(/-/g, "/"));
    var dt2 = Date.parse(date2.replace(/-/g, "/"));
    try{
        return ((dt2 - dt1) / objInterval[interval]).toFixed(2);//保留两位小数点
    }catch (e){
        return e.message;
    }
}

/**
 * 加载脚本基础方法
 * @private
 * @param {string} filepath 路径
 * @param {function} callback 回调方法
 * @return {undefined} undefined 
 */
function loadScript(url, callback){
	var head = document.getElementsByTagName("head")[0];
	var script = document.createElement("script");			
	script.type = "text/javascript";
	script.charset = 'utf-8';
	script.src = url;
	var timeout = setTimeout(
		function (){
			head.removeChild(script);
			callback.call(this,false);	
		},
		30000
	);
	
	onload(
		script,
		function(Ins){
			head.removeChild(script);
			clearTimeout(timeout);
			callback(true);
		}
	);
	head.appendChild(script);
	return true;
}

/**
 * 加载脚本完成后的处理
 * @private
 * @param {dom} node script DOM
 * @param {function} callback 回调方法
 * @return {undefined} undefined 
 */
function onload(node, callback){		
	var isImpOnLoad = ('onload' in node) ? true :
		(function(){
			node.setAttribute('onload','');
			return typeof node.onload == 'function' ; 
		})();

	if(document.addEventListener){
		node.addEventListener('load', function(){
			callback.call(this,node);
		}, false);	
	}
	else if (!isImpOnLoad){
		node.attachEvent ('onreadystatechange', function(){
			var rs = node.readyState.toLowerCase();
			if (rs === 'loaded' || rs === 'complete') {
				node.detachEvent('onreadystatechange');
				callback.call(this,node.innerHTML);
			}
		});
	}
	else{
		//maybe someother browser
	}
}

/**
 * js截取字符串，中英文都能用
 * @param str：需要截取的字符串
 * @param len: 需要截取的长度
 */
function cutstr(str,len)
{
   var str_length = 0;
   var str_len = 0;
   str_cut = new String();
   str_len = str.length;
   for(var i = 0;i<str_len;i++)
   {
        a = str.charAt(i);
        str_length++;
        if(escape(a).length > 4)
        {
         //中文字符的长度经编码之后大于4
         str_length++;
         }
         str_cut = str_cut.concat(a);
         if(str_length>=len)
         {
         str_cut = str_cut.concat("...");
         return str_cut;
         }
    }
    //如果给定字符串小于指定长度，则返回源字符串；
    if(str_length<len){
     return  str;
    }
}