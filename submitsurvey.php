<?php

/*
********************************************************
*** This script modified from MySQL/PHP Database     ***
*** Applications by Jay Greenspan and Brad Bulger    ***
***                                                  ***
*** Modification by WIlliam Morse                   ***
********************************************************
*/

/*
Application: Survey
Described in: Chapter 9
Name: thanks.php
Purpose: This page writes a survey response to the database 

This script is accessed when the user presses the 'Submit Survey'
button in the survey form displayed by the main survey page (index.php).

*/
foreach($_POST as $key => $value){
		$$key =$value;
}
foreach($_GET as $key => $value){
		$$key =$value;
}
include "header.php";
include ("authenticate.php");


$userquery = "SELECT `userLastname`,`userFirstname`,`userEMail` FROM `users` "." WHERE 1 AND `userID` = '$userID';";
$userResult = safe_query($userquery);

//asign each colume to a global variable
set_result_variables($userResult);

if (isset ($ins)) {
	$userSched_MapID = $ins;
	$sqlGetSurvID = "SELECT s.surveyID as survID, s.surveyTitle, DATE_FORMAT(sc.dateDue,'%b %e') as ddate, s.surveyContact  "
				  ."FROM survey s "
				  ."JOIN schedule sc ON s.surveyID = sc.surveyID "
				  ."JOIN userScheduleMap us ON sc.scheduleID = us.scheduleID "
				  ."WHERE us.userScheduleMapID ="
				  .$userSched_MapID." AND us.userID = $userID";
	$resultsGetSurvID = safe_query($sqlGetSurvID);
	$row = mysql_fetch_assoc($resultsGetSurvID);
	$survID = $row['survID'];
	$surveyTitle = $row['surveyTitle'];
	$dueDate = $row['ddate'];
	$adminEmail = $row['surveyContact'];

	//get Reviewers for this survey' .
	$sqlReviewers = "SELECT DISTINCT r.userEMail "
				  ."FROM users r "
				  ."JOIN reviewSurveyMap rsm ON r.userID = rsm.reviewUserID "
				  ."WHERE rsm.surveyID = '". $survID . "' ";
    $resultsReviewers  = safe_query($sqlReviewers);
    $arrReviewers = null;
    while ($row = mysql_fetch_assoc($resultsReviewers) ){
    	$arrReviewers[]= $row["userEMail"];
    }			   
}
if (!isset($survID))  {
   echo "<p><strong> It does not appear that this report/form is available. </strong><br>Please report problems to the site administrator. ";
   die;
}

if (isset ($QuestionIDarr)) {
	include "./includes/postsurvey.php";

} //END if $Question is not set

$page_title = "Report Submitted!";
include "start_page.php";
print "<DIV class=Thanknote>";

/****/

//Check to see if user has an isCommitted value of N to allow access to page, else display message
$commitQuery = "SELECT us.userID, sc.surveyID, us.isCommitted "
              ."FROM userScheduleMap us "."JOIN schedule sc ON sc.scheduleID=us.scheduleID "
              ."WHERE us.userScheduleMapID =$userSched_MapID";

$commitResult = safe_query($commitQuery);

//asign each column to a global variable
set_result_variables($commitResult);

//Display application complete message if user isCommitted is Y
if ($isCommitted == 1) {
	print "<p><strong>You have already submitted this report.</strong><br>If you have done so in error, please contact <a href=mailto:$adminEmail>$adminEmail</a>";
	
		print "<BR><BR><a href=\"index.php?frmno=$surveyID\">Return Home Page</a>";
	//If user isCommitted is N give them the thank you page
} else {
print "<BR><BR><BR>";
print paragraph("<b>Thank you for completing this application. </b>"); /* Here are your answers:*/

	//The seeding method used below is generally believed to produce the most random results.
	//This is a value that will constantly change, and makes a good seed.
	srand((double) microtime() * 1000000);
	//The above is only need in PHP 4.1 and earlier.
	$conf_num = rand(1000000000, 9999999999);

	print "<font size=2 face=Verdana, Arial, Helvetica, sans-serif><strong>**Confirmation Page**</strong></font><br><font color=FF0000>Thank you!</font><p>";

	print "<strong>Confirmation Number</strong>: $conf_num<br>";
	print "Date Submitted: " . date("D dS M, Y h:i a")."<p>";

	//query to update the userScheduleMap table when the user submits the survey upon completion
	$updInvitequery = "UPDATE userScheduleMap SET isCommitted=1, confirmationNumber=\"$conf_num\" , dateCommitted = CURDATE() WHERE UserID=$userID AND userScheduleMapID=$userSched_MapID;";
	$InvResult = safe_query($updInvitequery);

	//send an email to the admin to let know
	$body = "$userFirstname $userLastname as completed the $surveyTitle for $dueDate";
	mail ($adminEmail, "$surveyTitle completed by $userFirstname $userLastname", $body, "From: $adminEmail");

	//send an email to each reviewer for this report
	if(is_array($arrReviewers)){
		foreach ($arrReviewers as $thisEmail){
			   mail ($thisEmail, "$surveyTitle completed by $userFirstname $userLastname", $body, "From: $adminEmail");
		}
	}
//TODO PUT REAL HOME PAGE
	print "<a href=\"index.php?frmno=$surveyID\">Return to Application Home Page</a>";
}
	print "</DIV>";
include "end_page.php";
?>
