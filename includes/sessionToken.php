<?php
$TOKEN_LIFE = 45; //minutes
$TOKEN_LIFE_TYPE = "MINUTE"; //OTHER VALUES INCLUDE 'SECOND' 'HOUR' 'DAY' 'MICROSECOND'
function checkValidToken(){
	$retVal = false;
	if ((isset($_COOKIE['session'])) AND (strLen($_COOKIE['session'])>0)){
		$mysqlCookieSession= mysql_real_escape_string($_COOKIE['session']);
		if(isValidToken($mysqlCookieSession)){
			$retVal=true;
		}
	}else {
		$retVal= false;
	}
	return $retVal;
}

function isValidToken($sessionToken) {
	global $TOKEN_LIFE;
	global $TOKEN_LIFE_TYPE;
	$queryStatement = "SELECT st.`sessionToken` " .
			 	  "FROM `sessionTokens`  st " .
			      "WHERE (DATE_SUB(CURRENT_TIMESTAMP(),INTERVAL $TOKEN_LIFE $TOKEN_LIFE_TYPE) <= st.`tokenUpdated`  
			      		OR st.`tokenOveride`  <> 0) " .
				  "AND invalidated = 0 " .	
			      "AND `sessionToken` = '$sessionToken'";
	$sqlData = safe_query($queryStatement);
	$numrow =mysql_num_rows($sqlData);
	return ($numrow>0) ? true : false;
}

function getUserIDFromToken($sessionToken) {
	global $TOKEN_LIFE;
	global $TOKEN_LIFE_TYPE;
	$queryStatement = "SELECT st.`userID` " .
			 	  "FROM `sessionTokens`  st " .
			      "WHERE `sessionToken` = $sessionToken";
	$sqlData = safe_query($queryStatement);
	$row = mysql_fetch_assoc($sqlData);
	return ($row['userID']) ? $row['userID'] : false;
}

function generateValidToken($userID,$allowFirstTime=true,$emailedSpecialToken=false,$override=false){
	$thisallowFirstTime = ($allowFirstTime)? 1:0;
	$thisemailedSpecialToken = ($emailedSpecialToken)? 1:0;
	$thisoverride = ($override)? 1:0;
	$retVal= false;
	$token=rand(1000000000,9999999999);
	$token= 123456789;
	$result = false;
	while(!$result){
		$token=rand(1000000000,9999999999);
		$sqlInsert="INSERT INTO sessionTokens (sessionToken,
		                                       tokenCreated,
		                                       tokenUpdated,
		                                       userID,
		                                       tokenOveride,
		                                       firstTimeAllowance,
		                                       emailedSpecialToken) 
		                               VALUES ($token, 
		                                       NOW(),
		                                       NOW(),
		                                       '$userID',
		                                       '$thisoverride' ,
		                                       '$thisallowFirstTime',
		                                       '$thisemailedSpecialToken')";
		$result = safe_query($sqlInsert);
	}
	$retVal =$token;
	if($thisallowFirstTime){
		setcookie('session',"$token");
	}
	return $retVal;
}
function updateSessionTime($sessionToken=-1){
	$sqlUpdate = "UPDATE `sessionTokens`  st " .
	              "SET st.`tokenUpdated` = NOW() " .
	              "WHERE `sessionToken` = '$sessionToken'"; 
	$result = safe_query($sqlUpdate);
}
function invalidateSession($sessionToken=-1){

	$sqlUpdate = "UPDATE `sessionTokens`  st " .
	              "SET st.`invalidated` = -1 " .
	              "WHERE `sessionToken` = '$sessionToken'"; 
	$result = safe_query($sqlUpdate);

}
function logoutUser(){
	//Should only be called if user has been checked for a valid session
	$retVal = false;
	if ((isset($_COOKIE['session'])) AND (strLen($_COOKIE['session'])>0)){
		$mysqlCookieSession= mysql_real_escape_string($_COOKIE['session']);
		invalidateSession($mysqlCookieSession);
	}
}


function hasFirstTimeAllowance($sessionToken){
	$queryStatement = "SELECT st.`sessionToken` " .
			 	  "FROM `sessionTokens`  st " .
			      "WHERE ( st.`firstTimeAllowance` <> 0) " .
			      "AND `sessionToken` = '$sessionToken'";
	$sqlData = safe_query($queryStatement);
	$numrow =mysql_num_rows($sqlData);
	return ($numrow>0) ? true : false;
}

function setFirstTimeAllowanceFlag($sessionToken= -1, $flag){
	$thisFlag = ($flag)? 1:0;
	$sqlUpdate = "UPDATE `sessionTokens`  st " .
	              "SET st.`firstTimeAllowance` = $thisFlag " .
	              "WHERE `sessionToken` = '$sessionToken'"; 
	$result = safe_query($sqlUpdate);
}

function getUserID($username = "", $password = ""){
	global $GLOBAL_DEFAULT_MYSQL_PASSWORD_HASH_METHOD;
	$query = "select userID from users
            where password = $GLOBAL_DEFAULT_MYSQL_PASSWORD_HASH_METHOD('$password') 
            and username = '$username'";
	$result = safe_query($query);
	$row = mysql_fetch_assoc($result);
	return ($row['userID']) ? $row['userID'] : false;

}

function activateAccount($userID){
	$sqlUpdate = "UPDATE `users`  u " .
	              "SET u.`userActive` = 1 " .
	              "WHERE u.`userID` = '$userID'"; 
	$result = safe_query($sqlUpdate);

}

function isActiveAccount($userID){
	$queryStatement = "SELECT u.`userID` " .
			 	  "FROM `users`  u " .
			      "WHERE userActive <> 0 " .
			      "AND `userID` = '$userID'";
	$sqlData = safe_query($queryStatement);
	$numrow =mysql_num_rows($sqlData);
	return (($numrow > 0) ? true : false);
}
function setEmailSpecialFlag($sessionToken= -1, $flag){
	$thisFlag = ($flag)? 1:0;
	$sqlUpdate = "UPDATE `sessionTokens`  st " .
	              "SET st.`emailedSpecialToken` = $thisFlag " .
	              "WHERE `sessionToken` = '$sessionToken'"; 
	$result = safe_query($sqlUpdate);
}
function hasEmailVerification($userID,$sessionToken){
	//Returns true is the session token exists and is the specially mailed one
	$queryStatement = "SELECT st.`sessionToken` " .
			 	  "FROM `sessionTokens`  st " .
			      "WHERE ( st.`emailedSpecialToken` <> 0) " .
			      "AND `sessionToken` = '$sessionToken'".
	              "AND `userID` = '$userID'";
	$sqlData = safe_query($queryStatement);
	$numrow =mysql_num_rows($sqlData);
	return ($numrow>0) ? true : false;
}
function expiredSession(){
	$retVal = false;
	if ((isset($_COOKIE['session'])) AND (strLen($_COOKIE['session'])>0)){
		$mysqlCookieSession= mysql_real_escape_string($_COOKIE['session']);
		if(isExpiredToken($mysqlCookieSession)){
			$retVal=true;
		}
	}else {
		$retVal= false;
	}
	return $retVal;
}

function isExpiredToken($sessionToken){
	global $TOKEN_LIFE;
	global $TOKEN_LIFE_TYPE;
	$expireMessageLimit = $TOKEN_LIFE + 30;
	$queryStatement = "SELECT st.`sessionToken` " .
			 	  "FROM `sessionTokens`  st " .
			      "WHERE (((DATE_SUB(CURRENT_TIMESTAMP(),INTERVAL $expireMessageLimit $TOKEN_LIFE_TYPE) <= st.`tokenUpdated`) " .
                         " AND (DATE_SUB(CURRENT_TIMESTAMP(),INTERVAL $TOKEN_LIFE $TOKEN_LIFE_TYPE) > st.`tokenUpdated` )) OR st.`tokenOveride`  <> 0) " .
			      "AND `sessionToken` = '$sessionToken'";
	$sqlData = safe_query($queryStatement);
	$numrow =mysql_num_rows($sqlData);
	return ($numrow>0) ? true : false;
}

function getUserIDFromUserScheduleMapID($userScheduleMapID) {
	$query = "select userID from userScheduleMap
            where userScheduleMapID = '$userScheduleMapID'";

	$result = safe_query($query);
	$row = mysql_fetch_assoc($result);
	return ($row['userID']) ? $row['userID'] : false;

}
function isLoggedIn(){
	$retVal = false;
	$mysqlCookieSession="";
	if (checkValidToken()){
		if ((isset($_COOKIE['session'])) AND (strLen($_COOKIE['session'])>0)){
			$mysqlCookieSession= mysql_real_escape_string($_COOKIE['session']);
		}
		$userID= getUserIDFromToken($mysqlCookieSession);
		return $userID;
	}//The session may have just started so let's give it a check
	if (isset($_POST['username']) && isset($_POST['password'])){
		$serverAuthUser=  mysql_escape_string($_POST['username']);
		$serverAuthPW=  mysql_escape_string($_POST['password']);
		$userID = getUserID($serverAuthUser,$serverAuthPW);
		return $userID;
	}
	return $retVal;
}
class User
{
	// property declaration
	public $username;
	public $userLastname;
	public $userFirstname;
	public $userID;
	public $userEMail;
	
	// method declaration
	public function displayVar() {
		return $this->$username .  ": " . $this->userFirstname . " ".  $this->userLastname;
	}

	function  __construct($userID=NULL){
		//Get and display overallsurvey info
		$this->userID = $userID;
		$userquery  =
		"SELECT  `users`.`userID`,`username`, `userLastname`,`userFirstname`,`userEMail` FROM `users` 
 				WHERE `users`.`userID` = '" .$userID . "';";
		$userResult = safe_query($userquery);
		$row = mysql_fetch_assoc($userResult );

		$this->username = $row['username'];
		$this->userLastname= $row['userLastname'];
		$this->userFirstname= $row['userFirstname'];
		$this->userEMail= $row['userEMail'];
	}
}

?>