<?php
// pull the user id from the database
include_once("./header.php");
//postsurveyneeds to be aware of users, even if they are going to a non-standard page
//Because postsuvey changes data

//TODO include("./authenticate.php");


$survID = $GLOBAL_SurveyID;
//TODO hacky --yucky
if(isset($userScheduleMapID)){
	$userSched_MapID=$userScheduleMapID;
}else die("Don't know the form so I am dying");
//What do do in case where user re-fill out part of a survey-- how to get rid of unwanteed ansers???
if(isset($_POST['QuestionIDarr'])){
	$QuestionIDarr = $_POST['QuestionIDarr'];
$QuestionType = $_POST['QuestionType'];
$AnswerText = $_POST['AnswerText'];
if(isset($_POST['ChoiceID'])){
	$ChoiceID= $_POST['ChoiceID'];
}
if(isset($_POST['GridAnswerText'])){
	$GridAnswerText= $_POST['GridAnswerText'];
}
	while (list($key,) = each($QuestionIDarr))
    {
    	$questionID = $QuestionIDarr[$key];
    	//if question is not a file upload type
    	$type = getQuestionType($questionID);
    	if ($type!="File Upload"){
	    	$sqlDel= "DELETE FROM answers WHERE (userScheduleMapID = $userSched_MapID) AND questionID = $questionID";
	        $result=safe_query($sqlDel);
     	}
            if ($QuestionType[$key] == "Ranking"){
               if ($RankText[$key] != ""){
                  if (is_array($RankText[$key])){
                     while (list($innerkey,$rankval) = each($RankText[$key])){
                     $rankval = mysql_real_escape_string($rankval);
                      $RankChoiceID = $RankID[$key][$innerkey];
					  if (!($rankval=="")){
	                      safe_query("insert into answers (userScheduleMapID, choiceID, answer, questionID)
    	                  values ($userSched_MapID,$RankChoiceID,$rankval,$QuestionIDarr[$key])");
    	                 }
                      }
                  }
               }
            }else if ($QuestionType[$key] == "TextArea"){
            	    	$tmpAnswer = mysql_real_escape_string($AnswerText[$key]);
                safe_query("insert into answers (userScheduleMapID, LongAnswer, QuestionID)
                     values ($userSched_MapID,'$tmpAnswer',$QuestionIDarr[$key])");
            }//FILE UPLOAD
            else if ($QuestionType[$key] == "File Upload"){ 
            	echo "\n<!-- CHECK $key" . "\nQid ".$questionID. 
            	"\nName ".$_FILES[AnswerFile][name][$key] .  "\nType ". $_FILES[AnswerFile][type][$key] .
            	     "\ntmp_name ". $_FILES[AnswerFile][tmp_name][$key] . "\nerror ". $_FILES[AnswerFile][error][$key] .
            	     "\nsize ". $_FILES[AnswerFile][size][$key] ."\nDelete list ". $stirateID[$key] . "\n --> \n" ;
            	      $filename = mysql_real_escape_string($_FILES[AnswerFile][name][$key]);
                      $error = $_FILES[AnswerFile][error][$key];
                      $size =$_FILES[AnswerFile][size][$key];
                      $tmp_name = $_FILES[AnswerFile][tmp_name][$key]; 
                      $doDelete = is_numeric($stirateID[$key])? $stirateID[$key]:0;
                      if($doDelete==1 ){
                         //DELETE file from system
                         //Update database
                        $sqlUpdate = "UPDATE userFiles set `isDeleted` = '1' WHERE (userScheduleMapID = '$userSched_MapID') AND questionID = '$questionID'";
                        safe_query($sqlUpdate);	 	 
                      	$sqlDel= "DELETE FROM answers WHERE (userScheduleMapID = $userSched_MapID) AND questionID = $questionID";
                      	safe_query($sqlDel);
                      }else{  //NOT A DELETE requset
                      if(   !($error==4 || $error=="")   && $size ==0){
                      	  //Update Answers with error messages
                      	  safe_query("insert into answers " .                      	              "(userScheduleMapID,  " .                      	              "answer, LongAnswer,questionID) ". 
                      	              "values ($userSched_MapID,'Error','$filename',$QuestionIDarr[$key])");
                      }else/*not an error*/ {
                      	if($error==0 && is_uploaded_file($tmp_name) && $size !=0){ 
                      	//Move file to good location //insert record to DB about file location	//update Answers to refer to file
                      	//TODO MAKE SURE THIS IS SAFE
                      	$newFileName = $userSched_MapID ."_".  $questionID. "_". basename($filename); 
                      	if(move_uploaded_file($tmp_name,"./upFiles/".$newFileName)){
    						$sqlDel= "DELETE FROM answers WHERE (userScheduleMapID = $userSched_MapID) AND questionID = $questionID";
                      	safe_query($sqlDel);
                      	safe_query("insert into answers " .
                      	              "(userScheduleMapID,  " .
                      	              "answer, LongAnswer,questionID) ". 
                      	              "values ($userSched_MapID,'$newFileName','$filename','$questionID')");
                      	 $upAnswerID = mysql_insert_id();
                      	 $sqlUpFiles = "INSERT INTO `userFiles` (answerID,userScheduleMapID,questionID, localFileName,origininalFileName) ". 
                      	               " VALUES ('$upAnswerID','$userSched_MapID','$questionID','$newFileName','$filename')";
                      	 safe_query($sqlUpFiles);                 	
                      	}// end able to move file
                      	
                      }
                     }//end not an errpr 
				} //END not a DELETE request
            } //end file upload
            else {
               if ((isset($ChoiceID[$key])) and ($ChoiceID[$key] != ""))
               {
                 if (is_array($ChoiceID[$key]))
                 {
                   while (list(,$choiceval) = each($ChoiceID[$key]))
                   {
                      $choiceval =mysql_real_escape_string($choiceval);
                      safe_query("insert into answers (userScheduleMapID, choiceID, questionID)
                      values ($userSched_MapID,$choiceval,$QuestionIDarr[$key])");
                   }
                 }
                 else         
                 {
                     // write non-blank responses to the database.
                     $tmpC = mysql_real_escape_string($ChoiceID[$key]);
               safe_query("insert into answers (userScheduleMapID, choiceID, questionID)
                     values ($userSched_MapID,'$tmpC',$QuestionIDarr[$key])");
                 }
               }
               if (isset($AnswerText[$key]) AND $AnswerText[$key] != "")
               {
               	$temp =$AnswerText[$key];
                 if (is_array($AnswerText[$key]))
                 {
                   while (list(,$choiceval) = each($AnswerText[$key]))
                   {
               safe_query("insert into answers (userScheduleMapID, answer, questionID)
                     $choiceval =mysql_real_escape_string($choiceval);
                     values ($userSched_MapID,$choiceval,$QuestionIDarr[$key])");
                   }
                 }
                 else         
             {
               // write non-blank responses to the database.
              $tmpAnswer = mysql_real_escape_string($AnswerText[$key]);
               safe_query("insert into answers (userScheduleMapID, answer, questionID) 
                     values ($userSched_MapID,'$tmpAnswer',$QuestionIDarr[$key])");
             }
               }
     
            }//else not Qtype Ranking
       }
}       
       if(isset($GridAnswerText)){processGridAnswers();}
       
function processGridAnswers($GridAnswerText,$userSched_MapID){
	while (list($QID,$value) = each($GridAnswerText)){
		$sqlDeleteOld =" DELETE FROM answers " .
				"WHERE (userScheduleMapID = $userSched_MapID) " .
				"AND questionID = $QID";
		        safe_query($sqlDeleteOld );
		        if (!empty($value)){
		        	$value =mysql_real_escape_string($value);
               $sqlInsert= "insert into answers (userScheduleMapID, answer, questionID) 
                     values ($userSched_MapID,'$value',$QID)";
                 safe_query($sqlInsert);
		        }
	}
}          
?>