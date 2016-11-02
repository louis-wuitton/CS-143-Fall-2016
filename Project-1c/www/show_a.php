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
    <h1>Actor Information Page</h1>
    <div class="jumbotron">
    <?php
        $db = mysqli_connect('localhost', 'cs143', '', 'CS143');
        if(!$db){
          echo "<p> Error: Unbale to connect to MySQL. </p>";
          echo "<p> Error Message: " . mysqli_connect_error(). "</p>";
        }
        $actorID = trim($_GET["id"]);
        if($actorID){
          echo "<h3> Actor Information is: </h3>";
          $actorQuery = "SELECT first, last, sex, dob, dod FROM Actor WHERE id = $actorID";
          $actorResults = mysqli_query($db, $actorQuery);
          if(!$actorResults){
            echo '<p>' . mysqli_error($db) . '</p>';
          }else{
            echo "<div class=\"table-responsive\">";
            echo "<table class=\"table table-bordered\">";
            echo "<thead>
                    <tr>
                      <td>Name</td>
                      <td>Role</td>
                      <td>Date of Birth</td>
                      <td>Date of Death</td>
                    </tr>
                  </thead>";
            echo "<tbody>";
            while($actorrow= mysqli_fetch_assoc($actorResults)){
              echo "<tr>";
                echo "<td>";
                  echo $actorrow["first"] . " ". $actorrow["last"];
                echo "</td>";
                echo "<td>";
                  echo $actorrow["sex"];
                echo "</td>";
                echo "<td>";
                  echo $actorrow["dob"];
                echo "</td>";
                echo "<td>";
                  if($actorrow["dod"] != ""){
                    echo $actorrow["dod"];
                  }else {
                    echo "Still Alive";
                  }
                echo "</td>";
              echo "</tr>";
            }
            echo"</tbody></table></div>";
          }
          echo "<hr>";
          echo "<h3> Actor Movies And Role: </h3>";
          $roleQuery = "SELECT M1.role, M2.title, M2.id FROM MovieActor M1, Movie M2 WHERE M1.mid = M2.id AND M1.aid = $actorID ORDER BY M2.year DESC";
          $roleResults = mysqli_query($db, $roleQuery);
          if(!$roleResults){
            echo '<p>' . mysqli_error($db) . '</p>';
          }else{
            echo "<div class=\"table-responsive\">";
            echo "<table class=\"table table-bordered\">";
            echo "<thead>
                    <tr>
                      <td>Role</td>
                      <td>Movie Title</td>
                    </tr>
                  </thead>";
            echo "<tbody>";
            while($rolerow= mysqli_fetch_assoc($roleResults)){
              echo "<tr>";
                echo "<td>";
                  echo $rolerow["role"];
                echo "</td>";
                echo "<td>";
                  echo "<a href=\"show_m.php?id=" .$rolerow["id"]. "\">" . $rolerow["title"]."</a>";
                echo "</td>";
              echo "</tr>";
            }
            echo"</tbody></table></div>";
          }
            /*

            echo "<h3> Actor Movies And Role: </h3>";

            echo "<div class =\"table-responsive\">";
            echo "<table class=\"table table-bordered\">";
            echo "<thead> <tr> <td>role</td> <td>Movie Title</td> </tr> </thead>";
            echo "<tbody>";

            while($rolerow = mysqli_fetch_assoc($roleResults)){
              echo "<tr>"
              echo "<td> " . $rolerow["role"] . "</td>";
              echo "<a href=\"show_a.php?id=".$rolerow["id"]."\" >".$rolerow["role"]. "</a> </td>";
              echo "</tr>";
            }
            echo "<br>";
            mysqli_free_result($roleResults);

          }
          */
        }
   ?>

	<form method='get' action='search.php' class='form-horizontal'>
	   <center><legend>Please search for actors here</legend></center>
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


</body>
</html>
