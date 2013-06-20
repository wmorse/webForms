<?php
/*
********************************************************
*** William Morse-- September 19 2003                ***
***                                                  ***
*** Purpopse of this script is to extract answers and***
*** create tables based on admin users choices       ***
***                                                  ***
********************************************************
*** Change Log                                       ***
***                                                  ***
*** Date     ** By  ** Reason                        ***
********************************************************
*** 20030919 ** WFM ** Initial coding                ***
***          **     **                               ***
********************************************************
********************************************************
*/
ini_set("memory_limit","100M"); //need to perform metrics and tweak
include_once("./includes/adminFunctions.php");
  $COLUMNNAMEMAX = 64; //SPECIFIED by MySQL docuimentation
  //use administrative authentication
foreach($_GET as $key => $value){
		$$key =$value;
	}
foreach($_POST as $key => $value){
		$$key =$value;
}

$out = "";	

if(!isset($survID)){
	$survID = "'UNDEFIND'";
	$SurveyTitle = "'UNDEFIND'";
	}	
$thisPage = $_SERVER['PHP_SELF'];
$queryString = $_SERVER['QUERY_STRING'];
  $MainAdminPAge = "main.php";
  include("header.php");
  include("authenticate.php");

  //Get overall survey info
$surveyQuery =  "SELECT surveyTitle FROM survey
                 WHERE  surveyID = $survID;";
$sqresult = safe_query($surveyQuery);

//asign each colume to a global variable
set_result_variables($sqresult);

  $page_title = "TableReport Administration";
  
 //Determine just how many SCHEDULES there are for this survey -- if there is only one dont
 // do anything special. If there are more than one, include (within the form the a set of check
 // boxes to allow the user to select a set of schedules
 
 $multipleScheduleTable = "";
 $numSch = countSchedules($survID);
 if($numSch>1){
 	$multipleScheduleTable.="<a href=\"javascript:toggleDiv('scheduleTable')\">Show/Hide all the schedules for this form</a>";
   $multipleScheduleTable.="<BR>Selected instances will be included in the output table(s) ";
 	$multipleScheduleTable.= "<div id=\"scheduleTable\" style=\"display:none\" >\n";
 	$multipleScheduleTable.="<TABLE><TR><TH>Due Date</TH><TH>&nbsp;</TH></TR>\n";
 	$sqlSchedules="SELECT sch.scheduleID, sch.dateDue "
 	             . "FROM schedule sch "
 	             . "WHERE sch.surveyID = '$survID'";
 	$results = safe_query($sqlSchedules);
 	if($results){
 	    while ($row = mysql_fetch_assoc($results)){
 	    	$scheduleID =$row['scheduleID'];
 	    	$dateDue =$row['dateDue'];
 	    	
 	        $schedChecksOut = checkbox_field("schedcheck[$i]","$scheduleID","","$scheduleID");
 	        $multipleScheduleTable.="<TR><TD>$dateDue</TD><TD>$schedChecksOut</TD></TR>";  		
 	    }
 	}
 	$multipleScheduleTable.="</TABLE>";
    $multipleScheduleTable.="</DIV>\n";
    
 }
  //Allow a toggle to show or not show the "ACTIVE schedules"

//process posted choices
  if (isset($usercheck)) {
  	//stuff outputfield in Questions table
	foreach($usercheck as $key => $value) {
		$newLabel =$userq[$key];
		$userq[$key]=trim($newLabel);
		$sql = "UPDATE questions SET qLabel = '$userq[$key]' WHERE questionID = '$value'";
		$result= safe_query($sql);
		if (!$result){
			die("Problem Naming columns");
		}
	}
    //collect html output
    $schedWHERE = "";
    if(isset($schedcheck)){
    	$schedWHERE .=" AND ( 0 ";
    	foreach($schedcheck as $key => $value){
    		$schedWHERE .= " OR sch.scheduleID ='$value' ";
    	}
    	$schedWHERE .= ")";
    }
//How many tables will we need
$MAXCOLUMNS = 500;
$serverAuthUser = $_SERVER['PHP_AUTH_USER'];
$numberOfColumns = count($usercheck);
$tempTableName = "temp$serverAuthUser";
for($i=0;$i<($numberOfColumns /$MAXCOLUMNS);$i++){
	$tableNameArr[]=$tempTableName.$i;
}

   //come up with tempfile name for use with this
   $tableNumber=0;
   foreach($tableNameArr as $newtable){

     $dropSQL = "DROP TABLE IF EXISTS $newtable;";
     $createSQL = "CREATE TABLE $newtable (";
     //Loop through the userschosen q's and form the  varchar
     $createSQL .= "userID int(11) NOT NULL, ";
     $createSQL .= " username varchar(50) NOT NULL, ";
     $createSQL .= " userLastname varchar(20) NOT NULL, ";
     $createSQL .= " userScheduleMapID int(11) NOT NULL,";
     $createSQL .= " dateDue date NOT NULL default '0000-00-00' ";
     
     
     $where = " 0 ";
     $inputSQLsnippet = "";
     $questionCount =$tableNumber * $MAXCOLUMNS;
     $qMax =($tableNumber +1)* $MAXCOLUMNS;
     $inWhileQcount = 0;
     reset($usercheck);
     while ((list($x,$QID) = each($usercheck)) AND $inWhileQcount < $qMax ) {
	      $inWhileQcount++;
	      if($inWhileQcount<=$questionCount){
	      	continue;
	      }
		  $colName = $userq[$x];
		  
		 //figure out the type of question
		  $sql = "SELECT qt.questionType As thisType FROM questionTypes qt " .
		  		" JOIN questions q on qt.questionTypeID = q.questionTypeID " .
		  		" WHERE q.`questionID` = '$QID'";
		  $thisTypeResult =safe_query($sql);
		  set_result_variables($thisTypeResult);
		  $qtypeArr[$QID]=$thisType;

		 if (!isset($thisType) or ($thisType=="TextArea")){
		 	$createSQL .= ",   $colName TEXT character set utf8 collate utf8_unicode_ci";
		 }else{ 

		 //figureout max columnsize
		  $sql = "SELECT max( Char_Length( answer ) ) As thisLen FROM answers WHERE 1 AND `questionID` = '$QID'";
		  $thisSizeResult =safe_query($sql);
		  set_result_variables($thisSizeResult);
		  $colSize=20;

		  if(isset($thisLen) AND ($thisLen >10)){
		  	$colSize=$thisLen;

		  }
		  if($colSize > 255 ){
		  	$colSize = 255;
		  }
	      $createSQL .= ",   $colName varchar($colSize) default NULL";
		 }
	      $inputSQLsnippet .= ", `$colName`";
	      // in the same pass we can create a wherer statement
	      $where .= " or answers.questionID = " . $QID;
     }
     $createSQL .= ",  PRIMARY KEY  (userScheduleMapID)) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;";

     $result = safe_query($dropSQL );
      $result = safe_query($createSQL );
     $usersSQL = "SELECT DISTINCT u.userID, u.username, u.userLastname, usm.userScheduleMapID, sch.dateDue  " .
     		"FROM users u " .
     		"JOIN userScheduleMap usm ON usm.userID = u.userID " .
     		"JOIN schedule sch ON sch.scheduleID = usm.scheduleID  " .
     		"JOIN survey s ON s.surveyID = sch.surveyID  " .
     		"WHERE s.surveyID = '$survID' $schedWHERE ORDER BY usm.userScheduleMapID;";
     $result = safe_query($usersSQL);
     $arrayUsers = Array();
     $arraySQLInserts = Array();
     $arrayUsers = db_values_array_SQL($usersSQL);
     $userWhereclause = " 0 ";
     while ($row = mysql_fetch_assoc($result)) {
     	$curUserID = $row['userID'];
     	$curUserSchedMapID = $row['userScheduleMapID'];
     	$curUsername = mysql_escape_string($row['username']);
     	$curUserLastname = mysql_escape_string($row['userLastname']);
     	$curDateDue= $row['dateDue'];
        $curSQL = "INSERT INTO $newtable (`userID`, `username`, `userLastname`, `userScheduleMapID` , `dateDue` $inputSQLsnippet) VALUES ($curUserID, '$curUsername', '$curUserLastname' ,$curUserSchedMapID, '$curDateDue' ";
        $userWhereclause .= " OR userScheduleMapID = " . $curUserSchedMapID;
        $arraySQLInserts[$curUserSchedMapID] = $curSQL;
     }
     $insertCap =  " );";
     //get the answers

     $answerSQL = "SELECT  answers.*, choices.choiceDescription "
                . "FROM answers "
                . "LEFT JOIN choices ON answers.choiceID = choices.choiceID "
                . "WHERE ($where ) AND ($userWhereclause ) ORDER BY userScheduleMapID;";



     $result = safe_query($answerSQL);

     $answerArray = array();
     while ($row = mysql_fetch_assoc($result)) {
       $currentVal = "NULL";
       $curUserSchedMapID = $row["userScheduleMapID"];
       $curQuestionID = $row["questionID"];

       if (isset($row["answer"])){
          $currentVal = $row["answer"];
       }
       else if (isset($row["choiceID"])){
          $currentVal = $row["choiceDescription"];
       }
       else if (isset($row["longAnswer"])){
//	$foo=$row["longAnswer"];	
//       	$currentVal = html_entity_decode($row["longAnswer"],ENT_NOQUOTES,"UTF-8");
              $currentVal = $row["longAnswer"];

//		if ($curQuestionID=872){ print("<BR>Orig:  $foo <BR><BR>Special: $currentVal  <BR><BR> ");		}
       }
       $answerArray[$curUserSchedMapID][$curQuestionID]=$currentVal;
     }

     while (list($userSchedMapID, $oneUserAnswers  )= each ($answerArray)) {
         //
         $someSQL = "";
         reset($usercheck);
         $inWhileQcount = 0;
         while ((list($key,$qid) =each($usercheck )) AND ($inWhileQcount < $qMax )){
	      $inWhileQcount++;
    	  if($inWhileQcount<=$questionCount){
      		continue;
      	  }

           if (isset($oneUserAnswers[$qid])){
              $someSQL .= ", '". mysql_real_escape_string($oneUserAnswers[$qid]) ."'";
           }
           else {
              $someSQL .= ", NULL";
           }

         }
         $arraySQLInserts[$userSchedMapID] .=  $someSQL .  $insertCap;
         $result = safe_query($arraySQLInserts[$userSchedMapID]);
     }
     $tableNumber++;
}

     $otherquery = "select * "
          . "FROM  $tableNameArr[0] "
          . "LIMIT 0, 10;";


  //Make a simple form that asks which questions



//Get list from post varibles
//Make table
//  Loop through list to produce SQL CREATE statement
//
//Get Users that have answered any of the questions
//  produce a WHERE statement with the q'ids OR'ed
//
//For each user get the answers
//  Write an INSERT INTO statement to add the answers
//
//

//Form

  $otherquery = "select * "
          . "FROM  $tableNameArr[0] "
          . "LIMIT 0, 10;";

    $arr_num_fields = mysql_num_fields(safe_query($otherquery));
    $result = safe_query($otherquery);
    for ($ii=0; $ii < $arr_num_fields; $ii++) {
      $hash_field_names[$ii] = mysql_field_name($result, $ii);
    }

    $out.= "<center><h2>Table $tableNameArr[0] </h2></center>";
    $out.= '<table align="center" border="2"  cellpadding="2" cellspacing="0"><tr>';
    $result = safe_query($otherquery);
    for ($ii=0; $ii < $arr_num_fields; $ii++) {
      $out.= "<th>";
      $out.= $hash_field_names[$ii];
      $out.= "</th>";
    }
    $out.= "</tr><tr>";
    $number_of_rows = @mysql_num_rows($result);
    for ($iii = 0; $iii < $number_of_rows; $iii++) {
      $record = @mysql_fetch_row($result);
      for ($ii=0; $ii < $arr_num_fields; $ii++) {
        $out.= "<td>";
        $out.= $record[$ii];
        $out.="</td>";
      }
    $out.= "</tr>";
    }
    $out.= "</table>";

$downloadfile = $tableNameArr[0] . ".csv";
//include("makefile.php");


//$downloadURL = "download.php";
//$downloadthis = $downloadURL . "?file=$downloadfile";
//$out .= "<BR>" . '<a href="'. $downloadthis . '">Download the table</a>';
}//isset otherquery
  //Maximum length of MySQL field name
  $maxFieldNameLength =strlen("12345678901234567891234567890123456789");
  //check for admin user
//$query = "select QuestionID, QuestionText, QuestionType "
//          . "FROM  Questions q, QuestionTypes qt , SurveySections ss, Survey sv "
//          . "WHERE qt.QuestionTypeID = q.QuestionTypeID AND "
//          . "q.SurveySectionID = ss.SurveySectionID AND "
//          . "ss.SurveyID = sv.SurveyID AND "
//          . "ss.SurveyID = $survID "
//          . "ORDER BY ss.SectionOrdinal, QuestionNumber;";

$SQL_questions ="select questionID, questionText,questionInstruction , q.qLabel,q.questionTypeID, questionNumber, ss.SurveySectionUserMa "
          . "FROM  questions q, questionTypes qt , surveySections ss, survey sv "
          . "WHERE qt.QuestionTypeID = q.QuestionTypeID AND "
          . "q.SurveySectionID = ss.SurveySectionID AND "
          . "ss.surveyID = sv.SurveyID AND "
          . "ss.surveyID = $survID "
          . "ORDER BY ss.SectionOrdinal, QuestionNumber;";
$result_questions = safe_query($SQL_questions);
          
  $questionCells = "";

  $arrayofQuestions = array();
//  $arrayofQuestions = db_values_array_SQL($SQL_questions);

  $myset = "abcdefghijklmnopqrstuvwxyz1234567890_ABCDEFGHIJKLMNOPQRSTUVWXYZ ";
	while ($row_question = mysql_fetch_array($result_questions,MYSQL_ASSOC)){
		$QID = $row_question["questionID"];
		$curLabel = $row_question["qLabel"];
		if(!is_null($curLabel) AND (strlen($curLabel)>0)){
			$QText = $curLabel;
		}else{
			$QID = $row_question["questionID"];
			$QText =$row_question["questionText"];
			if(is_null($QText) OR  (strlen($QText)<1)){
			$QText =$row_question["questionInstruction"];
			if(is_null($QText) OR  (strlen($QText)<1)){
			$QText =$row_question["SurveySectionUserMa"] ."_" . $row_question["questionNumber"];
			}
			}
			$QText = substr($QText,0,$maxFieldNameLength);
			$QText = my_remove($QText,$myset," ");
			$QText = ltrim($QText);
			$QText = trim($QText);
			$QText = strtr($QText, " ", "_");
		}
		if (in_array($QText,$arrayofQuestions)){
			$test =$QText."_2";
			$i=2;
			while(in_array($test,$arrayofQuestions)){
				$i++;
				$test=$QText."_".$i;
			}

			$QText = $test;
		}
		$arrayofQuestions[$QID] = $QText;
	}
//start form
//start table

//Table row color switching
$foo[true] = "#EEEFFF";
$foo[false] = "#FFFEEE";
$fubar = true;
//Table row color switching

$questionTable = "\n<table align=\"center\" border=\"2\"  cellpadding=\"2\" cellspacing=\"0\">\n";
//display header row
$headerRow = "\t<tr><th colspan =\"100%\">$surveyTitle</th></tr>
			  <tr><th>Question Section<BR>and Number</th>
              <th>Question</th>
			  <th>Question Instruc</th>
			  <th>Output Label</th>
			  <th>Essay/Long Answer</th>
			  <th>Select</th></tr>\n";
$questionTable .= $headerRow;
// QuestionValue, Output preloaded column label, Length, EssayType,SelectCHBox
//endtable
//Get question info from database
$result_questions = safe_query($SQL_questions);
$i=0;
while ($row_question = mysql_fetch_array($result_questions,MYSQL_ASSOC)){

   $qrow = "<tr BGCOLOR = '$foo[$fubar]' BORDERCOLOR = \"#CCC691\">";
   $fubar = ($fubar == false) ?true :false;
   $SectionMarker = $row_question["SurveySectionUserMa"];
   $QuestionNumber = $row_question["questionNumber"];
   $cell_QSection_QNumber = "<td width = '5%'>$SectionMarker $QuestionNumber</td>";

   $QuestionText = $row_question["questionText"];
   $cellQuestionText = "<td width = '25%'>$QuestionText</td>";
  $QuestionInstr = $row_question["questionInstruction"];
   $cellQuestionInstr = "<td width = '25%'>$QuestionInstr</td>";

   $QuesID = $row_question["questionID"];
   $shortLabel =  $arrayofQuestions[$QuesID];


   $cellOutputLabel = "<td width = '30%'>" . hidden_field("QID[$i]",$QuesID);
   $cellOutputLabel .= text_field("userq[$i]","$shortLabel",$maxFieldNameLength+1);
   $cellOutputLabel .= "</td>";

   $IsEssay = "<nobr>";
   if ($row_question["questionTypeID"]=="5"){$IsEssay = "<B>X</B>";}
   $cellEssay = "<td align='center' width = '10%'>$IsEssay</td>";

   $userCheck = checkbox_field("usercheck[$i]","$QuesID","","$QuesID");
   $cellCheck = "<td width = '5%'>$userCheck</td>";

   $qrow .= $cell_QSection_QNumber . $cellQuestionText . $cellQuestionInstr
         . $cellOutputLabel . $cellEssay. $cellCheck;


   $qrow .= "</tr>";
$questionTable .= $qrow;
$i++;
}
$questionTable .= "</table>\n";




    $targetString= $thisPage.$queryString;
    if(isset($out)){
    	$htmlOut .= $out;
    }
    $arrLen = count($tableNameArr );
    $htmlOut .= "<br>";
    if(is_array($tableNameArr )){
	    foreach($tableNameArr as $key => $thisTable){
	    	$part = $key + 1;
	    	$htmlOut .= "Download spreadsheet part $part of $arrLen , CLICK <a href=\"./export.php?part=$thisTable\" target=\"_blank\"> <u>HERE</u></a>. <br>";
	    }
    }
    $htmlOut .= start_form($targetString);
    $htmlOut .= "<H3><B>THIS PAGE IS UNDER CONSTRUCTION</B></H3><BR />";
    $htmlOut .= $multipleScheduleTable;
    $htmlOut .= $questionTable;

  $htmlOut .= paragraph(
    submit_field("submitchoice","Submit")
    , reset_field()
);

  $htmlOut .= end_form();


  $htmlOut .= "<BR><BR><BR>" . '<a href="'. $MainAdminPAge . '">Go to survey admin page</a>';

//include("end_page.php");
?>