<?php
include('header.php');
include('start_page.php');


$queryString = $_SERVER['QUERY_STRING'];
$queryString = empty($queryString )? "":$queryString;


$formPage='';
if(isset($GLOBAL_DEFAULT_PAGE)){
	$formPage= $GLOBAL_DEFAULT_PAGE;
}
$actionPage = "./$formPage?$queryString";
if(!empty($GLOBAL_StartingPoint)){
$actionPage = "$GLOBAL_StartingPoint?$queryString";
}
//REFERER
//TODO remove total rediret hack
$clean = array();
$clean["isAdmin"]=0;
if (isset($_GET["admin"])){
	$clean["isAdmin"]=(is_numeric($_GET["admin"])) ?$_GET["admin"]  : 0 ;
}

if($clean["isAdmin"]){
$actionPage = "./main.php?$queryString";

}

//TODO make this configurable in data
$acctCreateOptionHTML = "\t<TR>\n".  
                        "\t\t<TD colspan=2>".
                        "<a href=\"./accntCREATE.php?$queryString\">Create a user account</a>".
                        "\t\t</TD>\n".
                        "\t</TR>\n";

$hiddenElements = "";
if(isset($_GET['ftu'])){
	$ftu = urlencode($_GET['ftu']);
	$hiddenElements .= "<INPUT type=hidden name=ftu value=\"$ftu\" >";
}
$htmlOutput ="";
 //
 $submitLabel = "Login";
 
 $htmlOutput .= "<DIV  style='text-align:center'><h4> $page_title</h4></DIV>";
 $htmlOutput .= "<DIV class=loginForm>";
 $htmlOutput.= "<form method=\"post\" action=\"$actionPage\">";
 $htmlOutput.= "<TABLE>\n";
 $htmlOutput.= "\t<TR>\n";
 $htmlOutput.= "\t\t<TD>";
 $htmlOutput .= "User:&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp\n";
 $htmlOutput .= "\t\t</TD>\n";
 $htmlOutput.= "\t\t<TD>";
 $htmlOutput .= "<input name=\"username\" type=text size=22 maxlength=50>\n";
 $htmlOutput .= "\t\t</TD>\n";
 $htmlOutput.= "\t</TR>\n";
 $htmlOutput.= "\t<TR>\n";
 $htmlOutput.= "\t\t<TD>";
 $htmlOutput .= "Password:\n";
 $htmlOutput .= "\t\t</TD>\n";
 $htmlOutput.= "\t\t<TD>";
 $htmlOutput .= "<input name=\"password\" type=password size=12 maxlength=50>\n";
 $htmlOutput .= "\t\t</TD>\n";
 $htmlOutput.= "\t</TR>\n";
 $htmlOutput.= "\t<TR>\n";
 $htmlOutput.= "\t\t<TD>";
 $htmlOutput .= "\t\t</TD>\n";
 $htmlOutput.= "\t\t<TD>";
 $htmlOutput .= "<input type=\"submit\" value=\"Login\" name=\"submitme\"/>";  
 $htmlOutput .= "\t\t</TD>\n";
 $htmlOutput.= "\t</TR>\n";
 $htmlOutput.= "\t<TR>\n";
 $htmlOutput.= "\t\t<TD colspan=2>";
 $htmlOutput .= "<a href=\"./pwreset.php?$queryString\">Forgot your password?</a>";
 $htmlOutput .= "\t\t</TD>\n";
 $htmlOutput.= "\t</TR>\n";
 $htmlOutput.=$acctCreateOptionHTML; 
 $htmlOutput.= "\t</TABLE>\n";
 $htmlOutput.=$hiddenElements;
 $htmlOutput.="</FORM>";
 $htmlOutput .= "</DIV>";
 print $htmlOutput;
 include('end_page.php');
?>
