<!DOCTYPE html>
    <head>
        <title>CS 143 Project 1C</title>
        <link rel="stylesheet" type="text/css" href="style.css">
        <link href="css/bootstrap.min.css" type="text/css" rel="stylesheet">
        <link href="http://www.w3schools.com/lib/w3.css" rel="stylesheet">
        <link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css">
        <link href="http://www.w3schools.com/lib/w3.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
        <!-- jQuery library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <!-- Latest compiled JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>


<body>
<nav class="navbar navbar-default">
      <div class="container-fluid">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="index.php">CS 143 Project 1C</a>
                </div>
                <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                    <ul class="nav navbar-nav">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Add New Content <span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="#">Add Actor/Director</a></li>
                                <li><a href="addmovie.php">Add Movie Information</a></li>
                                <li><a href="addMRelation.php">Add Movie/Actor Relation</a></li>
                                <li><a href="addPrelation.php">Add Movie/Director Relation</a></li>
                            </ul>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Browsing<span class="caret"></span></a>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="show_a.php">Show Actor Information</a></li>
                                <li><a href="show_m.php">Show Movie Information</a></li>
                            </ul>
                        </li>
                        <li><a href="search.php">Searching</a></li>
                    </ul>
                </div>
    </div>
 </nav>

  <div class="container">
    <h1> Add a new Actor/Director </h1>
    <div class="well bs-component">
      <form method="post" action="submit.php" class="form-horizontal">
            <center><legend>Please enter the following </legend></center>
            <div class="form-group"><center>
	      	<label class="radio-inline">
              	<input type="radio" name="identity" value="actor" checked>Actor
              	</label>
	      	<label class="radio-inline">
	      	<input type="radio" name="identity" value="director">Director<br>
              	</label></center>
	     </div>
	     <div class="form-group">
		 <label for="firstname" class="col-sm-3 control-label">First Name </label>
		 <div class="col-sm-3">
			<input type="text" class="form-control" name="fname">
		 </div>
		 <label for="lastname" class="col-sm-3 control-label">Last Name</label>
		 <div class="col-sm-3">
			<input type="text" class="form-control" name="lname">
		 </div>
	     </div>
	     <div class="form-group"><center>
		<label class="radio-inline">
		  <input type="radio" name="gender" value="male" checked>Male
		</label>
		<label class="radio-inline">
		  <input type="radio" name="gender" value="female">Female</label>
		</label></center>
	     </div>

	     <div class="form-group">
	     	<label for="dateofbirth" class="col-sm-3 control-label">Date of Birth</label>
		<div class="col-sm-3">
			<input type="text" class="form-control" name="dob">
		</div>
		<label for="dateofdeath" class="col-sm-3 control-label" name="dod">Date of Death</label>
		<div class="col-sm-3">
			<input type="text" class="form-control" name="dod">
		</div>
	     </div>

	     <div class="form-group">
		<div class="col-sm-4 col-sm-offset-5">
		<button class="btn btn-primary" type="submit" value="Submit">Submit</button>
		</div>
	     </div>
	</form>
	</div>
</div>





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
				$dbQuery = "INSERT INTO Actor (id, last, first, sex, dob, dod) VALUES('$newMaxID', '$dbLast', '$dbFirst', '$dbSex', '$dbDOB', NULL)";
			else
				$dbQuery = "INSERT INTO Actor (id, last, first, sex, dob, dod) VALUES('$newMaxID', '$dbLast', '$dbFirst', '$dbSex', '$dbDOB', '$dbDOD')";

	}else if ($db_identity =="director"){
		if($dbDOD=="")
					$dbQuery = "INSERT INTO Director (id, last, first, sex, dob, dod) VALUES('$newMaxID', '$dbLast', '$dbFirst', '$dbSex', '$dbDOB', NULL)";
				else
					$dbQuery = "INSERT INTO Director (id, last, first, sex, dob, dod) VALUES('$newMaxID', '$dbLast', '$dbFirst', '$dbSex', '$dbDOB', '$dbDOD')";

	}

?>











 <footer class="row">
     <br>
     <div class="small-12 columns"><center>COPYRIGHT @ 2016 LOUIS WU & JOHN KIM</center>
    </div>
  </footer>
</div>

</body>
</html>
