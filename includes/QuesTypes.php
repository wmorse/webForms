<?php
//TODO INcludes are assumed--need to fix this WFM 2/14/2003

function DropDownList($QID,$counter,$answers,$depChoice){
	$theseChoices =NULL;
	$thisChoice =NULL;
	if(is_array($answers)){
      $theseChoices = $answers["ChoiceID"];
      $thisChoice =$theseChoices[0]; 
	}
    if ( ! is_null($depChoice) ) {
       $childchoice = getdependchildren($depChoice);
    $output = select_field("ChoiceID[$counter]", $childchoice, "$thisChoice");
    }else{
    $output = db_select_field ("ChoiceID[$counter]", "choices","choiceID" ,"choiceDescription" ,"ChoiceNumber", "$thisChoice", "choices.questionId = $QID");
    }
    return $output;
}
function DropDownListDisplay($QID,$counter,$answers,$depChoice){
	$theseChoices =NULL;
	$thisChoiceID =NULL;
	$thisChoiceValue =NULL;
	if(is_array($answers)){
      $theseChoices = $answers["ChoiceID"];
      $thisChoiceID =$theseChoices[0];
      $thisChoiceValue = getChoiceValueFromID($thisChoiceID); 
	}
	
//OLD    $output = db_select_field ("ChoiceID[$counter]", "choices","choiceID" ,"choiceDescription" ,"ChoiceNumber", "$thisChoiceID", "choices.questionId = $QID");
    $output = text_field("ChoiceID[$counter]","$thisChoiceValue");
    return $output;
}
function TextBox($QID,$counter,$answers,$width,$atts=""){
	
	$thisAnswer="";
	$thisAnswervalue="";
	if (isset($answers["Answer"])){
		$thisAnswer = $answers["Answer"];
		$thisAnswervalue= $thisAnswer[0];
	}
	$ansOutput = "AnswerText[" .$counter . "]"; 
	$maxLen=""; 
    $output = text_field("$ansOutput","$thisAnswervalue", $width,$maxLen,$atts);
    return $output;
}

function DisplayTextBox($QID,$counter,$answers){
	$ansVal = "{$answers["Answer"][0]}";
	$width= strlen($ansVal)+4;
    $output = text_field("AnswerText[$counter]",$ansVal,$width);
    return $output;
}
function Ranking($QID,$counter,$answers){
    $queryRanking = "SELECT ChoiceID, ChoiceDescription FROM choices WHERE QuestionID = $QID ORDER BY `ChoiceNumber`;";
    $resultRanking = safe_query($queryRanking);
    $rankCounter = 0;
    $output ="";
    while ($row = mysql_fetch_row($resultRanking)){
    $rankCounter = $rankCounter + 1;
    $output =$output . hidden_field("RankID[$counter][$rankCounter]","$row[0]"). "\n";
    $rankAnswer = "";

    if ($answers != ""){
       if (is_array($answers["ChoiceID"])){
          while (list($offset,$rankID) = each($answers["ChoiceID"])){
             if ($rankID==$row[0]){
                $rankAnswer = $answers["Answer"][$offset];
                break;
             }
          }
       }
    }
    $output =$output . text_field("RankText[$counter][$rankCounter]", "$rankAnswer", 3). "\n";
    $output =$output . "<b>$row[1]</b><br>\n";
    }
     
    return $output;
}
function TextArea($QID,$counter,$answers,$width=50,$height=6 ){
	$thisAnswer="";
	$thisAnswervalue="";
	if(isset($answers["LongAnswer"])){
		$thisAnswer=$answers["LongAnswer"];
		$thisAnswervalue=$thisAnswer[0];
	}
    $output = textarea_field("AnswerText[$counter]","$thisAnswervalue",$width, $height);
    return $output;
}
function DisplayTextArea($QID,$counter,$answers,$width=50,$height=6){
	$thisAnswer = "";
	if (isset($answers)){
		if (isset($answers["LongAnswer"])){
			$thisAnswer = $answers["LongAnswer"][0];
		}
	}
	$answerLen= strlen($thisAnswer);
	$rows = ceil($answerLen / $width);
	$rowAttr = "";
	if ($rows < $height) {
		$rowAttr = "rows=\"$height\"";
    }
	
	
    //SWAP out hard returns for <BR>
    $output = "<DIV class=textAreaWhite>". nl2br($thisAnswer). "</DIV>";
//    $output = <<<EOQ
//<textarea DISABLED name="$name" cols="$width" $rowAttr wrap="soft">$thisAnswer</textarea>
//EOQ;

    //$output = "<DIV class=textAreaWhite> backgound><BR><B>$answerLen</B>". textarea_field("AnswerText[$counter]","{$answers["LongAnswer"][0]}",$width, $height);
    return $output;
}

function DropDownListOther($QID, $counter, $answers,$depChoice,$width=50){
	$theseChoices =NULL;
	$theseAnswers =NULL;
	$thisAnswer="";
	if(is_array($answers)){
      $theseChoices = $answers["ChoiceID"];
      if(isset($answers["Answer"])){
      $theseAnswers = $answers["Answer"];      
      $thisAnswer = $theseAnswers[0];
      } 
	}
    $output = db_select_field ("ChoiceID[$counter]", "choices","choiceID" ,"choiceDescription" ,"ChoiceNumber", $theseChoices, "choices.questionId = $QID");
      $output .= paragraph("<b>If other:</b>",
                 text_field("AnswerText[$counter]","$thisAnswer", $width));
    return $output;
}
function DropDownListOtherTextArea($QID){
    $output = "In DDL Other Textarea";
    return $output;
}

function MultipleSelection($QID,$counter,$answers){
	$theseChoices =NULL;
		if(is_array($answers)){
     $theseChoices = $answers["ChoiceID"];
		}
    $output = db_select_field_multiple("ChoiceID[$counter][]", "choices","choiceID" ,"choiceDescription"    ,"ChoiceNumber", $theseChoices, "choices.questionId = $QID");
    return $output;
}
function MultipleSelectionOther($QID,$counter,$answers){


     $theseAnswers = $answers["ChoiceID"];

    $output = db_select_field_multiple("ChoiceID[$counter][]", "choices","choiceID" ,"choiceDescription"    ,"ChoiceNumber", $theseAnswers, "choices.questionId = $QID");
    $output .= paragraph("<b>If other:</b>",
                 text_field("AnswerText[$counter]","{$answers["Answer"][0]}", $width));

    return $output;
}

function RadioBox($QID,$counter,$answers,$depChoice,$listColumns =1){
	//TODO implement columns for Radio box
	if($listColumns < 1){$listColumns= 1;}
	 
$theseAnswers= NULL;
	if (isset($answers["ChoiceID"])){
    	$theseAnswers = $answers["ChoiceID"];
	}
    $output = db_radio_fieldBR("ChoiceID[$counter]"
            , "choices", "choiceID", "choiceDescription", "choiceNumber"
            , "{$theseAnswers[0]}"
            , "QuestionID = $QID",$listColumns
        );
    return $output;
} 

function PrintRadioBox($QID,$counter,$answers,$depChoice,$listColumns =1){
	if($listColumns < 1){$listColumns= 1;}
	 
$theseAnswers= NULL;
	if (isset($answers["ChoiceID"])){
    	$theseAnswers = $answers["ChoiceID"];
	}
    $output = print_db_radio_fieldBR("ChoiceID[$counter]"
            , "choices", "choiceID", "choiceDescription", "choiceID"
            , "{$theseAnswers[0]}"
            , "QuestionID = $QID",$listColumns
        );
    return $output;
} 
function RadioOther($QID,$counter,$answers,$depChoice,$width=50){
	$theseAnswers =NULL;
	$thisAnswer =NULL;
	$otherAnswer = NULL; 
	if(isset($answers)){
		if (isset($answers["ChoiceID"])){	
			$theseAnswers = $answers["ChoiceID"];
			if(isset($theseAnswers) and is_array($theseAnswers)){
				$thisAnswer = $theseAnswers[0];
			}
		}
		if (isset($answers["Answer"])){	
			$otherAnswers =$answers["Answer"];
			if(isset($otherAnswers )and is_array($otherAnswers )){
				$otherAnswer=$otherAnswers[0]; 
			}
		}
	}
	
    $output = db_radio_fieldBR("ChoiceID[$counter]"
            , "choices", "choiceID", "choiceDescription", "choiceID"
            , "$thisAnswer" // no matching value
            , "QuestionID = $QID"
        );
      $output .= paragraph("<b>If other:</b>",
                 text_field("AnswerText[$counter]","$otherAnswer", $width));

    return $output;
}

function CheckBoxMultiple($QID,$counter,$answers,$columns){

$theseAnswers = NULL;
if(is_array($answers)){
     $theseAnswers = $answers["ChoiceID"];
}
    $output = db_checkbox_field_multiple("ChoiceID[$counter][]", "choices","choiceID" ,"choiceDescription"    ,"ChoiceNumber", $theseAnswers, "choices.questionId = $QID",$columns);
    return $output;
}
function CheckBoxMultipleOther($QID,$counter,$answers,$columns,$width=50){


     $theseAnswers = $answers["ChoiceID"];
      
    $output = db_checkbox_field_multiple("ChoiceID[$counter][]", "choices","choiceID" ,"choiceDescription"    ,"ChoiceNumber", $theseAnswers, "choices.questionId = $QID",$columns);
    $output .= paragraph("<b>If other:</b>",
                 text_field("AnswerText[$counter]","{$answers["Answer"][0]}", $width));


    return $output;
}
function MakeGrid($QID,$userScheduleMapID,$counter,$atts=""){
	$width = 3;
	$class = "class = GridTable";
	//Get number of rows
	//Get number of columns
	$sqlGetTableInfo ="SELECT * from gridLabels WHERE gridMotherID ='$QID' ORDER BY `order`";
	$resultGetTableInfo = safe_query($sqlGetTableInfo);
	$rowArray = array();
	$colArray = array();
	$rowCount = 0;
	$colCount = 0;
	$cornerText = "";
	while ($row = mysql_fetch_assoc($resultGetTableInfo)) {
		$type = $row['type'];
		switch ($type) {
			case 'Row':
				$rowArray[]=$row['label'];
				break;
			case 'Column':
			    $colArray[]=$row['label'];	
				break;
			case 'Corner':
				$cornerText=$row['label'];
				break;
			default:
				break;
		}
	}
	$rowCount = count($rowArray);
	$colCount = count($colArray);
	$htmlTable = "";
	$headerRow = "";
	$bodyRows = "";
	
	//make Array of QuestionIDs that belong to this grid
	$sqlGetGridMembers = "SELECT q.questionID from questions q "
					   . "JOIN gridMembers g ON q.questionID = g.memberQuestionID "
					   . "WHERE g.motherQuestionID = $QID "
					   . "ORDER BY q.questionNumber"; 
    $resultGetGridMembers= safe_query($sqlGetGridMembers);
    $qnumsArray = array();					    
	while ($row= mysql_fetch_assoc($resultGetGridMembers)){
		$qnumsArray[]=$row['questionID'];
	}
	
	
	$headerRow ="\n\t<TR>\n\t\t<TH>$cornerText</TH>";
	
    for ($index = 0; $index < sizeof($colArray); $index++) {
		$cell = "\n\t\t<TH>" . $colArray[$index] . "</TH>";
		$headerRow .="\n\t\t$cell";
	}
	$headerRow .= "\n\t</TR>"; 
	
	
	//Build Table with two loops
	$questionCounter = 0;
	for ($index = 0; $index < sizeof($rowArray); $index++) {
		//start new row
		$rowLabel = $rowArray[$index];
		$thisRow = "\n\t<TR>\n\t\t<TH>$rowLabel</TH>";
		//$array_element = $rowArray[$index];
		for ($j = 0; $j < sizeof($colArray); $j++) {
			$thisQID= $qnumsArray[$questionCounter];
			$thisAnswerText = "";
			$thisAnswer = getAnswer($thisQID,$userScheduleMapID);
			if(!is_null($thisAnswer) AND isset($thisAnswer['Answer'])){
				$thisAnswerText = $thisAnswer['Answer'][0];
			}
			$ansOutput = "GridAnswerText[".$thisQID. "]"; 
			$textBox = text_field("$ansOutput","$thisAnswerText", $width,"",$atts);
			
			$thisCell ="\n\t\t<TD>" .$textBox. "</TD>"; 
		    $questionCounter++;
		    $thisRow.=$thisCell ; 			
		}
		$thisRow .= "\n\t</TR>";
		$bodyRows.= $thisRow;
	}	
	$htmlTable = "\n<TABLE $class >" . $headerRow. $bodyRows. "\n</TABLE>";
	return $htmlTable ; 
}
function getAnswer($QID,$userScheduleMapID) {
	$retVal=array();
	$sqlGetAnswer = "SELECT * from answers WHERE questionID='$QID' AND userScheduleMapID='$userScheduleMapID'";
	$returnGetAnswer = safe_query($sqlGetAnswer);
	$numAnswers = mysql_num_rows($returnGetAnswer);
	if ($numAnswers =0 ){
		return NULL;
	}else{
		//loop
		while ($row = mysql_fetch_assoc($returnGetAnswer)){
			$answer = $row['answer'];
			$longAnswer = $row['longAnswer'];
			$choiceID = $row['choiceID'];
			if (!(!isset($answer) or $answer=="")) {
			    $retVal["Answer"][] = $answer;
			}
            if (!(!isset($choiceID) or $choiceID=="")) {
			    $retVal["ChoiceID"][] = $choiceID;
   			}
            if (!(!isset($longAnswer) or $longAnswer=="")) {
    			$retVal["LongAnswer"][] = $longAnswer;
            }
		}//endwhile		
	}
	return $retVal;
}
function chunkOutGridLabels($questionID){
	$glChuncks ="";
	$glChuncks .= "\n<table border=0 cellpadding=2 cellspacing=1>";
	
	$sqlGetTableInfo ="SELECT  `gridLabelID`, `gridMotherID`, `label`, `type`, `order` from gridLabels WHERE gridMotherID = $questionID ORDER BY `type`,`order`";
	$resultGetTableInfo  = safe_query($sqlGetTableInfo);
	$num = mysql_num_rows($resultGetTableInfo);

	while( list($gridLabelID, $gridMotherID, $label, $type, $order)= mysql_fetch_row($resultGetTableInfo))
	{

	$glChuncks .="<TR>";
	$glChuncks .="<TD>";
	$glChuncks .= $order . " " .$type;
	$glChuncks .="</TD>";
	$glChuncks .="<TD>";
	$glChuncks .= "<INPUT type = text name=gridLabel[". $gridLabelID. "] value = '". $label. "' />";
	$glChuncks .="</TD>";
	$glChuncks .="</TR>";	
	}
	$glChuncks .= "</TABLE>";
	return $glChuncks ;
}
	function processUpdatedGridLabels($gridLabelArr){
		if (!is_array($gridLabelArr)){
			return null;
			
		}
		foreach($gridLabelArr as $gridID => $val){
			$sqlUpdate = "UPDATE gridLabels SET `label` = '$val' WHERE gridLabelID=$gridID";
			$result = safe_query($sqlUpdate);
		}
		//need better control
		return true;
	}
function	processUpdatedQuestions($fields){
		if (!is_array($fields)){
						return null;
		}
		foreach($fields as $qID =>$qArray){
	$questionID= $qArray['questionID'];
	$questionText_dirty = $qArray['questionText'];			
	$questionText = mysql_real_escape_string(stripslashes($questionText_dirty));
   $questionNumber = $qArray['questionNumber'];
   $qInstr_dirty = $qArray['questionInstruction'];
       $qInstr = mysql_real_escape_string(stripslashes($qInstr_dirty));
    $questionTypeID= $qArray['questionTypeID'];
    $textWidth = $qArray['textWidth']; 
    $qListColumns =$qArray['listColumns'];
    $qAlignment = $qArray['alignment'];
    $qTextHeight= $qArray['textHeight']; 
    $qTableLabel= $qArray['qLabel'];
    $qSameLine = $qArray['sameLine'];
    
    $sqlUpdateQuestions = "UPDATE questions set questionText = '$questionText', "
                        ."questionNumber = '$questionNumber', "
                        . "questionInstruction = '$qInstr', "
                        . "textWidth = '$textWidth', "                        
                        . "questionTypeID = '$questionTypeID', "
                        . "sameLine = '$qSameLine', "
                        . "qLabel = '$qTableLabel', "
                        . "listColumns = '$qListColumns' "
                        . "WHERE questionID =" .$questionID;	 	
	$result= safe_query($sqlUpdateQuestions);
		}
	}	
	
function	processUpdatedSections($secFields){
		if (!is_array($secFields)){
						return null;
		}
		foreach($secFields as $secID =>$secArray){
	$SurveySectionID= $secArray['SurveySectionID'];			
			$SurveyID = $secArray['SurveyID'];
	$surveySectionInform_dirty = $secArray['SurveySectionInform'];
    $SurveySectionInform = mysql_real_escape_string(stripslashes($surveySectionInform_dirty));
    $surveySectionTitle_dirty=$secArray['SurveySectionTitle'];
    $SurveySectionTitle = mysql_real_escape_string(stripslashes($surveySectionTitle_dirty));
    $SectionOrdinal_dirty= $secArray['SectionOrdinal'];
    $SectionOrdinal= mysql_real_escape_string(stripslashes($SectionOrdinal_dirty));
    $SurveySectionUserMa_dirty = $secArray['SurveySectionUserMa'];
    $SurveySectionUserMa = mysql_real_escape_string(stripslashes($SurveySectionUserMa_dirty));
    $SurveySectionInstru_dirty =  $secArray['SurveySectionInstru'];
    $SurveySectionInstru =mysql_real_escape_string(stripslashes($SurveySectionInstru_dirty));
    $Printable = $secArray['Printable'];
    
    $sqlUpdateSections = "UPDATE surveySections set SurveyID= '$SurveyID', "
                        ."SurveySectionInform = '$SurveySectionInform', "
                        . "SurveySectionTitle = '$SurveySectionTitle', "
                        . "SectionOrdinal = '$SectionOrdinal', "                        
                        . "SurveySectionUserMa = '$SurveySectionUserMa', "
                        . "SurveySectionInstru = '$SurveySectionInstru', "
                        . "Printable = '$Printable' "
                        . "WHERE SurveySectionID =" .$SurveySectionID;	 	
	$result= safe_query($sqlUpdateSections);
		}
	}	

	function deleteQuestions($delQID){
				if (!is_array($delQID)){
						return null;
		}
		foreach($delQID as $key){
			$sqlNotReallyDelete = "UPDATE questions set surveySectionID = -1 "
			                    . "WHERE questionID =" . $key;
			$result = safe_query($sqlNotReallyDelete);			                    
		}
	}
function processUpdatedChoices($choices){
		if (!is_array($choices)){
				return null;
		}
		foreach($choices as $qID =>$choiceArray){
			if (!is_array($choiceArray)){
				return null;
			}
			foreach($choiceArray as $choiceCount => $choiceUpdates){
					$questionID= $choiceUpdates['questionID'];
					$choiceID= $choiceUpdates['choiceID'];
					$choiceDescription_dirty = 			$choiceUpdates['choiceDescription'];
					$choiceDescription = mysql_real_escape_string(stripslashes($choiceDescription_dirty));
					$choiceNumber_dirty = $choiceUpdates['choiceNumber'];
   					$choiceNumber = mysql_real_escape_string(stripslashes($choiceNumber_dirty));
				    
				    $sqlUpdateChoices = "UPDATE choices set questionID = '$questionID', "
				                        ."choiceNumber = '$choiceNumber', "
				                        . "choiceDescription = '$choiceDescription' "
				                        . "WHERE choiceID =" .$choiceID;	 	
					$result= safe_query($sqlUpdateChoices);
				}//end for loop
		}
}		
function processNewChoices($newchoices){
		if (!is_array($newchoices)){
				return null;
		}
		foreach($newchoices as $qID =>$newchoiceArray){
			if (!is_array($newchoiceArray)){
				return null;
			}
			foreach($newchoiceArray as $choiceCount => $choiceAddArray){
					$questionID= $choiceAddArray['questionID'];
					$choiceDescription = mysql_real_escape_string($choiceAddArray['choiceDescription']);
   					$choiceNumber = mysql_real_escape_string($choiceAddArray['choiceNumber']);
				    
				    $sqlUpdateChoices = "INSERT INTO choices (questionID, choiceDescription, choiceNumber) VALUES ("
				                        . "'$questionID','$choiceDescription','$choiceNumber' "
				                        .")";	 	
					$result= safe_query($sqlUpdateChoices);
				}//end for loop
		}
}
function deleteChoices($delchoices){
				if (!is_array($delchoices)){
						return null;
		}
		foreach($delchoices as $key){
			$sqlNotReallyDelete = "UPDATE choices set questionID  = -1 "
			                    . "WHERE choiceID =" . $key;
			$result = safe_query($sqlNotReallyDelete);			                    
		}
	
	return true;
}

function getSection($qid){
	if(!is_numeric($qid)){
		return  substr($qid,1, strlen($qid)-1);
		
	}
	$sqlGetSection = "SELECT surveySectionID from " 
			     .  "questions WHERE questionID =" . $qid;
	$result = safe_query($sqlGetSection );
	if($result){
		$row = mysql_fetch_array($result);
		return $row['surveySectionID'];
	}
	else return NULL;
}	
function makeRoomforNew($secID,$dirNew,$qtargetNew){
	//preset return val in case of empty section
	$step = 10;
	$currentVal=$step;
	$returnVal = $currentVal;
	$sqlGetQnums= "SELECT questionID, questionNumber "
	            . "FROM questions "
	            ."WHERE surveySectionID =" . $secID. " "
	            ."ORDER BY questionNumber ASC";
            
	$resultGetQnums = safe_query($sqlGetQnums);
	$qNumArray = array();
	$myIndex = 0;
	while($row = mysql_fetch_assoc($resultGetQnums)){
		$questionID = $row['questionID'];
		$questionNumber = $row['questionNumber'];
		$qNumArray[$myIndex]['questionID']=$questionID ;
		$qNumArray[$myIndex]['questionNumber']=$questionNumber ;
		$myIndex++;
	}	
	$sqcQCount = count($qNumArray);
	if($sqcQCount == 0){
		return  $currentVal;
		} 
		
$lowestQnum =  	$qNumArray[0]['questionNumber'];			
$highestQnum =  	$qNumArray[$sqcQCount-1]['questionNumber'];
	if($dirNew=='at the end'){
		return ( $highestQnum + 10);  
	}
	
	if($dirNew=='at the Start') {
		$qtargetNew=$qNumArray[0]['questionID'];
		$dirNew="Before";
	}
	for ($i = 0; $i < sizeof($qNumArray); $i++) {
		if ($qNumArray[$i]['questionID']==$qtargetNew){
			if ($dirNew=="Before"){
				$returnVal = $currentVal;
				$currentVal += $step;
				$qNumArray[$i]['questionNumber']= $currentVal;												
			}else if ($dirNew=="After"){
			$qNumArray[$i]['questionNumber']= $currentVal;
		    $currentVal += $step;
			$returnVal =$currentVal; 
			}
		}else
		{
			$qNumArray[$i]['questionNumber']= $currentVal;
		}
	$currentVal += $step;		
	}
	for ($j= 0; $j< sizeof($qNumArray); $j++) {
		$questionID = $qNumArray[$j]['questionID'];
		$questionNumber = $qNumArray[$j]['questionNumber'];
		$sqlUpdateQnum = "UPDATE questions set questionNumber = $questionNumber "
						. "WHERE questionID =" . $questionID;
		$resultUpdateQnum= safe_query($sqlUpdateQnum);	
	}
	return $returnVal;
	
}
function getFirstSection($survID){
	$sqlGetFirstSection = "SELECT SurveySectionID "
						. "FROM surveySections "
						. "WHERE SurveyID = " . $survID . " "
						. "ORDER BY SectionOrdinal ASC "
						. "LIMIT 0,1";
	$resultGetFirstSection = safe_query($sqlGetFirstSection);
	if ($resultGetFirstSection ){
		$row = mysql_fetch_assoc($resultGetFirstSection);
		return $row['SurveySectionID'];
	}else return NULL;
}
function getLastSection($survID){
	$sqlGetFirstSection = "SELECT SurveySectionID "
						. "FROM surveySections "
						. "WHERE SurveyID = " . $survID . " "
						. "ORDER BY SectionOrdinal DESC "
						. "LIMIT 0,1";
	$resultGetFirstSection = safe_query($sqlGetFirstSection);
	if ($resultGetFirstSection ){
		$row = mysql_fetch_assoc($resultGetFirstSection);
		return $row['SurveySectionID'];
	}else return NULL;
}
function processMoveRequest($qid, $editQs,$direction){
	$secID =getSection($qid);
	if(is_array($editQs)){
		for ($i = 0; $i < sizeof($editQs); $i++) {	
		$newQNum= makeRoomforNew($secID,$direction,$qid);
		$thisQ= $editQs[$i];

		$sqlUpdatePos =  "UPDATE questions set questionNumber = $newQNum, SurveySectionID = $secID "
						. "WHERE questionID =" . $thisQ;
		$resultUpdateQnum= safe_query($sqlUpdatePos);
		}
	}

}
function printTextBox($QID,$counter,$answers,$width){

	$thisAnswer="";
	$thisAnswervalue="";
	$tooBig =false;
	if (isset($answers["Answer"])){
		$thisAnswer = $answers["Answer"];
		$thisAnswervalue= $thisAnswer[0];
		$anlength = strlen($thisAnswervalue);
			$tooBig=(($width<$anlength)? true:false);
	}
	
	$ansOutput = "AnswerText[" .$counter . "]";  
	if (!$tooBig){
    $output = text_field("$ansOutput","$thisAnswervalue", $width);
	}else{
    if (!isset($width) OR $width ==0){
    	$width = 50;
    }		
	$height = ceil(intval($anlength) / intval($width)) + 1;
	
	$ceil = ceil((intval($anlength) / intval($width)));
	//$output = "<DIV >". nl2br($thisAnswervalue). "</DIV>";
	$output = textarea_field("$ansOutput","$thisAnswervalue", $width, $height) . "\n";
}
    return $output;
}	

function getChoiceValueFromID($thisChoiceID){
	$retVal = NULL;
	$sqlGetChoice = "SELECT c.choiceDescription "
						. "FROM choices c "
						. "WHERE choiceID = '" . $thisChoiceID . "' "
						. "LIMIT 0,1";
	$resultGetChoice = safe_query($sqlGetChoice);
	if ($resultGetChoice ){
		$row = mysql_fetch_assoc($resultGetChoice);
		return $row["choiceDescription"];
	}else return NULL;
	
	
	return $retVal;
}

function MakeUploadElement($QID,$userScheduleMapID,$counter){
	//TODO ee about width
	//TODO what is displayed depends on the answers
	//if there is an answer, (the filename)
	//then we need to present it as a non editable line with a checkbox or button
	$answers = getAnswer($QID,$userScheduleMapID);
	if(isset($answers )){
		if(isset($answers["Answer"])){
			$thisAnswer = $answers["Answer"];
			$thisAnswervalue= $thisAnswer[0];
		}
		if(isset($answers["LongAnswer"])){
			$thisLongAnswer = $answers["LongAnswer"];
			$thisLongAnswervalue= $thisLongAnswer[0];
		}
	}
	if (isset($thisAnswervalue)){
		$deleteBox = checkbox_field_multiple("stirateID[$counter]",array(1=>"Remove") );;
		//die($thisAnswervalue);
		if($thisAnswervalue!="Error"){
		$output ="<font SIZE=-3><TABLE>".
		                "<TR><TD>$deleteBox</TD><TD>$thisLongAnswervalue</TD></TR></TABLE></font>";
		} else  { //Normal with error processing
			$ansOutput = "AnswerFile[" .$counter . "]";
			$output = "Unable to upload ". $thisLongAnswervalue. "<BR>";   
            $output .= file_field("$ansOutput");
		}
	} else { //Normal process like first time
		$ansOutput = "AnswerFile[" .$counter . "]";  
	    $output = file_field("$ansOutput");
	}
	    return $output;
}

function DisplayUploadElement($QID,$userScheduleMapID,$counter){
	//TODO ee about width
	//TODO what is displayed depends on the answers
	//if there is an answer, (the filename)
	//then we need to present it as a non editable line with a checkbox or button
	$answers = getAnswer($QID,$userScheduleMapID);

	$thisAnswer = $answers["Answer"];
	$thisAnswervalue= $thisAnswer[0];
	$thisLongAnswer = $answers["LongAnswer"];
	$thisLongAnswervalue= $thisLongAnswer[0];
	
	
	
	if (isset($thisAnswervalue)){
		if($thisAnswervalue!="Error"){
		$localFileName= getLocalFileNameValueFromID($QID,$userScheduleMapID);	
		$output ="<font SIZE=-3><TABLE><TR><TD><P>These files have not been check for viruses and formats <BR>BE CAREFUL, DO NOT JUST OPEN<P></TD></TR>".
		                "<TR><TD></TD><TD><a href=\"./sendFile.php?q=$QID&usm=$userScheduleMapID\" target = _blank>$thisLongAnswervalue</A></TD></TR></TABLE></font>";
		} else  { //Normal with error processing
			$ansOutput = "AnswerFile[" .$counter . "]";
			$output = "Unable to fetch ". $thisLongAnswervalue. "<BR>";   
            $output .= file_field("$ansOutput");
		}
	} else { 
		$output = "No File uploaded";
	}
	    return $output;
}
	
?>