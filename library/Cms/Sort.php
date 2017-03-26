<?php

/**
 * Sort
 * 
 * @author Liujd
 */
class Cms_Sort {
	public static function key_sort($toHandler,$sort_key,$sort = 'ASC') {
		$finSort = array();
	
		$toSort = array();
		foreach($toHandler as $key => $value){
			if(!isset($value[$sort_key])){
				return $toHandler;
			}else{
				$toSort[$key] = $value[$sort_key];
			}
		}
		if($sort == "ASC"){
			asort($toSort);
		}else{
			arsort($toSort);
		}
	
		foreach ($toSort as $skey => $svalue) {
			$finSort[] = $toHandler[$skey];
		}
		return $finSort;
	}
}