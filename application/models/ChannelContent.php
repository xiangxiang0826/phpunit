<?php
/**
 * 频道内容模型
 * @author zhouxh
 *
 */
class Model_ChannelContent extends ZendX_Model_Base {
	/**
	 * 数据库
	 * @var string
	 */
	protected $_schema = 'cloud';
	/**
	 * 表名
	 * @var string
	 */
	protected $_table = 'EZ_CHANNEL_CONTENT';
	/**
	 * 状态
	 * 启用
	 * @var string
	 */
	const STATUS_ENABLE = 'enable';
	/**
	 * 禁用
	 * @var string
	 */
	const STATUS_DISABLE = 'disable';
	
	/**
	 * 创建查询条件
	 * @param array $condition
	 * @return Zend_Db_Select
	 */
	public function createSelect($condition = array()){
		$select = $this->get_db()->select();
		$select->from(array('t' => $this->_table));
		$select->order('t.id DESC');
		return $select;
	}
	
	/**
	 * 查询资讯的收藏数目
	 * @return array
	 */
	public function findAllFavNumber(){
		$sql = 'SELECT ch_content_id, COUNT(*) AS num FROM EZ_CHANNEL_FAV GROUP BY ch_content_id';
		return $this->get_db()->fetchPairs($sql);
	}
	
	/**
	 * 添加资讯
	 * @param string $title
	 * @param string $content
	 * @param string $source 资讯来源
	 * @param string $url 链接地址
	 * @param string $image
	 */
	public function addInfo($title, $content, $source, $url, $image){
		$data = array(
				'title' => $title,
				'content' => $content,
				'source' => $source,
				'url' => $url,
				'image' => $image,
				'publish_source' => 'oss',
				'status' => self::STATUS_DISABLE,
		);
		return $this->insert($data);
	}
	
	/**
	 * 编辑资讯
	 * @param string $title
	 * @param string $content
	 * @param string $source
	 * @param string $url
	 * @param string $image
	 * @param int $id 资讯ID
	 */
	public function editInfo($title, $content, $source, $url, $image, $id){
		$data = array(
				'title' => $title,
				'content' => $content,
				'source' => $source,
				'url' => $url,
				'image' => $image,
				'mtime' => date('Y-m-d H:i:s', time()),
		);
		$where = array('id =?' => $id);
		return $this->update($data, $where);
	}
	
	/**
	 * 删除资讯
	 * @param int $id
	 */
	public function deleteInfo($id) {
		$db = $this->get_db();
		return $db->delete($this->_table, array('id =?' => $id));
	}
	
	
}