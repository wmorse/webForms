<?php
if (empty($page_footer)) { 
include("$GLOBAL_DEFAULT_FOOTER_FILE_LOCATION");
}
$htmlOutput = <<<EOQ
<BR>
$page_footer	
      </BODY>
  </HTML>
EOQ;
print $htmlOutput;  
?>
