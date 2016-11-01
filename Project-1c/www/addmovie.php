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


<body data-spy="scroll" data-target=".navbar" data-offset="50">

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
                                <li><a href="addperson.php">Add Actor/Director</a></li>
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
	<h1> Add new Movie</h1>
	<div class="well bs-component">
	    <form method='post' action='addmovie.php' class="form-horizontal">
	  	<center><legend>Please enter the following</legend></center>
		<div class="form-group">
			<label for="title" class="col-sm-4 control-label">Title</label>
			<div class="col-sm-5 col-offset-sm-2">
				<input type="text" class="form-control" name="title">
			</div>
		</div>
		<div class="form-group">
			<label for="company" class="col-sm-4 control-label">Company</label>
			<div class="col-sm-5 col-offset-sm-2">
				<input type="text" class="form-control" name="company">
			</div>
		</div>
			<div class="form-group">
			<label for="year" class="col-sm-4 control-label">Year</label>
			<div class="col-sm-5 col-offset-sm-2">
				<input type="text" class="form-control" name="year">
			</div>
		</div>
	        <div class="form-group">
			<label for="title" class="col-sm-4 control-label">MPAA Rating</label>
			<div class="col-sm-5 col-offset-sm-2">
				<select class="form-control" name="rating">
					<option value="G">G</option>
					<option value="NC-17">NC-17</option>
					<option value="PG">PG</option>
					<option value="PG-13">PG-13</option>
 					<option value="R">R</option>
					<option value="surrendere">surrendere</option>
				</select>
			</div>
		</div>
		<div class="form-group"><center>
		<label>Genre: </label>
		<input type="checkbox" name="genre[]" value="Action">Action
		<input type="checkbox" name="genre[]" value="Adult">Adult
		<input type="checkbox" name="genre[]" value="Adventure">Adventure
		<input type="checkbox" name="genre[]" value="Animation">Animation
		<input type="checkbox" name="genre[]" value="Comedy">Comedy
		<input type="checkbox" name="genre[]" value="Crime">Crime
		<input type="checkbox" name="genre[]" value="Documentary">Documentary
		<input type="checkbox" name="genre[]" value="Drama">Drama
		<input type="checkbox" name="genre[]" value="Family">Family
		<input type="checkbox" name="genre[]" value="Fantasy">Fantasy
		<input type="checkbox" name="genre[]" value="Horror">Horror
		<input type="checkbox" name="genre[]" value="Musical">Musical
		<input type="checkbox" name="genre[]" value="Mystery">Mystery
		<input type="checkbox" name="genre[]" value="Romanance">Romance
		<input type="checkbox" name="genre[]" value="Sci-Fi">Sci-Fi
		<input type="checkbox" name="genre[]" value="Short">Short
		<input type="checkbox" name="genre[]" value="Thriller">Thriller
		<input type="checkbox" name="genre[]" value="War">War
		<input type="checkbox" name="genre[]" value="Western">Western

		</center></div>
		<div class="form-group">
			<div class="col-sm-4 col-sm-offset-5">
				<button type="submit" class="btn btn-primary">Submit</button>
			</div>
		</div>
   	 </form>

  <?php
	   $db = mysqli_connect('localhost', 'cs143', '', 'CS143');
	   if(!$db) {
		     echo "<p>Error: Unable to connect to MySQL.</p>";
		     echo "<p>Error Message: ".mysqli_connect_error().'</p>';
	   }

	   // Get user inputs
	   $db_title = trim($_POST["title"]);
	   $db_company = trim($_POST["company"]);
	   $db_year = $_POST["year"];
	   $db_rating = $_POST["rating"];
	   $db_genre = $_POST["genre"];

	   if ($db_title=="" && $db_company=="" && $db_year=="") {
		      // do nothing
	   }else if ($db_title==""){
 		    echo "Title can't be empty.<br />";
     }else if  ($db_company==""){
 		    echo "Company can't be empty.<br />";
     }else if ($db_year=="" || $db_year<=1895 || $db_year>=2050){
		    echo "Please enter a valid production year.<br />";
	   }else {
       // Get new max ID
        $maxID_rs = mysqli_query($db, "SELECT MAX(id) FROM MaxMovieID");
        if (!$maxID_rs) {
            echo '<p>'.mysqli_error($db).'</p>';
        }
        $max_row = mysqli_fetch_array($maxID_rs, MYSQLI_NUM);
        $new_MaxID = $max_row[0] + 1;

        $db_title = mysqli_real_escape_string($db, $db_title);
        echo "Look at the new string " . $db_title . "\n";

        $query = "INSERT INTO Movie (id, title, year, rating, company) VALUES('$new_MaxID', '$db_title', '$db_year', '$db_rating', '$db_company')";
        $rs = mysqli_query($db, $query);
        if(!$rs){
           echo '<p>' . mysqli_error($db) . '</p>';
        }else{
           mysqli_free_result($rs);
           $updateQuery = "UPDATE MaxMovieID SET id=$new_MaxID WHERE id=$max_row[0]";
           $rs = mysqli_query($db, $updateQuery);
           if(!$rs){
              echo '<p>' . mysqli_error($db) . '</p>';
           }else{
             for($i=0; $i < count($db_genre); $i++)
             {
                  $genreQ = "INSERT INTO MovieGenre (mid, genre) VALUES ('$new_MaxID', '$db_genre[$i]')";
                  $genreRS = mysqli_query($db, $genreQ);
                  if(!$genreRS){
                    echo '<p>'.mysqli_error($db).'</p>';
                  }
             }
             // SUCCESS!
             mysqli_free_result($genreRS);
             echo "New movie (ID: $new_MaxID) added!";
           }
        }
	  }
?>

	</div>
</div>

 <footer class="row">
     <br>
     <div class="small-12 columns"><center>COPYRIGHT © 2016 LOUIS WU & JOHN KIM</center>
    </div>
  </footer>
</div>

</body>
</html>
