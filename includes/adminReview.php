<?php
/* adminReview.php
 * Created on Sep 8, 2005by William Morse
 *
 * Description:
 * Inputs:
 * Outputs:
 * $htmlOut is expected to be asigned a value.
 * Known uses
 */
include_once("./header.php");
$postOut = "";
    $postOut .= "Post variables: <BR>". "\n";
foreach ($_POST as $key => $value) {
		$postOut .= "$key --> $value" . "<BR>\n"; 
	$$key = $value;
}
foreach ($_GET as $key => $value) {
	$$key = $value;
}


$thisPage = $_SERVER['PHP_SELF'];


$printPage = "adminPrint.php";
$action= "$thisPage?uc=survey&survID=$survID&Woption=ReviewForms";
$authorized = isAdminOrReviewer($userID,$survID);

if (!$authorized)  {
   echo "<p><strong> It does not appear that this report/form is available. </strong><br>Please report problems to the site administrator. ";
   die;
   }
   
$selectedUsers= NULL;
if (isset($users) ){
	$selectedUsers = $users;
}
if (isset($el)){
	$selectedSchedules = $el;
}

   
if (!isset($hiddenFields)){
	$hiddenFields="";
}
if (!isset($htmlLeft)){
	$htmlLeft="";
}
if (!isset($htmlOut)){
	$htmlOut="";
}

//JAVASCRIPT
$js =<<<EOQ
<script LANGUAGE="javascript">
        function jumpsubmit(){

        var thisForm = document.forms[0];
		var elts =thisForm.elements['equc'];
 		var elts_cnt  = (typeof(elts.length) != 'undefined')
                  ? elts.length
                  : 0;
        var myAction = "fubar";
    if (elts_cnt) {
        for (var i = 0; i < elts_cnt; i++) {
        	if (elts[i].checked ){
        		myAction =elts[i].value;
        		break;
        	}
        }
    }
    else{
    	myAction = "fubar";
    }  			
	if (myAction == "viewAll"){
		
		var myurl = "$printPage?uc=survey&survID=$survID&Woption=" + myAction;
		var scheds = thisForm.elements['el[]'];
		var scheds_cnt  = (typeof(scheds.length) != 'undefined')
                  ? scheds.length
                  : 0;		
	    if (scheds_cnt) {
	    	var weCool = false;
    	    for (var i = 0; i < scheds_cnt; i++) {
        		if (scheds[i].checked ){
        			myAction =scheds[i].value;
        			var weCool = true;
        			break;
        		}
        	}
			if (!weCool){
				return false;
        	}
        }

	}else if (myAction == "selUser"){
		var myurl = "$thisPage?uc=survey&survID=$survID&Woption=" + myAction;
		var users = thisForm.elements['usmArr[]'];
        if (typeof(users)!= 'undefined'){
	    	myurl = "$printPage?uc=survey&survID=$survID&Woption=" + myAction;
	    }
	}else 
	
	{

		var myurl = "$thisPage?uc=survey&survID=$survID&Woption=" + myAction;
	}	  
    thisForm.action = myurl;
    thisForm.submit();
	return true;
}
</script>
EOQ;
//JAVASCRIPT



$hiddenFields .= hidden_field("survID", $survID);

//check boxes for scheduled forms/reports 
$listOfScheduledItems= "";
			$whereClause = "( s.surveyID = $survID ) AND (s.isActive = 1 )";
			$sqlGetSchedule = "SELECT DATE_FORMAT(s.dateDue,'%b %e, %Y') as dateDue, "
				. "s.isActive , s.dateDue as ddate, s.surveyID,  s.scheduleID,s.isActive, v.surveyTitle, " 
				. "(NOW()>s.dateDue) as IsPassed "
				. "FROM schedule s "
				. "JOIN survey v ON v.surveyID = s.surveyID "
				. "WHERE " . $whereClause 
				. "ORDER BY s.dateDue";
		$resultsGetSchedule = safe_query($sqlGetSchedule );
		$tmpArr = array();
		$class = "noclass";
		$schedTableLabel= "\n<BR><SMALL>Schedule for Reports/Forms </SMALL><BR>";
		$schedTable = $schedTableLabel. "\n<TABLE name=table" . $key . " class=$class BORDER = 1><COLGROUP><COL width=10><COL width=0*>";
		$schedTable .= "<TR><TD colspan=3>&nbsp;&nbsp;</TD><TD>Users</TD><TD>Started</TD><TD>Complete</TD>";
		while ($rowGetSchedule= mysql_fetch_assoc($resultsGetSchedule )){
			$status = "";
			if ($rowGetSchedule['IsPassed']==1){
				$status = "Passed Due";
			}else
			if ($rowGetSchedule['isActive']==1){
				$status = "Open";
			}else{
				$status= "Not Open";
			}
			
			$schedID = $rowGetSchedule['scheduleID'];
			
	 					
			$thisRow = "\n\t<TR>";
			$thisSchedID=$rowGetSchedule['scheduleID'];  
			$checkBox = "<INPUT type = checkbox name = el[] value= $thisSchedID>"; 
			$thisRow.="\n\t\t<TD>" . $checkBox . "</TD>";
			
			$thisRow.= "\n\t\t<TD>" . $rowGetSchedule['dateDue']. "</TD>";
			
			$thisRow.= "\n\t\t<TD>" . $status . "</TD>";
			$numRegistered = getNumRegister($schedID);
			$thisRow.= "\n\t\t<TD>" . $numRegistered . "</TD>";

			$numStarted = getNumStarted($schedID);
			$thisRow.= "\n\t\t<TD>" . $numStarted . "</TD>";

			$numFinished = getNumFinished($schedID);
			$thisRow.= "\n\t\t<TD>" . $numFinished. "</TD>";

			$thisRow.="</TR>";		
					
			$schedTable.=$thisRow;	
			}
		$schedTable .= "\n</TABLE>";
		$listOfScheduledItems.= "<BR>" . $schedTable;
		$selectAllCheckBox = "<INPUT type = checkbox name = selAll value= selAll onclick=\"setCheckboxes('schedform', true); return false;\">";
		$selectAllCheckBox .= " Check all listed schedules";
		$listOfScheduledItems.=$selectAllCheckBox; 
		
$listOfUsers= "";		
if(isset($Woption) and ($Woption == "selUser")){
//MARKER
			$whereClause = " v.surveyID = $survID AND ( 0 ";
//Get selected schedules to put into where clause--
	if (!isset($el) OR !is_array($el) ){
		
	}else{
	foreach($el as $key => $value){
		$thisSchedule = $value;
		$whereClause .= " OR s.scheduleID = $value ";	 
	}
	}            	
    $whereClause .= ")";
			$sqlGetUsers = "SELECT usm.userScheduleMapID, u.userLastname,u.userFirstname,  "
			    . " DATE_FORMAT(usm.dateCommitted,'%b %e, %Y')  as complete, "
			    . " DATE_FORMAT(usm.dateStarted,'%b %e, %Y')  as started " 
				. "FROM users u "
				. "JOIN userScheduleMap usm ON usm.userID = u.userID "
				. "JOIN schedule s ON s.scheduleID = usm.scheduleID "				
				. "JOIN survey v ON v.surveyID = s.surveyID "
				. "WHERE " . $whereClause; 

		$resultsGetUser = safe_query($sqlGetUsers );

		$class = "noclass";
		$userTableLabel= "\n<BR><SMALL>Users for Selected Forms</SMALL><BR>";
		$userTable = $schedTableLabel. "\n<TABLE name=table" . $key . " class=$class BORDER = 1><COLGROUP><COL width=10><COL width=0*>";
		$userTable .= "<TR><TD colspan=3>&nbsp;&nbsp;</TD><TD>Started</TD><TD>Date Completed</TD>";
		while ($rowGetUser= mysql_fetch_assoc($resultsGetUser )){
			$status = "";
			
			$userLastname = $rowGetUser['userLastname'];
			$userFirstname = $rowGetUser['userFirstname'];
	 		$dateComplete = $rowGetUser['complete'];
	 		$dateStarted = $rowGetUser['started'];			
			$thisRow = "\n\t<TR>";
			$thisUserID=$rowGetUser['userScheduleMapID'];  
			$checkBox = "<INPUT type = checkbox name = usmArr[] value= $thisUserID>"; 
			$thisRow.="\n\t\t<TD>" . $checkBox . "</TD>";
			$thisRow.= "\n\t\t<TD>" . $userLastname. "</TD>";
			$thisRow.= "\n\t\t<TD>" . $userFirstname. "</TD>";
			$thisRow.= "\n\t\t<TD>" . $dateStarted. "</TD>";
			$thisRow.= "\n\t\t<TD>" . $dateComplete. "</TD>";						
			$thisRow.="</TR>";		
					
			$userTable.=$thisRow;	
			}
		$userTable .= "\n</TABLE>";
	
	
//MARKER
$listOfUsers.=$userTable ;
}		

//User interaction with this page
$radioChoices = "";
$radioChoices .= "<BR>".radio_field("equc", "viewAll", "View for All Users");
$radioChoices .= "<BR>".radio_field("equc", "showMiss", "Show Missing Names");
$radioChoices .= "<BR>".radio_field("equc", "selUser","Select Individual Users");
//."&nbsp;&nbsp;".select_field("dirNew", $dirNewArray)." Question # ".select_field("qtargetNew", $qArray)."<SMALL> (question numbers ignored if not needed )</SMALL>";
$goButton = "<button class=choice width = 10% onClick='jumpsubmit();'><font color=\"black\" face=\"verdana narrow\" size=1.5><b>GO!</b></font></button>";
$radioChoices.= paragraph($goButton);

//htmlOut is the expected to be filled bu this script
//Html left is not expected but it will be used to contain user actions
//IT is to be included in the page's form.
$htmlLeft .= "\n<FORM method= post name = schedform action=$action >\n";
$htmlLeft .= $radioChoices;
$htmlOut .= $listOfScheduledItems;
$htmlOut .= "<BR>".$listOfUsers;
$htmlOut .= $hiddenFields;
$htmlOut .= end_form();
?>