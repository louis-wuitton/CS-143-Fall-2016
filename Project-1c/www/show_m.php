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
<h1>Movie Information Page</h1>
<div class="jumbotron">
  <?php
      // Connect to the database
      $db = mysqli_connect('localhost', 'cs143', '', 'CS143');
      if(!$db) {
        echo "<p>Error: Unable to connect to MySQL.</p>";
        echo "<p>Error Message: ".mysqli_connect_error().'</p>';
      }

      $db_id = trim($_GET["id"]);
      if($db_id != "")
      {
        $query = "SELECT title, year, rating, company FROM Movie WHERE id=$db_id";

        $rs = mysqli_query($db, $query);
        if(!$rs){
            echo '<p>'.mysqli_error($db).'</p>';
        }else{
          $row=mysqli_fetch_row($rs);
        }
        echo "<h3>Movie Information is </h3>";

        echo "<b>Title:</b> ".$row[0]." (".$row[1].")<br/>";
        if($row[3] != "")
            echo "<b>Producer:</b> ".$row[3]."<br/>";
        else
            echo "<b>Producer:</b> N/A<br/>";
        echo "<b>MPAA Rating:</b> ".$row[2]."<br/>";
        mysqli_free_result($rs);

        // Director(s)... TODO: We need to show their DOB as well!!
        echo "<b>Director(s):</b> ";

        $query2 = "SELECT D.last, D.first, D.dob FROM MovieDirector MD, Director D WHERE MD.mid=$db_id AND MD.did=D.id";
        $rs2 = mysqli_query($db, $query2);
        if(!$rs2){
          echo '<p>'.mysqli_error($db).'</p>';
        }

        if(mysqli_num_rows($rs2) == 0){
          echo "N/A \n";
        }
        else{
          while($row2 = mysqli_fetch_assoc($rs2))
          {
              echo $row2['first']." " . $row2['last'];
              echo " " . "(" . $row2['dob'] . ")" . " ";
          }

        }
        mysqli_free_result($rs2);

        echo "<br>";
        echo "<b>Genre(s):</b> ";
        $query3 = "SELECT genre from MovieGenre WHERE mid=$db_id";
        $rs3 = mysqli_query($db, $query3);
        if(!$rs3){
            echo '<p>'.mysqli_error($db).'</p>';
        }

        if(mysqli_num_rows($rs3) == 0){
            echo "N/A \n";
        }
        else{
          while($row3 = mysqli_fetch_assoc($rs3)){
            echo $row3["genre"] . " ";
          }
        }
        mysqli_free_result($rs3);


        echo "<br/><br/>";
        // Related Actors Portion
        echo "<hr>";
        echo "<h2>Related Actors</h2>";



        $query4 = "SELECT MA.aid, MA.role, A.last, A.first FROM MovieActor MA, Actor A WHERE MA.mid=$db_id AND MA.aid=A.id";
        $rs4 = mysqli_query($db, $query4);
        if(mysqli_num_rows($rs4) == 0){
          echo "N/A \n";
        }else{
          echo "<div class=\"table-responsive\">";
          echo "<table class=\"table table-bordered\">";
          echo "<thead>
                  <tr>
                    <td>Name</td>
                    <td>Role</td>
                  </tr>
                </thead>";
          echo "<tbody>";
          while($row4= mysqli_fetch_assoc($rs4)){
            echo "<tr>";
              echo "<td>";
                echo "<a href=\"show_a.php?id=" .$row4["aid"]. "\">" . $row4["first"] ." ". $row4["last"]."</a>";
              echo "</td>";
              echo "<td>";
                echo $row4["role"];
              echo "</td>";
            echo "</tr>";
          }
          echo"</tbody></table></div>";
        }

        // Reviews and Average Rating
        echo "<hr>";
        echo "<h2>User Reviews</h2>";

        $query5 = "SELECT AVG(rating), COUNT(rating) FROM Review WHERE mid=$db_id";
        $rs5 = mysqli_query($db, $query5);
        if(!$rs5){
          echo '<p>'.mysqli_error($db).'</p>';
        }
        $row5 = mysqli_fetch_row($rs5);
        if($row5[0] == "")
        {
          echo "<a href=\"addreview.php?id=".$db_id."\">By now, nobody ever rates this movie. Be the first one to give a review!</a>";
        }
        else
        {
            $avgRating = $row5[0];
            echo "Average score for this movie is " ."$avgRating " . "/ 5 based on " . $row5[1] . " reviews";
            echo "<br>";
            echo "<a href=\"addreview.php?id=".$db_id."\">Leave your review as well</a>";
            mysqli_free_result($rs5);

            echo "<hr>";
            echo "<h2>Comment details shown below: </h2>";
            // Show latest reviews first
            $query6 = "SELECT time, name, rating, comment FROM Review WHERE mid=$db_id ORDER BY time DESC";
            $rs6 = mysqli_query($db, $query6);
            if(!$rs6){
              echo '<p>'.mysqli_error($db).'</p>';
            }
            else{

              while ($row6 = mysqli_fetch_assoc($rs6))
              {
                echo $row6["name"] . " rates this movie with the score " . $row6["rating"] . " and left a review at " . $row6["time"];
                echo " comment: ";
                echo "<br>";
                echo $row6["comment"];
                echo "<br><br>";
              }
            }

            mysqli_free_result($rs6);
          }
        }
      // close db connection
      mysqli_close($db);
      echo "<hr>";
?>

<form method='get' action='search.php' class='form-horizontal'>
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
	</div>
</div>



</div>

</body>
</html>
