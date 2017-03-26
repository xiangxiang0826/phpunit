<?php
/**
 * 字符截取助手类
 * 
 * @author etong <zhoufeng@wondershare.cn>
 * @version $id Cutstring.php v1 2014/07/29 19:32 $
 */
class ZendX_View_Helper_Cutstring extends  Zend_View_Helper_Abstract
{
 	/**
	 * 文字截取
	 * 
	 * @param string $text 待处理的字符串
	 * @param int $limit 限制的个数（1个汉字或1个英文字母均算一个字符）
	 * @param string $extstr 截取后末尾的字符串（默认为...）
	 * @return string
	 */
    public function Cutstring($text, $limit=12, $extstr="..."){
        $charset = "UTF-8";
        $more = false;
        if (function_exists('mb_substr')) {
            $more = (mb_strlen($text, $charset) > $limit) ? true : false;
            $text = mb_substr($text, 0, $limit, $charset);
            if ($more) {
                $text .= $extstr;
             }
            return $text;
        } elseif (function_exists('iconv_substr')) {
            $more = (iconv_strlen($text, $charset) > $limit) ? true : false;
            $text = iconv_substr($text, 0, $limit, $charset);
            if ($more) {
                $text .= $extstr;
            }
            return $text ;
        } else {
            //仅用于UTF-8的情况下，因为大部分情况下为utf-8的字符编码
            preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $text, $ar);
            if (func_num_args() >= 3) {
                if (count($ar[0]) > $limit) {
                    $more = true;
                    $text = join("", array_slice($ar[0], 0, $limit)) . $extstr;
                } else {
                    $more = false;
                    $text = join("", array_slice($ar[0], 0, $limit));
                }
            } else {
                $more = false;
                $text = join("", array_slice($ar[0], 0));
            }
            return $text;
        }
    }
}