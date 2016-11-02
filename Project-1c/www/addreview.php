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


<?php
  $db = mysqli_connect('localhost', 'cs143', '', 'CS143');
  if(!$db){
    echo "<p> Error: Unbale to connect to MySQL. </p>";
    echo "<p> Error Message: " . mysqli_connect_error(). "</p>";
  }
  $movie_id = $_GET["id"];
 ?>


<div class="container">
	<div class="well necomponent">
	<form method='get' action='addreview.php' class='form-horizontal'>
	   <center><legend>Add new comment here</legend></center>
	   <div class="form-group">
	   <label for="searching" class="col-sm-4 control-label">Movie Title</label>
     <div class="col-sm-5 col-offset-sm-2">
     <?php
          $MovieQuery = "SELECT id, title, year FROM Movie WHERE id=$movie_id";
          $movieinfo = mysqli_query($db, $MovieQuery);
          if(!$movieinfo){
              echo '<p>'.mysqli_error($db).'</p>';
          }
          echo "<select class=\"form-control\" name=\"id\">";
          if(mysqli_num_rows($movieinfo) == 0){
            echo "<p>There is no movie matching this id</p>";
          }else{
            while($movierow = mysqli_fetch_assoc($movieinfo)){
              echo "<option value=\"" . $movierow["id"] . "\">" . $movierow["title"] . " (" . $movierow["year"] . ") </option>";
            }
          }
          echo "</select>";
      ?>
    	</div>
    </div>

      <br>
      <div class="form-group">
 	        <label for="naming" class="col-sm-4 control-label">Your Name: </label>
 	        <div class="col-sm-5 col-offset-sm-2">
 	          <input type="text" class="form-control" name="name">
 	        </div>
      </div>

      <div class="form-group">
          <label for="title" class="col-sm-4 control-label">Rating</label>
          <div class="col-sm-5 col-offset-sm-2">
              <select class="form-control" name="rating">
                  <option value="1">1</option>
                  <option value="2">2</option>
                  <option value="3">3</option>
                  <option value="4">4</option>
                  <option value="5">5</option>
              </select>
          </div>
      </div>


      <div class="form-group">
          <div class="col-lg-5 col-lg-offset-4">
 	          <textarea class="form-control" name="comment" rows="5" placeholder="no more than 500 characters"></textarea>
          </div>
      </div>

      <div class="form-group">
 		     <div class="col-sm-4 col-sm-offset-5">
 		         <button class="btn btn-primary" type="submit" value="Submit">Rating it!</button>
 		     </div>
 	   </div>

	</form>

  <?php
      $name = trim($_GET["name"]);
      $movie_id = $_GET["id"];
      $rating = $_GET["rating"];
      $comment = trim($_GET["comment"]);
      if($name == ""){
        $name = "Mr.Anonymous";
      }
      if($name == "" && $movie_id == "" && $rating == "" && $comment == ""){

      }else if ($rating > 5 || $rating < 1 || $rating == ""){
          echo "<p>Please provive a valid rating</p>";
      }else{

        $name = mysqli_real_escape_string($db, $name);
        $comment = mysqli_real_escape_string($db, $comment);
        $ReviewQuery = "INSERT INTO Review (name, time, mid, rating, comment) VALUES ('$name', now(), '$movie_id', '$rating', '$comment')";
        $result = mysqli_query($db, $ReviewQuery);
        if(!$result){
            echo "something goes wrong";
            echo '<p>'.mysqli_error($db).'</p>';
        }else{
            echo "<p>Movie Review added successfully </p>";
            echo "<p>You can go back to <a href=\"show_m.php?id=".$movie_id."\">Movie Info</a>";
        }
      }


   ?>

</div>
</div>

</body>
</html>
