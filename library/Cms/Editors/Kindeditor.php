<?php
/**
 * Kindeitor编辑器
 *
 * @author tjx
 * 时间: 2013年5月17日
 */
class Cms_Editors_Kindeditor implements Cms_Editors_Interface
{
	static function editor()
	{
		$args = func_get_args();
		if(is_array($args[0]))	//应付一个页面初始化多个ckeditor
		{
			foreach($args[0] as $key => $selector)
			{
				$selector_arr[] = $selector;
			}
		}
		else
		{
			$selector_arr[0] = $args[0];
		}
		
		foreach($selector_arr as $key => $selector)
		{
			$instance = preg_replace('/^[ #\.]*/', '', $selector);
			$var_ckeditor[] = " var editor_{$instance}; ";
			$ckeditor[] = "
			editor_{$instance} = KindEditor.create('{$selector}',
			{
			      width : '{$args[2]}', height:'{$args[3]}', filterMode:false
			}); ";
				
		}
		$var_ckeditor = implode("\n", $var_ckeditor);
		$ckeditor = implode("\n", $ckeditor);
		$str = <<<HTML
		<script charset="utf-8" src="/static/js/libs/kindeditor/kindeditor.js"></script> 
		<script charset="utf-8"  src="/static/js/libs/kindeditor/lang/zh_CN.js"></script> 
		<link rel="stylesheet" href="/static/js/libs/kindeditor/themes/default/default.css" />
		<script type="text/javascript">
		{$var_ckeditor}
	    $(function()
        {
		    {$ckeditor}
	    });
        function editor_sync(id)
		{
	        eval('editor_' + id).sync();		
		}
		</script>
HTML;
		return $str;
	}
}