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
                                <li><a href="addActorMovie.php">Add Movie/Actor Relation</a></li>
                                <li><a href="addDirectorMovie.php">Add Movie/Director Relation</a></li>
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
<h1>Create Movie/Actor Relation </h1>
	<div class="well necomponent">
	<form method='post' action='#' class='form-horizontal'>
	   <center><legend>You could add movies/actors relation here</legend></center>
	   <div class="form-group">
		     <label for='title' class="col-sm-4 control-label">Movie Title: </label>
		     <div class="col-sm-5 col-offset-sm-2">
           <?php
              $db = mysqli_connect("localhost", "cs143", "", "CS143");
              if(!$db){
                echo "<p> Error: Unbale to connect to MySQL. </p>";
                echo "<p> Error Message: " . mysqli_connect_error(). "</p>";
              }
              $MovieQuery = "SELECT id, title, year FROM Movie ORDER BY title ASC";

              $movieResults = mysqli_query($db, $MovieQuery);
              //$movie_options = "";
              echo "<select class=\"form-control\" name=\"movie\">";
              if(!$movieResults){
                echo '<p>'.mysqli_error($db).'</p>';
              }else{
                if(mysqli_num_rows($movieResults) == 0){
                  //echo "<option value=\"22\"> abc </option>";
                }else{
                    while ($movierow=mysqli_fetch_array($movieResults)){
                        $mvid = $movierow["id"];
                        $title = $movierow["title"];
                        $year = $movierow["year"];
                        echo "<option value=\"$mvid\"> " . $title . " (" . $year . ")</option>";
                      }
                }
                echo "</select>";
              }
            ?>
		      </div>
	   </div>

	   <div class="form-group">
	       <label for='actor' class="col-sm-4 control-label">Actor: </label>
		     <div class="col-sm-5 col-offset-sm-2">
                <?php
                  $ActorQuery = "SELECT id, first, last, dob FROM Actor ORDER BY first ASC";
                  $actorResults = mysqli_query($db, $ActorQuery);
                  //$movie_options = "";
                  echo "<select class=\"form-control\" name=\"actor\">";
                  if(!$actorResults){
                    echo '<p>'.mysqli_error($db).'</p>';
                  }else{
                    if(mysqli_num_rows($actorResults) == 0){
                      //echo "<option value=\"22\"> abc </option>";
                    }else{
                        while ($actorrow=mysqli_fetch_array($actorResults)){
                            $aid = $actorrow["id"];
                            $first = $actorrow["first"];
                            $last = $actorrow["last"];
                            $dob = $actorrow["dob"];
                            echo "<option value=\"$aid\"> " . $first ." ". $last . " (" . $dob . ")</option>";
                        }
                    }
                     echo "</select>";
                 }
                ?>
			        </select>
		    </div>
	  </div>

    <div class="form-group">
	       <label for="searching" class="col-sm-4 control-label">Role: </label>
	       <div class="col-sm-5 col-offset-sm-2">
	          <input type="text" class="form-control" name="role">
	       </div>
    </div>

	  <div class="form-group">
		    <div class="col-sm-4 col-sm-offset-5">
		        <button class="btn btn-primary" type="submit" value="Submit">Click Me!</button>
		   </div>
	 </div>
	</form>
    <?php
        $selected_actor = $_POST["actor"];
        $selected_movie = $_POST["movie"];
        $new_role = $_POST["role"];

        if($selected_movie == "" && $selected_actor == "" && $new_role= ""){

        }else if ($new_role == ""){
          echo "<p> Please specify a role </p>";
        }else{
          $selected_actor = mysqli_real_escape_string($db, $selected_actor);
          $selected_movie = mysqli_real_escape_string($db, $selected_movie);
          $new_role = mysqli_real_escape_string($db, $new_role);
          $addRole = "INSERT INTO MovieActor (mid, aid, role) VALUES ('$selected_movie', '$selected_actor', '$new_role')";
          $insertResult = mysqli_query($db, $addRole);
          if(!$insertResult){
            echo '<p>'.mysqli_error($db).'</p>';
          }else{
              echo "Updated actor role to movie successfully";
              mysqli_free_result($insertResult);
              mysqli_free_result($movieResults);
              mysqli_free_result($actorResults);
          }
          mysqli_close($db);
        }


    ?>



	</div>
</div>


</body>
</html>
