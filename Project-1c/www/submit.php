<?php
	echo "<h1> Hello </h1>";
	$db = mysqli_connect('localhost', 'cs143', '', 'CS143');
	if(!$db){
		echo "<p> Error: Unbale to connect to MySQL. </p>";
		echo "<p> Error Message: " . mysqli_connect_error(). "</p>";
	}

	// get values by id
	$db_identity = trim($_POST["identity"]);
	$db_fname = trim($_POST["fname"]);
	$db_lname = trim($_POST["lname"]);
	$db_gender = trim($_POST["gender"]);
	$db_dob = trim($_POST["dob"]);
	$db_dod = trim($_POST["dod"]);

	// Do Add Constraints checking 
	$DobDate = date_parse($db_dob);
	$DodDate = date_parse($db_dod);
	


	// Query to maxperson id 
	$MaxID = mysqli_query($db, "SELECT MAX(id) FROM MaxPersonID");
	if(!$MaxID){
		echo '<p>' . mysqli_error($db) . '</p>';
	}
	
	//Update our max id
	$Max_Row = mysqli_fetch_array($MaxID, MYSQLI_NUM);
	$new_MaxID = $Max_Row[0] + 1;	
		
	

	//When everything is correct

	//The name might has single quote. e.g. Shaq O'Neal
	$db_fname = mysqli_escape_string($db_fname);
	$db_lname = mysqli_escape_string($db_lname);
	
	if($db_identity == "actor"){
		if($dbDOD=="")
			$db_query = "INSERT INTO Actor (id, last, first, sex, dob, dod) VALUES ('$new_MaxID', '$db_lname', '$db_fname', '$db_gender', '$db_dob')"	

	}else if ($db_identity =="director"){

	}
	
?>
