<?php

/**
 * 模版解析类
 * 
 * @author 刘通
 */
class Cms_Template_Parse
{
	/**
	 * 基本标签
	 * 
	 * @var array
	 */
	private static $_rules = array(
			'/{if\s+([^{}]+?)\s*}/'						=> '<?php if($1): ?>',
			'/{else\s*}/'								=> '<?php else : ?>',
			'/{elseif\s+([^}]*?)\s*}/'					=> '<?php elseif($1) : ?>',
			'/{\/if\s*}/'								=> '<?php endif; ?>',
			'/{loop\s+([^\s}]+)\s+([^\s}]+)\s*}/i'			=> '<?php $n=1; if(is_array($1)) foreach($1 as $2) { ?>',
			'/{loop\s+([^\s}]+)\s+([^\s}]+)\s+([^\s}]+)\s*}/i' => '<?php $n=1; if(is_array($1)) foreach($1 as $2 => $3) { ?>',
			'/{\/loop\s*}/'								=> '<?php $n++;} ?>',
			'/{=([^}]+)\s*}/'							=> '<?php echo $1; ?>',
			'/{for\s+([^{}]+?)\}/'						=> '<?php for ($1): ?>',
			'/{\/for}/'									=> '<?php endfor; ?>'
	);
	
	static function compile($content, $tpl)
	{
		/** 去掉html注释，并解析基本标签 */
		$content = preg_replace(array('/<!--\s*{\s*/', '/\s*}\s*-->/'), array('{', '}'), $content);
		$content = preg_replace(array_keys(self::$_rules), self::$_rules, $content);
	
		/**
		 *  模块标签
		 *  {article::info id="40"}		{article::list num="5"} 
		 */
		$content = preg_replace_callback('/{([a-z]*[:]{2}[a-z_]+)(\s+[^}]*?)?(\/?)}/', array('self', 'parseTag'), $content);
		$content = preg_replace('/{\/([a-z:_]+)}/i', '<?php } ?>', $content);
		
		/**
		 * 简写标签 
		 * 		{>>root_cat_name?id=123}
		 */
		$content = preg_replace_callback('/{>>([a-z_]+)\?pro_id=([^{}]+)}/', array('self', 'echoTag'), $content);
		
		/** 注释标签 */
		$content = preg_replace('/{\/\*+[^\*\/]*\*\/}/', '', $content);
		
		return Cms_Template_Cache::getInstance()->write($tpl, $content);
	}
	
	/** 解析mod标签 */
	static function parseTag($matches)
	{
		$return = 'r';
		$array = array();
		$tag = $matches[1];
		$str = $matches[2];
		$end = $matches[3];
	
		preg_match_all('/\s+([a-z_]+)\s*\=\s*([\"\'])(.*?)\2/i', stripslashes($str), $matches, PREG_SET_ORDER);
		foreach($matches as $k=>$v)
		{
			$v[3] = trim($v[3]);
			
			if($v[3] === '')
			{
				continue;
			}
			
			if ($v[1] == 'return')
			{
				$return = $v[3];
				continue;
			}
			
			if($v[3] && $v[3]{0} == '$')			//当前页面的实体ID和分页
			{
				$v[2] = '';
			}
			
			$array[] = "'" . $v[1] . "'" . '=>' . $v[2] . $v[3] . $v[2];
		}
		$array = implode(',', $array);
		$array .= $array ? ", 'site_id'=>\$site_id" : "'site_id'=>\$site_id";
	
		list($mod, $action) = explode('::', $tag, 2);
		if($mod)											//-->模块标签 {article::lists num="10"}
		{
			$mod_callback = self::loadObjectStr($mod, $action, $array);
			$data_arr = $return . '_' . $mod;
			$string = '<?php'."\n\$___{$data_arr} = {$mod_callback};\n";
			$string .= $end ? ("\${$return} = \$___{$data_arr};\n".'?>') : ("if(isset(\$___{$data_arr}['total'])){ extract(\$___{$data_arr}); \$___{$data_arr} = \$rows;}\n\${$return}_num=count(\$___{$data_arr});\nforeach((array)\$___{$data_arr} as \${$return}_i=>\${$return}){\n".'?>');
		}
		else												//-->其它标签 {::config module="site"}	{::page1}
		{
			if($action == 'page')
			{
				$array .= ", 'total'=>\$total, 'curr'=>\$page, 'pagesize'=>\$pagesize";
			}
			
			$array .= ", 'url'=>\$url";
			
			$string = '<?php echo ' . self::loadObjectStr('output', $action, $array) . '; ?>';
		}
	
		return $string;
	}
	
	/**
	 * 解析简写标签
	 * 
	 * @param array $matches
	 */
	static function echoTag($matches)
	{
		$action = $matches[1];
		$id = $matches[2];
		$array = "'site_id'=>\$site_id, 'pro_id'=>{$id}";
		return '<?php echo ' . self::loadObjectStr('echo', $action, $array) . '; ?>';
	}
	
	static function loadObjectStr($mod, $action, $array)
	{
		return "Cms_Template::loadDataObject('$mod')->{$action}(array({$array}))";
	}
}