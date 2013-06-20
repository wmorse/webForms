<?php
$userScheduleMapID=NULL;
include ("./header.php"); //will change userscheduleMapID to -1 or process GET/POST variables 
						 // a different (hopefully valid) one
$userID=NULL;
include("./authenticate.php"); //can/should change userID

//TODO might want to check userID/userScheduleMap combos before posting stuff
//Post the variableto the database if there are any
if (isset($_POST['QuestionIDarr'])){
   include("./includes/postsurvey.php");
}

if(isset($_GET['logout'])){

	//user has requested to logout
	//reset token and logout
	logoutUser();
	//redirect to login page.
	print "<meta http-equiv=\"refresh\" content=\"2; URL=$GLOBAL_loginPage\">";
		die("Logout");
}
include("./includes/QuesTypes.php");
include ("./includes/surveyfunctions.php");
$surveySession = new SurveySessionObject();
$user = new User($userID);
$surveySession->setUserObject($user);

$userLoginLogoutBlock = getUserLoginLogoutBlock($surveySession->user);
include "start_page.php";

$thisPage = $_SERVER['PHP_SELF'];
$queryString = $_SERVER['QUERY_STRING'];

$htmlSideBar="";

//Have we failed up to this point in identifying the form-schedule-user combo?
//TODO check for multiform users

if(!isset($GLOBAL_SurveyID)){ //requested form doesn't exist, or is otherwise not available
	$mainAreaContent= $GLOBAL_NO_FORM;
}else{ //requested form exists -- can the current user have it?
	$surveyID=	$GLOBAL_SurveyID;
	$survey = new Survey($surveyID);
	$surveySession->setSurveyObject($survey);	
	//If user is requesting to be added to the currently open form for the given surrvey, then add him before moving on
	if(isset($_GET['addReq'])&& isset($userID)){
		//TODO Add check for addreq wholesomeness
		$clean['addReq']=$_GET['addReq'];
		if ($clean['addReq']==123){
			if(isOpenEnroll($surveyID)){
				$schedForInsert= getMostRecentSchedule($surveyID);
				if($schedForInsert){
					$sql = "INSERT INTO userScheduleMap (userID, scheduleID) 
			                VALUES ('$userID', '$schedForInsert')";
					  $result= mysql_query($sql);
					  if($result){
					  	$userScheduleMapID = mysql_insert_id();
	    			}
				} 		   	
			}
		} 
	}
	
	$testUserID=$userID;//Simply create most common condition to initialize testUser
	if($userScheduleMapID== -1){
		$userScheduleMapID =getSingletonFromUserSurvey($userID,$surveyID);
	}else{
		//CHECK that this user has access to this form
		$testUserID=getUserIDFromUserScheduleMapID($userScheduleMapID);
		//later we will see if the authenticated user matched the userscheduleMap user
	}
	//Figure  out where on the survey we are by getting the index of the survey page.
	$secArray= getSectionArray($GLOBAL_SurveyID);
	$sectionsSize = sizeof($secArray);
	
	$clean = array();
	$clean['curSecInd']=0;
	if  (isset($_GET['curSecInd'])){ 
	$clean['curSecInd']= (( ctype_digit($_GET['curSecInd'])  
						&	$_GET['curSecInd']<= $sectionsSize & $_GET['curSecInd']>= 0) ? $_GET['curSecInd'] : 0 );
	}

	//Decide if we show the form or the index page.
	//If form is already committed
	//if no schedule map can be found
	//if user asked for another form
	// show the index of all forms for the user
	
	//Some reasons to show this content
	// 1) User is authenticated, but has not presented a valid survey or usmID
	// 2) User is authenticated but has multiple forms that match the requested survey (weekly report)
	// 3) User is authenticated, and form is valid, but marked as completed
	if ((isset($isCommitted) AND ($isCommitted == 1)) || (empty($userScheduleMapID)||$userScheduleMapID==-1 )||(!($testUserID==$userID))) {
	
	    //	Display application complete message if user isCommitted is 1
		if (isset($isCommitted) AND ($isCommitted == 1)){
			print "<p><strong>You have already submitted your form.</strong><br>
			       If you have done so in error, please contact <a href=mailto:$surveyContact>$surveyContact</a><BR>";	
		}
		include("./includes/appindex.php");
		$mainAreaContent=getAppIndex($userID);
			//XXX  change $page_title to GLOBAL page title??
			if(isOpenEnroll($GLOBAL_SurveyID)){
				$schedForInsert= getMostRecentSchedule($GLOBAL_SurveyID);
				if($schedForInsert){
					//If it's already in the list then the mapID is not null
				    if (!getMapID($userID,$schedForInsert)){
	      				$mainAreaContent.= "<BR>You have not previously enrolled in the form you requested.
	      				       <BR>Click here to add it to your forms and begin: 
	      				       <A href=\"$thisPage"."?$queryString&addReq=123\" > Add $GLOBAL_Page_Title</A>";			    	
				    }
				} 		   	
			}
	   
	    
	//Show the real form then --If user isCommitted is N give them the survey page
	} else {
	$schedule = new ScheduleOfUSM($userScheduleMapID);
	$userScheduleMapObj = new UserScheduleMap($userScheduleMapID);
	$surveySession->setScheduleObject($schedule );
	$surveySession->setUserScheduleMapObject($userScheduleMapObj);
	$mainAreaContent= getSurvey($surveyID,$userScheduleMapID,$clean['curSecInd']);
	$htmlSideBar= getSideBar($surveySession);
	}
}

$headerContent= <<<EOQ
<a class="logo" href="http://www.example.org"><img alt="" src="$GLOBAL_banner_logo_location" class="logo"/></a>&nbsp;$GLOBAL_Page_Title
EOQ;
//die($userLoginLogoutBlock);
//
$headerContent=getHeaderContent();
$htmlOut = <<<EOQ
<div id="container">
  <div id="header"><h1>$headerContent</h1>$userLoginLogoutBlock</div>
  <div id="wrapper">
    <div id="content">
    $mainAreaContent
    </div>
  </div><div id="navigation">$htmlSideBar</div>
  <div id="extra">Extra stuff</div>
  <div id="footer">Footer</div>
</div>  
EOQ;
print $htmlOut;
$surveySession->display();
?>