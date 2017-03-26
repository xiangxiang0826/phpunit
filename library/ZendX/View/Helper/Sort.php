<?php
/**
 * 数组排序助手类
 * 
 * @author etong <zhoufeng@wondershare.cn>
 * @version $id Sort.php v1 2014/07/29 19:32 $
 */
class ZendX_View_Helper_Sort extends  Zend_View_Helper_Abstract
{
 	/**
	 * 数组排序
	 * 
	 * @param string $arr 待处理的数组
	 * @param string keys 指定的key值
	 * @param string $orderby 排序的规则，默认为asc（字母升序）
	 * @return string
	 */
    public function Sort($arr, $keys, $orderby='asc'){ 
		$keysvalue = $new_array = array();
		foreach ($arr as $k=>$v){
			$keysvalue[$k] = $v[$keys];
		}
		if($orderby== 'asc'){
			asort($keysvalue);
		}else{
			arsort($keysvalue);
		} 
		reset($keysvalue);
		foreach ($keysvalue as $k=>$v){
			$new_array[] = $arr[$k];
		}
		return $new_array; 
	}
}