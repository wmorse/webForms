<?php
/*
********************************************************
***                                                  ***
*** This is a slip of script, that when included     ***
*** takes care of the authentication formalities.    ***
***                                                  ***
*** Upon Instalation, the site admin needs to        ***
*** specify the info for the connection string to    ***
*** database server, the database, and the user      ***
*** table                                            ***
***                                                  ***
********************************************************
*/
$queryString = $_SERVER['QUERY_STRING'];
$queryString = empty($queryString )? "":"?".$queryString; 

$redirect = "<h3>Notice -- Authentication Required to access this site:<BR>"
            ."You should soon be redirected to the login page."
            ."<BR>If you are not please click <A href=$GLOBAL_loginPage$queryString > here </A>"
            . "</h3><meta http-equiv=\"refresh\" content=\"4; URL=$GLOBAL_loginPage$queryString\">";
if(isset($_POST['ftu'])){
	$ftu = urlencode($_POST['ftu']);
	$queryString = $_SERVER['QUERY_STRING'];
	$queryString = empty($queryString )? "?ftu=$ftu":"?".$queryString."&ftu=$ftu"; 
	$redirect = "<h3>Authentication Required to access this site:<BR>"
            ."You should soon be redirected to the login page."
            ."<BR>If you are not please click <A href=$GLOBAL_loginPage > here </A>"
            . "</h3><meta http-equiv=\"refresh\" content=\"2; URL=$GLOBAL_loginPage?ftu=$ftu\">";
}
if($GLOBAL_AuthenticationMethod == "Standard"){

db_user_authenticate($GLOBAL_DATABASE_URL, $GLOBAL_DATABASE, $GLOBAL_DATABASE_USER_TABLE
    ,"Access to site "
    ,$redirect
);	
//IF we get to here, we didn't fail

$mysqlToken=null;	
$clean = array();
if(isset($_COOKIE['session'])){
$clean['token']=$_COOKIE['session'];
$mysqlToken = mysql_real_escape_string($clean['token']);
}

//If there is no Token available to php (because we just sent it to the client, or if there is an old cookie lying around the client.
if(strlen( $mysqlToken>0)&& isValidToken($mysqlToken)){
	$userID =getUserIDFromToken($mysqlToken);

}else {
	//since we are authenticated, but have no token, it must be the first time
			//This is necessary because _COOKIE is not yet available
    		$serverAuthUser=  mysql_escape_string($_POST['username']);
    		$serverAuthPW=  mysql_escape_string($_POST['password']);
	$userID = getUserID($serverAuthUser,$serverAuthPW);
}

}else
if($GLOBAL_AuthenticationMethod == "Drupal"){
//include("./drupalAuthenticate.php");
$userID = $formsDrupalUserID;
}




?>