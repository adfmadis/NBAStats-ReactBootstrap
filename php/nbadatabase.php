<?php

// declare variables for connecting to server
$server = "localhost";
$username = "anthony_anthony";
$password = "Ih7kmasnibt!";
$database = "anthony_adfmadis";

// create connection
$db = new mysqli($server,$username,$password,$database);
// check connection
if ($db->connect_error) {
  die("Connection failed: " . $connection->connect_error);
}

// call query to database to get all nba player data
$query = "SELECT * FROM NBAStats";
$result = $db->query($query);

if ($result) { // confirm query was called properly, store team player's data in array to use in javascript
  echo "<script> console.log(\"Database table acquired successfully\")</script>";

  $queryarr = array();

  // turn query result to array format
  for ($playernum = 0; $queryarr[$playernum] = $result->fetch_array(MYSQLI_NUM); $playernum++) {}

  $result->free();

} else { // alert that query didn't work
  echo "<script> console.log(\"Error retrieving data from database\");</script>";
} // else

// set up array, each value will represent the max value for 1 stat across the NBA
$maxarr = array();

$graphColumns = array("Games Played","Points","Rebounds","Assists","Steals","Blocks","Turnovers","FG%", "3P%");

$statnum = 0;
for ($columnnum = 0; $columnnum < count($queryarr[0]); $columnnum++) {

  if (is_numeric($queryarr[0][$columnnum])) { // column is only a stat if it is numeric (team/name/position are not stats)
    $query = "SELECT `$graphColumns[$statnum]` FROM NBAStats ORDER BY `$graphColumns[$statnum]` DESC LIMIT 0,1";
    $result = $db->query($query);
    $queryarr2 = array();
    // turn query result to array format
    for ($x = 0; $queryarr2[$x] = $result->fetch_array(MYSQLI_NUM); $x++) {}

    array_push($maxarr,$queryarr2[0][0]);

    $statnum++;
  } // if

} // for

/*
<?php
$db->close();
?>
*/
?>
