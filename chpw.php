<?php

# Script 12.10 - change_password.php
// TODO: Screen incoming data 
//This page allows a logged-in user to change their password.
$u = "";
// This page allows a user to reset their password, if forgotten.
require_once ('./header.php');
//require_once ('./authenticate.php');
// Set the page title and include the HTML header.
include_once ('./start_page.php');

// If no first_name variable exists, redirect the user.
//if (!isset ($_SERVER['PHP_AUTH_USER'])) {

//	header("Location:  http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php");
//	ob_end_clean();
//	exit ();
//} else {
	// Load the page
	$username = $_SERVER['PHP_AUTH_USER'];
	//Gather user info from database using authenticated php variables
	$userquery = "SELECT `userID`,`userLastname`,`userFirstname`, `userEMail` FROM `users` "." WHERE `username` = \"$username\";";
	$userResult = safe_query($userquery);
	set_result_variables($userResult); //assign each colume to a global variable

	if (isset ($_POST['submit'])) { // Handle the form.
		echo '<table cellpadding=0 cellspacing=0><tr><td width=9><td>';
		echo '<table width=375 class=errortxt cellpadding=5 cellspacing=0 align=center>';
        // Connect to the database.
		// Check for a new password and match against the confirmed password.
		if (eregi("^[[:alnum:]]{4,20}$", stripslashes(trim($_POST['password1'])))) {
			if ($_POST['password1'] == $_POST['password2']) {
				$p = $_POST['password1'];
			} else {
				$p = FALSE;
				echo '<tr><td>Your password did not match the confirmed password</td></tr>';
			}
		} else {
			$p = FALSE;
			echo '<tr><td>Please enter a valid password</td></tr>';
		}

		if ($p) { // If everything's OK.	
			// Make the update query
			$query = "UPDATE users SET password=$GLOBAL_DEFAULT_MYSQL_PASSWORD_HASH_METHOD('$p') WHERE userID='$userID'";
			$result = @ mysql_query($query); // Run the query.
			if (mysql_affected_rows() == 1) { // If it ran OK	

				// Send an email
				$body = "Your password to log into the Application site has been changed to '$p'. Please keep a record of this password for your future visits to example.org.";
				mail($UserEMail, 'Your Application Online Password.', $body, 'From: application@example.org');
				echo '<h1>Your password has been changed.</h1>';
				//echo '<br><span class=hdrtextdrk>Return to Process</span>';
				include ('./end_page.php'); // Include the HTML footer.				
				exit ();

			} else { // If it did not run OK.

				// Send a message to the error log, if desired.
				$message = '<tr><td>Your password could not be changed due to a system error. We apologize for any inconvenience.';

			}
			mysql_close(); // Close the database connection.

		} else { // Failed the validation test.
			echo '<tr><td>- please try again.</td></tr>';
		}
		echo '</table></td></tr></table>';
	} // End of the main Submit conditional.
$target = $_SERVER['PHP_SELF'];
$htmlOutput =<<<EOQ
<table>
	<tr>
		<td width="9">&nbsp;</td>
		<td>
			<table width="550" border="0" cellspacing="0" cellpadding="3">
			<form action="$target" method="post">
			  <tr>
				  <td colspan="2"><h1>Change Your Password</h1></td>
			  </tr> 
			  <tr>
				  <td colspan="2" class="bodytxtdrk">Use only letters and numbers. Must be between 4 and 20 characters long.</td>
			  </tr> 
			  <tr>
				<td class="hdrtextdrk" width="100" valign="top">New Password:</td>
				<td class="hdrtextdrk" valign="top"><input type="password" name="password1" size="20" maxlength="20" /></td>
			  </tr>
			  <tr>
				<td class="hdrtextdrk" valign="top">Confirm New Password:</td>
				<td valign="top"><input type="password" name="password2" size="20" maxlength="20" /></td>
			  </tr>
			  <tr>
				<td>&nbsp;</td>
				<td align="left"><input type="submit" name="submit" value="Change My Password" /></td>
			</form><!-- End of Form -->
			  </tr>  
			</table>
		</td>
	</tr>
</table>
EOQ;
	print $htmlOutput;
//} // End of the !isset($PHP_AUTH_USER ELSE.
include ('./end_page.php'); // Include the HTML footer.
?>
