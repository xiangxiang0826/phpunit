<?php  
/* 
 * PHP Logger Class 
 * Created: 2011-10-26 
 * Author: xingfei(http://blog.csdn.net/jakieyoung) 
 * Licence: Free of use and redistribution 
 */
  
if(!defined('_LOGGER_PHP_')) {  
    define('_LOGGER_PHP_', '1');  
}
  
if(!defined('LOG_ROOT')) {  
    define('LOG_ROOT', APPLICATION_PATH.DS."logs".DS);  
}  
  
define('LEVEL_FATAL', 0);  
define('LEVEL_ERROR', 1);  
define('LEVEL_WARN', 2);  
define('LEVEL_INFO', 3);  
define('LEVEL_DEBUG', 4);  
  
class Logger {
	 
	/**
	 * level
	 */  
    public static $LOG_LEVEL_NAMES = array('FATAL', 'ERROR', 'WARN', 'INFO', 'DEBUG');  
  
  	/**
  	 * level debug
  	 */
    private $level = LEVEL_DEBUG;  
  
  	/**
  	 * logger instance
  	 */
  	private static $instance = null;
  	private $log_path = NULL;
  	/**
  	 * get instance
  	 */
    public static function getInstance() {
        return self::$instance == null ? new Logger() : self::$instance;
    }  
  
  	/**
  	 * set log level
  	 * 
  	 * @param string $lvl
  	 */
    public function setLogLevel($lvl) {  
        if($lvl >= count(Logger::$LOG_LEVEL_NAMES)  || $lvl < 0) {  
            throw new Exception('invalid log level:' . $lvl);  
        }  
        $this->level = $lvl;  
    }  
    
    /**
     * set log path
     *
     * @param string $lvl
     */
    public function setLogPath($path) {
    	$this->log_path = $path;
    }
  
  	/**
  	 * main log
  	 * 
  	 * @param string $level
  	 * @param string $message
  	 * @param string $name
  	 */
    protected function _log($level, $message, $name) { 
        if($level > $this->level) {  
            return;  
        }
        $name = date('Y-m-d')."-{$name}"; // 日期拆分
        $log_file_path = $this->log_path ? "{$this->log_path}{$name}.log" : LOG_ROOT . $name . '.log';  
        $log_level_name = Logger::$LOG_LEVEL_NAMES[$level];  
        $content = date('Y-m-d H:i:s') . ' [' . $log_level_name . '] ' . $message . "\n";  
        file_put_contents($log_file_path, $content, FILE_APPEND);  
    }
  	
  	/**
  	 * debug
  	 */
    public function debug($message, $name = 'root') {  
        $this->_log(LEVEL_DEBUG, $message, $name);  
    }  
    
    /**
     * info
     */
    public function info($message, $name = 'root') {  
        $this->_log(LEVEL_INFO, $message, $name);  
    }  
    
    /**
     * warn
     */
    public function warn($message, $name = 'root') {  
        $this->_log(LEVEL_WARN, $message, $name);  
    } 
     
    /**
     * error
     */ 
    public function error($message, $name = 'root') {  
        $this->_log(LEVEL_ERROR, $message, $name);  
    }
    
    /**
     * fatal
     */  
    public function fatal($message, $name = 'root') {  
        $this->_log(LEVEL_FATAL, $message, $name);  
    }  
}