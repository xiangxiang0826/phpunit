<?php

class ZendX_Array
{
	/**
	 * @var  string  default delimiter for path()
	 */
	public static $delimiter = '.';
	
	/**
	 * Merges one or more arrays recursively and preserves all keys.
	 * Note that this does not work the same as [array_merge_recursive](http://php.net/array_merge_recursive)!
	 *
	 * $john = array('name' => 'john', 'children' => array('fred', 'paul', 'sally', 'jane'));
	 * $mary = array('name' => 'mary', 'children' => array('jane'));
	 *
	 * // John and Mary are married, merge them together
	 * $john = Arr::merge($john, $mary);
	 *
	 * // The output of $john will now be:
	 * array('name' => 'mary', 'children' => array('fred', 'paul', 'sally', 'jane'))
	 *
	 * @param   array  initial array
	 * @param   array  array to merge
	 * @param   array  ...
	 * @return  array
	 */
	public static function merge(array $a1 ,array $a2)
	{
		$result = array();
		for ($i = 0, $total = func_num_args(); $i < $total; $i ++)
		{
			// Get the next array
			$arr = func_get_arg($i);
			// Is the array associative?
			$assoc = self::is_assoc($arr);
			foreach ($arr as $key => $val)
			{
				if (isset($result[$key]))
				{
					if (is_array($val) and is_array($result[$key]))
					{
						if (self::is_assoc($val))
						{
							// Associative arrays are merged recursively
							$result[$key] = self::merge($result[$key], $val);
						}
						else
						{
							// Find the values that are not already present
							$diff = array_diff($val, $result[$key]);
							// Indexed arrays are merged to prevent duplicates
							$result[$key] = array_merge($result[$key], $diff);
						}
					}
					else
					{
						if ($assoc)
						{
							// Associative values are replaced
							$result[$key] = $val;
						}
						elseif (! in_array($val, $result, TRUE))
						{
							// Indexed values are added only if they do not yet exist
							$result[] = $val;
						}
					}
				}
				else
				{
					// New values are added
					$result[$key] = $val;
				}
			}
		}
		return $result;
	}



	/**
	 * Tests if an array is associative or not.
	 *
	 * // Returns TRUE
	 * Arr::is_assoc(array('username' => 'john.doe'));
	 *
	 * // Returns FALSE
	 * Arr::is_assoc('foo', 'bar');
	 *
	 * @param   array   array to check
	 * @return  boolean
	 */
	public static function is_assoc (array $array)
	{
		// Keys of the array
		$keys = array_keys($array);
		// If the array keys of the keys match the keys, then the array must
		// not be associative (e.g. the keys array looked like {0:0, 1:1...}).
		return array_keys($keys) !== $keys;
	}
	
	/**
	 * Convert a multi-dimensional array into a single-dimensional array.
	 *
	 * $array = array('set' => array('one' => 'something'), 'two' => 'other');
	 *
	 * // Flatten the array
	 * $array = Arr::flatten($array);
	 *
	 * // The array will now be
	 * array('one' => 'something', 'two' => 'other');
	 *
	 * [!!] The keys of array values will be discarded.
	 *
	 * @param   array   array to flatten
	 * @return  array
	 * @since   3.0.6
	 */
	public static function flatten ($array)
	{
		$flat = array();
		foreach ($array as $key => $value)
		{
			if (is_array($value))
			{
				$flat += self::flatten($value);
			}
			else
			{
				$flat[$key] = $value;
			}
		}
		return $flat;
	}
	/**
	 * Retrieve a single key from an array. If the key does not exist in the
	 * array, the default value will be returned instead.
	 *
	 * // Get the value "username" from $_POST, if it exists
	 * $username = Arr::get($_POST, 'username');
	 *
	 * // Get the value "sorting" from $_GET, if it exists
	 * $sorting = Arr::get($_GET, 'sorting');
	 *
	 * @param   array   array to extract from
	 * @param   string  key name
	 * @param   mixed   default value
	 * @return  mixed
	 */
	public static function get ($array ,$key ,$default = NULL)
	{
		return isset($array[$key]) ? $array[$key] : $default;
	}
	/**
	 * Gets a value from an array using a dot separated path.
	 *
	 * // Get the value of $array['foo']['bar']
	 * $value = Arr::path($array, 'foo.bar');
	 *
	 * Using a wildcard "*" will search intermediate arrays and return an array.
	 *
	 * // Get the values of "color" in theme
	 * $colors = Arr::path($array, 'theme.*.color');
	 *
	 * // Using an array of keys
	 * $colors = Arr::path($array, array('theme', '*', 'color'));
	 *
	 * @param   array   array to search
	 * @param   mixed   key path string (delimiter separated) or array of keys
	 * @param   mixed   default value if the path is not set
	 * @param   string  key path delimiter
	 * @return  mixed
	 */
	public static function path ($array ,$path ,$default = NULL ,$delimiter = NULL)
	{
		if (! self::is_array($array))
		{
			// This is not an array!
			return $default;
		}
		if (is_array($path))
		{
			// The path has already been separated into keys
			$keys = $path;
		}
		else
		{
			if (array_key_exists($path, $array))
			{
				// No need to do extra processing
				return $array[$path];
			}
			if ($delimiter === NULL)
			{
				// Use the default delimiter
				$delimiter = self::$delimiter;
			}
			// Remove starting delimiters and spaces
			$path = ltrim($path, "{$delimiter} ");
			// Remove ending delimiters, spaces, and wildcards
			$path = rtrim($path, "{$delimiter} *");
			// Split the keys by delimiter
			$keys = explode($delimiter, $path);
		}
		do
		{
			$key = array_shift($keys);
			if (ctype_digit($key))
			{
				// Make the key an integer
				$key = (int) $key;
			}
			if (isset($array[$key]))
			{
				if ($keys)
				{
					if (self::is_array($array[$key]))
					{
						// Dig down into the next part of the path
						$array = $array[$key];
					}
					else
					{
						// Unable to dig deeper
						break;
					}
				}
				else
				{
					// Found the path requested
					return $array[$key];
				}
			}
			elseif ($key === '*')
			{
				// Handle wildcards
				$values = array();
				foreach ($array as $arr)
				{
					if ($value = Arr::path($arr, implode('.', $keys)))
					{
						$values[] = $value;
					}
				}
				if ($values)
				{
					// Found the values requested
					return $values;
				}
				else
				{
					// Unable to dig deeper
					break;
				}
			}
			else
			{
				// Unable to dig deeper
				break;
			}
		}
		while ($keys);
		// Unable to find the value requested
		return $default;
	}
	/**
	 * Test if a value is an array with an additional check for array-like objects.
	 *
	 * // Returns TRUE
	 * Arr::is_array(array());
	 * Arr::is_array(new ArrayObject);
	 *
	 * // Returns FALSE
	 * Arr::is_array(FALSE);
	 * Arr::is_array('not an array!');
	 * Arr::is_array(Database::instance());
	 *
	 * @param   mixed    value to check
	 * @return  boolean
	 */
	public static function is_array ($value)
	{
		if (is_array($value))
		{
			// Definitely an array
			return TRUE;
		}
		else
		{
			// Possibly a Traversable object, functionally the same as an array
			return (is_object($value) and $value instanceof Traversable);
		}
	}
	
	/**
	 * 将一个平面的二维数组按照指定的字段转换为树状结构
	 *
	 * 当 $returnReferences 参数为 true 时，返回结果的 tree 字段为树，refs 字段则为节点引用。
	 * 利用返回的节点引用，可以很方便的获取包含以任意节点为根的子树。
	 *
	 * @param array $arr 原始数据
	 * @param string $fid 节点ID字段名
	 * @param string $fparent 节点父ID字段名
	 * @param string $fchildrens 保存子节点的字段名
	 * @param int $node 某个树节点ID
	 * @param boolean $getSubTree 是否返回指定节点的子树
	 * @param boolean $returnReferences 是否在返回结果中包含节点引用
	 *
	 * return array
	 */
	public static function array_to_tree($arr, $fid, $fparent = 'parent_id', $fchildrens = 'childrens', $node = 0, $getSubTree = false, $returnReferences = false) {
		$pkvRefs = array();
		foreach ($arr as $offset => $row) {
			$pkvRefs[$row[$fid]] =& $arr[$offset];
		}
		$tree = array();
		$subTree = array();
		foreach ($arr as $offset => $row) {
			$parentId = $row[$fparent];
			if ($parentId) {
				if (!isset($pkvRefs[$parentId])) {
					continue;
				}
				$parent =& $pkvRefs[$parentId];
				$parent[$fchildrens][] =& $arr[$offset];
			} else {
				$tree[] =& $arr[$offset];
			}
			if ($getSubTree && $node == $row[$fid]) {
				$subTree =& $arr[$offset];
			}
		}
		if ($getSubTree) {
			return $subTree;
		}
		if ($returnReferences) {
			return array('tree' => $tree, 'refs' => $pkvRefs);
		} else {
			return $tree;
		}
	}
	/**
	 * 返回一个多维数组的所有值组成的二维数组
	 *
	 * @param array $arrin
	 * @param array $arrout
	 * @return $arrout
	 */
	private static function _expArray($arrin, $arrout = array() ) {
		foreach ($arrin as $value) {
			if(is_array($value)){
				$arrout = self::_expArray($value, $arrout);
			}else{
				$arrout[] = $value;
			}
		}
		return $arrout;
	}
	
	/**
	 * 把一个Tree形数组转换成一个一维数组，用于方便地显示
	 *
	 * @param array  $tree 原始Tree形数组
	 * @param array  $arr 二维数组
	 * @param string $level 目录深度
	 * @param string $T 上下均有项目的符号，可以是图片路径
	 * @param string $L 这一级别中最末尾项目的符号，可以是图片路径
	 * @param string $I 上级连接符，可以是图片路径
	 * @param string $S 占位的空白符号，可以是图片路径
	 *
	 * 类似下面的效果
	 * ├- 1
	 * │  ├- 1.1
	 * │  └- 1.2
	 * └- 2
	 */
	public static function dumpArrayTree($tree, $level=0, $T='├', $L='└', $I='│', $S='　') {
		return self::_makeLevelstr(self::_dumpArrayTree($tree, array(), $level, $T, $L, $I, $S), $T, $L, $I, $S);
	}
	
	
	private static function _dumpArrayTree($tree, $arr=array(), $level=0, $T='├', $L='└', $I='│', $S='　') {
		foreach ($tree as $node) {
	
			$arr[] = $node;
			$arr[count($arr)-1]['level'] = $level;
	
			//如果存在下级类目，则去掉该键值，并加深一层类目深度
			if(isset($arr[count($arr)-1]['childrens'])) {
				unset($arr[count($arr)-1]['childrens']);
				$level = $level + 1;
			}
	
			//如果childrens仍有数据则递归一下
			if (isset($node['childrens'])) {
				$arr = self::_dumpArrayTree($node['childrens'], $arr, $level, $T, $L, $I, $S);
				$level = $level - 1;
			}
	
		}
	
		return $arr;
	
	}
	
	
	private static function _makeLevelstr($arr, $T, $L, $I, $S) {
	
		foreach ($arr as $key=>$value) {
			$arr[$key]['levelstr'] = '';
	
			//向下循环到数组尾部，寻找层特征
			$k = 0;
			$haveBrother = false;
			for($k=$key; $k<count($arr); $k++){
					
				if(isset($arr[$k+1])) {
	
					//有平级目录
					if($arr[$key]['level'] == $arr[$k+1]['level']){
						$haveBrother = true;
					}
					//本级别结束
					if($arr[$key]['level'] > $arr[$k+1]['level']) {
						break;
					}
				}
					
			}
	
			if ($haveBrother) {
				$arr[$key]['levelstr'] = $T;
				$arr[$key]['isend'] = false;
			}else {
				$arr[$key]['levelstr'] = $L;
				// isend 为 true 意味着这个节点是本级最后一个节点
				$arr[$key]['isend'] = true;
			}
	
	
			// $spaceHere 用来记录连接线的形态，表示当前行第几级是空白
			$spaceHere = array();
	
			// 逐级向上循环
			for($k=$key-1;$k>=0;$k=$k-1){
	
				//如果$k是同级尾部isend=true
				if($arr[$k]['isend']){
					$spaceHere[$key][$arr[$k]['level']] = true;
				}
				// 判断到根后中断循环
				if($arr[$k]['level']==0){
					break;
				}
					
			}
	
			//根据级别判定显示连接线的显示
			$frontLine = '';
			for($j=0; $j<$value['level']; $j++){
				if(isset($spaceHere[$key][$j])){
					$frontLine .= $S . $S;
				}else{
					$frontLine .= $I . $S;
				}
			}
			$arr[$key]['levelstr'] = $frontLine . $arr[$key]['levelstr'];
		}
		return $arr;
	}


}