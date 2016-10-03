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
	$inv_ops = preg_match("/\-[ ]*[\+\/\*]+/", $equ);

	echo "Division by zero: ".$divide_by_zero."<br \>"; // valid
	echo "Has basic pattern: ".$basic_pattern."<br \>"; // valid
	echo "Begins with zero: ".$begin_zero."<br />";
	echo "Zero then number: ".$zero_num."<br \>";
	echo "Invalid operators: ".$inv_ops."<br \>";
	
	echo "<br />";
	eval("\$ans = $equ ;");

	echo "<h2>Result</h2>";
	//echo "Expression ".$_GET["expr"]." has been received.<br />"; 
	echo $_GET["expr"]." = ".$ans;


} 
?>

</body>
</html>
