<!DOCTYPE html>
<html>
<head>
<?php $title = "PHP Calculator" ?>
<title><?php print "$title"; ?></title>
</head>

<body>

<h1>PHP Calculator</h1>
<form action="#" method="GET"> 
<input type="text" name="expr" /> 
<input type="submit" value="Calculate" /> 
</form> 

<?php

// about eval...

if($_GET["expr"]) { 
	$equ = $_GET["expr"];

	$divide_by_zero = preg_match("/\/[\s]*[0]/", $equ); // v
	$basic_pattern = preg_match("/^[0-9 \.\+\-\/\*]+$/", $equ); // v
	$begin_zero = preg_match("/^[0]+[0-9]+/", $equ); 
	$zero_num = preg_match("/[ \+\-\/\*]+0[0-9]+/", $equ); // inv
	$inv_ops = preg_match("/[\+\-\/\*][ ]*[\+\/\*]/", $equ);
	$two_dots = preg_match("/\.[0-9]+\./" , $equ);
	$negspace = preg_match("/[\+\-\*\/][ ]*\-[ ]+[0-9]/",$equ);
	$more_than_two_ops = preg_match("/[\+\-\*\/][ ]*[\+\-\*\/][ ]*[\+\-\*\/]/", $equ);
	$bad_float_1 = preg_match("/[\+\-\*\/ ]\./", $equ);
	$bad_floar_2 = preg_match("/\.[\+\-\*\/ ]/", $equ);
	$bad_float_3 = preg_match("/^\./", $equ);	

	/*
	echo "Division by zero: ".$divide_by_zero."<br \>"; // valid
	echo "Has basic pattern: ".$basic_pattern."<br \>"; // valid
	echo "Begins with zero: ".$begin_zero."<br />";
	echo "Zero then number: ".$zero_num."<br \>";
	echo "Invalid operators: ".$inv_ops."<br \>";
	echo "Invalid floats: ".$two_dots."<br \>";	
	echo "Space in negation: ".$negspace."<br \>";
	echo "More than two consecutive operators: ".$more_than_two_ops."<br \>";

	echo "<br />";
	*/

	
	
	


	if($basic_pattern){
		if($zero_num || $begin_zero){
			echo "Invalid Input: A non-zero number cannot start with zero";
		}else{
			if($divide_by_zero){
				echo "Invalid Input: A number that's not zero cannot start with a zero";
			}
			else if ($inv_ops || $two_dots || $negspace || $more_than_two_ops){
				echo "Invalid Input: Either invalid arrangement of operators or invalid floating points";
			}
			else if($bad_float_1 || $bad_float_2 || $bad_float_3){
				echo "Invalid Input: All floating point dots have to either precede or be followed by a digit";
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
		echo "Invalid Input: Input must contain only 0-9, +, -, *, / and . ";
	}

} 
?>

</body>
</html>
