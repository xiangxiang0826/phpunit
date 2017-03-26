<?php

/**
 * 简写标签，不返回数组，直接输出的，这个要保证必须有值
 * 
 * @author 刘通
 */
class Datas_Echo extends Cms_Data
{
	/**
	 * 获取产品分类所属的第一级分类的名字，导航高亮时用
	 * 
	 * @param array $params
	 */
	function root_cat_name($params)
	{
		extract($params);
		$pro_id = (int) $pro_id;
		$sql = "SELECT `cat_id` FROM `product_customs` WHERE `pro_id`={$pro_id}";
		$row = $this->fetchRow($sql);
		if(!$row)
		{
			return '';
		}
		
		$cat_model = new Product_Models_Category();
		$cat_ids = $cat_model->getParentsId($site_id, $row['cat_id']);
		
		$cat_id = 0;
		foreach($cat_ids as $id)
		{
			$tmp = $cat_model->getParentsId($site_id, $id);
			if(!$tmp)
			{
				$cat_id = $id;
				break;
			}
		}
		
		$sql = "SELECT `name` FROM `product_categorys` WHERE `cat_id`={$cat_id}";
		$row = $this->fetchRow($sql);
		
		return $row['name'];
	}
	
	/**
	 * 获取评论数
	 */
	function review_num($params)
	{
		if(empty($params['pro_id']))
		{
			return 0;
		}
		
		$sql = "SELECT count(*) num FROM `product_reviews` WHERE `pro_id`='{$params['pro_id']}' AND `check_state`='1' AND `is_delete`='0' LIMIT 1";
		$row = $this->fetchRow($sql);
		
		return $row['num'];
	}
	
	/**
	 * 获取平均星
	 */
	function avg_star($params)
	{
		if(empty($params['pro_id']))
		{
			return '';
		}
		
		$row = $this->fetchRow("SELECT * FROM `product_review_stars` WHERE `pro_id`={$params['pro_id']}");
		if(!$row)
		{
			$total = $star_num = 0;
		}
		else 
		{
			$total = $row['star_1'] + 2 * $row['star_2'] + 3 * $row['star_3'] + 4 * $row['star_4'] + 5 * $row['star_5'];
			$star_num = $row['star_1'] + $row['star_2'] + $row['star_3'] + $row['star_4'] + $row['star_5'];
		}
		
		if($star_num > 0)
		{
			$return['num'] = $total / $star_num;
		}
		else 
		{
			$return['num'] = 0;
		}
		
		return Cms_T::star($return['num']);
	}
	
	/**
	 * 获取starDropDown
	 */
	function star_dropdown($params)
	{
		if(empty($params['pro_id']))
		{
			return '';
		}
		
		$sql = "SELECT * FROM `product_review_stars` WHERE `pro_id`={$params['pro_id']}";
		$row = $this->fetchRow($sql);
		
		return Cms_T::starDropDown($row);
	}
}