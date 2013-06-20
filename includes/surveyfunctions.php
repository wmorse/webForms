<?php
function getTailPage($userScheduleMapID){
	$sql = "SELECT tailPage from Survey
	        Join schedule on survey.surveyID = schedule.surveyID 
	        Join userScheduleMap on userScheduleMap.scheduleID = schedule.scheduleID
	         WHERE userScheduleMapID = '" . $userScheduleMapID . "';";
	die("Need som work here: $sql");

}

function getJSControls(){
	$thisPage = $_SERVER['PHP_SELF'];
	$jsControls= <<<EOQ
<script LANGUAGE="javascript">
        function jumpToPage(surv,maxindex){

           var thisForm = document.forms[0];
           var myindex = thisForm.pageShowing.value;
           var mytarget = 0;

           if (myindex >= maxindex + 1){
              thisForm.pageShowing.value = maxindex;
              mytarget = maxindex -1;
           }
           else if (myindex >= 1 && myindex < maxindex+1){
              mytarget = myindex -1;
           }
           else {
              mytarget = 0; 
              thisForm.pageShowing.value = 1;
           }

           var myurl = "$thisPage" + "?frmno=" + surv + "&curSecInd=" + mytarget; 
                 thisForm.action = myurl;
                 thisForm.submit();

        }

        //function to jump to a page
        function jump(surv,maxindex,curindex,offset){
           //offset: -1 equals back, +1 equals forward
           //can this be done as named constants?         

           var thisForm = document.forms[0];
           var myindex = curindex + offset;
           var mytarget = 0;

           if (myindex >= maxindex + 1){
              thisForm.pageShowing.value = maxindex;
              mytarget = maxindex -1;
           }
           else if (myindex >= 1 && myindex < maxindex+1){
              mytarget = myindex -1;
              thisForm.pageShowing.value = myindex;
           }
           else {
              mytarget = 0; 
              thisForm.pageShowing.value = 1;
           }

           var myurl = "$thisPage" + "?frmno=" + surv + "&curSecInd=" + mytarget; 
                 thisForm.action = myurl;
                 thisForm.submit();
        }
        
        function jumpInPlace(surv,curindex){
           var thisForm = document.forms[0];
           var mytarget = curindex ;
           var myurl = "$thisPage" + "?frmno=" + surv + "&curSecInd=" + mytarget; 
                 thisForm.action = myurl;
                 try{
                 	if($("#commentForm").validate().form()){
                 
                 		thisForm.submit();
               
                 }else{
                 	var answer = confirm("There are required fields, or unacceptable responses. Click OK to save page as is");
                 	if (answer){
                 		thisForm.submit();
                 	}
                 	
                 }
                 }catch(e){
                	alert(e);
                }
        }

        function jumpInPlaceButton(surv,curindex){
           var thisForm = document.forms[0];
           var mytarget = curindex ;
           var myurl = "$thisPage" + "?frmno=" + surv + "&curSecInd=" + mytarget; 
                 thisForm.action = myurl;
        }
        
        function jumpInPlaceLogout(surv,curindex){
           var thisForm = document.forms[0];
           var mytarget = curindex ;
           var myurl = "$thisPage" + "?frmno=" + surv + "&curSecInd=" + mytarget +"&logout=1"; 
                 thisForm.action = myurl;
                 try{
                 	if($("#commentForm").validate().form()){
                 
                 		thisForm.submit();
               
                 }else{
                 	var answer = confirm("There are required fields, or unacceptable responses. Click OK to save page as is");
                 	if (answer){
                 		thisForm.submit();
                 	}
                 	
                 }
                 }catch(e){
                	alert(e);
                }
        }        
</script>
EOQ;

	return $jsControls;
}

function getSectionArray($surveyID){
	//Get Survey sections an put in array
	$surArray = array();
	$surSecQuery =   "SELECT surveySectionID, surveySectionTitle FROM surveySections
   		               WHERE  surveyID = '$surveyID' 
                  ORDER BY sectionOrdinal;";
	$surSecqresult = safe_query($surSecQuery);
	while ($row = mysql_fetch_row($surSecqresult)){
		$surArray[] = $row[0];

	}
	return $surArray;
}

function getTableOfContents($userScheduleMapID,$saveByJSMode=true){
	global $GLOBAL_DEBUG_BORDER;
	$thisPage = $_SERVER['PHP_SELF'];
	$tocHTML = "";
	// pull the user id from the database
	$userquery  =
"SELECT  `users`.`userID`,`userLastname`,`userFirstname`,`userEMail` FROM `users` 
JOIN `userScheduleMap` on `users`.`userID` = `userScheduleMap`.`userID`  
WHERE `userScheduleMap`.`userScheduleMapID` = '" . $userScheduleMapID . "';";
	$userResult = safe_query($userquery);
	//asign each column to a global variable
	set_result_variables($userResult);

	$survID = getSurveyByBUserSCheduleMap($userScheduleMapID);
	$sectionquery  = "SELECT a.surveySectionID, a.surveyID, a.surveySectionInform, a.surveySectionTitle, a.surveySectionUserMa, a.surveySectionInstru, a.sectionOrdinal
                FROM surveySections a WHERE a.surveyID = \"$survID\" ORDER BY a.sectionOrdinal;"; 
	$sectionResult = safe_query($sectionquery);
	$numsections = mysql_num_rows($sectionResult);


	if ($numsections < 1 ){
		$tocHTML .= "<h5>Click on the Section Name to go directly to <BR>that section of the application.<BR>(Save your work first)</H5>";
		$tocHTML .= "<table border = $GLOBAL_DEBUG_BORDER class=\"contents\">";
		$tocHTML .= "<tr ><th colspan = 3>Section Name</tr>";

		$tocHTML .= "<tr class = \"contents\"	><th colspan = 3> <B> No forms available for  $UserFirstname $UserLastname </th></B></tr>"; }
		$i = 0;
		$tocHTML .= "<table>";
		while ($sectrow = mysql_fetch_array($sectionResult,MYSQL_ASSOC))
		{
			// Run query for each Section to determine the number of q's and the number of q's that have
			//answers, and to calculate a quotient. This quotient can then be used to display a percent, and
			//control a gauge (image)

			$tocHTML .= "<tr class = \"contents\">";
			$secOffset = $i;
			if (is_array($sectrow))
			{
				$SurveySectionID = $sectrow["surveySectionID"];
				if($saveByJSMode){
					//Use javascipt to save form data upon jump to new page
					//			$atag ="<a href='javascript:jumpInPlace($survID,$secOffset); return false ;'". "> "
					//			. $sectrow["surveySectionTitle"] . "</a>";
					$atag ="<a href=\"$thisPage?frmno=$survID&curSecInd=$i&tr=$userScheduleMapID "
					. "\" onClick='jumpInPlace($survID,$secOffset); return false;'> "
					. $sectrow["surveySectionTitle"] . "</a>";
				}else{
					$atag ="<a href=\"$thisPage?frmno=$survID&curSecInd=$i&tr=$userScheduleMapID ". "\"> "
					. $sectrow["surveySectionTitle"] . "</a>";
				}



				$tocHTML .=  " <td> " . $sectrow["surveySectionUserMa"]
				. " <td>$atag </td>";
			}
			$tocHTML .= "</tr>";
			$i = $i + 1 ;
		}
		$tocHTML .= "</table>";
		return $tocHTML;
}

function getSideBar($surveySessionObj=null){
	global $GLOBAL_loginPage;

	$htmlSideBar ="No Navigation Available";
	$userScheduleMapID=null;
	$queryString = $_SERVER['QUERY_STRING'];
	$queryString = empty($queryString )? "":"?".$queryString;
	$openCloseText="";
	if($surveySessionObj instanceof SurveySessionObject){
		if ($surveySessionObj->schedule instanceof Schedule){
		
			$schedule = $surveySessionObj->schedule;
			$startDT =$schedule->dateOpened;
			$closingDT =$schedule->dateDue;
			$openCloseText ="Application Open from $startDT - $closingDT";	
		}
		$htmlSideBar ="<p class=\"p2\"><span>$openCloseText</span>
			<br /><br /><strong><a href=\"./accntCREATE.php$queryString\">Click Here</a></strong> to start an Online Application<br /><br />
			Already started an application?<br />
			<strong><a href=\"$GLOBAL_loginPage$queryString\">Login Here</a></strong></span></p>";
		if($surveySessionObj->userScheduleMap instanceof UserScheduleMap){
			$userScheduleMap = $surveySessionObj->userScheduleMap;
			$userScheduleMapID=$userScheduleMap->userScheduleMapID;
		}
		if (!is_null($userScheduleMapID)){
			$tocHTML =getTableOfContents($userScheduleMapID);
			$surveyID = getSurveyByBUserSCheduleMap($userScheduleMapID);
			//$schedule = new ScheduleOfUSM($userScheduleMapID);

			$dueDateBlurb="";
			if(isset($schedule->openDate)){
				$dueDateBlurb=	"Application Open <BR />from $schedule->openDate - $schedule->dateDue";

			}
			else{
				$dueDateBlurb= "Form Due on  $schedule->dateDue";
			}
			$htmlSideBar = <<<EOQ

			<p class="p2">$dueDateBlurb
			<br /><BR /><strong><a href="./index.php?frmno=$surveyID">Home</a></strong> <br /><br /></p>

			$tocHTML
<p class="p3"><strong><a href="./print_app.php?ins=$userScheduleMapID" target =\"_blank\">Click Here</a></strong> to print your application<br /></p>
EOQ;
		}
	}
	return $htmlSideBar;

}

function getSurvey($surveyID,$userScheduleMapID,$curSecInd){

	$thisPage= $thisPage = $_SERVER['PHP_SELF'];
	$nextSecInd = 1;//TODO CHANGE hard val
	$retVal = "";
	$ajs = "";
	$ajs = getAjaxControls();
	$js = $ajs."\n". getJSControls();
	$surveyHTML = "";
	$hiddenFields="";
	if(isset($userScheduleMapID)){
		$hiddenFields .= "\n<INPUT type=hidden name=ins value=$userScheduleMapID >\n";
		//TODO remove?		$addtoQString = "&ins=$tr";
	}
	$surveyHTML .= $js;
	//Get basic info about survey
	$survey = new Survey($surveyID);

	// begin the survey form. if the current section is the last, go tothe tail page
	$surArray= getSectionArray($surveyID);
	if (!isset($curSecInd))
	{
		$curSecInd = 0;
	}

	$curSecID = $surArray[$curSecInd];

	$section = new Section($curSecID );

	$nextSecInd=$curSecInd+1;
	$sizeOfSecArray = sizeof($surArray);
	if($sizeOfSecArray >1 ){
		$useNavINstuments= TRUE;
	}else
	{
		$useNavINstuments=FALSE;
	}

	$sizeOfSecArray= sizeof($surArray);
	if ($sizeOfSecArray > $nextSecInd )
	{
		$gotonext = $nextSecInd;
		$targetString = "$thisPage?frmno=$surveyID&curSecInd=$curSecInd";
	}
	else
	{
		$tailPage= $survey->tailPage;
		$targetString = "$tailPage?ins=$userScheduleMapID";
		$gotonext =$sizeOfSecArray -1;
	}

	$attlist["enctype"]= "multipart/form-data";
	$attlist["id"]="commentForm";
	$surveyHTML .= start_form($targetString,$attlist);
	//TODO put maximum file size as a data element - not as a hard coded thingy
	$surveyHTML .="\n<input type=\"hidden\" name=\"MAX_FILE_SIZE\" value=\"1200000\" /> \n";
	// get the current survey questions from the database and display
	// them to the user for voting.
	$mySubTitle = "";
	if (isset($section->secMarker)){
		$mySubTitle = "$section->secMarker";
	}

	$mySubTitle = $mySubTitle . $section->secTitle;

	if (!isset($pageShowing)){
		$pageShowing = $curSecInd + 1;
	}

	$surveyHTML .= subtitle($mySubTitle);
	$surveyHTML .=$section->surveySectionInform;
	$surveyHTML .= $section->surveySectionInstru;


	//get the user's previous answers
	$answerQuery =  "SELECT a.questionID, answer, choiceID, longAnswer
                                           FROM answers a, questions q  
                WHERE q.surveySectionID = $curSecID
                AND a.questionID = q.questionID 
                AND a.userScheduleMapID = '$userScheduleMapID' 
                ORDER BY a.questionID, choiceID,answer;";

	$answerResult = safe_Query($answerQuery);

	$answerArray = array();
	$indA = 0;
	$indC = 0;
	$indL = 0;
	$tempQID='';
	while ($row = mysql_fetch_assoc($answerResult)) {
		/* convert SQL values to PHP variables */
		while (list($key,$value) = each ($row)) {
			$$key = $value;
		}
		if ($questionID != $tempQID) {
			$indA = 0;
			$indC = 0;
			$indL = 0;
			$tempQID = $questionID;
		}
		if (!(!isset($answer) or $answer=="")) {
			$answerArray[$questionID]["Answer"][$indA] = $answer;
			$indA++;
		}
		if (!(!isset($choiceID) or $choiceID=="")) {
			$answerArray[$questionID]["ChoiceID"][$indC] = $choiceID;
			$indC++;
		}
		if (!(!isset($longAnswer) or $longAnswer=="")) {
			$answerArray[$questionID]["LongAnswer"][$indL] = $longAnswer;
			$indL++;
		}

	}
	//TODO Really need to remove dependency on USM in next line
	$surveyHTML .= buildQuestionsCells($curSecID,$answerArray,$userScheduleMapID);





	$navTool = <<<EOQ
<div class="navTool">       
<br>
<table border="0" cellpadding="0" width="1%" cellspacing="0"
 align="center">
       <tbody>
          <tr align="center" valign="top">

            <td nowrap="nowrap" valign="top">
<a href='javascript:jumpToPage($surveyID,1)'>
         <span class="b">Go to Start</span><img src="./images/prevSection.gif" width="18"
 height="26" alt="" border="0" hspace="5" vspace="5">
           </a> </td>

            <td nowrap="nowrap" valign="top">
<a href='javascript:jump($surveyID,$sizeOfSecArray,$pageShowing,-1)'; return true;>
         <span class="b">Previous Page</span><img src="./images/prevSection.gif" width="18"
 height="26" alt="" border="0" hspace="5" vspace="5">
           </a> </td>
EOQ;
	$middlePart = '<td valign="top" nowrap="nowrap">' . text_field("pageShowing","$pageShowing", 2 );

	$middlePart .= <<<EOQ
<br><a href='javascript:jumpToPage($surveyID,$sizeOfSecArray)'; return true;>
<img src="./images/pageJump.gif" height= "26"  border="0" hspace="5" vspace="5"><br>Go to<br>Page </a></td>
EOQ;

	$navTool .= $middlePart;
	$nextPart =<<<EOQ
            <td nowrap="nowrap"><a
 href='javascript:jump($surveyID,$sizeOfSecArray,$pageShowing,1)'><img
 src="./images/nextSection.gif" width="18" height="26" alt=""
 border="0" hspace="5" vspace="5">
            <span class="b">Next Page</span></a></td>
EOQ;
	if($sizeOfSecArray==$pageShowing){
		$nextPart =<<<EOQ
            <td nowrap="nowrap"><img
 src="./images/nextSection.gif" width="18" height="26" alt=""
 border="0" hspace="5" vspace="5">
            <span class="b">This is the last Page</span></a></td>

EOQ;
	}
	$navTool .=$nextPart;
	$navTool = $navTool . <<<EOQ
            <td nowrap="nowrap" valign="top"><a
 href='javascript:jump($surveyID,$sizeOfSecArray,$pageShowing,$sizeOfSecArray)'; return true;><img
 src="./images/nextSection.gif" width="18" height="26" alt=""
 border="0" hspace="5" vspace="5">
            <span class="b">Go to End</span></a></td>

          </tr>
  </tbody>
</table>
       </div>

<table align="center">
    <tr>
        <td></td>
        <td>&nbsp;</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td><a href="survey_toc.php?frmno=$surveyID&ins=$userScheduleMapID">Return to the Table of Contents</a></td>
        <td></td>
    </tr>
</table>        

EOQ;
	if ($useNavINstuments){
		$surveyHTML .=$navTool;
	}else
	{
		$hiddenFields .= "\n<INPUT type=hidden name=pageShowing value=$pageShowing >\n";
	}
	$partext = "";

	$surveyTitle = $survey->surveyTitle;
	if($sizeOfSecArray == $nextSecInd ) {
		//TODO change submit to call a special function that will validate the whole form --server side with ajax
		$partext  = "<button width = 10% onClick='submit();'><font color=\"black\" face=\"verdana narrow\" size=1.5><b>Submit $surveyTitle</b></font></button>";
	}

	if($sizeOfSecArray == $nextSecInd ) {
		$surveyHTML .="<strong>Important:</strong> Do not press the 'Submit' button until you have completed the $surveyTitle Application.";
	}
	$draftButton  = "<button width = 10% onClick='jumpInPlaceButton($surveyID,$curSecInd);'><font color=\"black\" face=\"verdana narrow\" size=1.5><b>Save Draft</b></font></button>";
	//TODO remove after testing
	$ajaxTestButton  = "<button width = 10% onClick='checkUserName(\"Maggie\", \"\");return false;'>Ajax Test</button>";
	$surveyHTML .= "<div id=\"buttonDiv\" > " . $draftButton .reset_field("DropChanges","Drop Changes"). $partext ."<BR>==<BR>". $ajaxTestButton . "</div>";
	$surveyHTML .=$hiddenFields;
	$surveyHTML .=end_form();



	//FORMATING
	$htmlMainContainer= "";
	$htmlMainContainerEND = "";
	//TODO define home in data
	$htmlMainBody = "";
	$htmlMainBodyEND = "";

	//FORMATING

	$retVal .= $htmlMainContainer;
	$retVal .= $htmlMainBody .$surveyHTML .  $htmlMainBodyEND;
	$retVal .= $htmlMainContainerEND;


	return $retVal;
}
function buildQuestionsCells($currentSectionID,$answerArray,$userScheduleMapID){
	// set the name of each question and each answer field
	// in the "field[]" format. PHP will treat the values
	// of these fields as arrays when the form is submitted.
	// we start the field index at 1 to avoid any problems
	// with the index seeming to be empty.
	global $GLOBAL_DEBUG_BORDER;
	$surveyHTML = "";
	$query = "select questionID, questionText, questionType, questionInstruction, "
	. "textWidth, questionWidth, listColumns, alignment, sameLine, "
	. "textHeight, hasDependent, dependentQID  "
	. "FROM  questions q, questionTypes qt "
	. "WHERE  q.surveySectionID = $currentSectionID "
	. "AND qt.questionTypeID = q.questionTypeID "
	. "AND qt.questionType <> 'GridElement' "
	. "ORDER BY questionNumber;";

	$result = safe_query($query);

	//mark a question for continuing the same row
	$boolIsContinue = 0;
	$i = 1;
	$questionCells="";
	while (list($questionID, $question, $QuestionType,$QuestionInstruction,
	$TextWidth , $QuestionWidth , $ListColumns ,  $Alignment ,
	$SameLine ,$TextAreaHeight, $HasDep, $DepQID ) = mysql_fetch_row($result))
	{
		// use the function specifiedfunction (defined in
		// /book/functions/forms.php) to construct a list
		// ofuserfor each answer to the question.

		//determine if there are dependecies on another question
		$isDependencyFufilled = FALSE;
		$depChoice = NULL;
		if ($HasDep == "C") {
			$isDependencyFufilled = getdependencystatus($UserID,$DepQID );
			$depChoice = getdepchoice($UserID,$DepQID);
		}
		$theseAnswers = "";
		if (isset($answerArray[$questionID])){
			$theseAnswers = $answerArray[$questionID];
		}
		//recalculate these answers if the field is dependent on another question

		//hide or disable dependent qusetions
		$thisQuestion = "";
		if ($QuestionType == "DropDownList"){
			$thisQuestion = DropDownList($questionID, $i, $theseAnswers,$depChoice );
		} elseif ($QuestionType == "TextBox"){
			//TODO this is a test place
			//
			$atts= array();
			$atts =  array("class" =>  "coolfield");

			$thisQuestion = TextBox($questionID,$i,$theseAnswers, $TextWidth,$atts );
		} elseif ($QuestionType == "Ranking"){
			$thisQuestion = Ranking($questionID,$i,$theseAnswers,$TextWidth);
		} elseif ($QuestionType == "TextArea"){
			$thisQuestion = TextArea($questionID,$i,$theseAnswers,$TextWidth);
		} elseif ($QuestionType == "DropDownListOther"){
			$thisQuestion = DropDownListOther($questionID,$i,$theseAnswers,$depChoice, $TextWidth);
		} elseif ($QuestionType == "DropDownListOtherLong"){
			$thisQuestion = DropDownListOther($questionID,$i,$theseAnswers,$depChoice ,$TextWidth);
		} elseif ($QuestionType == "MultipleSelection"){
			$thisQuestion = MultipleSelection($questionID,$i,$theseAnswers);
		} elseif ($QuestionType == "MultipleSelectionOther"){
			$thisQuestion = MultipleSelection($questionID,$i,$theseAnswers);
		} elseif ($QuestionType == "RadioBox"){
			$thisQuestion = RadioBox($questionID,$i,$theseAnswers,$depChoice );
		} elseif ($QuestionType == "RadioOther"){
			$thisQuestion = RadioOther($questionID,$i,$theseAnswers,$depChoice,$TextWidth );
		} elseif ($QuestionType == "CheckBox"){
			$thisQuestion = CheckBoxMultiple($questionID,$i,$theseAnswers, $ListColumns);
		} elseif ($QuestionType == "CheckBoxOther"){
			$thisQuestion = CheckBoxOther($questionID,$i,$theseAnswers, $ListColumns,$TextWidth);
		} elseif ($QuestionType == "GridTag"){
			//TODO test this place
			$atts =  array("class" =>  "number wowfield");
			$thisQuestion = MakeGrid($questionID,$userScheduleMapID ,$i ,$atts);
		}	elseif ($QuestionType == "File Upload"){
			$thisQuestion = MakeUploadElement($questionID,$userScheduleMapID,$i);
		} else{ $thisQuestion = "Question of unknown type-- contact tech support";}
		$myInstructions = "";

		if (strlen($QuestionInstruction)>0){$myInstructions ="<i>$QuestionInstruction</i>";}


		$tmpArr = array("valign"=>"top","bgcolor"=>"");
		if ($SameLine == "No" AND $boolIsContinue == 0) {
			$questionCells = "";
		}else {
			$boolIsContinue = -1;
		}
		//check if there is dependency is fufilled
		if ($HasDep != "C" OR  $isDependencyFufilled == true){
			if ($Alignment == "Over"){

				$questionStuff = "";
				if(strlen($question)>0) $questionStuff ="<b>$question</b><br>";
				$strHTMLstuff =
				$questionStuff
				. "$myInstructions"
				. hidden_field("QuestionIDarr[$i]", $questionID)
				. hidden_field("QuestionType[$i]", $QuestionType)
				. $thisQuestion
				;
				$questionCells =      $questionCells . table_cell($strHTMLstuff,$tmpArr);
			}else if ($Alignment == "Left"){
				$strHTMLstuff = $myInstructions ."<b>$question</b>  " . hidden_field("QuestionIDarr[$i]", $questionID)
				. hidden_field("QuestionType[$i]", $QuestionType). $thisQuestion;
				$questionCells   =    $questionCells . table_cell($strHTMLstuff);

			}else if ($Alignment == "Right"){
				$strHTMLstuff = table_cell($myInstructions). table_cell($thisQuestion . hidden_field("QuestionIDarr[$i]", $questionID)
				. hidden_field("QuestionType[$i]", $QuestionType))
				. table_cell(" <b>$question</b>")
				;
				$questionCells   =    $questionCells . $strHTMLstuff;
			}else if ($Alignment == "Under"){
				$strHTMLstuff = paragraph(
				$myInstructions
				, "<b>$question</b>  " . hidden_field("QuestionIDarr[$i]", $questionID)
				. hidden_field("QuestionType[$i]", $QuestionType). $thisQuestion
				);
				$questionCells   =    $questionCells . table_cell($strHTMLstuff);
			}
		}// if dependentfield is answered
		if ($SameLine == "No") {
			//feed line attributes
			$surveyHTML .="<table cellspacing= \"10\" border= \"$GLOBAL_DEBUG_BORDER\" >";
			$surveyHTML .=table_row($questionCells,$tmpArr) ;
			$surveyHTML .="</table>";
			$boolIsContinue = 0; //reset contiuation flag
			//HACK some settings for Row (of questions) settings
			$questionCells = "";

		}



		$i++;
	} //end while loop
	return $surveyHTML;
}


class Survey
{
	// property declaration
	public $styleSheet = "";
	public $Banner = "";
	public $tailPage = "";
	public $survey_image= "";
	public $surveyTitle = "";

	// method declaration
	public function displayVar() {
		echo $this->var;
	}

	function  __construct($survID){
		//Get and display overallsurvey info
		$surveyQuery =  "SELECT surveyID, surveyTitle, surveyInstructions,
	                 surveyInformation, surveyContact,  
	                 surveyTimetoComplet, surveyDisclaimer, imageLocation,tailPage,styleSheet as stsh, banner
	                  FROM survey
	                 WHERE  surveyID = '$survID';";
		$sqresult = safe_query($surveyQuery);
		$row = mysql_fetch_assoc($sqresult );
		//asign each colume to a global variable


		$this->styleSheet = $row['stsh'];
		$this->Banner = $row['banner'];
		$this->survey_image = $row['imageLocation'];
		$this->tailPage = $row['tailPage'];
		$this->surveyTitle = $row['surveyTitle'];
	}
}

class Section {
	public $secTitle= "";
	public $surveySectionInform = "";
	public $secMarker = "";
	public $surveySectionInstru  = '';
	function __construct($curSecID){

		$surCurSecQuery =   "SELECT surveySectionTitle,  surveySectionInform, surveySectionUserMa, surveySectionInstru
                  FROM surveySections 
                  WHERE  surveySectionID = $curSecID";
		$surCurSecqresult = safe_query($surCurSecQuery);
		$secRow = mysql_fetch_row($surCurSecqresult);

		$this->secTitle = $secRow[0];
		$this->surveySectionInform = $secRow[1];
		$this->secMarker = $secRow[2];
		$this->surveySectionInstru = $secRow[3];
	}
}

class Schedule{
	public      $scheduleID;
	public		$surveyID;
	public		$dateOpened;
	public		$dateDue;
	public 		$isActive;

	function __construct($scheduleID ){
		$sqlGetSchedule = "SELECT s.`scheduleID`, s.`surveyID`,
							s.`dateOpened`, s.`dateDue`, s.`isActive` 
							FROM schedule s "
							. "WHERE s.scheduleID = '$scheduleID'";
							$resultsGetSchedule = safe_query($sqlGetSchedule);
							$row = mysql_fetch_assoc($resultsGetSchedule);
							if($row){
								$this->scheduleID=$row['scheduleID'];
								$this->surveyID=$row['surveyID'];
								$this->dateOpened=$row['dateOpened'];
								$this->dateDue=$row['dateDue'];
								$this->isActive=$row['isActive'];		}
	}
}

class ScheduleOfUSM extends Schedule {
	function __construct($userSched_MapID) {
		$sqlGetSurvID ="SELECT s.`scheduleID`, s.`surveyID`,
							s.`dateOpened`, s.`dateDue`, s.`isActive` 
							FROM schedule s "
							."JOIN userScheduleMap usm ON s.scheduleID = usm.scheduleID " .
				"WHERE usm.userScheduleMapID ='" . $userSched_MapID . "';"; 

							$resultsGetSurvID = safe_query($sqlGetSurvID);
							$row = mysql_fetch_assoc($resultsGetSurvID);
							if($row){
								$this->scheduleID=$row['scheduleID'];
								$this->surveyID=$row['surveyID'];
								$this->dateOpened=$row['dateOpened'];
								$this->dateDue=$row['dateDue'];
								$this->isActive=$row['isActive'];
							}
	}
}
function getUserLoginLogoutBlock($user=null){
	global $GLOBAL_loginPage;
	$retVal = "<a href=\"$GLOBAL_loginPage\">login</a>";

	if($user instanceof User){
		$username = $user->username;
		$lolink ="<a class=logout href=\"\" onClick='jumpInPlaceLogout(); return false;'> "
		."logout" . "</a>";
		$retVal ="<span class=loginlogout>$username  -- $lolink</span>";
	}
	return $retVal ;
}

function getHeaderContent(){
	global $GLOBAL_banner_logo_location;
	global $GLOBAL_Page_Title;
	$headerContent= <<<EOQ
<a class="logo" href="http://www.example.org"><img alt="" src="$GLOBAL_banner_logo_location" class="logo"/></a>&nbsp;$GLOBAL_Page_Title
EOQ;
	return $headerContent;
}

class SurveySessionObject{
	// property declaration
	public $survey;
	public $user ;
	public $schedule;
	public $userScheduleMap ;
	



	public function setSurveyObject($survey ){
		if($survey instanceof Survey){
			$this->survey=$survey;
		}
	}

	public function setUserObject($user ){
		if($user instanceof User){
			$this->user=$user;
		}
	}
	public function setScheduleObject($schedule ){
		if($schedule instanceof Schedule){
			$this->schedule=$schedule;
		}
	}
	public function setUserScheduleMapObject($userScheduleMap ){
		if($userScheduleMap instanceof UserScheduleMap){
			$this->userScheduleMap=$userScheduleMap;
		}
	}
	public function display(){
		var_dump(get_object_vars($this));
	}
}

class UserScheduleMap{

	public $userScheduleMapID;
	public $userID;
	public $scheduleID;
	public $dateStarted;
	public $isCommitted;
	public $confirmationNumber;

	function __construct($userScheduleMapID) {
		$usmSQL = "SELECT u.`userScheduleMapID`, u.`userID`, u.`scheduleID`,
				u.`dateStarted`, u.`isCommitted`, u.`confirmationNumber`, 
				u.`dateCommitted` FROM userScheduleMap u 
				WHERE u.userScheduleMapID = '$userScheduleMapID'";
		$sqresult = safe_query($usmSQL );
		$row = mysql_fetch_assoc($sqresult );



		$this->userScheduleMapID=$row['userScheduleMapID'];
		$this->userID=$row['userID'];
		$this->scheduleID=$row['scheduleID'];
		$this->dateStarted=$row['dateStarted'];
		$this->isCommitted=$row['isCommitted'];
		$this->confirmationNumber=$row['confirmationNumber'];
	}
}
function getAjaxControls(){
	$jsAjaxControls= <<<EOQ
<script LANGUAGE="javascript">
		
var obj;

function ProcessXML(url) {
	// native object

	if (window.XMLHttpRequest) {
		// obtain new object
		obj = new XMLHttpRequest();
		// set the callback function
		obj.onreadystatechange = processChange;
		// we will do a GET with the url; "true" for asynch
		obj.open("GET", url, true);
		// null for GET with native object
		obj.send(null);
		// IE/Windows ActiveX object
	} else if (window.ActiveXObject) {
		obj = new ActiveXObject("Microsoft.XMLHTTP");
		if (obj) {
			obj.onreadystatechange = processChange;
			obj.open("GET", url, true);
			// don't send null for ActiveX
			obj.send();
		}
	} else {
		alert("Your browser does not support AJAX");
	}
}

function processChange() {
	// 4 means the response has been returned and ready to be processed
	if (obj.readyState == 4) {
		// 200 means "OK"
		if (obj.status == 200) {
			// process whatever has been sent back here:
			// anything else means a problem
		} else {
			alert("There was a problem in the returned data:");
		}
	}
}

function checkUserName(input, response) {
 // if response is not empty, we have received data back from the server
 		if(response != ''){
 // the value of response is returned from checkName.php: 1 means in use
 			if (response == '1') {
 				alert("username is in use");
			 }
 		} else {
 // if response is empty, we need to send the username to the server
 			url = 'http://eclipse/webForms/webForms/ajx/ajrp.php?q=' + input;
 ProcessXML(url);
 }
 
 var thisForm = document.forms[0];
 var mytarget = curindex ;
 var myurl = "" + "?frmno=" + surv + "&curSecInd=" + mytarget;
 thisForm.action = myurl;
 thisForm.submit();
//chit
}


</script>
EOQ;
	return $jsAjaxControls;
}
?>