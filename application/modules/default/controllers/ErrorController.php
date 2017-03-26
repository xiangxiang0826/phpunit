<?php

class ErrorController extends ZendX_Controller_Action
{

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        // 打印错误日志
        ZendX_Tool::log('error',$errors->exception->getTraceAsString() ."\r\n" .$errors->exception->getMessage());
        if (!$errors || !$errors instanceof ArrayObject) {
            $this->redirect('/');
        }
        
        if($this->getRequest()->isXmlHttpRequest()) {
        	$message = $errors->exception->getMessage();
        	$code = $errors->exception->getCode();
        	exit(json_encode(array('code'=>$code, 'msg'=>$message)));
        }
        
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                $this->view->message = '页面未找到';
                return $this->err404();
                break;
            default:
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->view->message = '应用服务器错误';
                return $this->err500();
                break;
        }
        
        $this->view->displayExceptions = $this->getInvokeArg('displayExceptions');
        $this->view->exception = $errors->exception;
        $this->view->request = $errors->request;
    }

    private function err404(){
    	echo $this->view->Render('error_404.phtml');
    }
    private function err500() {
    	echo $this->view->Render('error_500.phtml');
    }
    
}

