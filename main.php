<?php
/*
 * Created on Aug 4, 2005
 *
 * 
 */
 $js = "";
 $mainContent = "";
 $centerContent = "";
 $leftContent = "";
 foreach($_POST as $key => $value){
		$$key =$value;
}
 
foreach($_GET as $key => $value){
		$$key =$value;
}
 include("./header.php");
 include("./authenticate.php");
$clean = array();
$clean['token']=$_COOKIE['session'];
$mysqlToken = mysql_real_escape_string($clean['token']);
if(strlen( $mysqlToken>0)){
$userID =getUserIDFromToken($mysqlToken);
} else {
	//since we are authenticated, but have no token, it must be the first time
			//This is necessary because _COOKIE is not yet available
    		$serverAuthUser=  mysql_escape_string($_POST['username']);
    		$serverAuthPW=  mysql_escape_string($_POST['password']);
	$userID = getUserID($serverAuthUser,$serverAuthPW);
}
 
//Who the user is determines what they see
//User roles
 $sqlGetUserType = "SELECT ut.userType " 
 				 . " FROM userTypes ut "
 				 . " JOIN users u on ut.userTypeID = u.userTypeID "
 				 . " WHERE (u.userID = '$userID')";
 				 
$result = safe_query($sqlGetUserType);
$row = mysql_fetch_assoc($result);
$userRole = $row['userType'];
echo "\n<!-- userRole $userRole  -->\n";
//
$refreshToNewTarget ="";
switch ($userRole) {
	case 'participant':
		include("./participant.php");
		break;
	case 'administrator':
		include ("./administrator.php");
		break;
	case 'reviewer':
	include ("./reviewer.php");		
		break;

	default:
		//send to errorpage
		
		include("./error.php");
		break;
} 
 include("./start_page.php");
$beginbigTable = <<<EOQ
 <TABLE>
  <TBODY>
  <TR>
    <TD vAlign=top width=150 bgColor=#ccccff px>
EOQ;

$middleBigTable = <<<EOQ
 </TD>
    <TD vAlign=top width=600 bgColor=#ffffff>
EOQ;

$endBigTable = <<<EOQ
</TD></TR></TBODY></TABLE></BODY></HTML>
EOQ;
 print $js;
 print $beginbigTable;
 print $leftContent; 
 print $middleBigTable; 
 print $mainContent;
 print $centerContent;
 
 print $htmlEndBlurb;
 print $endBigTable;
 include ("./end_page.php");
?>
