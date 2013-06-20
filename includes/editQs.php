<?php

/* editQs.php
 * Created on Sep 8, 2005by William Morse
 *
 * Description:
 *
 *
 *
 * Inputs:
 *
 *
 *
 *
 *
 * Outputs:
 *
 *
 * 
 *
 * Known uses
 *
 *
 */
include "QuesTypes.php";
foreach ($_POST as $key => $value) {
	$$key = $value;
}
foreach ($_GET as $key => $value) {
	$$key = $value;
}
$thisPage = $_SERVER['PHP_SELF'];

if (isset ($gridLabel)) {
	processUpdatedGridLabels($gridLabel);
}
if (isset ($fields)) {
	processUpdatedQuestions($fields);
}
if(isset($choices)){
	processUpdatedChoices($choices);
}
if(isset($newchoices)){
	processNewChoices($newchoices);
}
if(isset($delChoices)){
	deleteChoices($delChoices);
}
if (isset ($secFields)) {
	processUpdatedSections($secFields);
}
if (isset ($delQID)) {
	deleteQuestions($delQID);
}
$boolDEBUGBorder = 0;
$hiddenFields = "";
if (!isset ($survID)) {
	print "<p><STRONG>You have reached an invalid report/application page.</STRONG>";
	die;
}

$surArray = array ();
$surSecQuery = "SELECT surveySectionID, surveySectionTitle survey FROM surveySections 
                  WHERE  surveyID = $survID
                  ORDER BY sectionOrdinal;";
$surSecqresult = safe_query($surSecQuery);
while ($row = mysql_fetch_row($surSecqresult)) {
	$surArray[] = $row[0];
	$sectionArray[] = $row;
}

if (!isset ($curSecInd)) {
	$curSecInd = 0;
}

$curSecID = $surArray[$curSecInd];
$nextSecInd = $curSecInd +1;
$sizeOfSecArray = mysql_num_rows($surSecqresult);

//Get and display overallsurvey info
$surveyQuery = "SELECT surveyID, surveyTitle, surveyInstructions, 
                 surveyInformation, surveyContact,  
                 surveyTimetoComplet, surveyDisclaimer, imageLocation, tailPage, styleSheet FROM survey
                 WHERE  surveyID = $survID;";
$sqresult = safe_query($surveyQuery);

//asign each colume to a global variable
set_result_variables($sqresult);

$page_title = $surveyTitle;

$survey_image = $imageLocation;
$surCurSecQuery = "SELECT surveySectionTitle,  surveySectionInform, surveySectionUserMa, surveySectionInstru
                  FROM surveySections 
                  WHERE  surveySectionID = $curSecID";
$surCurSecqresult = safe_query($surCurSecQuery);
$secRow = mysql_fetch_row($surCurSecqresult);

$secTitle = $secRow[0];
$surveySectionInform = $secRow[1];
$secMarker = $secRow[2];
$surveySectionInstru = $secRow[3];

//Display application complete message if user isCommitted is 1
$js =<<<EOQ
<script LANGUAGE="javascript">
        function jumpsubmit(){
        var thisForm = document.forms[0];
		var elts =thisForm.elements['equc'];
 		var elts_cnt  = (typeof(elts.length) != 'undefined')
                  ? elts.length
                  : 0;
        var myAction = "EditQs";
    if (elts_cnt) {
        for (var i = 0; i < elts_cnt; i++) {
        	if (elts[i].checked ){
        		myAction =elts[i].value;
        		break;
        	}
        }
    }
    else{
    	myAction = "EditQs";
    }  			


           var myurl = "$thisPage?uc=survey&survID=$survID&Woption=" + myAction;	  
         thisForm.action = myurl;
         thisForm.submit();
return true;
}
</script>
EOQ;
$htmlOut .= $js;
$htmlOut .= "<DIV  margin-right:10%><h4> Edit report/form $page_title</h4></DIV>";
$thisPage = $_SERVER['PHP_SELF'];
//$thisPage?uc=survey&survID=$survID&Woption=
$targetString = "$thisPage?uc=survey&survID=$survID&Woption=EditQs&Waction=";

//Question everything
$SQLsecCount= "SELECT SurveySectionID from surveySections where SurveyID = " . $survID . " "
              . "ORDER BY SectionOrdinal";
$resultsecCount = safe_query($SQLsecCount);
$secCount=0;
while($row = mysql_fetch_assoc($resultsecCount)){
	$secID = $row['SurveySectionID'];
$query = "select questionID, questionText, questionType, questionInstruction, "
       ."textWidth, questionWidth, listColumns, alignment, sameLine, "
       ."textHeight, hasDependent, dependentQID, q.surveySectionID  "
       ."FROM  questions q "
       ."JOIN surveySections ss ON ss.surveySectionID = q.surveySectionID "
       ."JOIN survey s ON s.surveyID = ss.surveyID "
       ."JOIN questionTypes qt ON qt.questionTypeID = q.questionTypeID "
       ."WHERE   1=1 "
   	   ."AND qt.questionType <> 'GridElement' "
       ."AND s.surveyID =".$survID." "
       ."AND ss.SurveySectionID =".$secID." "
       ."ORDER BY ss.sectionOrdinal, questionNumber;";
$result = safe_query($query);
$k=0;
while($qrow = mysql_fetch_assoc($result) ){
	$questionID = $qrow['questionID'];
	$qArray[$questionID] = "Sec: " . $secCount . " Q: " . ($k+1);
	$k++;
}
$secCount++;
}
$htmlOut .= start_form($targetString);
//USer choices
//Edit selected question
//insert new question {before|after} question # {same section
//move selected q  {before|after} q#, section 
//add new section
//remove q from survey
$dirNewArray = array ();
$dirNewArray['at the Start'] = 'at the Start';
$dirNewArray['Before'] = 'Before';
$dirNewArray['After'] = 'After';
$dirNewArray['at the end'] = 'at the end';
$dirMoveArray = array ();
$dirMoveArray['at the Start'] = 'at the Start';
$dirMoveArray['Before Question'] = 'Before Question';
$dirMoveArray['After Question'] = 'After Question';
$dirMoveArray['at the end'] = 'at the end';
$dirMoveArray['to section'] = 'to section';
$radioChoices = "";
$radioChoices .= "<BR>".radio_field("equc", "edit", "Edit Selected Questions");
$radioChoices .= "<BR>".radio_field("equc", "editSec", "Edit Selected Sections");
$radioChoices .= "<BR>".radio_field("equc", "insert", "Insert New Question")."&nbsp;&nbsp;".select_field("dirNew", $dirNewArray)." Question # ".select_field("qtargetNew", $qArray)."<SMALL> (question numbers ignored if not needed )</SMALL>";
$radioChoices .= "<BR>".radio_field("equc", "move", "Move selected Question ")."&nbsp;&nbsp;".select_field("dirMove", $dirMoveArray)." Question #/Section # ".select_field("qtargetMove", $qArray);
$radioChoices .= "<BR>".radio_field("equc", "addSec", "Add new section");
$radioChoices .= "<BR>".radio_field("equc", "del", "Remove Question from survey");
$goButton = "<button width = 10% onClick='jumpsubmit();'><font color=\"black\" face=\"verdana narrow\" size=1.5><b>GO!</b></font></button>";
$htmlOut .= $radioChoices;
$htmlOut .= paragraph($goButton);

$answerArray = array ();
$indA = 0;
$indC = 0;
$indL = 0;
$tempQID = '';
// set the name of each question and each answer field 
// in the "field[]" format. PHP will treat the values
// of these fields as arrays when the form is submitted.
// we start the field index at 1 to avoid any problems
// with the index seeming to be empty.
$htmlOut .= "<TABLE><COLGROUP><COL width=5% />";
for ($z = 0; $z < sizeof($sectionArray); $z ++) {
	$secTitle = "";
	if (isset ($sectionArray[$z][1]))
		$secTitle = $sectionArray[$z][1];
	$sectionID = $sectionArray[$z][0];
	$match = "NoMatch";
	$secChBx = checkbox_field("editSecs[]", $sectionID, "$z", "$match");
	$htmlOut .= "<TR><TD colspan = 2>Section $z:$secChBx $secTitle  </TD><TR>";

	$query = "select questionID, questionText, questionType, questionInstruction, "
	        ."textWidth, questionWidth, listColumns, alignment, sameLine, "
	        ."textHeight, hasDependent, dependentQID  "
	        ."FROM  questions q "
	        ."JOIN surveySections ss ON ss.surveySectionID = q.surveySectionID "
	        ."JOIN survey s ON s.surveyID = ss.surveyID "
	        ."JOIN questionTypes qt ON qt.questionTypeID = q.questionTypeID "
	        ."WHERE 1=1 "
	        ."AND qt.questionType <> 'GridElement' "
	        ."AND s.surveyID =".$survID." "
	        ."AND ss.surveySectionID =".$sectionID." "
	        ."ORDER BY ss.sectionOrdinal, questionNumber ;";
	$result = safe_query($query);

	//mark a question for continuing the same row
	$boolIsContinue = 0;
	$i = 1;
	$questionCells = "";
	$collectionOfQChBoxes = "";
	while (list ($questionID, $question, $QuestionType, $QuestionInstruction, $TextWidth, $QuestionWidth, $ListColumns, $Alignment, $SameLine, $TextAreaHeight, $HasDep, $DepQID) = mysql_fetch_row($result)) {
		// use the function specifiedfunction (defined in
		// /book/functions/forms.php) to construct a list
		// ofuserfor each answer to the question.

		//determine if there are dependecies on another question
		$isDependencyFufilled = FALSE;
		$depChoice = NULL;
		if ($HasDep == "C") {
			$isDependencyFufilled = getdependencystatus($UserID, $DepQID);
			$depChoice = getdepchoice($UserID, $DepQID);
		}
		$theseAnswers = "";
		if (isset ($answerArray[$questionID])) {
			$theseAnswers = $answerArray[$questionID];
		}
		//recalculate these answers if the field is dependent on another question

		//hide or disable dependent qusetions
		$thisQuestion = "";
		if ($QuestionType == "DropDownList") {
			$thisQuestion = DropDownList($questionID, $i, $theseAnswers, $depChoice);
		}
		elseif ($QuestionType == "TextBox") {
			$thisQuestion = TextBox($questionID, $i, $theseAnswers, $TextWidth);
		}
		elseif ($QuestionType == "Ranking") {
			$thisQuestion = Ranking($questionID, $i, $theseAnswers, $TextWidth);
		}
		elseif ($QuestionType == "TextArea") {
			$thisQuestion = TextArea($questionID, $i, $theseAnswers, $TextWidth);
		}
		elseif ($QuestionType == "DropDownListOther") {
			$thisQuestion = DropDownListOther($questionID, $i, $theseAnswers, $depChoice, $TextWidth);
		}
		elseif ($QuestionType == "DropDownListOtherLong") {
			$thisQuestion = DropDownListOther($questionID, $i, $theseAnswers, $depChoice, $TextWidth);
		}
		elseif ($QuestionType == "MultipleSelection") {
			$thisQuestion = MultipleSelection($questionID, $i, $theseAnswers);
		}
		elseif ($QuestionType == "MultipleSelectionOther") {
			$thisQuestion = MultipleSelection($questionID, $i, $theseAnswers);
		}
		elseif ($QuestionType == "RadioBox") {
			$thisQuestion = RadioBox($questionID, $i, $theseAnswers, $depChoice);
		}
		elseif ($QuestionType == "RadioOther") {
			$thisQuestion = RadioOther($questionID, $i, $theseAnswers, $depChoice, $TextWidth);
		}
		elseif ($QuestionType == "CheckBox") {
			$thisQuestion = CheckBoxMultiple($questionID, $i, $theseAnswers, $ListColumns);
		}
		elseif ($QuestionType == "CheckBoxOther") {
			$thisQuestion = CheckBoxOther($questionID, $i, $theseAnswers, $ListColumns, $TextWidth);
		}
		elseif ($QuestionType == "GridTag") {
			$thisQuestion = MakeGrid($questionID, -1, $i);

		} else {
			$thisQuestion = "Question of unknown type-- contact tech support";
		}
		$myInstructions = NULL;
		$match = "NoMatch";
		$collectionOfQChBoxes .= checkbox_field("editQs[]", $questionID, "$i", "$match")."<BR>";
		if (isset ($QuestionInstruction)) {
			$myInstructions = "<i>$QuestionInstruction</i>";
		}

		$tmpArr = array ("valign" => "top", "bgcolor" => "");
		if ($SameLine == "No" AND $boolIsContinue == 0) {
			$questionCells = "";
		} else {
			$boolIsContinue = -1;
		}
		//check if there is dependency is fufilled
		if ($HasDep != "C" OR $isDependencyFufilled == true) {
			if ($Alignment == "Over") {

				$strHTMLstuff = "<b>$question</b><br>"."$myInstructions<br>".hidden_field("QuestionID[$i]", $questionID).hidden_field("QuestionType[$i]", $QuestionType).$thisQuestion;
				$questionCells = $questionCells.table_cell($strHTMLstuff, $tmpArr);
			} else
				if ($Alignment == "Left") {
					$strHTMLstuff = paragraph($myInstructions, "<b>$question</b>  ".hidden_field("QuestionID[$i]", $questionID).hidden_field("QuestionType[$i]", $QuestionType).$thisQuestion);
					$questionCells = $questionCells.table_cell($strHTMLstuff);

				} else
					if ($Alignment == "Right") {
						$strHTMLstuff = table_cell($myInstructions).table_cell($thisQuestion.hidden_field("QuestionID[$i]", $questionID).hidden_field("QuestionType[$i]", $QuestionType)).table_cell(" <b>$question</b>");
						$questionCells = $questionCells.$strHTMLstuff;
					} else
						if ($Alignment == "Under") {
							$strHTMLstuff = paragraph($myInstructions, "<b>$question</b>  ".hidden_field("QuestionID[$i]", $questionID).hidden_field("QuestionType[$i]", $QuestionType).$thisQuestion);
							$questionCells = $questionCells.table_cell($strHTMLstuff);
						}
		} // if dependentfield is answered
		if ($SameLine == "No") {
			//feed line attributes
			$htmlOut .= "<TR>";
			$htmlOut .= "<TD>".$collectionOfQChBoxes."</TD>";
			$htmlOut .= "<TD>";
			$collectionOfQChBoxes = "";
			//innertables
			$htmlOut .= "<table cellspacing= \"10\" style = 'border-style=dashed;border-width=1;'>";
			$htmlOut .= table_row($questionCells, $tmpArr);
			$htmlOut .= "</table>";

			//innertables
			$htmlOut .= "</TD>";
			$htmlOut .= "</TR>";
			$boolIsContinue = 0; //reset contiuation flag
			$questionCells = "";

		}

		$i ++;
	} //end while loop
} //endsection for loop
$htmlOut .= "</table>";

$htmlOut .= hidden_field("survID", $survID);
$htmlOut .= $hiddenFields;
$htmlOut .= end_form();
?>