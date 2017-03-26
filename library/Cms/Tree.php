<?php

/**
 * Tree
 * 
 * @author 刘通
 */
class Cms_Tree
{
	/**
	 * 把数组形式转换成tree格式
	 * 
	 * @param array $nodes
	 * @example:
	 * 		$nodes = array(
	 * 			[0] => array(
	 * 				array('id' => 1),
	 * 				array('id' => 2),
	 * 			),
	 * 			[1]	=> array(
	 * 				array('id' => 3),
	 * 				array('id' => 4),
	 * 			),
	 * 			[2] => array(
	 * 				array('id' => 5),
	 * 				array('id' => 6),
	 * 			),
	 * 			[4] => array(
	 * 				array('id' => 7),
	 * 			),
	 * 		);
	 * 		=>
	 * 		array(
	 * 			array(
	 * 				'id' => 1,
	 * 				'children' => array(
	 * 					array('id' => 3),
	 * 					array(
	 * 						'id' => 4
	 * 						'children' => array(
	 * 							array('id' => 7),
	 * 						),
	 * 					),
	 * 				)
	 * 			),
	 * 			array(
	 * 				'id' => 2,
	 * 				'children' => array(
	 * 					array('id' => 5),
	 * 					array('id' => 6),
	 * 				),
	 * 			),
	 * 		);
	 */
	function arrayToTree($nodes)
	{
		foreach($nodes as &$node)
		{
			foreach($node as &$n)
			{
				if(isset($nodes[$n['id']]))
				{
					$n['children'] = &$nodes[$n['id']];
				}
			}				
		}
		return isset($nodes[0]) ? $nodes[0] : array();
	}
	
	/**
	 * 把数组中的数据，根据父子关系，处理出每项的所有父节点和所有子节点
	 * 
	 * @param array $data
	 * @example:
	 * 		$data = array(
	 * 			array('id' => 1, 'parent_id' => 0),
	 * 			array('id' => 2, 'parent_id' => 0),
	 * 			array('id' => 3, 'parent_id' => 1),
	 * 			array('id' => 4, 'parent_id' => 1),
	 * 			array('id' => 5, 'parent_id' => 2),
	 * 			array('id' => 6, 'parent_id' => 2),
	 * 			array('id' => 7, 'parent_id' => 4),
	 * 		);
	 * 		=>
	 * 		array(
	 * 			'parents' => array(
	 * 				1 => array(0),
	 * 				2 => array(0),
	 * 				3 => array(1, 0),
	 * 				4 => array(1, 0),
	 * 				5 => array(2, 0),
	 * 				6 => array(2, 0),
	 * 				7 => array(4, 1, 0),
	 * 			),
	 * 			'children' => array(
	 * 				0 => array(1, 2, 3, 4, 5, 6, 7),
	 * 				1 => array(3, 4, 7),
	 * 				2 => array(5, 6),
	 * 				3 => array(),
	 * 				4 => array(7),
	 * 				5 => array(),
	 * 				6 => array()
	 * 				7 => array(),
	 * 			),
	 * 		);
	 * @param string $id_key	$data中的主键，在本系统中parent_id不用传
	 */
	function treeInherit(array $data_arr, $id_key='id')
	{
		//新建以id为键的数组
		$sort_arr = array();
		foreach($data_arr as $data)
		{
			$sort_arr[$data[$id_key]] = $data;
		}
		
		$parents = $children = array();
		foreach($data_arr as $data)
		{
			$tmp = $data;
			$parents[$data[$id_key]] = array();
			/**
			 +-------------------------------------------------------------------------
			 | 这里因为用到了循环，如果数据库中的数据能做到很好的控制，是不会出什么问题的。
			 | 但是如果有人手动修改了数据库的id或pid，导致出现死循环就有问题了。有两种情况：
			 | 1.parent_id=id 这个可以用一个判断 if($data['id'] == $data['parent_id'])break;
			 | 2.parent_id 在一个循环中，但是没有包含到0, 
			 | 					这个可以用 if(in_array($parent_id, $parents)) break;
			 | 这两种方法是处理出现数据错误的时候用到的，此时生成的数据肯定也是错误的，
			 | 					但是可以保证不会陷在死循环中
			 | 我把代码写在下边，因为出现数据错误的可能性极小，就先注释掉了，如果需要可打开。
			 | 
			 | 													刘通		2012-05-12	注
			 +-------------------------------------------------------------------------
			 */
			//if($data['parent_id'] == $data['id']) break;
			while(($parent_id = $tmp['parent_id'])>0)
			{
				//if(in_array($parent_id, $parents[$data['id']])) break;
				$parents[$data[$id_key]][] = $parent_id;
				$children[$parent_id][] = $data[$id_key];
				$tmp = $sort_arr[$parent_id];
			}
		}
		
		return compact('parents', 'children');
	}
}