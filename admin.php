<?php
if (session_status() == PHP_SESSION_ACTIVE) {
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

date_default_timezone_set("Europe/Dublin") ;
?>
<html>
<head>
<title>Admin Page</title>
<link rel="stylesheet" href="styling.css" type="text/css">
</head>
<?php
if (isset($_SESSION["loggedIn"]))
{
  if ($_SESSION["adminStatus"] === "0")
  {
    include "home.php" ;
    exit() ;
  }
  if (isset($_POST["logOut"]))
  {
    include "logout.php" ;
    exit() ;
  }
  if (isset($_POST["setResult"]))
  {
    #First check that the PPS number entered is a valid
    $sql = "SELECT PPS, iv, covidStatus from users" ;
    $result = $conn->query($sql) ;
    $check = FALSE ;
    while ($row = $result->fetch_assoc())
    {
      $rowPPS = hex2bin($row["PPS"]) ;
      $rowIV = hex2bin($row["iv"]) ;

      $decrypted_pps = openssl_decrypt($rowPPS, $cipher, $key, OPENSSL_RAW_DATA, $rowIV) ;
      if ($decrypted_pps === $_POST["pps"])
      {
        $rowPPS = $row["PPS"] ;
        $check = TRUE ;
        break ;
      }

    }
    if ($check === TRUE)
    {

      $sql = "SELECT * from appointments WHERE PPS ='$rowPPS'" ;
      $result = $conn->query($sql) ;
      if ($result -> num_rows > 0)
      {
        $row = $result->fetch_assoc() ;
        $theIV = hex2bin($row["iv"]) ;
        $decrypted_date = openssl_decrypt(hex2bin($row["date"]), $cipher, $key, OPENSSL_RAW_DATA, hex2bin($row["iv"])) ;
        $todaysDate = date("Y-m-d") ;
        $formattedDate = date("Y-m-d", strtotime($decrypted_date)) ;
        if ($decrypted_date > $todaysDate)
        {
          echo "<h2>This patient has not had their appointment yet</h2>" ;
        }
        else
        {
          $escaped_status = $conn -> real_escape_string($_POST["status"]) ;

          $encrypted_status = openssl_encrypt($escaped_status, $cipher, $key, OPENSSL_RAW_DATA, $rowIV) ;

          $status_hex = bin2hex($encrypted_status) ;
          $prepStatement = $conn->prepare("UPDATE users SET covidStatus = ? WHERE PPS = ?") ;
          $prepStatement -> bind_param("ss", $status_hex, $rowPPS) ;
          if ($prepStatement->execute() === TRUE)
          {
              echo "<h2>Patient Result's updated</h2>" ;
          }
          else
          {
            die("Error: ".$prepStatement->error) ;
          }
          $prepStatement2 = $conn->prepare("DELETE from appointments WHERE PPS = ?") ;
          $prepStatement2 -> bind_param("s", $rowPPS) ;
          if ($prepStatement2->execute() === TRUE)
          {

          }
          else
          {
            die("Error: ".$prepStatement->error) ;
          }

        }

      }
      else
      {
        echo "<h2>This user has yet to book an appointment</h2>" ;
      }
    }
    else
    {
      echo "<h2>PPS entered does not exist in the database</h2>" ;
    }
  }

}
else
{
  include "index.php" ;
  exit() ;
}
?>
<body>

<h2>Covid Booking Application Admin Page</h2>
<h2>Enter a PPS number and select the status of their COVID-19 test results</h2>

<div class="formstyle">
<form action="admin.php" method="post">
<label for="pps" class="inline">PPS of Patient</label>
<input type="text" id="pps" name='pps' required class="inputsize" pattern="[0-9]{7}[A-Z]{1,2}" title="PPS must be seven numbers followed by one or two capital letters"><br>
<br>
<label for="status" class="inline">Test Result</label>
<select name="status" id="status">
<option value="Positive">Positive</option>
<option value="Negative">Negative</option>
</select><br>
<button type="submit" name="setResult">Set Result</button>
</form>
<form action="admin.php" method="post">
<button type="submit" name="logOut">Log Out</button>
</form>

</div>
</body>
</html>
