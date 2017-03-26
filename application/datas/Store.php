<?php
/**
 * Store模块的数据提取类
 * @author tjx
 * @time 2013-07-22
 */
class Datas_Store extends Cms_Data
{
    /**
     * 获取自定义store信息列表(二维数组)
     * 例：
     * {store::custom_store_list /}
     * 
     */
    public function custom_store_list($params)
    {
        //条件
        $condition = " `site_id`={$params['site_id']} AND `state` = 'able'";

        if(empty($params['sort']))
        {
            $params['sort'] = 'cat_id';
        }
        if(empty($params['order']) OR strtolower($params['order']) != 'asc')
        {
            $params['order'] = 'DESC';
        }
        
       $sql = "SELECT `cat_id`, `name`
                FROM `store_categorys` 
                WHERE {$condition} 
                ORDER BY `{$params['sort']}` {$params['order']}";
        $rows = $this->fetchAll($sql);
        return  $rows;
    }
    
    /**
     * 获取分类信息
     * {store::custom_store_info id="1" /}
     * 页面参数   id         自定义分类ID
     * @param array $params
     */
    public function custom_store_info($params)
    {
        if(empty($params['id']))
        {
            echo '没有发现 自定义分类ID';
            return array();
        }
    
        $cat_id = intval($params['id']);
        $sql = "SELECT * FROM `store_categorys` s_c WHERE `cat_id` = {$cat_id}  LIMIT  1";
        $row = $this->fetchRow($sql);
        
        $sql = "SELECT `pro_id` FROM `store_products` WHERE `cat_id` = {$cat_id}  ORDER BY `sort` DESC";
        $rows = $this->fetchAll($sql);
        $pro_ids = $s = '';
        foreach ($rows AS $value)
        {
            $pro_ids .= $s.$value['pro_id'];
            $s = ',';
        }
        $row['pro_ids'] = $pro_ids;
        return $row;
    }
    
    /**
     * 获取绑定产品列表
     * {store::bind_product_list cbs_id="123" num="1" /}
     * 页面参数   cbs_id     CBSID
     * 页面参数   num        条数
     * @param array $params
     */
    public function bind_product_list($params)
    {
        extract($params);
        if( empty($cbs_id))
        {
            echo '没有发现CBSID';
            return array();
        }
        
        $currency = !empty($currency) ? $currency : 'USD' ;
        $num = isset($num) ? intval($num) : 0;
        $tem_array = $this->_getBindData($cbs_id, $currency, $num);
        if(empty($tem_array))
        {
            return array();
        }
        
        //获取绑定产品的信息
        $bind_cbs_ids = $tem_array['cbs_ids'];
        $bind_data = $tem_array['data'];

        $sql = "SELECT *, `page_url` AS `url`
                FROM `product_customs`
                WHERE `cbs_id` IN ({$bind_cbs_ids}) AND `site_id` = {$site_id} AND `state` = 'able'
                ORDER BY FIND_IN_SET(`cbs_id`, '{$bind_cbs_ids}')";
        $rows = $this->fetchAll($sql);
        $lic_ids = $s = '';
        $product_bind_list = array();
        foreach ($rows AS $key => $value)
        {
            if(!empty($brand))
            {
                $t = trim($brand) . ' ';
                $value['name'] = str_replace($t, '', $value['name']);
            }
            $bind_data[$value['cbs_id']]['pro_info'] =  $value;
            $condition = "`pro_id` = {$value['pro_id']} AND `license_id`=11 ";
            
            if(!empty($license_name))//判断是否通过前台传license_name获取新价格替换绑定价格
            {
                 $condition = $this->quoteInto("{$condition} AND `license` LIKE ?", '%' . $license_name . '%');
                 
                 $sql = "SELECT `lic_id`, `swreg_url`, `avangate_url`
                         FROM `product_licenses`
                         WHERE {$condition}
                         LIMIT 1";
                 $row2 = $this->fetchRow($sql);
                 
                 $sql = "SELECT `price` FROM `product_prices` WHERE `lic_id`='{$row2['lic_id']}' AND `currency`='{$currency}'";
                 $row = $this->fetchRow($sql);
                 $bind_data[$value['cbs_id']]['new_price'] = $row['price'];
            }
            else 
            {
                $condition .= " AND `sub_license_id` = {$bind_data[$value['cbs_id']]['sub_license_id']} ";
                $sql = "SELECT `lic_id`, `swreg_url`, `avangate_url`
                        FROM `product_licenses`
                        WHERE {$condition}
                        LIMIT 1";
                $row2 = $this->fetchRow($sql);
            }
			
			//获取老价格
			$sql = "SELECT `lic_id`
        			FROM `product_licenses`
        			WHERE `pro_id` = {$value['pro_id']} AND `license_id`=11 AND `sub_license_id` = 0
        			LIMIT 1";
			$old_row2 = $this->fetchRow($sql);
			
			$old_row2['lic_id'] = intval($old_row2['lic_id']);
			$sql = "SELECT `price`
					FROM `product_prices` 
					WHERE `lic_id` = {$old_row2['lic_id']} AND `currency`='{$currency}'";
			$old_row3 = $this->fetchRow($sql);
			
			$bind_data[$value['cbs_id']]['old_price'] = $old_row3['price'];
            $bind_data[$value['cbs_id']]['swreg_url'] =  $row2['swreg_url'];
            $bind_data[$value['cbs_id']]['avangate_url'] =  $row2['avangate_url'];
            $bind_data[$value['cbs_id']]['lic_id'] = $row2['lic_id'];
            $product_bind_list[] = $bind_data[$value['cbs_id']];
        }
        unset($bind_data);
        return $product_bind_list;
    }
    
    /**
	 * 获取购物车信息
	 * 		{store::cart pro_id="7" license_id="11" /}
	 * 		{=$r['avangate']}
	 */
    public function cart($params)
    {
        $site_info = Cms_Site::getInfoById($params['site_id']);
        $data_pro = new Datas_Product();
        $data = $data_pro->cart($params);
        
        $data['payment_url'] = '';
        if($site_info['payment_mode'] == 'swreg')
        {
            $data['payment_url'] = empty($data['swreg']) ?  '' : $data['swreg'];
        }
        elseif($site_info['payment_mode'] == 'avangate')
        {
            $data['payment_url'] = empty($data['avangate']) ? '' : $data['avangate'];
        }
        
        return $data;
    }
    
    /**
     * 获取绑定产品购物车列表
     * {store::bind_product_cart pro_id="1" cbs_id="123" price="25.00"/}
     * 页面参数   pro_id         产品ID
     * 页面参数   cbs_id     CBSID
     * 页面参数   price     产品价格
     * @param array $params
     */
    public function bind_product_cart($params)
    {
        $type = !empty($params['type']) ? $params['type'] : 'swreg';
        $data = array();
        switch(trim($type))
        {
            case 'swreg' :
                $data =  $this->_bindCartSwreg($params);
                break;
            case 'avangate' :
                $data =  $this->_bindCartAvangate($params);
                break;
        }
        return $data;
    }
    
    /**
     * 获取Swreg绑定产品购物车数据
     * @param array $params
     */
    private function _bindCartSwreg($params)
    {
        extract($params);
        if(empty($pro_id) || empty($cbs_id) || empty($price))
        {
            echo '没有发现产品ID或CBSID或产品价格';
            return array();
        }
        
        $start_swreg_url = !empty($params['start_swreg_url']) ? $params['start_swreg_url'] : 'https://usd.swreg.org/cgi-bin/s.cgi?s=46639&q=1&d=0&p=';
        $currency = !empty($currency) ? $currency : 'USD' ; 
        $num = isset($num) ? intval($num) : 0;
        
        $tem_array = $this->_getBindData($cbs_id, $currency, $num);
        if(empty($tem_array))
        {
            return array();
        }
       
        $new_sum_price = $price;
        $old_sum_price = 0;
        $bind_cbs_ids = $tem_array['cbs_ids'];
        $cbs_ids = $cbs_id.','.$bind_cbs_ids;
        $bind_data = $tem_array['data'];
        unset($tem_array);
        
        //获取主产品的子sub_license_id
        $sql = "SELECT `sub_license_id` FROM `product_licenses` WHERE `pro_id`={$pro_id} AND `license_id`=11 AND `website_display`='1'";
        $row = $this->fetchRow($sql);
        $main_sub_license_id = $row['sub_license_id'];
        
        //获取产品的拼接swreg链接
        $sql = "SELECT `pro_id`, `cbs_id`
                FROM `product_customs` 
                WHERE `cbs_id` IN ({$cbs_ids}) AND `site_id` = {$site_id} AND `state` = 'able'
                ORDER BY FIND_IN_SET(`cbs_id`, '{$cbs_ids}')";
        $rows = $this->fetchAll($sql);
        $bind_product_data = array();
        $one_pro_id = $swreg_v_value = $swreg_p_value = $s = $s2 = $lic_ids = $main_p_value = $main_v_value = '';
        foreach ($rows AS $key => $value)
        {
            //设置主产品的sub_license_id
            $sub_license_id = $main_sub_license_id;
            if($key == 1)//设置第一个绑定产品的产品ID
            {
                $one_pro_id = $value['pro_id'];
            }
            
            if(isset($bind_data[$value['cbs_id']]))//是否为绑定产品
            {
                $new_sum_price += $bind_data[$value['cbs_id']]['new_price'];
                //设置绑定产品信息
                $bind_product_data[$value['pro_id']]['bind_info'] = $bind_data[$value['cbs_id']];
                //设置绑定产品的sub_license_id
                $sub_license_id = $bind_data[$value['cbs_id']]['sub_license_id'];
            }
            
            //获取产品的拼接swreg链接以及老价格的lic_ids
           $sql = "SELECT `lic_id`, `pro_id`, `swreg_url`, `sub_license_id`
                    FROM `product_licenses`
                    WHERE `pro_id` = {$value['pro_id']} AND `license_id`=11 AND `sub_license_id` IN(0, {$sub_license_id})";
            $row2 = $this->fetchAll($sql);
            foreach($row2 AS $value2)
            {
                if($value2['sub_license_id'] == 0)
                {
                    $lic_ids .= $s.$value2['lic_id'];//获取老价格用
                    $bind_product_data[$value2['pro_id']]['lic_id'] = $value2['lic_id'];
                    $s = ',';
                }
                if($value2['sub_license_id'] == $sub_license_id)//做拼接swreg链接用
                {
                    if(isset($swreg_url) && $value2['pro_id'] == $pro_id)
                    {
                        $value2['swreg_url'] = $swreg_url;
                    }
                    parse_str($value2['swreg_url'], $tem_array);
                    $p_value = !empty($tem_array['p']) ? $tem_array['p'] : '0';
                    $v_value = !empty($tem_array['v']) ? $tem_array['v'] : '0';
                    
                    if($value2['sub_license_id'] == $main_sub_license_id)//为主产品
                    {
                        $main_p_value = $p_value;
                        $main_v_value = $v_value;
                    }
                    else 
                    {
                        $bind_product_data[$value2['pro_id']]['bind_info']['p_value'] = $p_value;
                        $bind_product_data[$value2['pro_id']]['bind_info']['v_value'] = $v_value;
                    }
                    $swreg_p_value .= $s2.$p_value;
                    $swreg_v_value .= $s2.$v_value;
                    $s2 = ':';
                }
            }
        }

        $swreg_url = "{$start_swreg_url}{$swreg_p_value}&v={$swreg_v_value}";

        //获取总老价格
        $sql = "SELECT `price`, `lic_id` FROM `product_prices` WHERE `lic_id` IN ({$lic_ids}) AND `currency`='{$currency}'";
        $rows = $this->fetchAll($sql);
        foreach ($rows AS $value)
        {
            $old_sum_price += $value['price'];
            foreach ($bind_product_data AS &$value2)
            {
                if(isset($value2['lic_id']) && $value2['lic_id'] == $value['lic_id'])
                {
                    $value2['bind_info']['old_price'] =  $value['price'];
                }
            }
        }
       
        $save_sum_price = $old_sum_price - $new_sum_price;
        
        unset($bind_product_data[$pro_id]);
        if(empty($bind_product_data))
        {
            return array();
        }
            
        $return_array = array(
                    'swreg_url' => $swreg_url,
                    'band_cbs_ids' => $bind_cbs_ids,
                    'main_p_value' => $main_p_value,
                    'main_v_value' => $main_v_value,
                    'main_sub_license_id' => $main_sub_license_id,
                    'main_combination'  => $cbs_id.'_11_'.$main_sub_license_id,
                    'bind_product_data' => $bind_product_data, 
                    'old_sum_price' => sprintf('%0.2f', $old_sum_price),
                    'new_sum_price' => sprintf('%0.2f', $new_sum_price),
                    'save_sum_price' => sprintf('%0.2f',$save_sum_price), 
                    'nums' => count($bind_product_data),
                    'one_pro_id' => $one_pro_id
        );
        return $return_array;
    }
	
    /**
     * 获取Avangate绑定产品购物车数据
     * @param array $params
     */
    private function _bindCartAvangate($params)
    {
        extract($params);
        
        if(empty($pro_id) || empty($cbs_id) || empty($price))
        {
            echo '没有发现产品ID或CBSID或产品价格';
            return array();
        }
        
        $currency = !empty($currency) ? $currency : 'USD' ;
        $num = isset($num) ? intval($num) : 0;
        
        $tem_array = $this->_getBindData($cbs_id, $currency, $num);
        if(empty($tem_array))
        {
            return array();
        }
        
        $syn_cfg = Cms_Func::getConfig('cbs', 'url');
        $syn_avangate_url = $syn_cfg->avangate_url;
        $new_sum_price = $price;
        $old_sum_price = 0;
        $bind_cbs_ids = $tem_array['cbs_ids'];
        $cbs_ids = $cbs_id.','.$bind_cbs_ids;
        $bind_data = $tem_array['data'];
        unset($tem_array);
        
        //获取主产品的子sub_license_id
        $sql = "SELECT `sub_license_id` FROM `product_licenses` WHERE `pro_id`={$pro_id} AND `license_id`=11 AND `website_display`='1'";
        $row = $this->fetchRow($sql);
        $main_sub_license_id = $row['sub_license_id'];
       
        $sql = "SELECT *, `page_url` AS `url`
                FROM `product_customs` 
                WHERE `cbs_id` IN ({$cbs_ids}) AND `site_id` = {$site_id} AND `state` = 'able'
                ORDER BY FIND_IN_SET(`cbs_id`, '{$cbs_ids}')";
        $rows = $this->fetchAll($sql);
        
        $bind_product_data = array();
        $s = $lic_ids = '';
        $all_avangate_price = $price;
        $all_avangate_cbsids = $cbs_id;
        
        foreach ($rows AS $key => $value)
        {
            if(isset($bind_data[$value['cbs_id']]))//是否为绑定产品
            {
                if(!empty($brand))
                {
                    $t = trim($brand) . ' ';
                    $value['name'] = str_replace($t, '', $value['name']);
                }
                $bind_new_price = $bind_data[$value['cbs_id']]['new_price'];
                $all_avangate_cbsids .= ','.$value['cbs_id'];
                $all_avangate_price .= ','.$bind_new_price;
                $new_sum_price += $bind_new_price;
                $bind_product_data[$value['pro_id']]['pro_info'] = $value;
                
                //设置绑定产品信息
                $bind_product_data[$value['pro_id']]['bind_info'] = $bind_data[$value['cbs_id']];
                
                //获取绑定的avangate链接
                $avangate_price = "{$price},{$bind_new_price}";
                $avangate_cbsids = "{$cbs_id},{$value['cbs_id']}";
                $bind_product_data[$value['pro_id']]['avangate_url'] = file_get_contents("{$syn_avangate_url}&prices={$avangate_price}&product_ids={$avangate_cbsids}&curr={$currency}");
            }
            
            //获取产品的老价格的lic_ids
            $sql = "SELECT `lic_id`, `pro_id`, `swreg_url`
                    FROM `product_licenses`
                    WHERE `pro_id` = {$value['pro_id']} AND `license_id`=11 AND `sub_license_id` = 0";
            $row2 = $this->fetchAll($sql);
            foreach($row2 AS $value2)
            {
                $lic_ids .= $s.$value2['lic_id'];//获取老价格用
                if(!empty($bind_product_data[$value2['pro_id']]))
                {
                    $bind_product_data[$value2['pro_id']]['lic_id'] = $value2['lic_id'];
                }
                $s = ',';
            }
        }
        
        $all_avangate_url = file_get_contents("{$syn_avangate_url}&prices={$all_avangate_price}&product_ids={$all_avangate_cbsids}&curr={$currency}");
        
        if(empty($all_avangate_url) || substr_count($all_avangate_url, 'https://secure.avangate.com/order/checkout.php') == 0)
        {
            return array();
        }
        
        //获取总老价格
        $sql = "SELECT `price`, `lic_id` FROM `product_prices` WHERE `lic_id` IN ({$lic_ids}) AND `currency`='{$currency}'";
        $rows = $this->fetchAll($sql);
        foreach ($rows AS $value)
        {
            $old_sum_price += $value['price'];
            foreach ($bind_product_data AS &$value2)
            {
                if(isset($value2['lic_id']) && $value2['lic_id'] == $value['lic_id'])
                {
                    $value2['bind_info']['old_price'] =  $value['price'];
                }
            }
        }
        
        //获取保存价格
        $save_sum_price = $old_sum_price - $new_sum_price;
        
        unset($bind_product_data[$pro_id]);
        if(empty($bind_product_data))
        {
            return array();
        }
            
        $return_array = array(
            'all_avangate_url' => $all_avangate_url,
            'band_cbs_ids' => $bind_cbs_ids,
            'main_sub_license_id' => $main_sub_license_id,
            'main_combination'  => $cbs_id.'_11_'.$main_sub_license_id,
            'bind_product_data' => $bind_product_data, 
            'old_sum_price' => sprintf('%0.2f', $old_sum_price),
            'new_sum_price' => sprintf('%0.2f', $new_sum_price),
            'save_sum_price' => sprintf('%0.2f',$save_sum_price),
            'nums' => count($bind_product_data)
        );
        return $return_array;
    }
	
    /**
     * 获取绑定产品数据
     * $cbs_id cbsID
     * $currency 货币类型
     * 
     */
    private function _getBindData($cbs_id, $currency, $num)
    {
        $public_cache_model = new Public_Models_Cache();
        $bind_product_data = $public_cache_model->getBindProduct();
        $bind_product_data = isset($bind_product_data[$cbs_id]) ? $bind_product_data[$cbs_id] : array() ;
        if(empty($bind_product_data))
        {
            return array();
        }
        
        //获取绑定产品的信息
        $bind_data = array();
        $bind_cbs_ids = '';
        $s = '';
        $i = 1;
        foreach ($bind_product_data AS $key => $value)
        {
            $tem_array  = explode ('_', $key);
            $bind_cbs_ids .= $s.$tem_array[0];
            $bind_data[$tem_array[0]] = array('new_price' => $value[$currency], 'sub_license_id' => $tem_array[2], 'cbs_id' => $tem_array[0]);
            if($num!= 0 &&  $i == $num)
            {
                break;
            }
            $s = ',';
            $i++;
        }
        return array('cbs_ids' => $bind_cbs_ids, 'data' => $bind_data);
    }
}