<?php
/*
 * Created on Aug 4, 2005
 *
 * CHANGE BY KEN
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
$htmlOut = ""; 
foreach($_POST as $key => $value){
		$$key =$value;
}
foreach($_GET as $key => $value){
		$$key =$value;
}
 include("./header.php");
 include("QuesTypes.php");
 //put a title here with $page_title
 
 if(!isset($editSecs)){
 	$htmlOut = "You must chose at least one question";
 	
 }else{
$secChuncks = "";
$sectionIDCounter=0;
for ($i = 0; $i < sizeof($editSecs); $i++) {
$sectionIDCounter++;
$thissuperSec =$sectionIDCounter; 	
$sid = $editSecs[$i];
$sqlGetSections= "SELECT `SurveySectionID`,`SurveyID`, " 
			   . "`SurveySectionInform`,`SurveySectionTitle`, " 
			   . "`SectionOrdinal`,`SurveySectionUserMa`,`SurveySectionInstru`, Printable " 
               . "FROM `surveySections` " 
               . "WHERE `SurveySectionID` = $sid "
               . "ORDER BY SectionOrdinal";

$resultsGetSection = safe_query($sqlGetSections);				
list($SurveySectionID, $SurveyID, $SurveySectionInform, $SurveySectionTitle,$SectionOrdinal,
            $SurveySectionUserMa , $SurveySectionInstru , $Printable ) = mysql_fetch_row($resultsGetSection );	


$secChuncks .= "\n<table border=0 cellpadding=2 cellspacing=1>";
$secChuncks .= "\n\t<tr>";
$secChuncks .= "<th>Property</th>";
$secChuncks .= "<th>Value</th>";
$secChuncks .= "</tr>";

$secChuncks .= "\t<tr>\n";
$secChuncks .= "\t\t<td align=\"center\" bgcolor=\"#EEEEEE\">SurveyID</td><input type=hidden name=secFields[$thissuperSec][SurveySectionID] value = $SurveySectionID />";
$secChuncks .="\t\t</td>\n";
$secChuncks .= "\t\t<td bgcolor=\"#EEEEEE\">";
$secChuncks .= " <input type=\"text\" name=\"secFields[$thissuperSec][SurveyID]\" value=\"$SurveyID\" size=\"4\" maxlength=\"4\" class=\"textfield\" />";
$secChuncks .="\t\t</td>\n";
$secChuncks .= "\t</tr>\n";
$secChuncks .= "\t<tr>\n";
$secChuncks .= "\t\t<td align=\"center\" bgcolor=\"#EEEEEE\">SurveySectionInform</td>";
$secChuncks .="\t\t</td>\n";
$secChuncks .= "\t\t<td bgcolor=\"#EEEEEE\">";
$secChuncks .= "<textarea name=\"secFields[$thissuperSec][SurveySectionInform]\" ";
$secChuncks .= "class=textfield id=field_3_3 cols=80 rows=6/>". htmlspecialchars($SurveySectionInform) . "</textarea >";
$secChuncks .="\t\t</td>\n";
$secChuncks .= "\t</tr>\n";
$secChuncks .= "\t<tr>\n";
$secChuncks .= "\t\t<td align=\"center\" bgcolor=\"#EEEEEE\">SurveySectionTitle</td>";
$secChuncks .="\t\t</td>\n";
$secChuncks .= "\t\t<td bgcolor=\"#EEEEEE\">";
$secChuncks .= "<textarea name=\"secFields[$thissuperSec][SurveySectionTitle]\""; 
$secChuncks .= "class=textfield id=field_3_3 cols=80 rows=6/>". htmlspecialchars($SurveySectionTitle) . "</textarea >";
$secChuncks .="\t\t</td>\n";
$secChuncks .= "\t</tr>\n";
$secChuncks .= "\t<tr>\n";
$secChuncks .= "\t\t<td align=\"center\" bgcolor=\"#EEEEEE\">SectionOrdinal</td>";
$secChuncks .="\t\t</td>\n";
$secChuncks .= "\t\t<td bgcolor=\"#EEEEEE\">";
$secChuncks .= " <input type=\"text\" name=\"secFields[$thissuperSec][SectionOrdinal]\" value=\"$SectionOrdinal\" size=\"4\" maxlength=\"4\" class=\"textfield\" />";
$secChuncks .="\t\t</td>\n";
$secChuncks .= "\t</tr>\n";
$secChuncks .= "\t<tr>\n";
$secChuncks .= "\t\t<td align=\"center\" bgcolor=\"#EEEEEE\">SurveySectionUserMa</td>";
$secChuncks .="\t\t</td>\n";
$secChuncks .= "\t\t<td bgcolor=\"#EEEEEE\">";
$secChuncks .= " <input type=\"text\" name=\"secFields[$thissuperSec][SurveySectionUserMa]\" value=\"". htmlspecialchars($SurveySectionUserMa)."\" size=\"20\" maxlength=\"50\" class=\"textfield\" />";
$secChuncks .="\t\t</td>\n";
$secChuncks .= "\t</tr>\n";
$secChuncks .= "\t<tr>\n";
$secChuncks .= "\t\t<td align=\"center\" bgcolor=\"#EEEEEE\">SurveySectionInstru</td>";
$secChuncks .="\t\t</td>\n";
$secChuncks .= "\t\t<td bgcolor=\"#EEEEEE\">";
$secChuncks .= " <input type=\"text\" name=\"secFields[$thissuperSec][SurveySectionInstru]\" value=\"" . htmlspecialchars($SurveySectionInstru) . "\" size=\"20\" maxlength=\"50\" class=\"textfield\" />";
$secChuncks .="\t\t</td>\n";
$secChuncks .= "\t</tr>\n";
$secChuncks .= "\t<tr>\n";
$secChuncks .= "\t\t<td align=\"center\" bgcolor=\"#EEEEEE\">Printable</td>";
$secChuncks .="\t\t</td>\n";
$secChuncks .= "\t\t<td bgcolor=\"#EEEEEE\">";
$secChuncks .= " <input type=\"text\" name=\"secFields[$thissuperSec][Printable]\" value=\"$Printable\" size=\"3\" maxlength=\"3\" class=\"textfield\" onpropertychange=\"return unNullify('SurveyID', '0')\" tabindex=\"4\" id=\"field_2_3\" />";
$secChuncks .="\t\t</td>\n";
$secChuncks .= "\t</tr>\n";
} //chuncks
$secChuncks .= "\n</table>";
$hiddenFields =""; 
  //need a form

$thisPage = $_SERVER['PHP_SELF'];
$target="$thisPage?uc=survey&survID=$survID&Woption=EditQs"; 
$htmlOut .= start_form($target); 
$goButton = "<button width = 10% onClick='submit();'><font color=\"black\" face=\"verdana narrow\" size=1.5><b>Make changes</b></font></button>";
$htmlOut .= $goButton ;
$htmlOut .= $secChuncks; 
  //end a form
$htmlOut .= end_form();
 }  
?>
