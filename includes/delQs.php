<?php
/*
 * Created on Aug 4, 2005
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
$htmlOut = ""; 
foreach($_POST as $key => $value){
		$$key =$value;
}
foreach($_GET as $key => $value){
		$$key =$value;
}
 include("./header.php");
 include("./QuesTypes.php");
 //put a title here with $page_title
 
 if(!isset($editQs)){
 	$htmlOut = "You must chose at least one question";
 	
 }else{
$qChuncks = "";
$questionIDCounter=0;
for ($i = 0; $i < sizeof($editQs); $i++) {
$questionIDCounter++;
$thissuperQ =$questionIDCounter; 	
$qid = $editQs[$i];
$sqlGetQuestion = "SELECT  questionNumber, questionID, questionText, questionType, questionInstruction, "
        . "textWidth, questionWidth, listColumns, alignment, sameLine, "
        . "textHeight, hasDependent, dependentQID, q.questionTypeID  "
        . "FROM  questions q, questionTypes qt "
        . "WHERE  qt.questionTypeID = q.questionTypeID " 
        . "AND qt.questionType <> 'GridElement' " 
        . "AND questionID = " .$qid . " "
		. "ORDER BY questionNumber;";
$colorOne ="#E5E5E5";
$colorTwo = "#EEEEEE"; 
$bgColor = $colorOne;
$resultsGetQuestion = safe_query($sqlGetQuestion);				
list($questionNumber, $questionID, $question, $questionType,$questionInstruction,
            $textWidth , $QuestionWidth , $ListColumns ,  $Alignment ,
            $sameLine ,$TextAreaHeight, $HasDep, $DepQID, $questionTypeID ) = mysql_fetch_row($resultsGetQuestion );	

$bgColor = ($bgColor ==$colorOne)? $colorOne : $colorTwo;
$qChuncks .= "\n<table border=0 cellpadding=2 cellspacing=1>";
$qChuncks .= "\n\t<tr>";
$qChuncks .= "<th>Delete ?</th>";
$qChuncks .= "<th>Question</th>";
$qChuncks .= "<th>Question Instuctions</th>";
$qChuncks .= "</tr>";

$qChuncks .= "\n\t<tr>";
$qChuncks .= "\n\t<tr>";
$qChuncks .= "<td align=center bgcolor=$bgColor>" .checkbox_field("delQID[]", $questionID,"", "$questionID") . "</td>";
$qChuncks .= "<td bgcolor=$bgColor>"  .
		"<textarea name=qt[$questionID]\" " .
		"class=textfield id=field_3_3 cols=80  rows=6/>$question</textarea >";
$qChuncks .= "<BR><CENTER><SMALL>Instructions</SMALL></CENTER><BR>".
		"<textarea name=qi[$questionID]\" " .
		"class=textfield id=field_3_3 cols=80  rows=6/>$questionInstruction</textarea >";
$qChuncks .= "\n\t</tr>";
} //chuncks


$qChuncks .= "\n</table>";
$hiddenFields =""; 
  //need a form

$thisPage = $_SERVER['PHP_SELF'];
$target="$thisPage?uc=survey&survID=$survID&Woption=EditQs"; 
$htmlOut .= start_form($target); 
$goButton = "<button width = 10% onClick='submit();'><font color=\"black\" face=\"verdana narrow\" size=1.5><b>Delete Checked Questions</b></font></button>";
$htmlOut .= $goButton ;
$htmlOut .= $qChuncks; 
  //end a form
$htmlOut .= end_form();
 }  
?>
