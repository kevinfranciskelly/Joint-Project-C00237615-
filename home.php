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
<html>
<head>
<link rel="stylesheet" href="styling.css" type="text/css">
<title>Home</title>
</head>
<body>
<?php
  if (isset($_POST["appointment"]))
  {
      include "createAppointment.php" ;
  }
  else if (isset($_POST["viewDetails"]))
  {
      include "viewDetails.php" ;
  }
  else if (isset($_POST["LogOut"]))
  {
      include "logout.php" ;
  }
  else if (isset($_POST["deleteAcc"]))
  {
      include "delete.php" ;
  }
  else
  {
    if (isset($_SESSION["loggedIn"]))
    {
      echo "<h2>Hello ". htmlentities($_SESSION["firstName"])."</h2><br>" ;
      $sql = "SELECT * from appointments WHERE PPS = '$_SESSION[pps]'" ;
      if (!$conn->query($sql) === TRUE) {
        die('Error using database: ' . $conn->error);
      }
      if ($result = $conn->query($sql))
      {

        if ($result -> num_rows == 0)
        {
          echo "<h2>You do not have an appointment set</h2>" ;
        }
        else {
          $result = $conn->query($sql) ;
          #If an appointment has been set, list it
          #If the appointment is already done, display the status of the results
          while ($row = $result->fetch_assoc())
          {
            #Convert from hex to binary
            $appointDate = hex2bin($row["date"]);
            $appointTime = hex2bin($row["time"]) ;
            $appointBranch = hex2bin($row["branch"]) ;
            $appointiv = hex2bin($row["iv"]) ;

            $decrypted_date = openssl_decrypt($appointDate, $cipher, $key, OPENSSL_RAW_DATA, $appointiv) ;
            $decrypted_time = openssl_decrypt($appointTime, $cipher, $key, OPENSSL_RAW_DATA, $appointiv) ;
            $decrypted_branch = openssl_decrypt($appointBranch, $cipher, $key, OPENSSL_RAW_DATA, $appointiv) ;

            echo "<h2>Your appointment is ".$decrypted_date." at ".$decrypted_time." at the ".$decrypted_branch."</h2>" ;
          }
        }
      }
      echo "<div class='formstyle'><form method='post' action='home.php'>
            <input type='submit' name='appointment' value='Make Appointment' >
            <input type='submit' name='viewDetails' value='View Your details' >
            <input type='submit' name='deleteAcc' value='Delete your Account' >
            <input type='submit' name='LogOut' value='Log Out' >
            </form></div>";

    }#If Statement for checking Session Variable
    else
    {
      include "index.php" ;
    }
  } //Else statement

 ?>
</body>
</html>
