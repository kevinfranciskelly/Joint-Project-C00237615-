<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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

if (isset($_SESSION["loggedIn"]))
{

?>
<html>
<head>
<link rel="stylesheet" href="styling.css" type="text/css">
<title>View Details</title>
</head>
<body>
<?php
  if (isset($_POST["update"]))
  {
    /*$stmt = $conn->prepare("SELECT iv from users WHERE PPS = ?") ;
    $stmt->bind_param("s", $_SESSION["pps"]) ;
    $result = $stmt->execute() ;
    $stmt->store_result() ;

    $row = $result->fetch_assoc() ;*/
    $sql = "SELECT iv from users WHERE PPS = '".$_SESSION["pps"]."'" ;
    $result = $conn->query($sql) ;
    $row = $result->fetch_assoc() ;
    $iv = hex2bin($row["iv"]) ;


    #Escape the content
    $escaped_firstName = $conn -> real_escape_string($_POST['updatedFirst']) ;

    $_SESSION["firstName"] = $_POST['updatedFirst'] ;

    $escaped_lastName = $conn -> real_escape_string($_POST['updatedLast']) ;
    $escaped_dob = $conn -> real_escape_string($_POST['updateddob']) ;
    $escaped_addrLine1 = $conn -> real_escape_string($_POST['updatedAddr1']) ;
    $escaped_addrLine2 = $conn -> real_escape_string($_POST['updatedAddr2']) ;
    $escaped_county = $conn -> real_escape_string($_POST['updatedCounty']) ;
    $escaped_eir = $conn -> real_escape_string($_POST['updatedEir']) ;
    $escaped_email = $conn -> real_escape_string($_POST['updatedEmail']) ;

    #Encrypt the Data
    $encrypted_firstName = openssl_encrypt($escaped_firstName, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    $encrypted_lastName = openssl_encrypt($escaped_lastName, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    $encrypted_dob = openssl_encrypt($escaped_dob, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    $encrypted_addr1 = openssl_encrypt($escaped_addrLine1, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    $encrypted_addr2 = openssl_encrypt($escaped_addrLine2, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    $encrypted_county = openssl_encrypt($escaped_county, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    $encrypted_eir = openssl_encrypt($escaped_eir, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    $encrypted_email = openssl_encrypt($escaped_email, $cipher, $key, OPENSSL_RAW_DATA, $iv) ;

    #Change the data to hex
    $firstName_hex = bin2hex($encrypted_firstName) ;
    $lastName_hex = bin2hex($encrypted_lastName) ;
    $dob_hex = bin2hex($encrypted_dob) ;
    $addr1_hex = bin2hex($encrypted_addr1) ;
    $addr2_hex = bin2hex($encrypted_addr2) ;
    $county_hex = bin2hex($encrypted_county) ;
    $eir_hex = bin2hex($encrypted_eir) ;
    $email_hex = bin2hex($encrypted_email) ;

    #Prepare the Statement
    $ppsSesh = $_SESSION["pps"] ;
    $prepStatement = $conn->prepare("UPDATE users SET firstName = ?, lastName = ?, dateOfBirth = ?, AddrLine1 = ?, AddrLine2 = ?, County = ?, eirCode = ?, email = ? WHERE PPS = ?") ;
    /*if ($prepStatement = $conn->prepare("UPDATE users SET firstName = ? WHERE PPS = ?") === FALSE)
    {
      die ("Statement Error ". $conn->error) ;
    }*/
    $prepStatement->bind_param('sssssssss', $firstName_hex, $lastName_hex, $dob_hex, $addr1_hex, $addr2_hex, $county_hex, $eir_hex, $email_hex, $ppsSesh) ;

    if ($prepStatement->execute() === TRUE)
    {
      echo "<h2>Account Details Updated</h2>" ;
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

  /*  $stmt2 = $conn->prepare("SELECT * from users WHERE PPS = ?")  ;
    $ppsSesh = bin2hex($_SESSION["pps"]) ;
    $stmt2->bind_param("s", $ppsSesh) ;
    if ($stmt2->execute() === FALSE)
    {
        die('Error using database: ' . $conn->error);
    }
    $result = $stmt2->store_result() ;

    $row = $result->fetch();*/

    $sql = "SELECT * from users WHERE PPS = '".$_SESSION["pps"]."'" ;
    $result = $conn->query($sql) ;
    $row = $result->fetch_assoc() ;

    #Convert from hex to binary
    $firstName = hex2bin($row["firstName"]) ;
    $lastName = hex2bin($row["lastName"]) ;
    $dob = hex2bin($row["dateOfBirth"]) ;
    $addrLine1 = hex2bin($row["AddrLine1"]) ;
    $addrLine2 = hex2bin($row["AddrLine2"]) ;
    $county = hex2bin($row["County"]) ;
    $eirCode = hex2bin($row["eirCode"]) ;
    $email = hex2bin($row["email"]) ;
    $userIV = hex2bin($row["iv"]) ;

    #Decrypt the data
    $decrypted_firstName = openssl_decrypt($firstName, $cipher, $key, OPENSSL_RAW_DATA, $userIV) ;
    $decrypted_lastName = openssl_decrypt($lastName, $cipher, $key, OPENSSL_RAW_DATA, $userIV) ;
    $decrypted_dob = openssl_decrypt($dob, $cipher, $key, OPENSSL_RAW_DATA, $userIV) ;
    $decrypted_addrLine1 = openssl_decrypt($addrLine1, $cipher, $key, OPENSSL_RAW_DATA, $userIV) ;
    $decrypted_addrLine2 = openssl_decrypt($addrLine2, $cipher, $key, OPENSSL_RAW_DATA, $userIV) ;
    $decrypted_county = openssl_decrypt($county, $cipher, $key, OPENSSL_RAW_DATA, $userIV) ;
    $decrypted_eirCode = openssl_decrypt($eirCode, $cipher, $key, OPENSSL_RAW_DATA, $userIV) ;
    $decrypted_email = openssl_decrypt($email, $cipher, $key, OPENSSL_RAW_DATA, $userIV) ;

}
else
{
  include "index.php" ;
  exit() ;
}
?>
<div class="formstyle">
<form method="post" action="viewDetails.php">
<label for="updatedFirst" class="inline">First Name: </label>
<input type="text" name="updatedFirst" id="updatedFirst" class="inputsize" pattern="[A-Za-z' ]{1,}" required value="<?php echo htmlentities($decrypted_firstName); ?>" /><br>
<label for="updatedLast" class="inline">Last Name: </label>
<input type="text" name="updatedLast" id="updatedLast" class="inputsize" pattern="[A-Za-z' ]{1,}" required value="<?php echo htmlentities($decrypted_lastName); ?>" /><br>
<label for="updateddob" class="inline">Date of Birth: </label>
<input type="date" name="updateddob" id="updateddob" required class="inputsize" value="<?php echo htmlentities($decrypted_dob); ?>" /><br>
<label for="updatedAddr1" class="inline">Address Line 1: </label>
<input type="text" name="updatedAddr1" id="updatedAddr1" required class="inputsize" pattern="[A-Za-z0-9]{1,}" value="<?php echo htmlentities($decrypted_addrLine1); ?>" /><br>
<label for="updatedAddr2" class="inline">Address Line 2: </label>
<input type="text" name="updatedAddr2" id="updatedAddr2" required class="inputsize" pattern="[A-Za-z0-9]{1,}" value="<?php echo htmlentities($decrypted_addrLine2); ?>" /><br>
<label for="updatedCounty" class="inline">County: </label>
<input type="text" name="updatedCounty" id="updatedCounty" required class="inputsize" pattern="[A-Za-z]{4,13}" value="<?php echo htmlentities($decrypted_county); ?>" /><br>
<label for="updatedEir" class="inline">Eircode </label>
<input type="text" name="updatedEir" id="updatedEir" class="inputsize" pattern="[A-Z0-9]{7}" value="<?php echo htmlentities($decrypted_eirCode); ?>" /><br>
<label for="updatedEmail" class="inline">Email </label>
<input type="email" name="updatedEmail" id="updatedEmail" class="inputsize" required value="<?php echo htmlentities($decrypted_email); ?>" /><br>
<input type="submit" name="update" value="Update Details" >
<input type="submit" name="return" value="Return to Home" >
</form>
</div>
</body>
</html>
