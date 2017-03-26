<?php

/**
 * 直接输出的接口
 * 
 * @author 刘通
 */
class Datas_Output extends Cms_Data
{
	/**
	 * 分页函数
	 * 
	 * @param array $params
	 * 		example $params = array('total'=>'记录总数', 'curr'=>'当前页面', 'pagesize'=>'每页记录', 'url'=>'', 'site_id'=>1, prev_str="", next_str="")
	 */
	function page($params)
	{
		extract($params);
		$url = dirname($url). '/';
		
		$total_page = ceil($total / $pagesize);
		
		if(empty($prev_str))
		{
			$prev_str = 'Prev';
		}
		
		if(empty($next_str))
		{
			$next_str = 'Next';
		}
		
		$prev = $next = $page = '';
		if($curr == 1)
		{
			$prev = '<a class="bb">' . $prev_str . '</a>';
		}
		else
		{
			$prev_url = $url . ($curr - 1) . '.html';
			$prev = '<a href="'. $prev_url .'" class="bb">< ' . $prev_str . '</a>';
		}
		
		if(!$total_page OR $curr == $total_page)
		{
			$next = '<a class="bb">' . $next_str . '</a>';
		}
		else
		{
			$next_url = $url . ($curr + 1) . '.html';
			$next = '<a href="'. $next_url .'" class="bb">' . $next_str . '</a>';
		}
		
		if($curr > 5)
		{
			$start = $curr - 5;
		}
		else
		{
			$start = 1;
		}
		
		if($start + 9 < $total_page)
		{
			$end = $start + 9;
		}
		else
		{
			$end = $total_page;
		}
		
		for($i=$start; $i<=$end; $i++)
		{
			if($i == $curr)
			{
				$page .= "<a class=\"curr\">{$i}</a>";
			}
			else
			{
				$page_url = $url . $i . '.html';
				$page .= "<a href=\"{$page_url}\">{$i}</a>";
			}
		}
		
		$page_html = $prev . $page . $next;
		if(strpos($page_html, '/1.html'))
		{
			$page_html = str_replace('/1.html', '/', $page_html);
		}
		
		return '<div class="paging">' . $page_html . '</div>';
	}
	
	/**
	 * 输出块
	 * 		{::block file="header.html" url=""}
	 * 
	 * @param array $params
	 */
	function block($params)
	{
		if(empty($params['url']))
		{
			return '[没有发现URL]';
		}
		
		if(empty($params['file']))
		{
			return '[没有发现file]';
		}
		
		$count = substr_count($params['url'], '/');
		$count = $count ? $count - 1 : 0;
		$prefix = str_repeat('../', $count);
		
		$block_folder = Cms_Block::getFolder();
		
		return "<!--#include virtual=\"{$prefix}{$block_folder}/{$params['file']}\"-->";
	}
	
	/**
	 * 输出分页的canonical标签
	 * 		{::page_canonical host="$host"}
	 */
	function page_canonical($params)
	{
		extract($params);
		$dir = dirname($url);
		
		if(isset($host))
		{
			if(strncasecmp($host, 'http://', 7))
			{
				$host = 'http://' . $host;
			}
			
			$dir = $host . $dir;
		}
		
		$return = '<!--{if $page == 2}-->';
		$return .= "<link rel=\"prev\" href=\"{$dir}/\">\n";
		$return .= '<!--{elseif $page > 2}-->';
		$return .= "<link rel=\"prev\" href=\"{$dir}/{=\$page - 1}.html\">\n";
		$return .= '<!--{/if}-->';
		
		$return .= '<!--{if $page == 1}-->';
		$return .= "<link rel=\"canonical\" href=\"{$dir}/\">\n";
		$return .= '<!--{else}-->';
		$return .= "<link rel=\"canonical\" href=\"{$dir}/{=\$page}.html\">\n";
		$return .= '<!--{/if}-->';
		
		$return .= '<!--{if ceil($total / $pagesize) > $page}-->';
		$return .= "<link rel=\"next\" href=\"{$dir}/{=\$page + 1}.html\">\n";
		$return .= '<!--{/if}-->';
		
		return $return;
	}
}