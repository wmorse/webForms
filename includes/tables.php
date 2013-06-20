<?php
/*
********************************************************
*** This script from MySQL/PHP Database Applications ***
***         by Jay Greenspan and Brad Bulger         ***
***                                                  ***
***   You are free to resuse the material in this    ***
***   script in any manner you see fit. There is     ***
***   no need to ask for permission or provide       ***
***   credit.                                        ***
********************************************************
*/


// string start_table ([array attributes])


// This function returns an opening HTML <table> tag, inside an
// opening paragraph (<p>) tag. Attributes for the table may be supplied 
// as an array.

function start_table ($atts="")
{
	$attlist = get_attlist($atts);
	$output = <<<EOQ
<p>
<table $attlist>
EOQ;
	return $output;
}

// string end_table (void)

// This function returns a closing <table> tag, followed by a closing
// paragraph (<p>) tag. (Presumably closing the paragraph opened by
// start_table().)

function end_table ()
{
	$output = <<<EOQ
</table>
</p>
EOQ;
	return $output;
}

// string table_cell ([string value [, array attributes]])

// This function returns an HTML table cell (<td>) tag. The first
// argument will be used as the value of the tag. Attributes for the
// <td> tag may be supplied as an array in the second argument.
// By default, the table cell will be aligned left horizontally,
// and to the top vertically.

function table_cell ($value="",$atts="")
{
	$attlist = get_attlist($atts,array("align"=>"left","valign"=>"top"));

	$output = <<<EOQ
  <td $attlist>$value</td>
EOQ;
	return $output;
}

// string table_row ([mixed ...])

// This function returns an HTML table row (<tr>) tag, enclosing a variable
// number of table cell (<td>) tags. If any of the arguments to the function
// is an array, it will be used as attributes for the <tr> tag. All other
// arguments will be used as values for the cells of the row. If an
// argument begins with a <td> tag, the argument is added to the row as is.
// Otherwise it is passed to the table_cell() function and the resulting
// string is added to the row.

function table_row ()
{
	$attlist = "";
	$cellstring = "";

	$cells = func_get_args();
	while (list(,$cell) = each($cells))
	{
		if (is_array($cell))
		{
			$attlist .= get_attlist($cell);
		}
		else
		{
			if (!eregi("<td",$cell))
			{
				$cell = table_cell($cell);
			}
			$cellstring .= "  ".trim($cell)."\n";
		}
	}
	$output = <<<EOQ
 <tr $attlist>
$cellstring
 </tr>
EOQ;
	return $output;
}

?>
