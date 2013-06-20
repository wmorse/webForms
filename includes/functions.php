<?php
function getSingletonUserScheduleMap($userID = -1){
	$retVal = FALSE;
	$sqlStatement = "SELECT userScheduleMapID FROM userScheduleMap u where userID = '$userID'";
	//die($sqlStatement);
	$result= safe_query($sqlStatement);
	$num = mysql_num_rows($result);
	if ($num ==1){
		//die("FUN:".$num);
		$row = mysql_fetch_assoc($result);
		$usmID = $row['userScheduleMapID']; 
		$retVal = $usmID;
	}
	return $retVal;
}

//TODO remove or rewrite this function -- if there is more than one schedule for a user, then result set will have multiplt rows
function getMapID($userID,$schedID){
	$retVal = FALSE;
	$sqlGetMapID = "Select userScheduleMapID from userScheduleMap where userID = $userID "
	              . "AND scheduleID=$schedID";
	
	$result = safe_query($sqlGetMapID);
	if($result){
		$row = mysql_fetch_array($result);
		$retVal= $row[0];  
	}
	return $retVal;
}

function getSurveyByBUserSCheduleMap($userSched_MapID){
		$retVal = FALSE;
        $sqlGetSurvID ="SELECT s.surveyID as survID, s.surveyTitle, DATE_FORMAT(sc.dateDue,'%b %e') as ddate " .
				"FROM survey s " .
				"JOIN schedule sc ON s.surveyID = sc.surveyID " .
				"JOIN userScheduleMap us ON sc.scheduleID = us.scheduleID " .
				"WHERE us.userScheduleMapID ='$userSched_MapID'";
		$resultsGetSurvID = safe_query($sqlGetSurvID);
		if($resultsGetSurvID){
			$row = mysql_fetch_assoc($resultsGetSurvID);
			$survID  = $row['survID'];
	    	$retVal = $survID;
		}
		return $retVal;
}

function getSingletonFromUserSurvey($userID = -1,$surveyID = -1){
	$retVal = FALSE;
	$sqlStatement = "SELECT usm.userScheduleMapID 
	                 FROM userScheduleMap usm
	                 JOIN schedule sc ON usm.scheduleID = sc.scheduleID  
	                 where usm.userID = '$userID' 
	                 AND sc.surveyID ='$surveyID'";
	$result= safe_query($sqlStatement);
	$num = mysql_num_rows($result);
	if ($num ==1){
		$row = mysql_fetch_assoc($result);
		$usmID = $row['userScheduleMapID']; 
		$retVal = $usmID;
	}
	return $retVal;
}

function getQuestionType($questionID= -1){
	$retVal = FALSE;
	$sqlStatement = "SELECT qt.questionType  
	                 FROM questionTypes qt
	                 JOIN questions q ON qt.questionTypeID = q.questionTypeID 
	                 where q.questionID = '$questionID'";
	$result= safe_query($sqlStatement);
	$num = mysql_num_rows($result);
	if ($num ==1){
		$row = mysql_fetch_assoc($result);
		$questionType = $row['questionType']; 
		$retVal = $questionType;
	}
	return $retVal;
	
}

function getMostRecentSchedule($surveyID = -1){
	if (!is_numeric($surveyID)){
		$surveyID = -1;
	}
	$retVal = FALSE;
	$sqlStatement = "	SELECT sc.scheduleID
						FROM schedule sc
						WHERE sc.surveyID = '$surveyID'
						AND (sc.dateOpened <= NOW( ) OR sc.dateOpened is NULL) 
						AND sc.dateDue >= NOW( )
						ORDER BY sc.dateDue asc
						LIMIT 0,1  
	";
	$result= safe_query($sqlStatement);
	$num = mysql_num_rows($result);
	if ($num ==1){
		$row = mysql_fetch_assoc($result);
		$sched = $row['scheduleID']; 
		$retVal = $sched;
	}
	return $retVal;
}

function getLocalFileNameValueFromID($questionID,$userScheduleMapID){
	$retVal = NULL;
	$sqlGetLocalFileName = "SELECT uf.localFileName "
						. "FROM userFiles uf "
						. "WHERE uf.questionID = '" . $questionID 
						. "' AND uf.userScheduleMapID = '".$userScheduleMapID. "'"
						. " AND isDeleted =0 "
						. "LIMIT 0,1";
	$resultLocalFileName= safe_query($sqlGetLocalFileName);
	if ($resultLocalFileName ){
		$row = mysql_fetch_assoc($resultLocalFileName);
		return $row["localFileName"];
	}else return NULL;

	return $retVal;
}

function isOpenEnroll($survID){
 	$retVal = false;
	$sqlGetSurvOpenEnroll = "SELECT s.`openEnrollment` 
						   FROM survey s 
						   WHERE surveyID = '$survID';";
	$resultGetSurvOpenEnroll= safe_query($sqlGetSurvOpenEnroll);
	if ($resultGetSurvOpenEnroll){
		$row = mysql_fetch_assoc($resultGetSurvOpenEnroll);
		$retVal= $row["openEnrollment"]== -1 ?true: false;
	}
	return $retVal;
}

function getOriginalFileNameValueFromID($questionID,$userScheduleMapID){
	$retVal = NULL;
	$sqlGetOrigFileName = "SELECT uf.origininalFileName "
						. "FROM userFiles uf "
						. "WHERE uf.questionID = '" . $questionID 
						. "' AND uf.userScheduleMapID = '".$userScheduleMapID. "'"
						. " AND isDeleted =0 "
						. "LIMIT 0,1";
	$resultOrigFileName= safe_query($sqlGetOrigFileName);
	if ($resultOrigFileName ){
		$row = mysql_fetch_assoc($resultOrigFileName);
		return $row["origininalFileName"];
	}else return NULL;

	return $retVal;
}

//TODO rewrite to use obviousness that a schedule and a user imply a USMID
function getUserScheduleMapID($userID,$scheduleID,$surveyID){
	$retVal = FALSE;
	$sqlGetMapID = "Select userScheduleMapID from userScheduleMap usm
					JOIN schedule sc ON sc.scheduleID= usm.scheduleID 
					where userID = $userID "
	              . "AND usm.scheduleID=$scheduleID "
	              . "AND sc.surveyID=$surveyID ";
	
	$result = safe_query($sqlGetMapID);
	if($result){
		$row = mysql_fetch_array($result);
		$temp =$row[0];  
		if (!is_null($temp)){
			$retVal=$temp;
		}
	}
	return $retVal;
}

?>