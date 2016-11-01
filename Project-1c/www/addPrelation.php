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
<?php
      $db = mysqli_connect("localhost", "cs143", "", "CS143");
      if(!$db){
        echo "<p> Error: Unbale to connect to MySQL. </p>";
        echo "<p> Error Message: " . mysqli_connect_error(). "</p>";
      }
      $MovieQuery = mysqli_query("SELECT id, title, year FROM Movie ORDER BY title ASC");
      if(!$MovieQuery){
        echo "<p> Something goes wrong </p>";
      }
      $movie_options="";
      while ($row=mysqli_fetch_array($MovieQuery)){
        $mvid = $row["id"];
        $title = $row["title"];
        $year = $row["year"];
        $movie_options .= "<option value=\"mvid\"> " . $title . "(" . $year . ")</option>";
      }

      $director_options="";
      $DirectorQuery = mysqli_query($db, "SELECT id, first, last, dob FROM Director ORDER BY first ASC")
      while ($row=mysqli_fetch_array($MovieQuery)){
        $did = $row["id"];
        $first = $row["first"];
        $last = $row["last"];
        $dob = $row["dob"];
        $director_options .= "<option value=\"did\"> " . $first . " ". $last + "(" . $dob . ")</option>";
      }
 ?>
<div class="container">
<h1>Create Movie/Director Relation </h1>
	<div class="well necomponent">
	<form method='post' action='do_search.php' class='form-horizontal'>
	   <center><legend>You could add movies/directors relation here</legend></center>

	   <div class="form-group">
		<label for='title' class="col-sm-4 control-label">Movie Title: </label>
		<div class="col-sm-5 col-offset-sm-2">
			<select class="form-control" name="title">
        <?=$movie_options?>
			</select>
		</div>
	   </div>
	   <div class="form-group">
	   <label for='actor' class="col-sm-4 control-label">Director: </label>
		<div class="col-sm-5 col-offset-sm-2">
			<select class="form-control" name="actor">
          <?=$director_options?>
			</select>
		</div>
	   </div>


     <div class="form-group">
	   <label for="searching" class="col-sm-4 control-label">Role: </label>
	   <div class="col-sm-5 col-offset-sm-2">
	       <input type="text" class="form-control" name="searching">
	   </div>
    	   </div>
	   <div class="form-group">
		<div class="col-sm-4 col-sm-offset-5">
		   <button class="btn btn-primary" type="submit" value="Submit">Click Me!</button>
		</div>
	   </div>
	</form>

  <?php
    //make query here
    $selected_movie = $_POST("title");
    $selected_director = $_POST("actor");
    //so something here

   ?>



	</div>
</div>



</div>

</body>
</html>
