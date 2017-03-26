<?php

/**
 * 评论钩子
 * 
 * @author 刘通
 */
class Hooks_Product_Review extends Cms_Hook
{
	function afterAdd($data) {}
	function beforeEdit($old_data, $data) {}
	function afterEdit($data) {}
	function beforeDelete($data) {}
	function afterDelete($data) {}
	/**
	 * 审核后动作，因为审核都是批量操作，所以这里传过来的是个评论列表
	 * 		生成相关的产品页
	 * 
	 * @param array $review_arr
	 */
	function afterCheck($review_list)
	{
		//查询出来产品ID
		$pro_ids = array();
		foreach($review_list as $review)
		{
			$pro_ids[] = $review['pro_id'];
		}
		
		$pro_id = array_unique($pro_ids);
		
		if(!$pro_ids)
		{
			return true;
		}
		
		//生成页面
		$db = Cms_Db::getConnection();
		//overview、切换页、评论页和购买页
		$condition = $db->quoteInto("`site_id`={$this->site_id} 
										AND `entity_id` IN (?) 
										AND ((`module`='product' AND `type` IN ('overview', 'switch', 'list_review')) OR (`module`='store' AND `type`='buy')) 
										AND `state`='able'", $pro_ids);
		$sql = "SELECT `tpl_id`, `page_id` FROM `search_site_pages` WHERE {$condition}";
		$rows = $db->fetchAll($sql);
		
		foreach($rows as $row)
		{
			$create_obj = new Cms_Template_Create($row['tpl_id']);
			$create_obj->page($row['page_id']);
		}
		
		return true;
	}
}