<!DOCTYPE html>
<html>
<head>
<?php $title = "PHP Calculator" ?>
<title><?php print "$title"; ?></title>
</head>
<body>
<h1>PHP Calculator</h1>
(10/05/2016 Develoiped by John Kim and Tianyan Wu)

<form action="#" method="GET"> 
<input type="text" name="expr" /> 
<input type="submit" value="Calculate" /> 
</form> 

<ul>
    <li>Only numbers and +,-,* and / operators are allowed in the expression.
    </li><li>The evaluation follows the standard operator precedence.
    </li><li>The calculator does not support parentheses.
    </li><li>The calculator handles invalid input "gracefully". It does not output PHP error messages.
</li></ul>

Here are some(but not limit to) reasonable test cases:
<ol>
  <li> A basic arithmetic operation:  3+4*5=23 </li>
  <li> An expression with floating point or negative sign : -3.2+2*4-1/3 = 4.46666666667, 3*-2.1*2 = -12.6 </li>
  <li> Some typos inside operation (e.g. alphabetic letter): Invalid input expression 2d4+1 </li>
</ol>



<?php

// about eval...

    if($_GET["expr"]) { 
	// The equation itself
	$equ = $_GET["expr"];

	// Match the pattern
	$basic_pattern = preg_match("/^[0-9 \.\+\-\/\*]+$/", $equ);
	// Basic stuff
	$has_number = preg_match("/^[0-9]+$/", $equ);
	$begin_zero = preg_match("/^[0]+[0-9]+/", $equ); 
	$divide_by_zero = preg_match("/\/[\s]*[0]/", $equ);
	$zero_num = preg_match("/[ \+\-\/\*]+0[0-9]+/", $equ);
	$inv_ops = preg_match("/[\+\-\/\*][ ]*[\+\/\*]/", $equ);
	$two_dots = preg_match("/\.[0-9]+\./" , $equ);
	
	$op_begin = preg_match("/^[\+\*\/]/", $equ);
	$op_end = preg_match("/[\+\-\*\/]$/", $equ);
	$minus_begin = preg_match("/\- /", $equ);

	$negspace = preg_match("/[\+\-\*\/][ ]*\-[ ]+[0-9]/",$equ);
	$more_than_two_ops = preg_match("/[\+\-\*\/][ ]*[\+\-\*\/][ ]*[\+\-\*\/]/", $equ);
	$bad_float_1 = preg_match("/[\+\-\*\/ ]\./", $equ);
	$bad_float_2 = preg_match("/\.[\+\-\*\/ ]/", $equ);
	$bad_float_3 = preg_match("/^\./", $equ);
	$bad_float_4 = preg_match("/\.$/", $equ);	
	$space_between_no = preg_match("/[0-9][ ]+[0-9]/", $equ);


	echo "<br />";

	if($basic_pattern || $has_number){
		if($zero_num || $begin_zero){
			echo "Invalid Input: A non-zero number cannot start with zero";
		}else{
			if($divide_by_zero){
				echo "Invalid Input: You cannot divide a number by zero";
			}
			else if ($inv_ops || $two_dots || $negspace || $more_than_two_ops || $space_between_no){
				echo "Invalid Input: Either invalid arrangement of operators or invalid floating points or there is space at invalid position";
			}
			else if($bad_float_1 || $bad_float_2 || $bad_float_3 || $bad_float_4){
				echo "Invalid Input: All floating point dots have to either precede or be followed by a digit";
			}
			else if($op_begin || $op_end || $minus_begin){
				echo "Invalid Input: operators cannot just appear at the begin or end";
			}		
			else{
				$equ = str_replace("--","- -", $equ);
				eval("\$ans=$equ ;");
				echo "<h2>Result</h2>";
				echo $_GET["expr"]," = ".$ans;
			}
		}
	}			
	else{
	echo "Invalid Input: Input must only contain numbers, and operators like+, -, *, / and dot . ";
	}

	}
 ?>

</body>
</html>
