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

?>
<?php
#Check to ensure the user is logged in
#Check if the user login details are valid
if (isset($_POST['loginAttempt']))
{
    #Retrieve the ivs from the sql database
    $sql = "SELECT PPS, passWord, iv, firstName, lastName, isAdmin from users";
    $result = $conn->query($sql) ;
    $check = FALSE ;
    while ($row = $result->fetch_assoc() && $check === FALSE)
    {
      #Convert from hex to binary
      $currentiv = hex2bin($row["iv"]) ;
      $currentPPS = hex2bin($row["PPS"]) ;
      $currentHash = $row["passWord"] ;
      $currentName = hex2bin($row["firstName"]) ;
      $currentLastName = hex2bin($row["lastName"]) ;
      $checkAdmin = $row["isAdmin"] ;

      $decrypted_pps = openssl_decrypt($currentPPS, $cipher, $key, OPENSSL_RAW_DATA, $currentiv) ;
      $decrypted_name = openssl_decrypt($currentName, $cipher, $key, OPENSSL_RAW_DATA, $currentiv) ;
      $decrypted_lastName = openssl_decrypt($currentLastName, $cipher, $key, OPENSSL_RAW_DATA, $currentiv) ;

      if ($decrypted_pps === $_POST['pps'] && password_verify($_POST["passwd"], $currentHash))
      {
        $check = TRUE ;
        break ;
      }

    }#While Loop
    if ($check == TRUE)
    {
        $_SESSION["loggedIn"] = "SET" ;
        $_SESSION["firstName"] = $decrypted_name ;
        $_SESSION["lastName"] = $decrypted_lastName ;
        $_SESSION["pps"] = bin2hex($currentPPS) ;
        $_SESSION["decryptPPS"] = bin2hex($decrypted_pps) ;

        $sql = "SELECT isAdmin from users WHERE PPS = '$_SESSION[pps]'" ;
        $result = $conn->query($sql) ;
        $row = $result->fetch_assoc() ;
        $admin = $row["isAdmin"] ;
        $_SESSION["adminStatus"] = $admin ;


        if ($admin === "1")
        {
          include "admin.php" ;
          exit() ;
        }
        else
        {
          include "home.php" ;
          echo $_SESSION["adminStatus"] ;
          exit() ;
        }

    }
    else
    {
      echo "<h2>Invalid Login</h2>";
    }
  }
?>
<html>
<head>
<link rel="stylesheet" href="styling.css" type="text/css">
<title>Covid Booking</title>
</head>
<body>
<h2>Welcome to the COVID-19 Booking Site</h2><br>
<h2>Enter your login details below. Don't have an account? <a href="CreateUser.php">Sign up here</a></h2>

<div class="formstyle">
<form method="post" action="home.php">
<label for="pps" class="inline" >PPS Number</label>
<input type="text" id="pps" name='pps' class="inputsize" pattern="[0-9]{7}[A-Z]{1,2}" title="PPS must be seven letters followed by one or two capital letters"><br><br>
<label for="passwd" class="inline">Password</label>
<input type="password" id="passwd" name="passwd" class="inputsize"><br><br>
<button type="submit" name="loginAttempt">Login</button>
</form>
</div>

</body>
</html>
