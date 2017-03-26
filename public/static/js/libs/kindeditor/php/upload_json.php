<?php
/**
 * KindEditor PHP
 *
 * 本PHP程序是演示程序，建议不要直接在实际项目中使用。
 * 如果您确定直接使用本程序，使用之前请仔细确认相关安全设置。
 *
 */

require_once 'JSON.php';

//修改 kindeditor 上传，由上传到本身文件夹改为调用api接口上传
$url = $_SERVER["SERVER_NAME"]. "/api/upload/index";
$new_file = dirname($_FILES['imgFile']['tmp_name']) . "/{$_FILES['imgFile']['name']}";
copy($_FILES['imgFile']['tmp_name'], $new_file);

$data = array (
	"Filedata" => "@{$new_file}",
	'type'=>'news'
);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
$output = curl_exec($ch);

@unlink($new_file);
//if(!$output) file_put_contents('xx.log',curl_error($ch),FILE_APPEND);
curl_close($ch);
$resultJson = json_decode($output);
header('Content-type: text/html; charset=UTF-8');
$json = new Services_JSON();
echo $json->encode(array('error' => 0, 'url' => $resultJson->result->url));
exit;

function alert($msg) {
	header('Content-type: text/html; charset=UTF-8');
	$json = new Services_JSON();
	echo $json->encode(array('error' => 1, 'message' => $msg));
	exit;
}
?>