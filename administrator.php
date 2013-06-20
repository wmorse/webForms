<?php
/* administrator.php
 * Created on Aug 23, 2005by William Morse
 *
 * Description:
 *
 *
 *
 * Inputs:
 *
 *
 *
 *
 *
 * Outputs:
 *
 *
 * 
 *
 * Known uses
 *
 *
 */
include_once ('./formfunctions.php');
$htmlLeft="";
$htmlOut ="";
 $htmlOut .="<DIV  style='text-align:center'><h4>Administration Page</h4></DIV>\n";
$htmlOut .="<DIV class = administrator id=adframe>\n";
//what does  the administrator want to do?
if (!isset($uc)){
$actionPage = $_SERVER['PHP_SELF'];
$htmlChoicelist = "\n<ul>\n";
$htmlChoicelist .= "\t<li><A href=\"$actionPage?uc=survey\">Work with Reports/Surveys</A></li>\n";
$htmlChoicelist .= "\t<li><A href=\"$actionPage?uc=scheduling\">Scheduling</A></li>\n";
$htmlChoicelist .= "\t<li><A href=\"$actionPage?uc=userAd\">User Administration</A></li>\n";
$htmlChoicelist .= "\n</ul>";

$htmlOut .=$htmlChoicelist; 

} else 
{
	switch ($uc){
		case 'survey':
			include('adminSurvey.php');
			break;
		case 'scheduling':
		//TODO	include('adminScheduling.php');
			break;
		case 'userAd':
	//TODO		include('adminUser.php');
		    break;
	}
}
$htmlOut.= "</DIV>";
$mainContent=$htmlOut;
$leftContent= $htmlLeft
?>
