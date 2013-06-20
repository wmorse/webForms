<?php
if (empty($GLOBAL_Page_Title)) { $page_title =$GLOBAL_DEFAULT_PAGE_TITLE; } else {{ $page_title =$GLOBAL_Page_Title; }}
if (empty($GLOBAL_banner_logo_location) ){$GLOBAL_banner_logo_location = $GLOBAL_DEFAULT_PAGE_BANNER_LOCATION ;}
if (empty($charSet)) { $charSet = ""; }
if (empty($styleSheet ) ){$styleSheetlink = "";} else {
	$styleSheetlink="<LINK media= \"screen\" href=\"$styleSheet\" type=text/css rel=stylesheet>";
}

$start_Page_html_headers = <<<EOQ
<!DOCTYPE html PUBLIC "-//IETF//DTD HTML 2.0//EN">
<HTML>
<HEAD><TITLE>$page_title</TITLE>
<META http-equiv=content-type content=text/html;charset=utf-8>
<LINK media= "screen" href="./stylesheets/basic.css" type=text/css rel=stylesheet title="Default">
<LINK media= "screen" href="./stylesheets/basic2.css" type=text/css rel=stylesheet title="basic2">
<LINK media= "screen" href="./stylesheets/basic3.css" type=text/css rel=stylesheet title="basic3">
<LINK media= "screen" href="./stylesheets/basic4.css" type=text/css rel=stylesheet title="basic4">
$styleSheetlink
<script src="./js/jquery-1.5.2.min.js" type="text/javascript" language="javascript"></script>
<script src="./js/jquery.validate.min.js" type="text/javascript" language="javascript"></script>
<script src="./js/common.js" type="text/javascript" language="javascript"></script>
  <script>
  $(document).ready(function(){
    $("#commentForm").validate();
  });
  </script>

$charSet

<link href="./images/favico.ico" rel="shortcut icon"/>
</HEAD>
EOQ;
//<img alt="" src="$banner_logo_location" class="logo"/>

print $start_Page_html_headers;
?>

