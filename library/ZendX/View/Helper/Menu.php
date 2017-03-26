<?php
/**
 * 菜单自动生成助手类
 * 
 * @author etong <zhoufeng@wondershare.cn>
 * @version $id Menu.php v1 2014/07/30 22:23 $
 */
class ZendX_View_Helper_Menu extends  Zend_View_Helper_Abstract
{
    public $view;
    public function setView(Zend_View_Interface $view){
        $this->view = $view;
    }
	
 	/**
	 * 菜单自动生成,同时解决页面菜单的JS定位问题
	 * 
     * @param string $actName 最子集菜单的名称，默认为空
     * @param string $parentLink 父链接地址，默认自身,例如：/product/device/applyid
	 * @return string
	 */
    public function menu($actName = '', $parentLink = ''){
        $front = Zend_Controller_Front::getInstance();
        $moduleName = $front->getRequest()->getModuleName();
        $modName= '';
        foreach($this->view->modules as $mod){
            if($mod['label'] == $moduleName){
                $modName = $mod['name'];
            }
        }
        $moduleLink = $modName;
        $menus = $this->view->menuArray;
        $controllerLink = $controllerLinkFull = '';
        $jsLocation = '';
        // print_r($menus);exit();
        // print_r($this->view->visit_url);exit();
        foreach($menus as $menu){
            foreach($menu as $child){
                if($child['url']){
                    $url = (strpos($child['url'], '/') === 0)?substr($child['url'], 1):$child['url'];
                    if($url == substr($this->view->visit_url, 1)){
                        $controllerLink = ' &gt; '.$child['name'];
                        $controllerLinkFull = ' &gt; <a href="/'.$url.'">'.$child['name'].'</a>';
                        break 2;
                    }
                }
            }
        }
        // print_r($controllerLink);exit(',ln='.__line__);
        if($parentLink){
			// 查找parentName对应的中文名称
            // 统一父链接的方式
            // 增加js定位脚本
            
            $parentLink = (strpos($parentLink, '/') === 0)?substr($parentLink, 1):$parentLink;
            $jsLocation = '<script>$(function(){$.navLocation("/'.$parentLink.'");});</script>';
            foreach($menus as $menu){
                foreach($menu as $child){
                    if($child['url']){
                        $url = (strpos($child['url'], '/') === 0)?substr($child['url'], 1):$child['url'];
                        if($url == $parentLink){
                            $controllerLink = ' &gt; '.$child['name'];
                            $controllerLinkFull = ' &gt; <a href="/'.$parentLink.'">'.$child['name'].'</a>';
                            break 2;
                        }
                    }
                }
            }
        }
        // print_r($parentLink);exit(',ln='.__line__);
        $actionLink = '';
        if($actName){
            $actionLink = ' &gt; '.$actName;
            $controllerLink = $controllerLinkFull;
        }
        return $moduleLink.$controllerLink.$actionLink.$jsLocation;
    }
}