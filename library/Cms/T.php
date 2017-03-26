<?php

/**
 * 封装的模版用到的函数，取Tool小工具的第一个字母，是为了简短易书写
 * 
 * @author 刘通
 */
class Cms_T
{
	/**
	 * 根据平均星数，获取星号代码
	 * 
	 * @param double $star
	 */
	static function star($star)
	{
		$base = $star / 0.5;
		$on = intval($base / 2);
		$star_html = str_repeat('<li class="on"></li>', $on);
		if($half = $base % 2) {
			$star_html .= '<li class="half"></li>';
		}
		if($off = 5 - $on - $half) {
			$star_html .= str_repeat('<li class="off"></li>', $off);
		}
		
		return $star_html;
	}
	
	/**
	 * 根据产品星级信息得到星级下拉样式
	 *
	 * @param double $star
	 */
	static function starDropDown($star_array)
	{
	    //获取平均星级
	    $total = $star_array['star_1'] + 2 * $star_array['star_2'] + 3 * $star_array['star_3'] + 4 * $star_array['star_4'] + 5 * $star_array['star_5'];
	    $star_num = $star_array['star_1'] + $star_array['star_2'] + $star_array['star_3'] + $star_array['star_4'] + $star_array['star_5'];
	    if($star_num > 0)
	    {
	        $avg_star = $total / $star_num;
	    }
	    else
	    {
	        $avg_star = 0;
	    }
	    
	    $html = '<div class="distr">
                    <a href="javascript:void(0)" class="showBtn"></a>
                    <div class="distrArea">
                        <p class="distrAreaTitle">Ratings Distrubution</p>
                    	<ul class="vote-box-list clearfix" id="appVoteBox">   
                    		<li class="vl-item" id="voteItem0" >
                    			<div class="vote-item-wrap"> 
                    	            <span class="xx">5 Star</span>
                    				<div class="litem"><em class="vright"></em><span></span><em class="vright"></em></div>
                    				<div class="data"></div>
                    			</div>
                    		</li>
                    		<li class="vl-item" id="voteItem1" >
                    			<div class="vote-item-wrap"> 
                                    <span class="xx">4 Star</span>
                    				<div class="litem"><em class="vright"></em><span></span><em class="vright"></em></div>
                    				<div class="data"></div>
                    			</div>
                    		</li>
                    		<li class="vl-item" id="voteItem2" >
                    			<div class="vote-item-wrap">
                                    <span class="xx">3 Star</span>
                    				<div class="litem"><em class="vright"></em><span></span><em class="vright"></em></div>
                    				<div class="data"></div>
                    		    </div>
                    		</li>
                    		<li class="vl-item" id="voteItem3">
                    			<div class="vote-item-wrap"> 
                    	            <span class="xx">2 Star</span>
                    				<div class="litem"><em class="vright"></em><span></span><em class="vright"></em></div>
                    				<div class="data"></div>
                    			</div>
                    		</li>
                    		<li class="vl-item" id="voteItem4" >
                    			<div class="vote-item-wrap"> 
                    	            <span class="xx">1 Star</span>
                    				<div class="litem"><em class="vright"></em><span></span><em class="vright"></em></div>
                    				<div class="data"></div>
                    			</div>
                    		</li>
                    	</ul>
                      </div>
                    <a class="hideBtn" style="display: none;" href="javascript:void(0)"></a>
                </div>
	            <script type="text/javascript">
            		var vote_data=[];
            		vote_data["item_1"]=' . (empty ( $star_array['star_5'] ) ? 0 : $star_array['star_5']) . ';
            		vote_data["item_2"]=' . (empty ( $star_array['star_4'] ) ? 0 : $star_array['star_4']) . ';
            		vote_data["item_3"]=' . (empty ( $star_array['star_3'] ) ? 0 : $star_array['star_3']) . ';
            		vote_data["item_4"]=' . (empty ( $star_array['star_2'] ) ? 0 : $star_array['star_2']) . ';
            		vote_data["item_5"]=' . (empty ( $star_array['star_1'] ) ? 0 : $star_array['star_1']) . ';
            		var star_avg = ' . (empty ( $avg_star ) ? 0 : $avg_star) . ';
        		</script>';
	
	    return $html;
	}
	
	/**
	 * 处理URL，主要是去掉/index.html
	 */
	static function formatURL($url)
	{
		if(strpos($url, '/index.html') !== false)
		{
			$site_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('site_id');
			$site_info = Cms_Site::getInfoById($site_id);
			if($site_info['type'] == 'seo')
			{
				$url = str_replace('/index.html', '/', $url);
			}
		}
		
		return $url;
	}
	
	/**
	 * 截取swreg_url最后得到p=46637-WSDCST01&v=26这样
	 * @param string    swreg_url
	 * @author tjx
	 */
	static function cutSwregUrl($swreg_url)
    {
        $str = '';
        $swreg_url = trim($swreg_url);
        if(!empty($swreg_url))
        {
            $tem_array = parse_url($swreg_url);
            parse_str($tem_array['query'], $tem_array);
            $str = "p={$tem_array['p']}&v={$tem_array['v']}";
        }
        return $str;
    }
    
    /**
     * 字符串截取
     * @param string    $str //需要截取的字符串
     * @param int       $len //需要截取的长度
     * @param           $dot //截取的长度小于字符串长度需要在截取的字符串加...
     * @author tjx
     */
    static function strCut($str, $len, $dot=true)
    {
        if(strlen($str) > $len)
        {
            $pos = strpos($str, ' ', $len - 1);
            $pos = $pos ? $pos : strlen($str);
            $str = trim(substr($str, 0, $pos));
            if($dot)
            {
                $str .=  '...';
            }
        }
    
        return $str;
    }
}