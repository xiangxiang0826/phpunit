<?php
$___css_ver_style = Cms_Template::loadDataObject('style')->css(array('domain'=>"welcome.1719.com",'mod'=>"index", 'site_id'=>$site_id));
$css_ver = $___css_ver_style;
?>
<?php
$___js_ver_style = Cms_Template::loadDataObject('style')->js(array('domain'=>"welcome.1719.com",'mod'=>"index", 'site_id'=>$site_id));
$js_ver = $___js_ver_style;
?>
<link type="text/css" rel="stylesheet" href="http://images.1719.com/style/welcome/css/global_welcome.css?v=<?php echo $css_ver; ?>" />
<script language="javascript" type="text/javascript" src="http://images.1719.com/js/jquery1.8.2.min.js"></script>
<script language="javascript" type="text/javascript" src="http://images.1719.com/js/jquery.bxslider2.min.js"></script>
<script language="javascript" type="text/javascript" src="http://images.1719.com/js/auto.complete.js?v=<?php echo $js_ver; ?>"></script>
<script language="javascript" type="text/javascript" src="http://images.1719.com/js/global.js?v=<?php echo $js_ver; ?>"></script>
<script language="javascript" type="text/javascript" src="http://images.1719.com/js/welcome.js?v=<?php echo $js_ver; ?>"></script>