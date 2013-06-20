<?php
//CHANGE for each new survey application registration -- need to look up in database
//GLOBALS
//DEBUG STUFF
$GLOBAL_DEBUG_BORDER = 0;
$query_debug = 0;
//Database ISSUES
$GLOBAL_DATABASE = "flagReport";
$GLOBAL_DATABASE_USER_TABLE ="users";
$GLOBAL_DATABASE_USER ="username";
$GLOBAL_DATABASE_PASSWORD ="password";
$GLOBAL_DATABASE_URL = "localhost";
//LOGIN ISSUES
$GLOBAL_DEFAULT_loginPage = "loginPage.php";
$GLOBAL_DEFAULT_usernameField = "username";
$GLOBAL_DEFAULT_passwordField = "password";
$GLOBAL_DEFAULT_pwResetPage ="pwreset.php"; 
//SITE ISSUES
$GLOBAL_DEFAULT_SITE_ADMIN= "general@example.org";
$GLOBAL_DEFAULT_PAGE_TITLE = "Generic Page Title";
$GLOBAL_DEFAULT_PAGE_BANNER_LOCATION = "./images/example.org.jpg";

$GLOBAL_DEFAULT_FOOTER_FILE_LOCATION = "./footer.php";
$GLOBAL_DEFAULT_AUTHENTICATION_METHOD="Standard";
$GLOBAL_DEFAULT_INTROBLURB_LOCATION= "./introBlurb.php";
$GLOBAL_DEFAULT_MYSQL_PASSWORD_HASH_METHOD= "OLD_PASSWORD";
//list here the other methods 
$GLOBAL_DEFAULT_PAGE="newForm.php";
$GLOBAL_NO_FORM = "Unable to determine what resource you are requesting";
?>
