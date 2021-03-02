<?php
if (session_status() == PHP_SESSION_ACTIVE)
{

}
else {
  session_start() ;
}
$host = 'localhost';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password);

$cipher = 'AES-128-CBC';
$key = 'c36eddd978c4a55e';

if ($conn->connect_error) {
die('Connection failed: ' . $conn->connect_error);
}

$sql = 'USE covidbooking;';
if (!$conn->query($sql) === TRUE) {
  die('Error using database: ' . $conn->error);
}

?>

<?php
  if (isset($_SESSION["loggedIn"]))
  {
    if (isset($_POST["delete"]))
    {
      $ppsSesh = $_SESSION["pps"] ;
      $prepStatement = $conn->prepare("DELETE from appointments WHERE PPS= ?") ;
      $prepStatement->bind_param("s", $ppsSesh) ;
      if ($prepStatement->execute() === TRUE)
      {

      }
      else
      {
        die("Error: ".$prepStatement->error) ;
      }

      $prepStatement2 = $conn->prepare("DELETE from users WHERE PPS= ?") ;
      $prepStatement2->bind_param("s", $ppsSesh) ;

      if ($prepStatement2->execute() === TRUE)
      {
        echo "<h2>Account Deleted</h2>" ;
        session_destroy() ;
        exit() ;
      }
      else
      {
        die("Error: ".$prepStatement->error) ;
      }

    }
    else if (isset($_POST["return"]))
    {
      include "home.php" ;
      exit() ;
    }
  }
  else {
    include <"index.php"> ;
    exit() ;
  }
 ?>
<html>
<head>
<link rel="stylesheet" href="styling.css" type="text/css">
<title>Delete Account</title>
</head>
<body>
<h2>Are you sure you wish to delete your account? This will also delete any appointments you have also</h2>
<div style="formstyle">
<form method="post" action="delete.php">
<input type="submit" name="delete" id="delete" value="Delete Account" ><br>
<input type="submit" name="return" id="return" value="Return to Home" >
</form>
</div>
</body>
</html>
