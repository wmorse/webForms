<?php
//TODO configure $GLOBAL_pwResetPage to be as flexible as login page
include_once ('header.php');
$thisPage = $_SERVER['PHP_SELF'];
$path_parts = pathinfo($thisPage);
$dirinfo =  $path_parts['dirname'];	 
$queryString = $_SERVER['QUERY_STRING'];
$queryString = empty($queryString )? "":$queryString; 

//Get most recent schedule for the given survey
//TODO allow people with accounts to add new applications
$form1SchedID  = getMostRecentSchedule($GLOBAL_SurveyID);
//FILTER INCOMING POST AND GET VARIABLES
$clean = array();
switch($_POST['submitme']){
	case 'Create Account':
	case 'Continue':
	$clean['submitme']=	$_POST['submitme'];
	break;
}
if(isset($_POST['username'])){
	//TODO Add check for email wholesomeness
	$clean['username']=$_POST['username']; 
}
if(isset($_POST['password'])){
	//TODO Add check for password wholesomeness
	$clean['password']=$_POST['password']; 
}
if(isset($_POST['userLastName'])){
	$clean['userLastName']=$_POST['userLastName']; 
}
if(isset($_POST['userFirstName'])){
	$clean['userFirstName']=$_POST['userFirstName']; 
}


if ($clean['submitme']== "Create Account") //we've submitted once
	{
	$mysqlusername= '';	
	if(isset($clean['username'])){
		$mysqlusername= mysql_real_escape_string($clean['username']);		
	}
	if(isset($clean['password'])){
		$mysqlpassword= mysql_real_escape_string($clean['password']);		
	}
	if(isset($clean['userFirstName'])){
		$mysqluserFirstName= mysql_real_escape_string($clean['userFirstName']);		
	}
	if(isset($clean['userLastName'])){
		$mysqluserLastName= mysql_real_escape_string($clean['userLastName']);		
	}
	
	$userquery = "SELECT username as exUsername, userFirstname, userLastname, userEMail  FROM users WHERE username = '$mysqlusername';";
	$userResult = safe_query($userquery);
	//asign each colume to a global variable
	set_result_variables($userResult);
	$duplicateEntry = false;
	if (empty ($exUsername)) {
		// if we don't have an existing username with that value, then insert the data from the form
		$sqlInsert = "insert into users ( username, userTypeID, userLastName, userFirstName, userEMail, password, userActive, userDateFirstAccess) "
		           . " values ('$mysqlusername','1', '$mysqluserLastName', '$mysqluserFirstName', '$mysqlusername', $GLOBAL_DEFAULT_MYSQL_PASSWORD_HASH_METHOD('$mysqlpassword'), '0', NOW( ));";
		safe_query($sqlInsert);
		// get the ID of the new record in the users table
		// (automatically assigned by MySQL)
		$user_id = mysql_insert_id();
		$sql = "INSERT INTO userScheduleMap (userID, scheduleID) 
		                       VALUES ('$user_id', '$form1SchedID')";
		mysql_query($sql);
		$tr = mysql_insert_id();

	} else {
		$duplicateEntry = true;
		// if we have an existing account with that username, prompt user to select a different username
		$htmlDuplicateEntry=  "";
		$htmlDuplicateEntry .= 'The account username '
		     .'<strong>' .$clean['username']
		     .'</strong>'
		     .' already exists. If you think you might already have an account you may log in '
		     ."<a href=\"{$GLOBAL_loginPage}?$queryString\">"
		     .'here'
		     .'</a><BR>'
		     ." or if you have forgotten your password click <A href=\"$GLOBAL_pwResetPage?$queryString\" > reset </A> or if you may contact the site administrator "
		     ."<a href=mailto:$GLOBAL_SiteAdmin>"
		     .'send an email'.'</a>'
		     .' to the site administrator to get your account information.'
		     .'</td>'.'</tr>'.'</table>';
		$htmlDuplicateEntry .='<br>'.'<br>';
		
		//if there is an open schedule and there is open enrollment, show link to enroll user
		$htmlDuplicateEntry .= "click here to login and add this form <A href=\"$GLOBAL_loginPage?$queryString&addReq=123\" > Add </A>";
	}
} // end if ($submitme == "Create Account")
else {
	$submitLabel = "Create Account";
}


$htmlMainContainer= "<div id=\"container\">\n<div id=\"intro\">";
$htmlMainContainerEND = "</div><!---END container---></div><!---END intro--->";
$htmlMainBody = "<div id = \"preamble\">";

$htmlMainBodyEND = "</div><!---END preamble--->";


if($clean['submitme']=='Create Account'){
	if($duplicateEntry){
		$htmlOut = $htmlDuplicateEntry. getFirstTimeForm();
	}else {
		$target = "http://{$_SERVER['SERVER_NAME']}/$dirinfo/loginPage.php?$queryString";
		mailRegistrationInfo($user_id,	$clean['username'],$clean['password'],$userSched_MapID,$target);
		$htmlOut = "<BR><BR><BR>";
		//$firstTimeToken = generateValidToken($user_id);
		$actionPage = "./form.php?$queryString&sched=$form1SchedID";
		$submitLabel = "Continue";
		$htmlOut .= getNewUserForm($actionPage,$submitLabel, $clean['username'],$clean['password'],$clean['userFirstName'],$clean['userLastName']);
		
	}
} else { //not previously submitted
	//check that the form is available
	if(empty($form1SchedID)){
		//This should be developed to a nice looking fail
		die("That form is not available. Please contact the site administrator if you think this is an error.");
		
	}else 	$htmlOut = getFirstTimeForm();
	
}

include_once ("start_page.php");

print $htmlMainContainer;
print $htmlMainBody . $htmlOut. $htmlMainBodyEND;
print $htmlSideBar;
print $htmlMainContainerEND;


include_once ("end_page.php");

function getFirstTimeForm($submitLabel= "Create Account"){
$thisPage = $_SERVER['PHP_SELF'];
$queryString = $_SERVER['QUERY_STRING'];
$queryString = empty($queryString )? "":$queryString; 
	
$firstTimeThrough = <<<EOQ
<form onsubmit="return checkPWsame()" action="$thisPage?$queryString" method="post">  <table cellspacing="3" cellpadding="3" border="0">
    <tbody><tr>
	  <td colspan="3">
	<BR><BR><BR>  <p>Please create an account if you are eligible and intend to apply for this program.</p></td>
	</tr>
    <tr valign="top"> 
      <td width="25%" class="qname"><span class="qname">* Username:</span></td>
      <td width="30%" align="left"><input type="text" value="" maxlength="75" size="30" id="username" name="username"/></td>
      <td align="left" class="txtdir">Use your valid <strong>email address</strong> 
        as your Username.</td>
    </tr>
    <tr valign="top"> 
      <td width="25%" class="qname"><span class="qname">* Re-enter your Username:</span></td>
      <td width="30%" align="left"><input type="text" value="" maxlength="75" size="30" id="usernametwo" name="usernameII"/></td>
      <td align="left" class="txtdir">Please re-enter your <strong>email address</strong>.</td>
    </tr>
    
    <tr valign="top"> 
      <td class="qname">* Password:</td>
      <td align="left"><input type="password" maxlength="16" size="10" name="password"/></td>
      <td align="left" class="txtdir" rowspan="2">Use only letters and numbers. Must be between 4 and 20 characters long.</td>
    </tr>
    <tr valign="top"> 
      <td class="qname">* Confirm Password:</td>
      <td align="left"><input type="password" maxlength="16" size="10" name="passwordtwo"/></td>
    </tr>
    <tr> 
      <td width="25%" valign="top" class="qname">Name:</td>
      <td valign="top" align="left" class="txtffield"><input type="text" value="" size="20" id="userFirstName" name="userFirstName"/> 
        <br/>
        * First Name<br/> <input type="text" value="" size="20" id="userLastName" name="userLastName"/> 
        <br/>
        * Last Name (Family Name)</td>
      <td valign="top" align="left" class="txtdir">Spell your name <strong>exactly</strong> 
        as it appears in your passport, if available.</td>
    </tr>
    <tr> 
    </tr>
    <tr> 
    </tr>
    <tr> 
    </tr>
    <tr> 
    </tr>
    <tr> 
  </tr>
    <!--<tr> 
    </tr>
    <tr valign="top" height="5"> 
    </tr>-->    <tr valign="top"> 
      <td class="qname"> </td>
      <td align="left" colspan="2"> 
  <p>
<input type="submit" value="$submitLabel" name="submitme"/>
<input type="reset" value="Reset" name="reset"/>
</p>
      </td>
    </tr>
    <tr valign="top"> 
      <td class="qname"> </td>
      <td align="left" class="txtffield" colspan="2"><span class="txtffield">* 
        required field</span></td>
    </tr>
  </tbody></table>

</form>
EOQ;
return $firstTimeThrough;	
}function getNewUserForm($actionPage="",$submitLabel, $username,$password,$userFirstName,$userLastName){
			$newUserFormHTML =  "";
		$newUserFormHTML .= "<FORM action=\"$actionPage\" method=\"post\" > ";
		$newUserFormHTML .= 'You have entered the following information:'
		                .'<br>'.'Username:' 
		                .'<strong>'.$username.'</strong>'
		                .'<br>'.'Password: '.'<strong>'
		                .$password.'</strong>'
		                .'<br>'.'First Name '
		                .'<strong>'.$userFirstName.'</strong>'
		                .'<br>'.'Last Name '
		                .'<strong>'.$userLastName .'</strong>'
		                .'<br>'.'Email Address (same as user account)'.'<strong>'.$clean['username.']
		                .'</strong>'.'<br>'
		                .'Please make note of these items. You will receive a confirmation of your registration by e-mail. In order to
		                  access the application you will need to visit the URL in the e-mail to complete your registration.' 
		                .'<br>';
		                
		
		$newUserFormHTML .= "<input type=\"submit\" value=\"$submitLabel\" name=\"submitme\"/>";
		$newUserFormHTML .= '</FORM>';
	return $newUserFormHTML;
}
function mailRegistrationInfo($userID,$username,$password,$tr,$target){
		//send the email to the user with their account information
		$surveyID = getSurveyByBUserSCheduleMap($userScheduleMapID);
	
        $generatedMailToken= generateValidToken($userID,false,true);
		$mailUsername = $username;
		$mailPassword = urlencode($password);		
		$from = "application@example.org";

		$to = "$mailUsername";
		//TODO send to site admin
		$reply = "application@example.org";
		$subject = "Application Account Information";
		$headers = "From: Account Information<$from>\r\n";
		$headers .= "Bcc: $reply\r\n";
        $sendmailcommand = "-f application@example.org";
		$body = "Account Information
		==================================================
		
		Hello and thank you for registering.
		
		Your Account Information is listed below.
		
		Username: $mailUsername
		Password: $mailPassword
		Account Email Address: same as username: $mailUsername
		
		In order to continue your application after the initial registration,
		you must activate your account by vising the following site
		
		$target&ftu=$generatedMailToken
		
		Please keep this information for your records.";
		if (mail($to, $subject, $body, $headers,$sendmailcommand)) {
			//echo("<p><table width=375><tr><td>An email has been sent to $UserEMail with your confirmation number. Please keep this information for your records.</td></tr></table></p>");
			//die("<BR>".$to. "<BR>".$subject. "<BR>".$body."<BR>". $headers);
		} else {
			//die("<p>Message delivery failed...: TO: $to,<BR>SUBJECT: $subject<BR>Body:$body</p>");
		}
	
}
?>