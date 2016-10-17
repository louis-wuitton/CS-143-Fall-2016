<!DOCTYPE html>
<html>
<head>
<?php $title="CS143 Project 1B"; ?>
<title><?php print "$title"; ?></title>
</head>

<body>
<p>Developed by Tianyan Wu and John Kim</p>
<p>Please type the query in the following input field</p>


<form action="query.php" method="GET">
       <textarea name="query" cols="60" rows="8"></textarea><br>
       <input type="submit" value="Submit">
</form>



<p><small>Note: tables and fields are case sensitive. All tables in Project 1B are availale.</small>
</p>


<?php 
	
	$input = $_GET["query"];
	
	$db = mysqli_connect('localhost', 'cs143', '', 'CS143');
	if($mysqli->connect_errno){
		die('Unable to connect to database [' . $db->connect_error . ']');
	}

	$rs = mysqli_query($db , $input);	

?>



<h3>Results from MySQL:</h3>

<table border="1" cellspacing="1" cellpadding="2">
<?php


	if($rs){
		while($fieldinfo=mysqli_fetch_field($rs)){
			echo '<td><b>' . $fieldinfo->name . '</b></td>';
		}
		
		echo '<tr>';
		
		$num_of_fields = mysqli_num_fields($rs);

		while($row = mysqli_fetch_row($rs)){
			for($y=0; $y<$num_of_fields;$y++){
				if($row[$y] == NULL){
					echo '<td> N/A </td>';
				}else{
					echo '<td>' . $row[$y] . '</td>';
				}
			}
			
		}
		echo '</tr></table>';
		mysqli_free_result($rs);	
	}
	mysqli_close($db);

?>


</body>
</html>
