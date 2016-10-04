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

	// The equation itself
	$equ = $_GET["expr"];

	// Match the pattern
	$basic_pattern = preg_match("/^[0-9 \.\+\-\/\*]+$/", $equ);

	// Basic stuff
	$begin_zero = preg_match("/^[0]+[0-9]+/", $equ); 
	$divide_by_zero = preg_match("/\/[\s]*[0]/", $equ);
	$zero_num = preg_match("/[ \+\-\/\*]+0[0-9]+/", $equ);
	$inv_ops = preg_match("/[\+\-\/\*][ ]*[\+\/\*]/", $equ);
	$negspace = preg_match("/[\+\-\*\/][ ]*\-[ ]+[0-9]/",$equ);
	$more_than_two_ops = preg_match("/[\+\-\*\/][ ]*[\+\-\*\/][ ]*[\+\-\*\/]/", $equ);

	// Dot issues:
	$start_dot = preg_match("/^\.[ \+\-\*\/]*/", $equ);
	$end_dot = preg_match("/[ \+\-\*\/]*\.$/", $equ);
	$left_dot = preg_match("/\.[ \+\-\*\/]+/", $equ);
	$right_dot = preg_match("/[ \+\-\*\/]+\./", $equ);
	$two_dots = preg_match("/[0-9]+\.[ 0-9]*\.[ 0-9]*/" , $equ);

/*
	// Make sure your regex works properly
	echo "Has basic pattern: ".$basic_pattern."<br \>"; // valid
	echo "Division by zero: ".$divide_by_zero."<br \>";
	echo "Begins with zero: ".$begin_zero."<br />";
	echo "Zero then number: ".$zero_num."<br \>";
	echo "Invalid operators: ".$inv_ops."<br \>"; 
	echo "Space in negation: ".$negspace."<br \>";
	echo "2 consec operators: ".$more_than_two_ops."<br \>";
	echo "Start dot: ".$start_dot."<br \>";
	echo "End dot: ".$end_dot."<br \>";
	echo "Left dot: ".$left_dot."<br \>";
	echo "Right dot: ".$right_dot."<br \>";
	echo "Two+ dots: ".$two_dots ."<br \>";
*/
	echo "<br />";

	echo "<h2>Result</h2>";
	if($basic_pattern){
		if($divide_by_zero){
			echo "Division by zero error!";
		}else{
			
			if ($start_dot || $end_dot || $left_dot || $right_dot ||
				$inv_ops || $more_than_two_ops || $zero_num || 
				$negspace || $begin_zero || $two_dots){
				echo "Invalid Expression!";
			}
			else{
				$equ = str_replace("--","- -", $equ);
				eval("\$ans=$equ ;");
				echo $_GET["expr"]." = ".$ans;
			}
		}
	}
	else{
		echo "Invalid Expression!";
	}
} 
?>

</body>
</html>
