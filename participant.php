<?php
/*
 * Created on Aug 4, 2005
 */
$LANGUAGEUTILREPORTS = "%Utilization Report";
$INTERNSHIPREPORTS = "%Internship Report";

$nextLUReportDueDate=NULL;
$nextLUReportButton=NULL;
$previousLUReportButton=NULL;
$viewLUReportsButton=NULL;

$nextIntReportDueDate =NULL;
$nextIntReportButton=NULL;
$previousIntReportButton=NULL;
$viewIntReportsButton=NULL;


$nextReportID=NULL;
$thismyApe=NULL;
include_once ('./formfunctions.php');
include_once ('./schedLayout.php');
//TODO From Old httpAuth
//if (!isset ($username)) {
//	die();
//}
//getUserID
$clean = array();
$clean['token']=$_COOKIE['session'];
$mysqlToken = mysql_real_escape_string($clean['token']);
if(strlen( $mysqlToken>0)){
$userID =getUserIDFromToken($mysqlToken);
}

//get the users's schedule for the LANGUAGE Utilization reports
//the needed survey is... 

$sqlGetSchedule = "SELECT DATE_FORMAT(s.dateDue,'%b %e') as dateDue, "
				. "m.isCommitted,  s.isActive , m.userScheduleMapID, s.dateDue as ddate, " 
				. "(NOW()>s.dateDue) as IsPassed, v.styleSheet, v.banner "
				. "FROM schedule s "
				. "JOIN survey v ON v.surveyID = s.surveyID "
				. "JOIN userScheduleMap m ON s.scheduleID = m.scheduleID "
				. "WHERE v.surveyTitle LIKE '$LANGUAGEUTILREPORTS' "
				. "AND m.userID = $userID "
				. "ORDER BY s.dateDue";
$result = safe_query($sqlGetSchedule);
if (!$result || !mysql_num_rows($result)) {
	//jAVASCRIPT TO NOWHERE?
}
$headRowLU = "\t<TR>\n";
$headRowLU .= "\t\t<TH>Due Date</TH>\n";
$headRowLU .= "\t\t<TH>User Completed</TH>\n";
$headRowLU .= "\t\t<TH>Active Form</TH>\n";
$headRowLU .= "\t</TR>\n";
$contentRowsLU = "";
$arrayLUSchedule =NULL;
while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
if (!is_array($row)) {
	print $query."<li>no array returned : result=$result row=$row";
}
//set the variable styles for the set of reports
$StyleSheet = $row[styleSheet];
$Banner = $row[banner];
//check the data for the next report
	$arrayLUSchedule[]=$row;
	if(!$row['IsPassed'] and !$row['isCommitted'] and !isset($nextReportID)){
		$nextReportID = $row['userScheduleMapID'];
		$nextLUReportDueDate = $row['dateDue'];
		
	}
$contentRow = "\t<TR>\n";
while (list ($key, $value) = each($row)) {
	$contentRow .= "\t\t<TD>".$value."</TD>\n";	
}

$contentRow .= "\t</TR>\n";
$contentRowsLU .=$contentRow;
$locationBase ="http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$nextLUReportButton = "<button class=choice onClick=\"window.location='$locationBase/form.php?st=nrb&nr=$nextReportID'\">Fill out a new report</button>";     
$previousLUReportButton = "<button class=choice onClick=\"window.location='$locationBase/template.php'\">Continue working on a report</button>";
$viewLUReportsButton = "<button class=choice onClick=\"window.location='$locationBase/template.php'\">View previous reports</button>";
}


//Get user info for Internship reports


$sqlGetSchedule = "SELECT DATE_FORMAT(s.dateDue,'%b %e') as dateDue, "
				. "m.isCommitted,  s.isActive , m.userScheduleMapID, s.dateDue as ddate, " 
				. "(NOW()>s.dateDue) as IsPassed "
				. "FROM schedule s "
//				. "JOIN inviteList i ON i.surveyID = s.surveyID "
				. "JOIN survey v ON v.surveyID = s.surveyID "
				. "JOIN userScheduleMap m ON s.scheduleID = m.scheduleID "
				. "WHERE v.surveyTitle LIKE '$INTERNSHIPREPORTS' "
				. "AND m.userID = $userID "."AND m.userID = m.userID "
				. "ORDER BY s.dateDue";
$result = safe_query($sqlGetSchedule);
echo "<!-- MADE IT TO HERE -->\n";
if (!$result || !mysql_num_rows($result)) {
	//jAVASCRIPT TO NOWHERE?
}
$nextIntReportID = NULL;
$contentRowsInt = "";
$arrayIntSchedule =NULL;
while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
	
if (!is_array($row)) {
	print $query."<li>no array returned : result=$result row=$row";
}
//check the data for the next report
	$arrayIntSchedule[]=$row;
	if(!$row['IsPassed'] and !$row['isCommitted'] and !isset($nextIntReportID)){
		$nextIntReportID = $row['userScheduleMapID'];
		$nextIntReportDueDate = $row['dateDue'];
	}
$contentRow = "\t<TR>\n";
while (list ($key, $value) = each($row)) {
	$contentRow .= "\t\t<TD>".$value."</TD>\n";	
}

$contentRow .= "\t</TR>\n";
$contentRowsInt .=$contentRow;
}
echo "<!-- MADE IT TO THIS POINT :$nextIntReportID  -->\n";	
if(!isset($nextIntReportDueDate) or is_null($nextIntReportDueDate) ){
	$nextIntReportDueDate= "--Not currently scheduled --";
}	

$locationBase ="http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$nextIntReportButton = "<button class=choice onClick=\"window.location='$locationBase/form.php?st=nrb&nr=$nextIntReportID '\">Fill out a new report</button>";     
$previousIntReportButton = "<button class=choice onClick=\"window.location='$locationBase/template.php'\">Continue working on a report</button>";
$viewIntReportsButton = "<button class=choice onClick=\"window.location='$locationBase/template.php'\">View previous reports</button>";



//put a title here with $page_title
$page_title = "Report page";
$centerContent .= "<DIV id=centerSection>";
$centerContent .= "<font color=#CC3300><B>Language Utilization Reports</B></font> <BR><BR>";
$centerContent .= "Your next report is due on <font color = blue>$nextLUReportDueDate</font><BR><BR>" ;

$leftContent .= "<DIV id=navfunc>LANGUAGE REPORTS<BR>";
//20070821 HACK
$leftContent .= "<BR><br><br><br><br><br><br>";
/*
$leftContent .= "$nextLUReportButton<BR>";
$leftContent .= "$previousLUReportButton<BR>";
$leftContent .= "$viewLUReportsButton<BR><br><br><br><br>";
*/ //20070821 HACK
$centerContent .= "Submission Log<BR>";
$centerContent .= makeSubLogTable("Fall 2007","LU",$arrayLUSchedule,7,null,null) ;

$centerContent .= "<BR><BR><font color=#CC3300><B>Internship Reports</B></FONT><BR><BR>";
$centerContent .= "Your next report is due on <font color = blue>$nextIntReportDueDate</font><BR><BR>" ;

$leftContent .= "<BR><BR><BR>INTERNSHIP REPORTS<BR>";
//20070821 HACK
$leftContent .= "<BR><br><br><br><br><br><br>";
/*
$leftContent .= "$nextIntReportButton<BR>";
$leftContent .= "$previousIntReportButton<BR>";
$leftContent .= "$viewIntReportsButton<BR><BR>";
$leftContent  .= "</DIV>";
*/ //20070821 HACK

$centerContent .= "Submission Log<BR>";
$centerContent .= makeSubLogTable("Fall 2007","Int",$arrayIntSchedule,7,null,null) ;





$leftContent .= "<ul id=navlist>";
/* $leftContent .= "EVALUATIONS";
$leftContent .= "<li><A href=\"./template.php?hl=myape&amp;$thismyApe\">Mid-Year Academic Program Evaluation</A></li>";
$leftContent .= "<li><A href=\"./template.php?hl=myape&amp;$thismyApe\">Mid-Year Homestay Evaluation</A></li>";
$leftContent .= "<BR>";
$leftContent .= "<li><A href=\"./template.php?hl=myape&amp;$thismyApe\">Final Academic Program Evaluation</A></li>";
$leftContent .= "<li><A href=\"./template.php?hl=myape&amp;$thismyApe\">Final Homestay Evaluation</A></li>";
$leftContent .= "<li><A href=\"./template.php?hl=myape&amp;$thismyApe\">Final Internship Evaluation</A></li>";
$leftContent  .= "</ul>";
*/


$centerContent .= "<BR>QUESTIONS/COMMENTS?";
$centerContent .= "&nbsp;&nbsp;&nbsp;<SPAN text-align=center><a href=mailto:application@example.org>Email US!</a></SPAN>";
$centerContent .= "</DIV>"; 
?>