<?php
/*
*/
function authenticate ($errmsg,$otherMessage='',$timeDelay=0)
{
global $GLOBAL_loginPage;
    if(isset($otherMessage) AND strlen($otherMessage)> 0){
    	print $otherMessage;
    }
	sleep($timeDelay);
    die($errmsg);
}

// void db_authenticate([string table [, string realm [, string error message [, string username field name [, string password field name]]]])


function db_user_authenticate($server="XXX"
    ,$database="XXX"
    ,$table="users"
    ,$realm="Secure Area"
    ,$errmsg="Please check your username and password"
    ,$user_field=""
    ,$password_field=""
)
{
	global $GLOBAL_loginPage;
	global $GLOBAL_username;
	global $GLOBAL_password;
	global $GLOBAL_DEFAULT_MYSQL_PASSWORD_HASH_METHOD;
    if (!checkValidToken())
    {
            if(expiredSession()){
            	 setcookie('session', '', time()-1000);
            	 authenticate($errmsg,"It appears your session has expired, Please log in again <BR>",4);
            }
            
    	$referer= (isset($_SERVER['HTTP_REFERER'])?($_SERVER['HTTP_REFERER']):"");
//Before we check the username and password
    	$refererOK = (0< stripos($referer,$GLOBAL_loginPage));
		//TODO Temporary Drupal Hack for testing
		//$refererOK = 1;	
    	if($refererOK){
    		$serverAuthUser=  mysql_escape_string($_POST['username']);
    		$serverAuthPW=  mysql_escape_string($_POST['password']);
    	}
    
        if (empty($user_field)) { $user_field = "username"; }
        if (empty($password_field)) { $password_field = "password"; }
        if(empty($serverAuthPW) && !$refererOK){
                //re-route to login page
                //TODO really need to pass the query string...
                if(strpos($GLOBAL_loginPage,"?")){
                	$addToQ = "&";
                }
                else { 
                	$addToQ = "?";
                }
        	$queryString = $_SERVER['QUERY_STRING'];
                $queryString = empty($queryString )? "":$addToQ.$queryString;
                
                $err ="<meta http-equiv=\"refresh\" content=\"0; URL=$GLOBAL_loginPage$queryString\">";
                authenticate($err);         
        }
        $query = "select $user_field from $table 
            where $password_field = $GLOBAL_DEFAULT_MYSQL_PASSWORD_HASH_METHOD('$serverAuthPW') 
            and $user_field = '$serverAuthUser'";
        $result = safe_query($query);
        if ($result) { list($valid_user) = mysql_fetch_row($result); }
        if (!$result || empty($valid_user)||!$refererOK  ){
         	//die("Fooo88888:$refererOK");
        	authenticate($errmsg,"Log in failed, Please check your username and password<BR>",4);
            die("If this message appears, please contact site administrator: XLDO");
        	}else {
        		
        		//CHECK if user is active
        		$userID = getUserID($serverAuthUser,$serverAuthPW);
        		$isActive = isActiveAccount($userID);
        		if(!$isActive){
					$ftu = "";
					if(isset($_POST['ftu']) AND strlen($_POST['ftu'])> 0){
        				$ftu = mysql_real_escape_string($_POST['ftu']);
        				if(hasEmailVerification($userID,$ftu)){
        					 activateAccount($userID);
        					 generateValidToken($userID);
        				}else { //No activated and funny code
       					
        					authenticate($errmsg,"This Account can not be activated, Please check your e-mail or contact the site administrator<BR>",10);
        				}
        			}else { //Just not activated
        				//        		die("did we get this far:C:$isActive" );
        				authenticate("","This Account has not been activated, Please check your e-mail for instructions or contact the site administrator<BR>",10);
        			}
        			
        		}else{ //Normal successful log in
        			
        			$token = generateValidToken($userID);
        		}
        	}
    }else {
    	$token = mysql_real_escape_string($_COOKIE['session']);
    	updateSessionTime($token);
    }
}
// string get_attlist ([array attributes [,array default attributes]])

// This function will take an associative array and format as a string
// that looks like 'name1="value1" name2="value2"', as is used by HTML tags.
// Values for keys in the first argument will override values for the
// same keys in the second argument. (For example, if $atts is (color=>red)
// and $defaults is (color=>black, size=3), the resulting output will
// be 'color="red" size="3"'.)
 
function get_attlist ($atts="",$defaults="")
{
    $localatts = array();
    $attlist = "";

    if (is_array($defaults)) { $localatts = $defaults; }
    if (is_array($atts)) { $localatts = array_merge($localatts, $atts); }

    while (list($name,$value) = each($localatts))
    {
        if ($value == "") { $attlist .= "$name "; }
        else { $attlist .= "$name=\"$value\" "; }
    }
    return $attlist;
}

include("db.php");
include("forms.php");
include("html.php");
include("tables.php");
include("CheckBox.php");
include("sessionToken.php");
?>
