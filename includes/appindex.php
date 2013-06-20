<?php
/*
********************************************************
* William Morse 04042003
********************************************************
*/
// begin the survey form.
// pull the user id from the database
function getAppIndex($userID){
	global $GLOBAL_DEFAULT_PAGE;
	$appindexhtmlout ="";
//TODO should we just send a user class instance??

	if($userID==NULL){return;}
$userQuery = "SELECT `userID`,`userLastname`,`userFirstname`,`userEMail` FROM `users` "
            . " WHERE 1 AND `userID` = '$userID';"; 
           
$uResult = safe_query($userQuery);
 if (!$uResult || !mysql_num_rows($uResult)) {
 	//do nothing
 }else{
    $row = mysql_fetch_array($uResult,MYSQL_ASSOC);
    if (!is_array($row)) 
    { 
		print $userQuery."<li>no array returned : result=$uResult row=$row"; 
        ; 
    }else{
    while (list($key,$value) = each($row))
    {
        
        $$key = $value;
    }
    }
 }

//Show individualized greeting


//Get the surveys that this user has been asigned
$surveyquery   = "SELECT DATE_FORMAT(s.dateDue,'%b %e %y') as dateDue, v.surveyTitle, "
				. "m.isCommitted,  s.isActive , m.userScheduleMapID, s.dateDue as ddate, " 
				. "(NOW()>s.dateDue) as IsPassed, s.surveyID, m.confirmationNumber "
				. "FROM schedule s "
				. "JOIN survey v ON v.surveyID = s.surveyID "
				. "JOIN userScheduleMap m ON s.scheduleID = m.scheduleID "
				. "WHERE m.userID = '$userID' "
				. "ORDER BY s.dateDue";
//echo "<!--\n $surveyquery \n -->";
$surveyResult = safe_query($surveyquery);
$numsurveys = mysql_num_rows($surveyResult);


$appindexhtmlout .= "<table border = 1>";
$DueColumn = "<th> Due Date </th>";
$appindexhtmlout .= "<tr><th> Available forms for  $userFirstname $userLastname </th><th> Submission Status</th>$DueColumn </tr>";
if ($numsurveys < 1 ){
        $appindexhtmlout .= "<tr><th colspan = 4> <B> No forms available for  $UserFirstname $UserLastname </th></B></tr>"; }
while ($surrow = mysql_fetch_array($surveyResult,MYSQL_ASSOC))
{
   $appindexhtmlout .= "<tr>";

   if (is_array($surrow))
   {
   	 $surveyID=$surrow["surveyID"];
	 $This_IsCommitted = $surrow["isCommitted"];
	 $userSCheduleMapID= $surrow["userScheduleMapID"];
	 if ($This_IsCommitted ==1){
	 	$confNum = $surrow["confirmationNumber"];
	 	$cellOne = " <td>  "
            . $surrow["surveyTitle"]   . " </td> ";
        $cellTwo ="<td  align = center> Completed:<BR> <a href=\"print_app.php?frmno=$surveyID&tr=$userSCheduleMapID\" target=\"_blank\" > Confirmation Number << $confNum >><BR> Open printable version</a>  <BR></td>";
	 }	
	 else {
	 	$cellOne = " <td> <a href=\"$GLOBAL_DEFAULT_PAGE?ins=$userSCheduleMapID\"> "
            . $surrow["surveyTitle"] . "</a>"  . " </td> ";
        $cellTwo = "<td align = center> " ."PENDING". " </td> " ;
	 }	
     $cellThree =" <td> ". $surrow["dateDue"] . " </td> ";
     $appindexhtmlout .= $cellOne . $cellTwo .$cellThree ;
   }
   $appindexhtmlout .= "</tr>";
}
$appindexhtmlout .= "</table>";
return 	$appindexhtmlout ;
}
?>
