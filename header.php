<?php
/*

If the constant LOADED_HEADER is not defined, this script will
include the basic function files used by all of these examples,
connects to the MySQL survey database, and define functions used
in this example. It will also check to see if the user is
coming from a blocked domain. If so, the script prints out
an error and exits.

It is accessed by an include statement at the beginning of each
page in this application.

*/

if(!defined("LOADED_HEADER"))
{
	if(substr(PHP_OS,0,3) == "WIN"){
		define('INCLUDE_PATH', './includes/');
	}else 	{
		if(defined('ROOT_PATH')){
		define('INCLUDE_PATH', ROOT_PATH . '/includes/');
		}
	}

	$includePath = get_include_path();
//	set_include_path( $includePath . ":" . INCLUDE_PATH);
//	set_include_path('/includes/');
	set_include_path(INCLUDE_PATH);
	include ("basic.php");
	include ("appConfig.php");
	dbconnect($GLOBAL_DATABASE,$GLOBAL_DATABASE_USER,$GLOBAL_DATABASE_PASSWORD,$GLOBAL_DATABASE_URL);
	include("functions.php");
	define("LOADED_HEADER", "yes");
}
//Make some attempt to identify the particular form/group that the user is wants
// in order to select the start and end page stuff, css, and logo's

//CHECK THE URL STRING FOR A FORM Identifier --a form id, a schedule ID or a user schedule map ID
//First see if there is a schedule-user reference
// then check where that reference wants to go. If necessary check authentication
//
//TODO this is a hack that solves a problem when the query string doesn't give any good rsults from the database
$GLOBAL_loginPage = $GLOBAL_DEFAULT_loginPage;
$GLOBAL_AuthenticationMethod= $GLOBAL_DEFAULT_AUTHENTICATION_METHOD;
$GLOBAL_Page_Title = "";
$surveyWHEREclause="";
$scheduleWHEREclause ="";
$userScheduleMapWHEREclause= "";
$theJOINClause ="";
$userScheduleMapID=-1;
$clean = array();
$clean['frmno']=-1;
if(isset($_GET['frmno'])) {
	$clean['frmno']= (ctype_digit($_GET['frmno']) ? $_GET['frmno']: -1 ) ;
}

if($clean['frmno']!=-1){
	$surveyID =$clean['frmno'];
	$surveyWHEREclause = " AND s.surveyID = '$surveyID' ";
} else {
	//lets be kind to legacy links that may have survID
	if(isset($_GET['survID'])) {
		$clean['frmno']= (ctype_digit($_GET['survID']) ? $_GET['survID']: -1 ) ;
	}
		if($clean['frmno']!=-1){
			$surveyID =$clean['frmno'];
			$surveyWHEREclause = " AND s.surveyID = '$surveyID' ";
	}
}

//TODO GET RID OF THIS LINE $surveyID =$clean['frmno'];
$clean['ver']=-1;
if(isset($_GET['ver'])) {
	$clean['ver']= (ctype_digit($_GET['ver']) ? $_GET['ver']: -1 ) ;
}
if($clean['ver']!= -1){
	$scheduleID = $clean['ver'];
	$scheduleWHEREclause = " AND sc.scheduleID = '$scheduleID'";
	$theJOINClause = " JOIN schedule sc ON s.surveyID = sc.surveyID ";//
}

$clean['ins']=-1;
if(isset($_GET['ins'])) {

	$clean['ins']= (ctype_digit($_GET['ins']) ? $_GET['ins']: -1 ) ;
}else if(isset($_POST['ins'])){
	$clean['ins']= (ctype_digit($_POST['ins']) ? $_POST['ins']: -1 ) ;
}
if($clean['ins']!= -1){
	$userScheduleMapID = $clean['ins'];
	$theJOINClause = " JOIN schedule sc ON s.surveyID = sc.surveyID " .
	                 " JOIN userScheduleMap usm ON sc.scheduleID = usm.scheduleID ";
	$userScheduleMapWHEREclause = " AND usm.userScheduleMapID = '$userScheduleMapID'";
}else {
	//LEts be kind to legacy tr systems

	if(isset($_GET['tr'])) {
		$clean['ins']= (ctype_digit($_GET['tr']) ? $_GET['tr']: -1 ) ;
	}
	if($clean['ins']!= -1){
		$userScheduleMapID = $clean['ins'];
		$theJOINClause = " JOIN schedule sc ON s.surveyID = sc.surveyID " .
		                 " JOIN userScheduleMap usm ON sc.scheduleID = usm.scheduleID ";
		$userScheduleMapWHEREclause = " AND usm.userScheduleMapID = '$userScheduleMapID'";
	}
	
}


if(!($clean['frmno']==-1 && $clean['ver']==-1 && $clean['ins']==-1)) {
	$chooseSurveyInstance = "SELECT s.surveyID, s.surveyTitle, s.surveyTitle,
						s.styleSheet,
						s.banner,
						s.surveyContact,
						s.introBlurb,
						s.loginPage,
						s.footer,
						s.surveyContact,
						s.authenticationMethod,
						s.startingPoint  
						FROM survey s
						$theJOINClause
						WHERE 1=1 
						$surveyWHEREclause
						$scheduleWHEREclause
						$userScheduleMapWHEREclause
						"; //TODO Decide if limit one is appropriate
						$resultSurveyInstance = safe_query($chooseSurveyInstance);

						if($resultSurveyInstance ){

							if(mysql_num_rows($resultSurveyInstance)){
								//echo "NUM:". mysql_num_rows($resultSurveyInstance); //TODO Remove commenting
								//echo "<BR>Q:" . $chooseSurveyInstance; //TODO Remove commenting
								$row = mysql_fetch_assoc($resultSurveyInstance);
								$styleSheet = $row['styleSheet'];
								$GLOBAL_banner_logo_location = $row['banner'];
								$GLOBAL_Page_Title = $row['surveyTitle'];
								//TODO one of these has to go
								$page_title = "NEED TO UPDATE " .$row['surveyTitle'];

								$loginPage = $row['loginPage'];
								$GLOBAL_loginPage = empty($loginPage) ? $GLOBAL_DEFAULT_loginPage: $loginPage;
								$footer = $row['footer'];
								$GLOBAL_footer = empty($footer) ? $GLOBAL_DEFAULT_FOOTER_FILE_LOCATION: $footer;  
								//echo "<BR>LoginPage: $GLOBAL_loginPage";
								$surveyContact = $row['surveyContact'];
								$GLOBAL_SiteAdmin = empty($surveyContact) ? $GLOBAL_DEFAULT_SITE_ADMIN: $surveyContact;
								$authenticationMethod = $row['authenticationMethod'];
								$GLOBAL_AuthenticationMethod = empty($authenticationMethod) ? $GLOBAL_DEFAULT_AUTHENTICATION_METHOD: $authenticationMethod;
								$GLOBAL_StartingPoint = $row['startingPoint'];
								$GLOBAL_SurveyID = $row['surveyID'];
								//TODO define $GLOBAL_Content
							}else {
								//TODO This outcome is possible when there is no frm associated with the survey (formno);
								// is it also possible under other circumstances?
								//if so then some information needs to be communicated to the developer and/or user
								//Also possible under conditions of a bad survery-schedule-usm combinations
								$GLOBAL_NO_FORM = "No Form";
							}
						} else die("Please contact site administrator");
} else {
	$GLOBAL_NO_FORM = "No Form;Bad Request";
}	//else echo "Bad Query string BoilerPlate page ";
?>