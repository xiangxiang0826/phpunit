<?php
/* 
 * 系统模块
 *  */
class System_ApigrantController extends ZendX_Controller_Action
{
	
    /**
     * 厂商列表
     */
	public function indexAction()
	{		
		$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
		$rows = isset($_GET['rows']) ? intval($_GET['rows']) : $this->page_size;
		$offset = ($page-1)*$rows;
		$query = "";
		$arrQuery = array();
		$search = $this->_request->getParam("search");
		
        // 
		if(!empty($search['status'])){
			$queryString = "a.status = '". $search['status'] ."'";
			array_push($arrQuery, $queryString);
        }else{
            $queryString = "(a.status  IN ( 'enable', 'locked', 'deleted')  )";
            array_push($arrQuery, $queryString);
        }
		if(!empty($arrQuery)){
			$query = implode(" AND ", $arrQuery);
		}
		
		$result = array();
		// $Enterprise_Models_Account = new Model_Enterprise();
        $Model_ApiEnterpriseGrant = new Model_ApiEnterpriseGrant();
		$row = $Model_ApiEnterpriseGrant->getTotalMix($query);
		$result["total"] = $row[0]['total'];
		$items = $Model_ApiEnterpriseGrant->getListMix($query, $offset, $rows);
		$result['rows'] = $items;
		$pagenation = new Zend_Paginator(new Zend_Paginator_Adapter_Null($result["total"]));
		$pagenation->setCurrentPageNumber($page)->setItemCountPerPage($rows);
		$this->view->results =  $result;
		$this->view->pagenation = $pagenation;
		$this->view->search = $search;
	}
}