<?php
/**
 * 设备报修处理记录
 * @author zhouxh
 *
 */
class Model_DeviceRepairRecord extends ZendX_Model_Base {
	/**
	 * 数据库配置
	 * @var string
	 */
	protected $_schema = 'cloud';
	/**
	 * 表名
	 * @var string
	 */
	protected $_table = 'EZ_SERVICE_REPAIR_RECORD';
	
	/**
	 * 查询处理记录列表
	 * @param int $repaireId
	 * @return Ambigous <multitype:, multitype:mixed Ambigous <string, boolean, mixed> >
	 */
	public function getRecordList($repaireId) {
		$select = $this->get_db()->select();
		$select->from(array('t' => $this->_table));
		$select->where('repair_id = ?', $repaireId);
		$select->order('t.id DESC');
		$result = $this->get_db()->fetchAll($select);
		return $result;
	}
	
	/**
	 * 添加处理
	 * @param string $content
	 * @param int $repairId
	 * @param string $status
	 * @param string $name
	 */
	public function addRecord($content, $repairId, $status, $name = '系统'){
		$tr = $this->get_db()->beginTransaction();
		try {
			$data = array(
					'content' => $content,
					'repair_id' => $repairId,
					'status' => $status,
					'type' => 'system',
					'name' => $name,
					'ctime' => date('Y-m-d H:i:s', time())
			);
			$res = $this->insert($data);
			$this->get_db()->update('EZ_SERVICE_REPAIR', array('mtime' => date('Y-m-d H:i:s', time()),  'status'=> $status), array('id = ?' => $repairId));
			$tr->commit();
			return $res; 
		} catch (Exception $e) {
			$tr->rollBack();
		}
	}
}