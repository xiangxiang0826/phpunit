<?php
/**
 * CKeditor编辑器
 *
 * @author LiuTong
 * 时间: 2012年5月7日
 */
class Cms_Editors_Ckeditor implements Cms_Editors_Interface
{
	static function editor()
	{
		$args = func_get_args();
		if(is_array($args[0]))	//应付一个页面初始化多个ckeditor
		{
			foreach($args[0] as $key => $selector)
			{
				$selector_arr[] = $selector;
				$content_arr[] = isset($args[1][$key]) ? $args[1][$key] : $args[1];
			}
		}
		else
		{
			$selector_arr[0] = $args[0];
			$content_arr[0] = $args[1];
		}
		
		foreach($selector_arr as $key => $selector)
		{
			$instance = preg_replace('/^[ #\.]*/', '', $selector);
			$ckeditor[] = "
					if(CKEDITOR.instances['{$instance}']){
						CKEDITOR.instances['{$instance}'].destroy(true);
					}
					$('{$selector}').ckeditor(function(){}, {
						'width':'{$args[2]}',
						'height':'{$args[3]}'
					});
					
					$('{$selector}').val(\"{$content_arr[$key]}\");
					";
				
		}
		$ckeditor = implode("\n", $ckeditor);
		$str = <<<HTML
		<script type="text/javascript" src="resources/js/libs/ckeditor/ckeditor.js"></script>
		<script type="text/javascript" src="resources/js/libs/ckeditor/adapters/jquery.js"></script>
		<script type="text/javascript">
			$(function(){
				{$ckeditor}
			});
			function editor_sync(id)
			{
					return true;
			}
		</script>\n
HTML;
		return $str;
	}
}