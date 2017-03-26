<?php

/**
 * 扩展分类Model
 *
 * @author liujd<liujd@wondershare.cn>
 * @date 2014-07-04
 */
class Model_Announce_Test extends EZX_Framework_DatabaseTestCase {

    /**
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    public function getDataSet(){
        $ds =   $this->createFlatXMLDataSet(dirname(__FILE__) . '/_files/announce.xml');
        $compositeDs = new PHPUnit_Extensions_Database_DataSet_CompositeDataSet(array($ds));
        return $compositeDs;
    }

    public function testModelIsEmptyAtConstruct()
    {
    	$announce = new Model_Announce(APPLICATION_ENV);
        $res = $announce->getTotal();
        $this->assertSame(1, intVal($res));
    }
}