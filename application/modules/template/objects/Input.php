<?php

/**
 * 获取Input类型的数据
 */

/**
 * 获取对象的工厂方法
 */
class Template_Objects_Input
{
	static function getObject($type)
	{
		$type = ucfirst(strtolower($type));
		$class_name = 'Input_'.$type;
		if(!class_exists($class_name))
		{
			exit('不存在的类型');
		}
		
		return new $class_name;
	}
}

abstract class Input_Base
{
	/**
	 * 获取对应的HTML代码
	 * @param array $args
	 * 			example:array('name'=>, 'value'=>, 'id'=>'', 'width'=>, 'height'=>, 'code'=>);
	 */
	abstract function getInput(array $args);
}

/**
 * input
 */
class Input_Text extends Input_Base
{
	function getInput(array $args)
	{
		$style = $args['input_width'] > 0 ? "style=\"width:{$args['input_width']}px\"" : '';
		$input_value = htmlspecialchars($args['input_value']);
		
		if($args['is_unique'] == 'Y')
		{
			$class = ' class="is_unique"';
		}
		else 
		{
			$class = '';
		}
		
		if($args['is_null'] == 'N')
		{
			$validate = ' data-validate="{require:[true, \'' . Cms_L::_('require') . '\']}"';
		}
		else 
		{
			$validate = '';
		}
		
		return <<<EOT
		<input type="text" name="data[{$args['field_name']}]" value="{$input_value}" id="{$args['field_name']}"{$class}{$validate} {$style} size="100" />
EOT;
	}
}

/**
 * textarea
 */
class Input_Textarea extends Input_Base
{
	function getInput(array $args)
	{
		extract($args);
		$style = 'style="';
		$style .= $input_width > 0 ? "width:{$input_width}px;" : '';
		$style .= $input_height > 0 ? "height:{$input_height}px;" : '';
		$style .= '"';
		
		$input_value = htmlspecialchars($input_value);
		
		if($args['is_unique'] == 'Y')
		{
			$class = ' class="is_unique"';
		}
		else 
		{
			$class = '';
		}
		
		if($args['is_null'] == 'N')
		{
			$validate = ' data-validate="{require:[true, \'' . Cms_L::_('require') . '\']}"';
		}
		else 
		{
			$validate = '';
		}
		
		return <<<EOT
			<textarea name="data[{$field_name}]" id="{$field_name}"{$class}{$validate} rows="15" cols="75" {$style}>{$input_value}</textarea>
EOT;
	}
}

/**
 * select
 */
class Input_Select extends Input_Base
{
	function getInput(array $args)
	{
		extract($args);
		
		if($args['is_unique'] == 'Y')
		{
			$class = ' class="is_unique"';
		}
		else 
		{
			$class = '';
		}
		
		if($args['is_null'] == 'N')
		{
			$validate = ' data-validate="{require:[true, \'' . Cms_L::_('require') . '\']}"';
		}
		else 
		{
			$validate = '';
		}
		
		if($input_option)
		{
			$list = explode("\n", $input_option);
			$str = '';
			foreach($list as $opt)
			{
				if($opt) {
					$opt_arr = explode(',', $opt, 2);
					if(count($opt_arr) == 1) {
						$val = $key = $opt_arr[0];
					}else{
						list($key, $val) = $opt_arr;
					}
					$val = trim($val);
					$key = trim($key);
					$selected = $input_value == $val ? 'selected="selected"' : '';
					$str .= "<option value=\"{$val}\" {$selected}>{$key}</option>";
				}
			}
			
			return <<<EOT
			<select name="data[{$field_name}]" id="{$field_name}"{$class}{$validate}>{$str}</select>
EOT;
		}
		else 
		{
			return <<<EOT
			<select name="data[{$field_name}]" id="{$field_name}"{$class}{$validate} data-selected="{$input_value}">
				<option value="0"></option>
			</select>
EOT;
		}
	}
}

/**
 * fulltextarea
 */
class Input_Fulltext extends Input_Base
{
	function getInput(array $args)
	{
		extract($args);
		$input_width = $input_width > 0 ? $input_width : 710;
		$input_height = $input_height > 0 ? $input_height : 300;
		$input_value = htmlspecialchars($input_value);
		
		$editor = new Cms_Editor();
		$editor = $editor->editor("#{$field_name}", '', $input_width, $input_height);
		
		//为富文本框而设，用kinderEditor在本系统的提交方式下要同步一下，这里用fulltext-inputclass来标识富文本字段
		return <<<EOT
			<textarea id="{$field_name}" name="data[{$field_name}]" class="fulltext-input">{$input_value}</textarea>{$editor}
EOT;
	}
}