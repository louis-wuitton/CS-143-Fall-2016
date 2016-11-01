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
<h1>Movie Information Page</h1>
	<div class="well necomponent">
	<form method='post' action='show_m.php' class='form-horizontal'>
	   <center><legend>Please Search Movie Here</legend></center>
	   <div class="form-group">
	   <label for="searching" class="col-sm-4 control-label">Search:</label>
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
      // Connect to the database
      $db = mysqli_connect('localhost', 'cs143', '', 'CS143');
      if(!$db) {
        echo "<p>Error: Unable to connect to MySQL.</p>";
        echo "<p>Error Message: ".mysqli_connect_error().'</p>';
      }

      // Select DB to use
      mysqli_select_db("CS143", $db);

      // NOTE, somehow we will have to bring SEARCH into this.
      // For now, let the user input be ID.
      $db_id = trim($_GET["id"]);
      if($dbID=="")
      {
        echo "Please provide a valid movie ID.";
        echo "<br/>";
      }
      else
      {
        $query = "SELECT title, year, rating, company FROM Movie WHERE id=$dbID";
        $rs = mysqli_query($db, $query);

        // get movie info
        $row=mysqli_fetch_row($rs);

        // Title, year, producer, mpaa rating
        echo "<b>Title:</b> ".$row[0]." (".$row[1].")<br/>";
        if($row[3] != "")
            echo "<b>Producer:</b> ".$row[3]."<br/>";
        else
            echo "<b>Producer:</b> N/A<br/>";
        echo "<b>MPAA Rating:</b> ".$row[2]."<br/>";
        mysqli_free_result($rs);

        // Director(s)... TODO: We need to show their DOB as well!!
        echo "<b>Director(s):</b> ";
        $query2 = "SELECT D.last, D.first FROM MovieDirector MD, Director D WHERE MD.mid=$dbID AND MD.did=D.id";
        $rs2 = mysqli_query($db, $query2);
        $first=true;
        while($row2 = mysqli_fetch_assoc($rs2))
        {
          if(!$first) echo ", ";
          else $first=false;
          echo $row2["first"]." ".$row2["last"];
        }
        if($first) // no directors
        {
          echo "N/A";
        }
        echo "<br/>";
        mysqli_free_result($rs2)

        // Genre
        echo "<b>Genre(s):</b> ";
        $query3 = "SELECT genre from MovieGenre WHERE mid=$dbID";
        $rs3 = mysqli_query($db, $query3);
        $first=true;
        while($row3 = mysqli_fetch_assoc($rs3))
        {
          if(!first) echo ", ";
          else $first=false;
          echo $row3["genre"];
        }
        if($first)
        {
          echo "N/A";
        }
        mysqli_free_result($rs3);
        echo "<br/><br/>";

        // Related Actors Portion
        echo "<hr>";
        echo "<h2>Related Actors</h2>";

        $query4 = "SELECT MA.aid, MA.role, A.last, A. first FROM MovieActor MA, Actor A WHERE MA.mid=$dbID AND MA.aid=A.id";
        $rs4 = mysqli_query($db, $query4);
        while ($row4 = mysqli_fetch_assoc($rs4))
        {	// TODO: We need a different .php file name here
          $link = "<a href=\"showActorInfo.php?id=".$row3["aid"]."\">".$row3["first"]." ".$row3["last"]."</a>";
          echo $link." as ".$row3["role"]."<br/>";
        }
        echo "<br/>";
        mysqli_free_result($rs4);


        // Reviews and Average Rating
        echo "<hr>";
        echo "<h2>User Reviews</h2>";
        echo "<b>Average Rating:</b> ";
        $query5 = "SELECT AVG(rating), COUNT(rating) FROM Review WHERE mid=$dbID";
        $rs5 = mysqli_query($db, $query5);
        $row5 = mysqli_fetch_row($rs5);
        if($row5[0] == "")
        {	// TODO: Will we have a dedicated addMovieComment page?
          echo "No reviews exist!<br/><br/>";
          echo "Be the first to <a href=\"addMovieComment.php?id=".$dbID."\">submit a review</a>!<br/><br/>";
        }
        else
        {
          $avgRating = $row5[0] + 0;
          echo "$avgRating out of 5<br/>";
          echo "Reviewed $row4[1] times. <a href=\"addMovieComment.php?id=".$dbID."\">Submit a review</a><br/><br/>";
        }
        mysqli_free_result($rs5);

        // Show latest reviews first
        $query6 = "SELECT time, name, rating, comment FROM Review WHERE mid=$dbID ORDER BY time DESC";
        $rs6 = mysqli_query($db, $query6);

        // review count
        $count=mysql_num_rows($rs6);

        // show the reviews
        while ($row6 = mysql_fetch_assoc($rs6))
        {
          echo "<b>Review #".$count."</b> written on ".$row4["time"]."<br/>";
          echo "Author: ".$row4["name"].", Rating: ".$row4["rating"]."<br/>";
          echo "Comment: ".$row4["comment"]."<br/>";
          echo "<br/>";
          $count--;
        }
        echo "<br/>";
        mysqli_free_result($rs6);
      }
      // close db connection
      mysqli_close($db);
?>

	</div>
</div>



</div>

</body>
</html>
