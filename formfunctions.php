<?php
/*
********************************************************
*** This script from MySQL/PHP Database Applications ***
***         by Jay Greenspan and Brad Bulger         ***
***                                                  ***
***   You are free to resuse the material in this    ***
***   script in any manner you see fit. There is     ***
***   no need to ask for permission or provide       ***
***   credit.                                        ***
********************************************************
*/
/*
Application: Survey
Described in: Chapter 9
Name: functions.php
Purpose: This script defines functions used by this application.

It is accessed by an include statement in header.php.

*/

// string weekstart ([string date])
// This function returns SQL to determine the beginning date of the
// week that contains the date specified in the first argument.
// if no date is given, the current date is used. if the first
// argument is "create_dt", this value is used as a literal
// value, to get the weekdate of values in the create_dt field
// of the responses table.
function weekstart ($when="")
{
    if (empty($when)) { $when = "now()"; }
    elseif ($when != "create_dt") { $when = "'$when'"; }
    return "from_days(to_days($when)-dayofweek($when) + 1)";
}

// array country_list (void)
// This function returns an array containing an initial blank entry
// followed by a list of all the countries in the countries database table.
function country_list ()
{
    $countries[""] = "";
    $countries = array_merge($countries
        ,db_values_array("countries","country")
    );
    return $countries;
}

// array state_list (void)
// This function returns an array containing an initial blank entry
// followed by a list of US states, as returned by the states()
// function (defined in /book/functions/basic.php).
function state_list ()
{
    $states[""] = "";
    $states = array_merge($states, states());
    return $states;
}

// int fetch_question ([int question_id])
// This function uses the fetch_record() function (defined in
// /book/functions/db.php) to get information about a question
// from the database and set the values returned as global
// variables.
function fetch_question ($question_id="")
{
    if (empty($question_id)) { $question_id = 0; }
    $result = fetch_record("questions","question_id",$question_id);
    return $result;
}

// int fetch_user ([int user_id])
// This function uses the fetch_record() function to get information 
// about a user from the database and set the values returned as global
// variables.
function fetch_user ($user_id="")
{
    if (empty($user_id)) { $user_id = 0; }
    $result = fetch_record("users","user_id",$user_id);
    return $result;
}

//WFM-Added 20030928
// int getdependencystatus([int DepQID])
// This function uses the references the database to see if a dependent value has been set
// variables.
function getdependencystatus( $SUserID, $DepQID= 0){
    $depstatus = FALSE;
    $SQL = "SELECT answers.answer, choices.choiceDescription "
         . "FROM answers LEFT JOIN choices ON answers.choiceID = choices.choiceID "
         . "WHERE (((answers.questionID)=$DepQID) AND ((answers.userID)=$SUserID));";

    $result = safe_query($SQL);
    while ($row = mysql_fetch_assoc($result))  {
          if ($row["choiceDescription"]!="--Select One--"){
               $depstatus  = TRUE;
          }
    }


    return $depstatus;
}

function get_dependent_answers($UserID,$QuestionID){
  $SQL = "SELECT choices.choiceID, choices.QuestionID, "
       . "choices.ChoiceDescription, choices.ChoiceNumber "
       . "FROM Questions, Answers, DependentFields, Choices "
       . "WHERE (Answers.choiceID = DependentFields.MotherChoiceID) "
       . "AND (Questions.DependentQID = Answers.QuestionID) "
       . "AND (Questions.QuestionID = choices.QuestionID) "
       . "AND (DependentFields.ChildChoiceID = choices.choiceID) "
       . "AND (Questions.QuestionID = $QuestionID) "
       . "AND (Answers.UserID = $UserID);";
}

function getdepchoice($UserID,$MotherQID){
  $depchoice  = NULL;
  $SQL = "SELECT Answers.choiceID "
       . "FROM Answers "
       . "WHERE (Answers.QuestionID =$MotherQID) AND (Answers.UserID = $UserID);";
  $result = safe_query($SQL);
  $row = mysql_fetch_row($result);
  $depchoice = $row[0];
  return $depchoice;


}
function getdependchildren ($depchoice) {
  $SQL = "SELECT choices.choiceID, choices.choiceDescription "
       . "FROM  dependentFields, choices "
       . "WHERE dependentFields.motherChoiceID = $depchoice "
       . "AND (dependentFields.childChoiceID = choices.choiceID) "
       . "ORDER BY choices.choiceNumber;";
  $depchildren = array();
      $result = safe_query($SQL);
    if ($result)
    {
        while (list($value,$label) = mysql_fetch_array($result))
        {
            $depchildren[$value] = $label;
        }
    }
  return $depchildren;
}
function getUserIDfromUserName($name){
	$retVal = FALSE;
	$sqlGetUserID = "Select userID from users where username = lower('$name')";
	$result = safe_query($sqlGetUserID);
	if($result){
		$row = mysql_fetch_array($result);
		$retVal= $row[0];  
	}
	return $retVal;
}


function getNumRegister($schedID){
			$sqlGetNumRegistered = "SELECT sm.userScheduleMapID, u.userID " 
							 . "FROM userScheduleMap sm "
							 . "JOIN schedule sc ON sc.scheduleID = sm.scheduleID "
							 . "JOIN users u ON u.userID = sm.userID "
							 . "WHERE sc.scheduleID = $schedID";
	 		$result = safe_query($sqlGetNumRegistered);
	 		$numRegistered = mysql_num_rows($result);
			return $numRegistered;
}

function getNumStarted($schedID){
			$sqlGetNumStarted= "SELECT sm.userScheduleMapID, u.userID " 
							 . "FROM userScheduleMap sm "
							 . "JOIN schedule sc ON sc.scheduleID = sm.scheduleID "
							 . "JOIN users u ON u.userID = sm.userID "
							 . "WHERE sc.scheduleID = $schedID "
							 . "AND sm.dateStarted IS NOT NULL " ;
	 		$result = safe_query($sqlGetNumStarted);
	 		$numStarted = mysql_num_rows($result);
			return $numStarted;
}
function getNumFinished($schedID){
			$sqlGetNumFinished = "SELECT sm.userScheduleMapID, u.userID " 
							 . "FROM userScheduleMap sm "
							 . "JOIN schedule sc ON sc.scheduleID = sm.scheduleID "
							 . "JOIN users u ON u.userID = sm.userID "
							 . "WHERE sc.scheduleID = $schedID "
							 . "AND sm.dateStarted IS NOT NULL " 
							 . "AND sm.isCommitted <> 0 ";
	 		$result = safe_query($sqlGetNumFinished);
	 		$numFinished = mysql_num_rows($result);
			return $numFinished;
}

function isAdminOrReviewer($userID, $survID){
			$retVal = false;
			$sqlCheckUserAdminReviewer= "SELECT am.adminSurveyMapID, u.userID " 
							 . "FROM adminSurveyMap  am "
							 . "JOIN survey s on s.surveyID = am.surveyID "
							 . "JOIN users u ON u.userID = am.adminUserID "
							 . "JOIN userTypes ut on ut.userTypeID = u.userTypeID "
							 . "WHERE s.surveyID = $survID "
							 . "AND u.userID = $userID " 
							 . "AND (ut.userType = 'administrator'  OR  ut.userType = 'reviewer')";
	 		$result = safe_query($sqlCheckUserAdminReviewer);
	 		$numRet = mysql_num_rows($result);
	 		if ($numRet>0){
	 			$retVal = true;
	 		}
			$sqlCheckUserAdminReviewer= "SELECT rm.reviewSurveyMapID, u.userID " 
							 . "FROM reviewSurveyMap  rm "
							 . "JOIN survey s on s.surveyID = rm.surveyID "
							 . "JOIN users u ON u.userID = rm.reviewUserID "
							 . "JOIN userTypes ut on ut.userTypeID = u.userTypeID "
							 . "WHERE s.surveyID = $survID "
							 . "AND u.userID = $userID " 
							 . "AND (ut.userType = 'administrator'  OR  ut.userType = 'reviewer')";
	 		$result = safe_query($sqlCheckUserAdminReviewer);
	 		$numRet = mysql_num_rows($result);
	 		if ($numRet>0){
	 			$retVal = true;
	 		}
	 		
	 		
	 		
			return $retVal;
}

?>
