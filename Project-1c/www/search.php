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
<h1>Searching Page: </h1>
	<div class="well necomponent">
	<form method='get' action='search.php' class='form-horizontal'>
	   <center><legend>You could search for movies or actors here</legend></center>
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
      $db = mysqli_connect('localhost', 'cs143', '', 'CS143');
      if(!$db){
        echo "<p> Error: Unbale to connect to MySQL. </p>";
        echo "<p> Error Message: " . mysqli_connect_error(). "</p>";
      }
      $searching = $_GET["searching"];
      $searching = mysqli_real_escape_string($db, $searching);
      //We want to get arrays from the searching input
      $search_array = explode(' ', $searching);
      $arr_count = count($search_array);

      $searching = trim($searching);
      if($searching != "" and $arr_count > 0){
          echo "<h3>Maching Actors are: </h3>";
          $actorQuery = "SELECT id, first, last, dob FROM Actor WHERE (first LIKE '%$search_array[0]%' OR last LIKE '%$search_array[0]%')";
          for($i = 1; $i < $arr_count; $i++){
            $actorQuery .= " AND (first LIKE '%$search_array[$i]%' OR last LIKE '%$search_array[$i]%')";
          }


          $actorResults = mysqli_query($db, $actorQuery);
          if(!$actorResults){
            echo '<p>' . mysqli_error($db) . '</p>';
          }
          else{
              if(mysqli_num_rows($actorResults) == 0){
                  echo "<p>No actor is found based on your searching</p>";
              }else{
                  echo "<div class=\"table-responsive\">";
                  echo "<table class=\"table table-bordered\">";
                    echo "<thead>
                            <tr>
                              <td>Name</td>
                              <td>Date of Birth</td>
                            </tr>
                        </thead>";
                  echo "<tbody>";
                  while($actorrow= mysqli_fetch_assoc($actorResults)){
                    echo "<tr>";
                      echo "<td>";
                        echo "<a href=\"show_a.php?id=" .$actorrow["id"]. "\">" . $actorrow["first"] ." ". $actorrow["last"]."</a>";
                      echo "</td>";
                      echo "<td>";
                        echo "<a href=\"show_a.php?id=" .$actorrow["id"]. "\">" . $actorrow["dob"] . "</a>";
                      echo "</td>";
                    echo "</tr>";
                  }
                 echo"</tbody></table></div>";
               }
             }

             echo "<h3> Matching Movies are: </h3>";
             $movieQuery = "SELECT id, title, year FROM Movie WHERE title LIKE '%$search_array[0]%'";
             for($i = 1; $i < count($search_array); $i++){
               $movieQuery .= " AND title LIKE '%$search_array[$i]%'";
             }
             $movieResults = mysqli_query($db, $movieQuery);
             if(!$movieResults){
               echo '<p>' . mysqli_error($db) . '</p>';
             }else{
                  if(mysqli_num_rows($movieResults) == 0){

                  }else{
                      echo "<div class=\"table-responsive\">";
                      echo "<table class=\"table table-bordered\">";
                      echo "<thead>
                                <tr>
                                    <td>Title</td>
                                    <td>Year</td>
                               </tr>
                            </thead>";
                      echo "<tbody>";
                      while($movierow= mysqli_fetch_assoc($movieResults)){
                          echo "<tr>";
                              echo "<td>";
                                  echo "<a href=\"show_m.php?id=" .$movierow["id"]. "\">" . $movierow["title"] ."</a>";
                              echo "</td>";
                              echo "<td>";
                                  echo "<a href=\"show_m.php?id=" .$movierow["id"]. "\">" . $movierow["year"] . "</a>";
                              echo "</td>";
                          echo "</tr>";
                      }
                      echo"</tbody></table></div>";
                }
            }

        }

     ?>
</div>
</div>

</body>
</html>
