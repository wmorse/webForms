<?php
/*
 * Created on Aug 4, 2005
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
$thisPage = $_SERVER['PHP_SELF']; 
$htmlOut = "";
$hiddenFields =""; 
foreach($_POST as $key => $value){
		$$key =$value;
}
foreach($_GET as $key => $value){
		$$key =$value;
}
 include_once("./header.php");
 include_once("QuesTypes.php");
 //put a title here with $page_title
//is this a call for a newquesetion?
if(isset($newChoice)){
 processNewChoices($newchoices);
}
if(isset ($Woption)){
	
	if ($Woption=="insert"){
	if (isset($qtargetNew) and isset($dirNew)){
		if($dirNew== 'at the Start'){
			$secID = getFirstSection($survID);
		}else 		if($dirNew== 'at the Start'){
				$secID = getLastSection($survID);
		}else {
			$secID = getSection($qtargetNew);
		}
		$newQNum= makeRoomforNew($secID,$dirNew,$qtargetNew);
		$defaultQType = 2; //text box
		$sqlInsertNewQ = "INSERT INTO questions (questionText,questionNumber, questionTypeID, surveySectionID) "
		              . "VALUES ('New Question ',$newQNum,$defaultQType,$secID)";
        $result = safe_query($sqlInsertNewQ );
	        if($result){
		        $editQs[]= mysql_insert_id();
	        }           
		}
	}
}
//Javascript
$js =<<<EOQ
<script LANGUAGE="javascript">
        function jumpChoiceAdd(){
        var thisForm = document.forms[0];
		var elts =thisForm.elements['hitchoices'];
 		var elts_cnt  = (typeof(elts.length) != 'undefined')
                  ? elts.length
                  : 0;
        if(elts_cnt){                  
        var myTarget = "$thisPage?uc=survey&survID=$survID&Woption=edit&newChoice=1";
        }else		
		myTarget = "$thisPage?uc=survey&survID=$survID&Woption=edit&newChoice=1";

	  
         thisForm.action = myTarget;
         thisForm.submit();
return true;
}
</script>
EOQ;


//Get Question type select list
$sqlGetQTypes = "SELECT questionTypeID, questionType from questionTypes ORder By questionTypeID";
$resultGetQTypes  = safe_query($sqlGetQTypes );
$qtypeArr = array();
while($row=mysql_fetch_assoc($resultGetQTypes  )  ){
	$qtID =$row['questionTypeID'];
	$qtLabel =$row['questionType'];
	$qtypeArr[$qtID]= $qtLabel;
}    
 if(!isset($editQs)){
 	$htmlOut = "You must chose at least one question";
 	
 }else{
for ($i = 0; $i < sizeof($editQs); $i++) {
	$hiddenFields .= "\n". hidden_field("editQs[]",$editQs[$i]);
		
}
$htmlOut.= $js; 	
$qChuncks = "";
$questionIDCounter=0;
for ($i = 0; $i < sizeof($editQs); $i++) {
$questionIDCounter++;
$thissuperQ =$questionIDCounter; 	
$qid = $editQs[$i];
$sqlGetQuestion = "SELECT  questionNumber, questionID, questionText, questionType, questionInstruction, "
        . "textWidth, questionWidth, listColumns, alignment, sameLine, "
        . "textHeight, hasDependent, dependentQID, q.questionTypeID, qLabel  "
        . "FROM  questions q, questionTypes qt "
        . "WHERE  qt.questionTypeID = q.questionTypeID " 
        . "AND qt.questionType <> 'GridElement' " 
        . "AND questionID = " .$qid . " "
		. "ORDER BY questionNumber;";

$resultsGetQuestion = safe_query($sqlGetQuestion);				
list($questionNumber, $questionID, $question, $questionType,$questionInstruction,
            $textWidth , $QuestionWidth , $ListColumns ,  $Alignment ,
            $sameLine ,$TextAreaHeight, $HasDep, $DepQID, $questionTypeID, $qLabel ) = mysql_fetch_row($resultsGetQuestion );	

//Get the choices for each page
$isChoiceable =true;
if ($isChoiceable){
	$choiceTable  = "\n" . hidden_field("hitchoices" , 1);
	$choiceTable .= "\n<!-- Table of Question choices -->\n<TABLE class=choices>\n";
	$choiceTable .="\t<TR>\n";
	$choiceTable .="\t\t<TH>\n";
	$choiceTable .="\t\tDelete";
	$choiceTable .="\t\t</TH>\n";
	$choiceTable .="\t\t<TH>\n";
	$choiceTable .="\t\tChoice Description";
	$choiceTable .="\t\t</TH>\n";
	$choiceTable .="\t\t<TH>\n";
	$choiceTable .="\t\tChoice Number";
	$choiceTable .="\t\t</TH>\n";
	$choiceTable .="\t</TR>\n";
	
	$sqlChoices = "SELECT choiceID,  choiceDescription, choiceNumber "
			    . "FROM choices "
	            . "WHERE questionID =" . $qid . " "
	            . "ORDER BY choiceNumber";
	
	$resultChoices = safe_query($sqlChoices );
	$choiceArr= NULL;
	if(mysql_num_rows($resultChoices))
	{
		$choiceArr =array();
		while($row = mysql_fetch_assoc($resultChoices)){
			$choiceArr[]=$row;
		}
	//Go through existing choices
	    $count= 0;
		foreach($choiceArr as $choiceDataRow){
			$choiceRow = "\t<TR>\n";
			$choiceID = $choiceDataRow['choiceID'];
			$choiceDesc = htmlspecialchars($choiceDataRow['choiceDescription']);
			$choiceOrder = $choiceDataRow['choiceNumber'];
			$deleteCHBox = checkbox_field("delChoices[]", $choiceID , "", "nomatch");
			$hiddenChoiceID ="\n\t\t\t<input type=hidden name=choices[$thissuperQ][$count][choiceID] value = $choiceID >\n";
			$hiddenChoiceQID ="\t\t\t<input type=hidden name=choices[$thissuperQ][$count][questionID] value = $qid >\n";
			$textFieldChoiceDes= text_field( "choices[$thissuperQ][$count][choiceDescription]","$choiceDesc");
			$textFieldChoiceNum= text_field( "choices[$thissuperQ][$count][choiceNumber]","$choiceOrder");
			
			$choiceRow .= "\t\t<TD>" .$deleteCHBox. "</TD>\n";
			$choiceRow .= "\t\t<TD>" .$hiddenChoiceID.$hiddenChoiceQID. $textFieldChoiceDes . "</TD>\n";
			$choiceRow .= "\t\t<TD>" . $textFieldChoiceNum. "</TD>\n";
			$choiceRow .= "\t</TR>\n";
			$choiceTable  .=$choiceRow ;
			$count++;
		}
	}
	$choiceTable .= "</TABLE>"; 
	$addChoiceButton = "<button width = 10% onClick='jumpChoiceAdd();'><font color=\"black\" face=\"verdana narrow\" size=1.5><b>Add another choice</b></font></button>";	
    $choiceTable.= $addChoiceButton;
    if(isset($newChoice)){
    //post any previously added choices
	$choiceTable .= "\n<!-- Table of New Question choices -->\n<TABLE class=choices>\n";
	$choiceTable .="\t<TR>\n";
	$choiceTable .="\t\t<TH>\n";
	$choiceTable .="\t\tChoice Description";
	$choiceTable .="\t\t</TH>\n";
	$choiceTable .="\t\t<TH>\n";
	$choiceTable .="\t\tChoice Number";
	$choiceTable .="\t\t</TH>\n";
	$choiceTable .="\t</TR>\n";
    	
    	for ($i= 0; $i< $newChoice; $i++) {
			$choiceRow = "\t<TR>\n";
			$hiddenChoiceQID ="<input type=hidden name=newchoices[$thissuperQ][$i][questionID] value = $qid >";
			$textFieldChoiceDes= text_field( "newchoices[$thissuperQ][$i][choiceDescription]","");
			$textFieldChoiceNum= text_field( "newchoices[$thissuperQ][$i][choiceNumber]","");
			$choiceRow .= "\t\t<TD>" .$hiddenChoiceQID . $textFieldChoiceDes . "</TD>\n";
			$choiceRow .= "\t\t<TD>" . $textFieldChoiceNum. "</TD>\n";
					 
		
			$choiceRow .= "\t</TR>\n";
			
			$choiceTable  .=$choiceRow ;
    		
			
		}
		$choiceTable .= "</TABLE>";
    }
}


$qChuncks .= "\n<table border=0 cellpadding=2 cellspacing=1>";
$qChuncks .= "\n\t<tr>";
$qChuncks .= "<th>Property</th>";
$qChuncks .= "<th>Value</th>";
$qChuncks .= "</tr>";

$qChuncks .= "\n\t<tr>";
$qChuncks .= "\n\t<tr>";
$qChuncks .= "<td align=center bgcolor=#E5E5E5>questionText</td>";
$qChuncks .= "<td bgcolor=#E5E5E5>" . "<input type=hidden name=fields[$thissuperQ][questionID] value = $questionID >" .
		"<textarea name=\"fields[$thissuperQ][questionText]\" " .
		"class=textfield id=field_3_3 cols=80  rows=6/>". htmlspecialchars($question) . "</textarea >";
$qChuncks .= "\n\t</tr>";

$qChuncks .= "\n\t<tr>";
$qChuncks .= "<td align=center bgcolor=#EEEEEE>questionInstruction</td>";
$qChuncks .= "<td bgcolor=#EEEEEE><textarea name=\"fields[$thissuperQ][questionInstruction]\" cols = 60 rows=6/>". htmlspecialchars($questionInstruction) . "</TEXTAREA></td>";
$qChuncks .= "\n\t</tr>";

$qChuncks .= "\n\t<tr>";
$qChuncks .= "<td align=center bgcolor=#E5E5E5>questionNumber</td>";
$qChuncks .= "<td bgcolor=#E5E5E5><input type=text name=
\"fields[$thissuperQ][questionNumber]\" value=\"$questionNumber\" size=20
maxlength=99 class=textfield tabindex=13 id=field_5_3 /> </td>";
$qChuncks .= "\n\t</tr>";

$qChuncks .= "\n\t<tr>";
$qChuncks .= "<td align=center bgcolor=#EEEEEE>questionTypeID</td>";
$qChuncks .= "<td bgcolor=#EEEEEE>" . select_field("fields[$thissuperQ][questionTypeID]",$qtypeArr,$questionTypeID)."</td>";
$qChuncks .= "\n\t</tr>";


$qChuncks .= "\n\t<tr>";
$qChuncks .= "<td align=center bgcolor=#EEEEEE>textWidth</td>";
$qChuncks .= "<td bgcolor=#EEEEEE><input type=text name=
\"fields[$thissuperQ][textWidth]\" value=\"$textWidth\" size=20 maxlength=
99 class=textfield  tabindex=28 id=
field_10_3 /> </td>";
$qChuncks .= "\n\t</tr>";


$qChuncks .= "\n\t<tr>";
$qChuncks .= "<td align=center bgcolor=#EEEEEE>listColumns</td>";
$qChuncks .= "<td bgcolor=#EEEEEE><input type=text name=
\"fields[$thissuperQ][listColumns]\" value=\"$ListColumns\" size=20 maxlength=
99 class=textfield  tabindex=34 id=
field_12_3 /> </td>";
$qChuncks .= "\n\t</tr>";

$qChuncks .= "\n\t<tr>";
$qChuncks .= "<td align=center bgcolor=#E5E5E5>alignment</td>";
$selected = "selected=selected";
$overselected = "";
$rightselected = "";
$leftselected = "";
$underselected = "";
	switch ($Alignment){
	case 'Over':
			$overselected = $selected;
			break;
	case 'Right':
			$rightselected= $selected;
			break;
	case 'Under':
			$underselected= $selected;
			break;
	case 'Left':
			$leftselected= $selected;
			break;
	}
	
$qChuncks .= "<td bgcolor=#E5E5E5>
<select name=fields[$thissuperQ][alignment]
tabindex=37 id=field_13_3>
<option value=\"\"></option>
<option value=\"Over\" $overselected>Over</option>
<option value=\"Left\" $leftselected>Left</option>
<option value=\"Under\" $underselected>Under</option>
<option value=\"Right\"$rightselected>Right</option>
</select> </td>";
$qChuncks .= "\n\t</tr>";

//same line
$yesChecked = "";
$noChecked = "checked=checked";
if(isset($sameLine) and $sameLine=="Yes"){
$yesChecked ="checked=checked";
$noChecked = "";	
}

$qChuncks .= "\n\t<tr>";
$qChuncks .= "<td align=center bgcolor=#EEEEEE>sameLine</td>";
$qChuncks .= "<td bgcolor=#EEEEEE><input type=radio name=fields[$thissuperQ][sameLine] value=
\"No\" id=field_14_3_0  $noChecked tabindex=40 /><label for=
field_14_3_0>No</label> <input type=radio name=
fields[$thissuperQ][sameLine] value=
\"Yes\" id=field_14_3_1 $yesChecked 
 tabindex=40 /><label for=field_14_3_1>Yes</label> </td>";
$qChuncks .= "\n\t</tr>";

$qChuncks .= "\n\t<tr>";
$qChuncks .= "<td align=center bgcolor=#E5E5E5>textHeight</td>";
$qChuncks .= "<td bgcolor=#E5E5E5><input type=text name=
\"fields[$thissuperQ][textHeight]\" value=\"\" size=20 maxlength=
99 class=textfield tabindex=43 id=
field_15_3 /> </td>";
$qChuncks .= "\n\t</tr>";


$qChuncks .= "\n\t<tr>";
$qChuncks .= "<td align=center bgcolor=#EEEEEE>label for tables</td>";
$qChuncks .= "<td bgcolor=#EEEEEE><input type=text name=
\"fields[$thissuperQ][qLabel]\" value=\"$qLabel\" size=40 maxlength=192
class=textfield tabindex=52 id=field_18_3 />
</td>";
$qChuncks .= "\n\t</tr>";
if($questionType=='GridTag'){
	$qChuncks .= "<TR><TD colspan 2>". chunkOutGridLabels($qid). "</TD></TR>";
}


} //chuncks
if(!is_null($isChoiceable) ){
$qChuncks .= "<TR><TD> $choiceTable </TD></TR>";
}
$qChuncks .= "\n</table>";
  //need a form

$thisPage = $_SERVER['PHP_SELF'];
$target="$thisPage?uc=survey&survID=$survID&Woption=EditQs"; 
$htmlOut .= start_form($target); 
$goButton = "<button width = 10% onClick='submit();'><font color=\"black\" face=\"verdana narrow\" size=1.5><b>Make changes</b></font></button>";
$htmlOut .= $goButton ;
$htmlOut .= $qChuncks; 
$htmlOut .= $hiddenFields;
  //end a form
$htmlOut .= end_form();
 }  
?>
