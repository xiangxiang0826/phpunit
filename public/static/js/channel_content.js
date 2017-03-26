var channel_content = {
		delete_id : null,
		init_page : function(){
			channel_content.bind_manager_event();
		},
		bind_manager_event : function(){
			$('#add_content').click(function(){
				location.href = '/operation/channel/add';
			})
			$('.news_link').click(function(e){
				var target = $(e.target);
				if(!target.is('a')) {
					return false;
				}
				var info = target.attr('id').split('_');
				switch(info[0]) {
				     case 'del': {
				    	 channel_content.delete_id = info[1];
				    	 $("#delete_dialog").dialog("open");
				    	 break;
				     }
				     case 'edit': {
				    	 location.href = '/operation/channel/edit/id/' + info[1];
				    	 break;
				     }
				     case 'publish': {
				    	 $.post('/operation/channel/publish', {id:info[1]}, function(res){
				    		 if(res.status == 200) {
				    			 location.reload(true);
				    		 }
				    	 },'json');
				    	 break;
				     }
				     
				}
			});
		}
		
};
$("#delete_dialog").dialog({
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
        	 $.post('/operation/channel/del', {id:channel_content.delete_id}, function(res){
	    		 if(res.status == 200) {
	    			 location.reload(true);
	    		 }
	    	 },'json');
        }
    }
});
channel_content.init_page();