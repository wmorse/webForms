<?php
/********************************************************
*** William Morse Spring 2003                        ***
********************************************************
*** Change Log                                       ***
***                                                  ***
*** Date     ** By  ** Reason                        ***
********************************************************
********************************************************
********************************************************/

//include_once("../functions/tables.php");

// string db_checkbox_field_multiple ([string name [, string table name [, string value field [, string label field [, string sort field [, string match text [, string where clause]]]]]]])
//062103 recoding by WIlliam Morse
// Treats a series of checkl boxes as a single name
// Assumes that the name will be an array e.g. "MyArr[]"
// Modified from to work with the  multiple attribute of a selevt statement.
// This function returns a set of HTML checkbox fields , based
// on the values in the MySQL database table specified by the second argument,
// as returned by the db_values_array() function (defined in 
// /book/functions/db.php).

function db_checkbox_field_multiple ($name="", $table="", $value_field=""
    , $label_field="", $sort_field="", $match="", $where="", $columns
)
{
    $values = db_values_array($table, $value_field, $label_field
        , $sort_field, $where
    );
    $output = checkbox_field_multiple($name, $values, $match, $columns);
    return $output;
}

// string checkbox_field ([string name [, string value [, string label [, string match]]]])

// This function returns an HTML checkbox field. The optional third argument
// will be included immediately after the checkbox field, and the pair
// is included inside a HTML <nobr> tag - meaning that they will be
// displayed together on the same line.  If the value of the
// second or third argument matches that of the fourth argument,
// the checkbox will be 'checked' (i.e., flipped on).


function checkbox_field_multiple ($name="", $array="",  $match="", $columns =1)
{
	//Sanity check for $columns
	if ($columns==0){
		$columns=1;
	}
	$checked="";
	$celloutput="";
$chDEBUG = 0;

    $output = "";
    $rowoutput ="<table border= $chDEBUG >";
    if (is_array($array))
    {
        $numElements = count($array);
        $maxelementsPerColumn = ceil($numElements / $columns) ;
        $currentColumnCount = 0;
        $currentcount = 0;
        $rowoutput ="<table border= $chDEBUG >";

        while (list($avalue,$alabel) = each($array))
        {
            $currentColumnCount = $currentColumnCount +1;
            $currentcount = $currentcount +1;
                  //check if $value is an array
                  if (is_array($match) ){
                     for ($i=0; $i < count($match); $i++ ){
                        $checked  = "";
                        if ($avalue == $match[$i] || $alabel == $match[$i]){
                           $checked = "checked";
                           break;              
                        }
                     } 
                    }
                    else {
                       if ($match != "") {
                         $checked = ($avalue == $match || $alabel == $match ) ? "checked" : "";
                       }
                    }
            $output .= <<<EOQ
<input type="checkbox" name="$name" value="$avalue" $checked> $alabel
EOQ;
            if ($currentColumnCount < $maxelementsPerColumn && $currentcount < $numElements    )
            {
               $output .= "<br>";
            }
            else{ //CurrentColCount == Columns
                 $celloutput = $celloutput. table_cell($output);
                 $output = "";
                 $currentColumnCount = 0;
            }
        }// endwhile loop

        $rowoutput .=  table_row($celloutput);
    }
    $rowoutput .= "</table>";
    return $rowoutput;
}

?>
