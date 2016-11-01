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
      <form method="post" action="addperson.php" class="form-horizontal">
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
		  <input type="radio" name="gender" value="Male" checked>Male
		</label>
		<label class="radio-inline">
		  <input type="radio" name="gender" value="Female">Female</label>
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

  <?php
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

    //Now we will consider all the possible restrictions
    if($db_fname == "" && $db_lname == "" && $db_dod == "" && $db_dob == ""){

    }else if ($db_fname == "" || $db_lname == ""){
      echo "<p> Please provde first name and last name </p>";
    }
    else if ($db_dob != ""){
        if($db_dod != "" && strtotime($db_dod) < strtotime($db_dob)){
          echo "<p> The date of death date cannot be smaller than date of birth date </p>";
        }else{
  	       $MaxID = mysqli_query($db, "SELECT MAX(id) FROM MaxPersonID");
  	       if(!$MaxID){
  		         echo '<p>' . mysqli_error($db) . '</p>';
  	       }

  	        //Update our max id
           $Max_Row = mysqli_fetch_array($MaxID, MYSQLI_NUM);
  	       $new_MaxID = $Max_Row[0] + 1;
           $db_fname = mysqli_real_escape_string($db, $db_fname);
           $db_lname = mysqli_real_escape_string($db, $db_lname);

  	       if($db_identity == "actor"){
  		         if($db_dod=="")
  				         $dbQuery = "INSERT INTO Actor (id, last, first, sex, dob, dod) VALUES ('$new_MaxID', '$db_lname', '$db_fname', '$db_gender', '$db_dob', NULL)";
  			       else
  				         $dbQuery = "INSERT INTO Actor (id, last, first, sex, dob, dod) VALUES ('$new_MaxID', '$db_lname', '$db_fname', '$db_gender', '$db_dob', '$db_dod')";

      	   }else if ($db_identity =="director"){
  		         if($db_dod=="")
  					       $dbQuery = "INSERT INTO Director (id, last, first, sex, dob, dod) VALUES ('$new_MaxID', '$db_lname', '$db_fname', '$db_gender', '$db_dob', NULL)";
  				     else
  					       $dbQuery = "INSERT INTO Director (id, last, first, sex, dob, dod) VALUES ('$new_MaxID', '$db_lname', '$db_fname', '$db_gender', '$db_dob', '$db_dod')";
  	       }

           $queryResult = mysqli_query($db, $dbQuery);

           if(!$queryResult){
             echo '<p>' . mysqli_error($db) . '</p>';
           }
           else{
             //update the max actor table
             mysqli_free_result($queryResult);
             $update_Query = "UPDATE MaxPersonID SET id=$new_MaxID WHERE id=$Max_Row[0]";
             $queryResult = mysqli_query($db, $update_Query);
             if(!$queryResult){
               echo '<p>' . mysqli_error($db) . '</p>';
             }else{
                  echo '<p> Successfully added a new a new ' . $db_identity . ' with an ID ' . $newMaxID;
                  mysqli_free_result($queryResult);
             }
          }
        }
       }
       mysqli_close($db);
       //get the query results and analyze it
       ?>
	</div>
</div>






 <footer class="row">
     <br>
     <div class="small-12 columns"><center>COPYRIGHT @ 2016 LOUIS WU & JOHN KIM</center>
    </div>
  </footer>
</div>

</body>
</html>
