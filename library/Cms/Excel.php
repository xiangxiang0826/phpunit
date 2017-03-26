<?php

/**
 * Excel操作类
 * 
 * @author tjx
 */
defined('CMS_LIB_ROOT') or define('CMS_LIB_ROOT', dirname(__FILE__).'/Libs');

class Cms_Excel
{
    // 初始化
    public function __construct()
    {
    
    }
    
	/**
	 * 生成xls到浏览器
	 * 
	 * @example
	 * $file_name = '评论导出'  
	 * $title_name = 'test.xls'
	 * $title_array =  array('A' => '季度1', 'B' => '季度2', 'C' => '季度3', 'D' => '季度4');
	 * $data_array = array(0 => array('user_1' => 112, 'user_2' => 134, 'user_3' => 156, 'user_4' => '178'),
	 *                     1 => array('user_1' => 12, 'user_2' => 34, 'user_3' => 56, 'user_4' => '78') 
	 *               );        
     * Cms_Excel::createXls($file_name, $title_name, $title_array, $data_array)
     * 
	 * @param string $file_name 文件名称
	 * @param array  $title_name 标题名称
	 * @param array $title_array 标题数组
	 * @param array data_array 数据数组
	 * 
	 */
	static  public function createXls($file_name, $title_name, array $title_array, array $data_array)
	{
	    $data_array = array_values($data_array);
	    $title_keys = array_keys($title_array);
	    
	    // Create new PHPExcel object
	    include CMS_LIB_ROOT . '/Excel/PHPExcel.php';
	    $objPHPExcel = new PHPExcel();
	    
	    // Add title data
	    $set_cell_str = '';
	    foreach ($title_keys AS $value)
	    {
	        $title_array[$value] = addslashes($title_array[$value]);
	        $set_cell_str .= "->setCellValue('{$value}1', '{$title_array[$value]}')";
	    }
	    
	    eval("\$objPHPExcel->setActiveSheetIndex(0){$set_cell_str};");
        
	    // Add content data
	    if(!empty($data_array))
	    {
    	    foreach ($data_array AS $key => $value)
    	    {
    	        $set_cell_str = '';
    	       	foreach (array_values($value) AS $key2 => $value2)
    	        {
    	            $cell_key = $title_keys[$key2].($key + 2);
    	            $value2 = addslashes($value2);
    	            $set_cell_str .= "->setCellValue('{$cell_key}', '{$value2}')";
    	        }
    	        eval("\$objPHPExcel->setActiveSheetIndex(0){$set_cell_str};");
	        }
	    }
	    
	    // Rename worksheet
	    $objPHPExcel->getActiveSheet()->setTitle($title_name);
	    
	    header("charset=utf-8");
	    header("Content-Type: application/vnd.ms-excel;charset=utf-8");
	    header("Content-Disposition: attachment;filename={$file_name}");
	    header("Cache-Control: max-age=0");
	    //输出到浏览器
	    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	    $objWriter->save('php://output');
	}
}