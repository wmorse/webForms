<?php
$emailHTML ='';
if(isset($GLOBAL_SiteAdmin)){
$emailHTML = "E-mail: <a href=\"mailto:$GLOBAL_SiteAdmin\">
$GLOBAL_SiteAdmin</a>";	
}



$page_footer = <<<EOQ
<div class="foot-box">
	<div class="standard">
		This website is a project of Example Organization,  &copy 2011. 
	</div>
	<div class="standard">
		This page was last updated: 2011-04-30
	</div>
	<div class="standard">
		Example Organization
1234 Street Address, 12th Floor, BigCity, SA  87654<br>
Ph: 123 123 1235, Fax: 123 123 1234
$emailHTML 
	</div>
</div>
EOQ;
?>