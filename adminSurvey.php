<?php
$thisPage = $_SERVER['PHP_SELF'];
$administratorFunctions="";
if ($userRole=="administrator"){
$page_title = "Survey Administration";
$administratorFunctions = "<li> <a href=\"$thisPage?uc=survey&survID=$survID&Woption=EditQs\">Edit survey questions</a>";
}else if ($userRole=="reviewer"){
	$page_title = "Reviewer Zone";
}

//Get info on relevant surveys
//$serverAuthUser = $_SERVER['PHP_AUTH_USER']; 
//$userquery  = "SELECT `userID`,`userLastname`,`userFirstname`,`userEMail` FROM `users` "
//            . " WHERE `username` = \"$serverAuthUser\" ;";
//$userResult = safe_query($userquery);
//asign each colume to a global variable
//set_result_variables($userResult);

if (!isset($survID )){
if ($userRole=="administrator"){
$surveyquery  = "SELECT  a.surveyID, a.surveyTitle,  a.surveyTimetoComplet "
			  . "FROM survey a, adminSurveyMap b WHERE a.surveyId = b.surveyID AND b.adminUserID =  \"$userID\" ;";

}else if ($userRole=="reviewer"){
$surveyquery  = "SELECT  a.surveyID, a.surveyTitle,  a.surveyTimetoComplet "
			  . "FROM survey a, reviewSurveyMap b WHERE a.surveyId = b.surveyID AND b.reviewUserID =  \"$userID\" ;";

}
$surveyResult = safe_query($surveyquery);
$numsurveys = mysql_num_rows($surveyResult);

$htmlOut ="\n<table border >";

$htmlOut .="\n\t<tr>\n\t\t<th> Surveys Administered by  $userFirstname $userLastname \n\t</tr>";
if ($numsurveys < 1 ){
        $htmlOut .= "\n\t<tr> <B> $userFirstname $userLastname does not have priveledges to view  any surveys. </B></tr>";
		}
while ($surrow = mysql_fetch_array($surveyResult,MYSQL_ASSOC))
{
   $htmlOut .= "\n\t<tr>";
   if (is_array($surrow))
   {
	$cellOne = "\n\t\t<td><a href=\"$thisPage?uc=survey&survID=". $surrow["surveyID"] ."\">". $surrow["surveyTitle"] . "</a>  </td> ";
	 }
     $surveyId = $surrow["surveyID"];
   $htmlOut .= $cellOne;

   $htmlOut .= "\n\t</tr>";
}
   $htmlOut .="</table>";
}// not survID set
else {                 //survID IS set
	if (!isset($Woption)){
	$choiceListHTML = <<<EOQ
<p>
What do you want to do?
</p>
<ul>
$administratorFunctions
<li> <a href="$thisPage?uc=survey&survID=$survID&Woption=GetTables">Get responses in a table</a>
<li> <a href="$thisPage?uc=survey&survID=$survID&Woption=GetSummary">Get a summary of reponses</a>
<li> <a href="$thisPage?uc=survey&survID=$survID&Woption=ReviewForms">View user responses </a>
</ul>
EOQ;
	$htmlOut.= $choiceListHTML;
	}//Woption is NOT set
	else { //Woption IS Set
		switch ($Woption) {
			case "EditQs":
			include("./includes/editQs.php");
			//$htmlOut.= "Edit page not yet programmed";
			break;
			case "edit":
			include("./includes/editQsform.php");
			//$htmlOut.= "Edit page not yet programmed";
			break;
			
			case "GetTables":
			include("./includes/admin_table.php");
            break;
			case "editSec":
			include("./includes/editSec.php");
            break;            
			case "del":
			include("./includes/delQs.php");
            break;            
			case "insert":
			include("./includes/editQsform.php");
            break;            
			case "ReviewForms":
			include("./includes/adminReview.php");
            break;            
			case "selUser":
			include("./includes/adminReview.php");
            break;
			case "foo":
			$foo = "./includes/admin_table.php?survID=$survID";
			$htmlOut.= '<head>' . '<meta http-equiv="refresh" content="0; URL=' .  $foo.  '">'. '</head>';//DEBUG
            break;            
            default:
			$htmlOut.= "This option has not yet been programmed";
		}
	}//END of else clause --Woption IS set
	

}//END of else clause --survID IS set



$htmlOut.= <<<EOQ
<br>
<br>
<br>
<a href="$thisPage">Go to form $userRole page</a>
EOQ;
?>
