var selInit = function(arr, onload){
	var len = arr.length;
    if(onload == 1){
        $('input:checkbox[name="ids[]"]').prop('checked', false);
        if(len > 0){
            for(var i=0; i<len; i++){
                $('input:checkbox[name="ids[]"][value='+arr[i]+']').prop('checked', true);;
            }
        }
    }else{
        $('#auth_ids').find('input:checkbox[name="ids[]"]').prop('checked', false);
        if(len > 0){
            for(var i=0; i<len; i++){
                $('#auth_ids').find('input:checkbox[name="ids[]"][value='+arr[i]+']').prop('checked', true);;
            }
        }
    }
};
var selOption =  function(opt){
	switch(opt){
		case 'All':{
			$('#auth_ids').find('input:checkbox[name="ids[]"]').prop('checked', true);
			break;
		}
		case 'None':{
			$('#auth_ids').find('input:checkbox[name="ids[]"]').prop('checked', false);
			break;
		}
		case 'Init':{
			if(typeof(selInitDefault) != 'undefined'){
				selInit(selInitDefault);
			}
			break;
		}
		default:{
			if(opt.substr(0,1) == '_'){
				selOptions = $('input:radio[value="sel'+opt+'"]').attr('data');
				if(selOptions){
					selInit(selOptions.split(','), onload=1);
				}
			}
		}
	}
};

$(function () {
	// 提示信息
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
	
	// 单选框
    $('.selOption').click(function(){
        return selOption($(this).val().substr(3));
    });
	
	// 后退
	$('#back').click(function () {
		history.go(-1);
	});
});