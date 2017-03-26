<?php
/*
 * CKFinder
 * ========
 * http://cksource.com/ckfinder
 * Copyright (C) 2007-2013, CKSource - Frederico Knabben. All rights reserved.
 *
 * The software, this file and its contents are subject to the CKFinder
 * License. Please read the license.txt file before using, installing, copying,
 * modifying or distribute this file or part of its contents. The contents of
 * this file is part of the Source Code of CKFinder.
 */

/**
 * Main heart of CKFinder - Connector
 *
 * @package CKFinder
 * @subpackage Connector
 * @copyright CKSource - Frederico Knabben
 */

/**
 * Protect against sending warnings to the browser.
 * Comment out this line during debugging.
 */
// error_reporting(0);

/**
 * Protect against sending content before all HTTP headers are sent (#186).
 */
ob_start();

define('CF_DIR', dirname(__FILE__));

/**
 * define required constants
 */
require_once CF_DIR.'/constants.php';

// @ob_end_clean();
// header('Content-Encoding: none');

/**
 * we need this class in each call
 */
require_once CKFINDER_CONNECTOR_LIB_DIR . '/CommandHandler/CommandHandlerBase.php';
/**
 * singleton factory
 */
require_once CKFINDER_CONNECTOR_LIB_DIR . '/Core/Factory.php';
/**
 * utils class
 */
require_once CKFINDER_CONNECTOR_LIB_DIR . '/Utils/Misc.php';
/**
 * hooks class
 */
require_once CKFINDER_CONNECTOR_LIB_DIR . '/Core/Hooks.php';
/**
 * Simple function required by config.php - discover the server side path
 * to the directory relative to the '$baseUrl' attribute
 *
 * @package CKFinder
 * @subpackage Connector
 * @param string $baseUrl
 * @return string
 */
function resolveUrl($baseUrl) {
    $fileSystem =& CKFinder_Connector_Core_Factory::getInstance('Utils_FileSystem');
    $baseUrl = preg_replace('|^http(s)?://[^/]+|i', '', $baseUrl);
    return $fileSystem->getDocumentRootPath() . $baseUrl;
}

$utilsSecurity =& CKFinder_Connector_Core_Factory::getInstance('Utils_Security');
$utilsSecurity->getRidOfMagicQuotes();

/**
 * $config must be initialised
 */
$config = array();
$config['Hooks']['AfterFileUpload'][]	= array('onAfterFileUpload');
$config['Hooks']['InitCommand'][]		= array('onInitCommand');
$config['Hooks']['BeforeExecuteCommand'][] = array('onBeforeExecuteCommand');
$config['Plugins'] = array();

/**
 * Fix cookies bug in Flash.
 */
if (!empty($_GET['command']) && $_GET['command'] == 'FileUpload' && !empty($_POST)) {
	foreach ($_POST as $key => $val) {
		if (strpos($key, 'ckfcookie_') === 0)
			$_COOKIE[str_replace('ckfcookie_', '', $key)] = $val;
	}
}

/**
 * read config file
 */
require_once CKFINDER_CONNECTOR_CONFIG_FILE_PATH;

CKFinder_Connector_Core_Factory::initFactory();
$connector = CKFinder_Connector_Core_Factory::getInstance('Core_Connector');

//var_export( $connector );exit;


$GLOBALS['config'] = $config;
$GLOBALS['connector'] = $connector;


if(isset($_GET['command'])) {
    $connector->executeCommand($_GET['command']);
}
else {
    $connector->handleInvalidCommand();
}



//$GLOBALS['file_obj'] 在File_IndexController的connectorAction方法中定义的


function onInitCommand( &$connectorNode ){
	return true;
}

//note 上传文件成功后回调
function onAfterFileUpload( $currentFolder, $uploadedFile, $filePath ){
	//覆盖原同名文件
	preg_match('/\([\d]+\)\./', $filePath, $arr );
	if( !empty( $arr ) ){
		
		$oriname = str_replace( $arr[0], '.', $filePath);
		unlink( $oriname );//删除原同名文件
		$thumbs = str_replace('httpdocs', '_thumbs/'.$_GET['type'], $oriname);
		if( file_exists( $thumbs )) unlink( $thumbs );//删除原同名缩略文件

		rename( $filePath, $oriname );//重命名新文件
		$filePath = $oriname;
	}
	$GLOBALS['file_obj']->uploadFileAction( $filePath, $_GET['domain']);
	return true;
}


function onBeforeExecuteCommand( &$command ){
	switch( $command ){
		case 'RenameFile'://重命名文件
			$GLOBALS['file_obj']->renameFileAction( $_GET['currentFolder'], $_GET['fileName'], $_GET['newFileName'],$_GET['domain']);
			break;
		case 'DeleteFiles'://删除文件
			$GLOBALS['file_obj']->deleteFileAction( $_POST['files'],$_GET['domain']);
			break;
		case 'SaveFile'://编辑文件
			$GLOBALS['file_obj']->editFileAction( $_GET['currentFolder'], $_POST['fileName'],$_GET['domain']);
			break;
		case 'DeleteFolder'://删除文件夹
			$GLOBALS['file_obj']->deleteDirAction( $_GET['currentFolder'],$_GET['domain']);
			break;
		case 'GetFiles'://取得所有文件列表
			if( $_GET['type'] == 'All files list' ){
				$GLOBALS['file_obj']->getFilesAction($_GET['domain'],$_GET['domain']);
			}
			break;
		case 'Init'://删除文件夹
			$GLOBALS['file_obj']->getFilesAction( $_GET['domain']);
			break;
	}
	return true;
}


