<?php
class Form {

	/**
	 * 产生input表单数据类型
	 * 
	 * @param array $properties 属性数据
	 */
	public static function text($properties)
	{
		$html = '<input type="text"';
		foreach($properties as $key => $value){
			$html .= ' '.$key.'="'.$value.'"';	
		}
		$html .= " />\n";
		return $html;
	}
	
	/**
	 * 产生hidden类型的表单
	 * 
	 * @param array $properties 属性数据
	 */
	public static function hidden($properties)
	{
		$html = '<input type="hidden"';
		foreach($properties as $key => $value){
			$html .= ' '.$key.'="'.$value.'"';
		}
		$html .= " />\n";
		return $html;
	}
	
	/**
	 * 产生select类型的表单（单选）
	 * 
	 * @param array $properties 属性数据
	 * @param array $values 产生select的数据
	 * @param string $selected 选中的数据
 	 */
	public static function select($properties,$values,$selected)
	{
		$html_sel = '<select';
		foreach($properties as $key => $value){
			$html_sel .= ' '.$key.'="'.$value.'"';
		}
		$html_sel .= ">\n";
		
		$html_opt = '';
		foreach($values as $key => $value){
			if($value == $selected){
				$html_opt .= '<option value="'.$key.'" selected>'.$value.'</option>'."\n";				
			}else{
				$html_opt .= '<option value="'.$key.'">'.$value.'</option>'."\n";	
			}
		}
		$html_sel .= $html_opt.'</select>';
		return $html_sel;
	}

}
?>