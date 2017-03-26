<?php
// set our app paths and environments
define('BASE_PATH', realpath(dirname(__FILE__) . '/../'));
if(!defined('DS'))   define('DS', DIRECTORY_SEPARATOR);
define('APPLICATION_PATH', BASE_PATH . DS . 'application');
define('APPLICATION_ENV', 'testing');

// We wanna catch all errors en strict warnings
error_reporting(E_ALL);
$library = BASE_PATH . DS . 'library';
$path = array(
    $library,
	get_include_path()
);
set_include_path(implode(PATH_SEPARATOR, $path));
require_once 'Zend/Loader/Autoloader.php';
require_once 'Zend/Loader/Autoloader/Resource.php';
$resourceLoader = new Zend_Loader_Autoloader_Resource(array(
  'namespace' => '',
  'basePath'  => BASE_PATH
));
$resourceLoader->addResourceType('model', 'application/models/', 'Model');
$resourceLoader->addResourceType('ezx', 'tests/ezx', 'EZX');