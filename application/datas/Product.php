<?php
/**
 * 产品模块的数据提取类
 * 
 * @author 刘通
 */
class Datas_Product extends Cms_Data
{
	/**
	 * 获取产品信息
	 * 		{product::info pro_id="$entity_id" high_pro_id="" brand="Wondershare" /}
	 * 
	 * @author 刘通
	 * 
	 * 参数说明：
	 * 		pro_id		产品ID
	 * 		high_pro_id	高板本是该ID的产品
	 * 		brand		如果不为空，则去品牌名
	 */
	function info($params)
	{
		extract($params);
		
		$condition = "`site_id`={$site_id} AND `state`='able'";
		if(isset($pro_id))
		{
			$pro_id = (int) $pro_id;
			$condition .= " AND `pro_id`={$pro_id}";
		}
		else if(isset($high_pro_id))
		{
			$high_pro_id = (int) $high_pro_id;
			$condition .= " AND `high_pro_id`={$high_pro_id}";
		}
		else 
		{
			return array();
		}
		
		$sql = "SELECT pc.*, pc.`page_url` AS `url` FROM `product_customs` pc WHERE {$condition}";
		$row = $this->fetchRow($sql);
		
		//获取扩展字段
		if($row)
		{
			$sql = "SELECT `field_id`, `data` FROM `product_ext_datas` WHERE `pro_id`={$row['pro_id']} AND `state`='able'";
			$ext_data = $this->fetchAll($sql);
			if($ext_data)
			{
				$ext_fields = $this->extField($site_id, 'product');
				foreach($ext_data as $data)
				{
					if(isset($ext_fields[$data['field_id']]['field_name']))
					{
						$row[$ext_fields[$data['field_id']]['field_name']] = $data['data'];
					}
				}
			}
			
			$row['product_os'] = strtolower($row['product_os']);
			if(!empty($brand))
			{
				$t = trim($brand) . ' ';
				$row['name'] = str_replace($t, '', $row['name']);
			}
			
			//获取高板本url
			if(!$row['tpl_id'] && $row['high_pro_id'])
			{
				$sql = "SELECT `page_url` FROM `product_customs` WHERE `site_id`={$site_id} AND `pro_id`={$row['high_pro_id']} AND `state`='able'";
				$tmp = $this->fetchRow($sql);
				if($tmp)
				{
					$row['url'] = $tmp['page_url'];
				}
			}
			
			$row['url'] = $row['page_url'] = $this->_formatURL($row['url']);
		}
		
		return $row;
	}
	
	/**
	 * 获取有扩展字段的产品ids
	 * 		{product::ext_pro_ids field_name="end_time" /}
	 */
	function ext_pro_ids($params)
	{
		extract($params);
		$ext_fields = $this->extField($site_id, 'product');
		foreach($ext_fields as $field)
		{
			if($field['field_name'] == $field_name)
			{
				$field_id = $field['field_id'];
				break;
			}
		}
		
		if(empty($field_id))
		{
			return array();
		}
		
		$sql = "SELECT 
					`pro_id` 
				FROM
					`product_ext_datas` 
				WHERE pro_id IN (SELECT `pro_id` FROM `product_customs`	WHERE site_id = {$site_id} AND `state` = 'able') 
					AND `state` = 'able' 
					AND `data` <> '' 
					AND `field_id`={$field_id}";
		$rows = $this->fetchAll($sql);
		
		$return = array();
		foreach($rows as $row)
		{
			$return[] = $row;
		}
		
		return $return;
	}
	
	/**
	 * 获取产品信息列表
	 * 		{product::lists pro_ids="2,4,7" cat_id="3" rank="1" label="Top Seller" product_os="Win,Mac" show_os="Mac" brand="Wondershare"}
	 * 		<li>{=$r['name']}</li>
	 * 		{/product}
	 * 
	 * @author 刘通
	 * 
	 * 参数说明：
	 * 		pro_ids		产品ID，用逗号分隔开，如果cat_id不为空，则此值被忽略
	 * 		cat_id		分类ID，获取该分类以及子分类的产品
	 * 		rank		是否重要产品，1为重要
	 * 		label		标签，可选值 New | Free | Upgrade | Top Seller | Top Downloader
	 * 		is_free		是否免费
	 * 		free_or_try	免费或试用的
	 * 		product_os	产品平台，1. 如果为Win,Mac，则显示Win和Mac分开，根据show_os来组织数据 2.如果为空，这两者皆有
	 * 		version		产品版本
	 * 		high_pro_id	对应低版本
	 * 		brand		品牌，如果此值不为空，把产品名去品牌
	 */
	function lists($params)
	{
		extract($params);
		if(!empty($cat_id))
		{
			$cat_model = new Product_Models_Category();
			$cat_ids = $cat_model->getChildrenId($site_id, $cat_id);
			$cat_ids[] = $cat_id;
			
			$sql = $this->quoteInto("SELECT `pro_id` FROM `product_customs` WHERE `cat_id` IN (?)", $cat_ids);
			$rows = $this->fetchAll($sql);
			$pro_ids = array();
			foreach($rows as $row)
			{
				$pro_ids[] = $row;
			}
		}
		
		$condition = "`site_id`={$site_id} AND `state`='able' AND (`tpl_id`>0 OR `self_overview_url`<>'' OR (`tpl_id`=0 AND `high_pro_id`>0))";
		if(isset($pro_ids))
		{
			if(empty($pro_ids))
			{
				return array();
			}
			
			if(is_string($pro_ids))
			{
				$pro_ids = Cms_Func::strToArr($pro_ids);
			}
			
			if($pro_ids)
			{
				$condition .= $this->quoteInto(" AND `pro_id` IN (?)", $pro_ids);
			}
		}
		
		//排序
		if(isset($sort))
		{
			if(empty($order) OR strtolower($order) != 'desc')
			{
				$order = 'ASC';
			}
		}
		else if(isset($pro_ids) && empty($cat_id))
		{
			$pro_ids = implode(',', $pro_ids);
			$sort = " FIND_IN_SET(`pro_id`, '{$pro_ids}')";
			$order = '';
		} else {
			$sort = 'sort';
			$order = 'desc';
		}
		
		
		//是否重要
		if(!empty($rank))
		{
			$condition .= " AND `rank`='1'";
		}
		
		//标签
		if(!empty($label))
		{
			$condition .= " AND FIND_IN_SET('{$label}', `label`)";
		}
		
		if(isset($is_free))
		{
			$is_free = (int) $is_free;
			$condition .= " AND `is_free`='{$is_free}'";
		}
		
		if(!empty($free_or_try))
		{
			$condition .= " AND (`is_free`='1' OR `is_try`='1')";
		}
		
		//产品版本
		if(!empty($version))
		{
			$version = Cms_Func::strToArr($version);
			$version = array_map('strval', $version);
			$condition .= $this->quoteInto(" AND `version` IN (?)", $version);
		}
		
		//对应低版本
		if(!empty($high_pro_id))
		{
			$condition .= $this->quoteInto(" AND `high_pro_id`=?", $high_pro_id);
		}
		
		//平台
		if(!empty($product_os))
		{
			$product_os = explode(',', strtolower($product_os));
			$product_os = array_map('trim', $product_os);
			$condition .= $this->quoteInto(" AND `product_os` IN (?)", $product_os);
		}
		
		if(empty($show_os))
		{
			$show_os = 'win';
		}
		
		$show_os = strtolower($show_os);
		
		//返回数量
		$limit = '';
		if(!empty($num))
		{
			if(empty($product_os) OR count($product_os)==1)
			{
				$limit = " LIMIT {$num}";
			}
		}
		
	    $sql = "SELECT pc.*, page_url AS url 
				FROM `product_customs` pc 
				WHERE {$condition}
				ORDER BY {$sort} {$order}
				{$limit}";
		$rows = $this->fetchAll($sql);
		
		$tmp = array();
		foreach ($rows as $row)
		{
			$row['product_os'] = strtolower($row['product_os']);
			//如果tpl_id=0,查询高版本地址
			if($row['tpl_id'] == 0 && $row['high_pro_id'])
			{
				$sql = "SELECT `page_url` FROM `product_customs` WHERE `site_id`={$site_id} AND `pro_id`={$row['high_pro_id']} AND `state`='able'";
				$t = $this->fetchRow($sql);
				if($t)
				{
					$row['url'] = $t['page_url'];
				}
			}
			$tmp[$row['pro_id']] = $row;
		}
		$rows = $tmp;
		
		//重新处理product_os="Win,Mac"的情况
		if(!empty($product_os) && count($product_os)>1)
		{
			if(empty($num))
			{
				$num = 1000;			//不限制
			}
			
			$self_pros = $rel_pros = array();
			foreach($rows as $row)
			{
				if(strtolower($row['product_os']) != $show_os)
				{
					$rel_pros[$row['pro_id']] = $row;
				}
				else 
				{
					$self_pros[$row['pro_id']] = $row;
				}
			}
			
			$i = 0;
			foreach($self_pros as $k => &$pro)
			{
				if(++$i > $num)
				{
					unset($self_pros[$k]);
					continue;
				}
				
				if(array_key_exists($pro['rel_pro_id'], $rel_pros))
				{
					$pro['rel_url'] = $rel_pros[$pro['rel_pro_id']]['url'];
					unset($rel_pros[$pro['rel_pro_id']]);
				}
			}
			
			if($i < $num)
			{
				$rows = array_merge($self_pros, array_slice((array)$rel_pros, 0, $num - $i));
			}
			else 
			{
				$rows = $self_pros;
			}
		}
		
		//查找对应的地址
		foreach($rows as $key => &$row)
		{
			//处理对应URL
			if($row['rel_pro_id'] && empty($row['rel_url']))
			{
				if(isset($rows[$row['rel_pro_id']]))
				{
					$row['rel_url'] = $this->_formatURL($rows[$row['rel_pro_id']]['url']);
					$row['rel_name'] = $rows[$row['rel_pro_id']]['name'];
				}
				else 
				{
					$tmp = $this->info(array('site_id'=>$site_id, 'pro_id'=>$row['rel_pro_id']));
					if($tmp)
					{
						$row['rel_url'] = $this->_formatURL($tmp['url']);
						$row['rel_name'] = $tmp['name'];
					}
				}
			}
			
			$row['url'] = $row['page_url'] = $this->_formatURL($row['url']);
		}
		
		//去品牌名
		if(!empty($brand))
		{
			$brand = trim($brand) . ' ';
			foreach($rows as &$row)
			{
				$row['name'] = str_replace($brand, '', $row['name']);
				if(isset($row['rel_name']))
				{
					$row['rel_name'] = str_replace($brand, '', $row['rel_name']);
				}
			}
		}

		return array_values($rows);
	}
	
	/**
	 * 获取Feature信息
	 * 		{product::feature type="key" pro_id="$pro_id"}
	 * 		<li>{=$r['title']}</li>
	 * 		{/product::feature}
	 */
	function feature($params)
	{
		extract($params);
		if(empty($pro_id))
		{
			return array();
		}
		
		$pro_id = (int) $pro_id;
		
		if(empty($type))
		{
			return array();
		}

		$condition = $this->quoteInto(" AND `type`=?", $type);
		$sql = "SELECT * FROM `product_features` WHERE `pro_id`={$pro_id} AND `state`='able'{$condition} ORDER BY `sort`";
		return $this->fetchAll($sql);
	}
	
	/**
     * 获取产品的licenses
     * 		{product::license_list pro_id="1" license_name="SKU-WE"}
     * 		<li>{=$r['license']}</li>
     * 		{/product::license_list}
     * 
     * 页面参数 id 产品ID
     * @param array $params
     */
	function license_list($params)
	{
		extract($params);
		if(empty($pro_id))
		{
			return array();
		}
		
		$condition = $this->quoteInto("`pro_id`=?", $pro_id);
		if(empty($license_name))
		{
			$condition .= " AND `website_display`='1'";
		}
		else 
		{
			$condition .= $this->quoteInto(" AND `license` LIKE ?", '%' . $license_name . '%');
		}
        
		//添加者:tjx 用于多license在前台的排序（如:可以按照价格排序显示...）
		$order_str = '';
		if(!empty($params['sort']))
		{
		    $order_str .= $params['sort'];
		    if(empty($params['order']) OR strtolower($params['order']) != 'asc')
		    {
		        $order_str .= ' DESC';
		    }
		    $order_str .= ',';
		}
		
		$pro_id = (int) $pro_id;
		$sql = "SELECT `lic_id`, `license_id`, `sub_license_id`, `license` 
                FROM `product_licenses`
                WHERE {$condition}
                ORDER BY {$order_str} `license_id`";

		return $this->fetchAll($sql);
	}
	
	/**
	 * 获取产品价格
	 * 		{product::price pro_id="115" license_id="11" license_name="" currency="USD" /}
	 * 		{=$r['price']}
	 */
	function price($params)
	{
		extract($params);
		if(empty($pro_id))
		{
			return false;
		}
		
		if(empty($license_id))
		{
			$license_id = 11;
		}
		
		if(isset($license_name))
		{
			$condition = $this->quoteInto("`pro_id`={$pro_id} AND `license_id`={$license_id} AND `license` LIKE ?", '%' . $license_name . '%');
			$sql = "SELECT `license_id`, `sub_license_id` FROM `product_licenses` WHERE {$condition} ORDER BY `price` DESC";
			$row = $this->fetchRow($sql);
			if(!$row)
			{
				return false;
			}
			extract($row);
		}
		
		if(empty($sub_license_id))
		{
			$sql = "SELECT `license_id`, `sub_license_id` FROM `product_licenses` WHERE `pro_id`={$pro_id} AND `license_id`={$license_id} AND `website_display`='1'";
			$row = $this->fetchRow($sql);
			if(!$row)
			{
				return false;
			}
			extract($row);
		}
		
		if(empty($currency))
		{
			$currency = 'USD';
		}
		
		//取原价
		$sql = "SELECT `lic_id` FROM `product_licenses` 
				WHERE `pro_id`='{$pro_id}' 
					AND `license_id`={$license_id} 
					AND `sub_license_id`=0";
		$row = $this->fetchRow($sql);
		
		$sql = "SELECT `price` FROM `product_prices` WHERE `lic_id`={$row['lic_id']} AND `currency`='{$currency}'";
		$row = $this->fetchRow($sql);
		
		$row['old_price'] = $row['price'];
		$row['sub_license_id'] = $sub_license_id;
		if($sub_license_id != 0)
		{
			$sql = "SELECT `lic_id` FROM `product_licenses` 
					WHERE `pro_id`='{$pro_id}' 
						AND `license_id`={$license_id} 
						AND `sub_license_id`={$sub_license_id}";
			$tmp = $this->fetchRow($sql);
			
			if($tmp)
			{
				$sql = "SELECT `price` FROM `product_prices` WHERE `lic_id`={$tmp['lic_id']} AND `currency`='{$currency}'";
				$tmp = $this->fetchRow($sql);
				$row['price'] = $tmp['price'];
			}
		}
		
		return $row;
	}
	
	/**
	 * 获取购物车信息
	 * 		{product::cart pro_id="7" license_id="11" /}
	 * 		{=$r['avangate']}
	 */
	function cart($params)
	{
		extract($params);
		if(empty($pro_id))
		{
			return array();
		}
		
		if(empty($license_id))
		{
			$license_id = 11;
		}
		
		$license_id = (int) $license_id;

		if(isset($license_name))
		{
			$condition = $this->quoteInto("`pro_id`={$pro_id} AND `license_id`={$license_id} AND `license` LIKE ?", '%' . $license_name . '%');
			$sql = "SELECT 
	                    `swreg_url` AS `swreg`, 
						`regnow_url` AS `regnow`, 
						`shareit_url` AS `shareit`, 
						`avangate_url` AS `avangate`, 
						`cart_url` AS `cart`,
						`license` AS `license_name`
			        FROM `product_licenses` 
			        WHERE {$condition}
			        ORDER BY `price` DESC";
			$row = $this->fetchRow($sql);
		}
		else 
		{
    		if(!isset($sub_license_id))
    		{
    			$sql = "SELECT `sub_license_id` FROM `product_licenses` WHERE `pro_id`={$pro_id} AND `license_id`={$license_id} AND `website_display`='1'";
    			$row = $this->fetchRow($sql);
    			if(!$row)
    			{
    				return array();
    			}
    			
    			$sub_license_id = $row['sub_license_id'];
    		}
    		
    		$sub_license_id = (int) $sub_license_id;
    		$sql = "SELECT 
    					`swreg_url` AS `swreg`, 
    					`regnow_url` AS `regnow`, 
    					`shareit_url` AS `shareit`, 
    					`avangate_url` AS `avangate`, 
    					`cart_url` AS `cart`,
    					`license` AS `license_name`
    				FROM `product_licenses` 
    				WHERE `pro_id`={$pro_id} 
    					AND `license_id`={$license_id}
    					AND `sub_license_id`={$sub_license_id}";
    		$row = $this->fetchRow($sql);
		}
		
		//添加者:tjx 是否需要通过cbs接口获取avangate连接 默认为cms里面的avangate 如： es需要取二种语言的avangate链接  但cms只存了同一款产品的一中语言的avangate链接
		if(isset($is_cbs_avangate_url) && $is_cbs_avangate_url == '1')
		{
		    $syn_cfg = Cms_Func::getConfig('cbs', 'url');
		    $syn_avangate_url = $syn_cfg->avangate_url;
		     
		    $price_info = $this->price($params);
		    $cbs_id = $this->_getCbsIdByProId($pro_id);
		    $row['avangate'] = file_get_contents("{$syn_avangate_url}&prices={$price_info['price']}&product_ids={$cbs_id}&curr={$currency}");
		}
		
		return $row;
	}
	
	/**
	 * 获取产品下载链接
	 * 		{product::version pro_id="7" /}
	 * 		{=$r['download_url']}
	 */
	function version($params)
	{
		extract($params);
		if(empty($pro_id))
		{
			return array();
		}
		
		$pro_id = (int) $pro_id;
		$sql = "SELECT `version_name`, `download_url`, `product_size` FROM `product_versions` WHERE `pro_id`={$pro_id} AND `is_sale`='1' LIMIT 1";
		$row = $this->fetchRow($sql);
		
		return $row;
	}
	
    
	/**
	 * 获取产品的What's news信息
	 * 		{product::whats_news pro_id="7" /}
	 * 		{=$r['content']}
	 */
	function whats_news($params)
	{
		extract($params);
		if(empty($pro_id))
		{
			return array();
		}
		
		$sql = "SELECT `whatisnew_title` AS `title`, `whatisnew_content` AS `content` FROM `product_versions` WHERE `pro_id`={$pro_id} AND `is_sale`='1'";
		$row = $this->fetchRow($sql);
		
		return $row;
	}
	
	/**
	 * 获取产品的What's news信息
	 * 		{product::whats_news_list pro_id="7" /}
	 * 		{=$r['content']}
	 */
	function whats_news_list($params)
	{
		extract($params);
		if(empty($pro_id))
		{
			return array();
		}
		
		$sql = "SELECT `version_name`, `whatisnew_title` AS `title`, `whatisnew_content` AS `content`, `publish_date` 
				FROM `product_versions` 
				WHERE `pro_id`={$pro_id} 
				ORDER BY `publish_date` DESC";
		
		$rows = $this->fetchAll($sql);
		
		return $rows;
	}
	
	/**
	 * 获取块内容
	 * 		{product::block blk_id="$blk_id" /}
	 * 
	 */
	function block($params)
	{
		extract($params);
		if(empty($blk_id))
		{
			return false;
		}
		
		$blk_id = (int) $blk_id;
		$sql = "SELECT * FROM `product_blocks` WHERE `blk_id`={$blk_id}";
		return $this->fetchRow($sql);
	}
	
	/**
	 * 获取侧边模块信息
	 * 		{product::block_list pro_id="33"} or {product::block_list blk_ids="2,3,4"}
	 * 		<li>{=$r['title']}</li>
	 * 		{/product}
	 */
	function block_list($params)
	{
		extract($params);
		$condition = "`site_id`={$site_id} AND `state`='able'";
		if(isset($pro_id))
		{
			$pro_id = (int) $pro_id;
			$sql = "SELECT `blk_ids` FROM `product_customs` WHERE `pro_id`={$pro_id}";
			$row = $this->fetchRow($sql);
			$blk_ids = $row['blk_ids'];
		}
		
		if(empty($blk_ids))
		{
			return array();
		}
		
		$blk_ids = Cms_Func::strToArr($blk_ids);
		$condition .= $this->quoteInto(" AND `blk_id` IN (?)", $blk_ids);
		
		$sql = "SELECT `name`, `title`, `content`, `image_href`, `image_url`, `is_recom`
				FROM `product_blocks`
				WHERE {$condition}";
		return $this->fetchAll($sql);
	}
	
	/**
	 +--------------
	 | 产品评论接口 |
	 +--------------
	 */
	
	/**
	 * 获取评论列表
	 * 		分页用
	 * 		{product::page_review_list pro_id="63" sort="" order="" page="$page" pagesize="15"}
	 * 		<li>{=$r['title']}</li>
	 * 		{/product}
	 */
	function page_review_list($params)
	{
		if(empty($params['pro_id']))
		{
			return array();
		}
		
		$condition = "`site_id`={$params['site_id']} AND `pro_id`={$params['pro_id']} AND `is_delete`='0' AND `check_state`='1'";
		
		if(empty($params['sort']))
		{
			$params['sort'] = 'date';
		}
		
		if(empty($params['order']) OR strtolower($params['order']) != 'asc')
		{
			$params['order'] = 'DESC';
		}
		
		$params['sql'] = "SELECT *
						FROM `product_reviews` 
						WHERE {$condition}
						ORDER BY `{$params['sort']}` {$params['order']}";
		
		$return = $this->pageQuery($params);
		
		foreach($return['rows'] as &$row)
		{
		    $row['title'] = stripslashes($row['title']);
			$row['review'] = stripslashes($row['review']);
		}
		
		return $return;
	}
	
	/**
	 * 获取评论列表
	 * 		不分页
	 * 		{product::review_list pro_id="63" is_recom="1" num="3"}
	 * 		<li>{=$r['title']}</li>
	 * 		{/product::review_list}
	 */
	function review_list($params)
	{
		extract($params);
		if(empty($pro_id))
		{
			return array();
		}
		
		$pro_id = (int) $pro_id;
		$condition = "`site_id`={$site_id} AND `pro_id`={$pro_id} AND `check_state`='1'";
		
		if(!empty($is_recom))
		{
			$condition .= " AND `is_rec`=1";
		}
		
		if(empty($sort))
		{
			$sort = 'id';
		}
		
		if(empty($order) OR strtolower($order) != 'asc')
		{
			$order = 'DESC';
		}
		
		$limit = '';
		if(!empty($num))
		{
			$limit = " LIMIT {$num}";
		}
		
		$pro_id = (int) $pro_id;
		
		$sql = "SELECT *
				FROM `product_reviews` 
				WHERE {$condition}
				ORDER BY `{$sort}` {$order}, `date` DESC
				{$limit}";
		
		$rows = $this->fetchAll($sql);
		
		foreach($rows as &$row)
		{
		    $row['title'] = stripslashes($row['title']);
			$row['review'] = stripslashes($row['review']);
		}
		
		return $rows;
	}
	
	/**
	 * 获取评论信息
	 * 		{product::review pro_id="63" sort="star" order="desc" /}
	 * 		{=$r['author']}
	 */
	function review($params)
	{
		extract($params);
		if(empty($pro_id))
		{
			return array();
		}
		
		if(empty($sort))
		{
			$sort = "id";
		}
		
		if(empty($order) OR strcasecmp($order, 'asc'))
		{
			$order = 'desc';
		}
		
		$limit = '';
		if(!empty($num))
		{
			$limit = " LIMIT " . intval($num);
		}
		
		$sql = "SELECT * FROM `product_reviews` 
				WHERE `site_id`={$site_id} AND `pro_id`={$pro_id} AND `check_state`=1 AND `is_delete`=0
				ORDER BY {$sort} {$order}, `date` DESC
				{$limit}";
		
		$row = $this->fetchRow($sql);
		
		if($row)
		{
			$row['review'] = stripslashes($row['review']);
		}
		
		return $row;
	}
	
	/**
	 * 获取评论数量
	 * 		{product::review_num pro_id="63" /}
	 * 		{=$r['num']}
	 */
	function review_num($params)
	{
		if(empty($params['pro_id']))
		{
			return false;
		}
		
		$sql = "SELECT count(*) num FROM `product_reviews` WHERE `pro_id`='{$params['pro_id']}' AND `check_state`='1' AND `is_delete`='0'";
		$row = $this->fetchRow($sql);
		
		return $row;
	}
	
	/**
	 * 获取评论星信息
	 * 		{product::star pro_id="63" type="html" /}
	 * 		{=$r['star_1']}
	 */
	function star($params)
	{
		if(empty($params['pro_id']))
		{
			return false;
		}
		
		$sql = "SELECT * FROM `product_review_stars` WHERE `pro_id`={$params['pro_id']}";
		$row = $this->fetchRow($sql);
		
		if(isset($params['type']) && $params['type'] == 'html')
		{
			$row['html'] = Cms_T::starDropDown($row);
		}
		
		return $row;
	}
	
	/**
	 * 获取平均星数
	 * 		{product::avg_star pro_id="63" type="num|html" /}
	 * 		{=$r['num']}
	 */
	function avg_star($params)
	{
		if(empty($params['pro_id']))
		{
			return 0;
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
		
		if(isset($params['type']) && $params['type'] == 'html')
		{
			$return['html'] = Cms_T::star($return['num']);
		}
		
		return $return;
	}
	
	/**
	 * 获取分类下的最好产品，用评论来判断，选择星数最多的，然后用五星
	 * 		{product::best_pro_list cat_id="3" num="3" brand="Wondershare"}
	 * 		<li><!--{=$r['name']}--></li>
	 * 		{/product}
	 * 
	 * @author 刘通
	 * 
	 * 参数说明
	 * 		cat_id	分类ID
	 * 		num		提取个数
	 * 其它参数参考 lists，不要有pro_ids
	 */
	function best_pro_list($params)
	{
		extract($params);
		if(empty($cat_id))
		{
			return array();
		}
		else 
		{
			$cat_model = new Product_Models_Category();
			$cat_ids = $cat_model->getChildrenId($site_id, $cat_id);
			$cat_ids[] = $cat_id;
			$cat_ids = implode(',', $cat_ids);
		}
		
		if(empty($num))
		{
			$num = 10;
		}
		
		$sql = "SELECT pro_id, (star_1 + star_2 + star_3 + star_4 + star_5) num, star_5 
				FROM product_review_stars 
				WHERE pro_id IN(SELECT pro_id 
								FROM `product_customs` 
								WHERE `site_id`={$site_id}
									AND `cat_id` IN ({$cat_ids})
									AND (`tpl_id`>0 OR (`tpl_id`=0 AND `high_pro_id`>0))
									AND `state`='able')
				ORDER BY num DESC, star_5 DESC
				LIMIT {$num}";
		
		$rows = $this->fetchAll($sql);
		$pro_ids = array();
		foreach($rows as $row)
		{
			$pro_ids[] = $row['pro_id'];
		}
		
		$params['pro_ids'] = implode(',', $pro_ids);
		unset($params['cat_id']);
		
		return $this->lists($params);
	}
	
	/**
	 * 推荐文章，主要是提取分类的产品的文章
	 * 		{product::recom_art_ids cat_id="3" num="4" /}
	 * 		{=$r['ids']}
	 */
	function recom_art_ids($params)
	{
		extract($params);
		if(empty($cat_id))
		{
			return false;
		}
		
		$condtion = $this->quoteInto("`site_id`={$site_id} AND `cat_id` IN (?) AND `art_ids`<>''", $this->_getChildAndSelfCatIds($site_id, $cat_id));
		
		$sql = "SELECT `art_ids` FROM `product_customs` WHERE {$condtion}";
		$rows = $this->fetchAll($sql);
		$art_ids = array();
		foreach($rows as $row)
		{
			$tmp = explode(',', $row['art_ids']);
			$art_ids = array_merge($art_ids, $tmp);
		}
		
		$art_ids = array_unique($art_ids);
		if(!$art_ids)
		{
			return false;
		}
		
		return array('ids' => implode(',', array_slice($art_ids, 0, $num)));
	}
	
	/**
	 +--------------
	 | 产品分类接口 |
	 +--------------
	 */
	
	/**
	 * 获取分类基本信息
	 * 		{product::category cat_id="105" child_id="" /}
	 * 		{=$r['url']}
	 * 
	 * 参数说明：
	 * 		cat_id		分类ID	获取该ID的分类信息
	 * 		child_id	获取改分类的父分类信息
	 * 注：
	 * 		如果child_id不为空，则cat_id无效
	 */
	function category($params)
	{
		extract($params);
		if(!empty($child_id))
		{
			$child_id = (int) $child_id;
			$sql = "SELECT `parent_id` FROM `product_categorys` WHERE `cat_id`={$child_id}";
			$row = $this->fetchRow($sql);
			if(!$row)
			{
				return array();
			}
			
			$cat_id = $row['parent_id'];
		}
		
		if(empty($cat_id))
		{
			return array();
		}
		
		$cat_id = (int) $cat_id;
		$sql = "SELECT pc.*, pc.`page_url` AS `url` FROM `product_categorys` pc WHERE `cat_id`={$cat_id} AND `state`='able'";
		$row = $this->fetchRow($sql);
		
		if($row)
		{
			$row['url'] = $row['page_url'] = $this->_formatURL($row['url']);
		}
		
		return $row;
	}
	
	/**
	 * 获取一级分类和二级分类列表
	 * 		{product::category_list layer="1" parent_id="103" no_link="0"}
	 * 		{=$r['name']}
	 * 		{/product}
	 */
	function category_list($params)
	{
		extract($params);
		if(empty($layer))
		{
			$layer = 1;
		}
		
		switch($layer)
		{
			case 1:
				$parent_id = 0;
				break;
			case 2:
			case 3:
				if(empty($parent_id))
				{
					return array();
				}
				break;
			default:
				return array();
		}
		
		if(empty($sort))
		{
			$sort = 'sort';
		}
		
		if(empty($order) OR strtolower($order) != 'desc')
		{
			$order = 'ASC';
		}
		
		$contition = "`site_id`={$site_id}
					AND `parent_id`={$parent_id} 
					AND `state`='able'
					AND `layer`={$layer}";
		
		if(empty($no_link))
		{
			$contition .= " AND `tpl_id`>0";
		}
		
		$sql = "SELECT `cat_id`, `name`, `alias`, `page_url` AS `url`, `summary` 
				FROM `product_categorys` 
				WHERE {$contition}
				ORDER BY {$sort} {$order}";
		$rows = $this->fetchAll($sql);
		
		foreach($rows as &$row)
		{
			$row['url'] = $row['page_url'] = $this->_formatURL($row['url']);
		}

		return $rows;
	}
	
	/**
	 +---------------
	 | 产品切换页接口 |
	 +---------------
	 */
	
	/**
	 * 获取切换页链接
	 * 		包括产品评论
	 * 		{product::switch_href pro_id="$entity_id"  /}
	 * 
	 * @param array $params
	 */
	function switch_href($params)
	{
		extract($params);
		if(empty($pro_id))
		{
			return array();
		}
		
		$sql = "SELECT 
					`name`,
					`page_url` AS `url` 
				FROM
					`product_switch_datas` sd 
					LEFT JOIN `product_switchs` s 
						ON sd.`switch_id` = s.`switch_id` 
				WHERE sd.`pro_id` = {$pro_id}
					AND sd.`state` = 'able'
					AND s.`state` = 'able' 
					AND sd.`tpl_id` > 0 
				ORDER BY `sort`";
		$rows = $this->fetchAll($sql);
		return $rows;
	}
	
	/**
	 * 获取切换页内容
	 * 		{product::switch_content pro_id="331" name="Guide" /}
	 *		{=$r['content']}
	 */
	function switch_content($params)
	{
		extract($params);
		if(empty($pro_id))
		{
			return array();
		}
		
		$condition = $this->quoteInto(" AND `name`=?", $name);
		$sql = "SELECT `content` FROM `product_switchs` s, `product_switch_datas` d
				WHERE s.`switch_id`=d.`switch_id` 
					AND s.`site_id`={$site_id}
					AND d.`pro_id`={$pro_id}
					AND s.`state`='able'
					AND d.`state`='able'{$condition}";
		$row = $this->fetchRow($sql);
		
		return $row;
	}
	
	/**
	 * 根据产品ID获取CBSID
	 *
	 * @param integer $pro_id
	 */
	private function _getCbsIdByProId($pro_id)
	{
	    $pro_id = (int) $pro_id;
	    $sql = "SELECT `cbs_id` FROM `product_customs` WHERE `pro_id`={$pro_id}";
	    $row = $this->fetchRow($sql);
	    return $row ? $row['cbs_id'] : 0;
	}
	
	/**
	 * 根据产品ID获取公共产品ID
	 * 
	 * @param integer $pro_id
	 */
	private function _getProductIdByProId($pro_id)
	{
		$pro_id = (int) $pro_id;
		$sql = "SELECT `product_id` FROM `product_customs` WHERE `pro_id`={$pro_id}";
		$row = $this->fetchRow($sql);
		return $row ? $row['product_id'] : 0;
	}
	
	/**
	 * 获取一个分类的子分类和自身
	 */
	private function _getChildAndSelfCatIds($site_id, $cat_id)
	{
		$cat_model = new Product_Models_Category();
		$cat_ids = $cat_model->getChildrenId($site_id, $cat_id);
		$cat_ids[] = $cat_id;
		
		return $cat_ids;
	}
}