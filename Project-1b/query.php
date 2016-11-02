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



<p><small>Note: tables and fields are case sensitive. All tables in Project 1B are available.</small>
</p>


<?php 
	
	$input = $_GET["query"];
	
	$db = mysqli_connect('localhost', 'cs143', '', 'CS143');
	if(!$db){
		echo "<p> Error: Unable to connect to MySQL. </p>";
		echo "<p>Error Message: ". mysqli_connect_error(). '</p>';
		
	}

	$rs = mysqli_query($db , $input);	

?>



<h3>Results from MySQL:</h3>

<table border="1" cellspacing="1" cellpadding="2">
<?php


	if($rs){
		if(mysqli_num_rows($rs) != 0){
			while($fieldinfo=mysqli_fetch_field($rs)){
				echo '<td><b>' . $fieldinfo->name . '</b></td>';
			}	
			$num_of_fields = mysqli_num_fields($rs);
		
		
			while($row = mysqli_fetch_row($rs)){
				echo '<tr>';
				for($y=0; $y<$num_of_fields;$y++){
					if($row[$y] == NULL){
						echo '<td> N/A </td>';
					}else{
						echo '<td>' . $row[$y] . '</td>';
					}
				}	
				echo '</tr>';
			}
			echo '</table>';
		}else{
			echo "<p> Result is empty </p>";
		}
		mysqli_free_result($rs);	
	}else{
		echo '<p>' . mysqli_error($db) . '</p>';
	}
	mysqli_close($db);

?>


</body>
</html>
