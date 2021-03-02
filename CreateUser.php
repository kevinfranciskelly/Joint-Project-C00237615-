<?php
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
<title>Covid Booking</title>
</head>
<body>
<?php
  if (isset($_POST['newUser']))
  {
    $iv = random_bytes(16) ;
    $escaped_pps = $conn -> real_escape_string($_POST['pps']) ;
    $escaped_firstName = $conn -> real_escape_string($_POST['firstName']) ;
    $escaped_lastName = $conn -> real_escape_string($_POST['lastName']) ;
    $escaped_dob = $conn -> real_escape_string($_POST['dob']) ;
    $escaped_addrLine1 = $conn -> real_escape_string($_POST['addrLine1']) ;
    $escaped_addrLine2 = $conn -> real_escape_string($_POST['addrLine2']) ;
    $escaped_county = $conn -> real_escape_string($_POST['county']) ;
    $escaped_eir = $conn -> real_escape_string($_POST['eirCode']) ;
    $escaped_condition = $conn -> real_escape_string($_POST['condition']) ;
    $escaped_email = $conn -> real_escape_string($_POST['email']) ;

    #Encrypt the Data
    $encrypted_pps = openssl_encrypt($escaped_pps, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    $encrypted_firstName = openssl_encrypt($escaped_firstName, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    $encrypted_lastName = openssl_encrypt($escaped_lastName, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    $encrypted_dob = openssl_encrypt($escaped_dob, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    $encrypted_addr1 = openssl_encrypt($escaped_addrLine1, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    $encrypted_addr2 = openssl_encrypt($escaped_addrLine2, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    $encrypted_county = openssl_encrypt($escaped_county, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    $encrypted_eir = openssl_encrypt($escaped_eir, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    $encrypted_condition = openssl_encrypt($escaped_condition, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    $encrypted_email = openssl_encrypt($escaped_email, $cipher, $key, OPENSSL_RAW_DATA, $iv) ;

    #Change the data to hex
    $pps_hex = bin2hex($encrypted_pps) ;
    $firstName_hex = bin2hex($encrypted_firstName) ;
    $lastName_hex = bin2hex($encrypted_lastName) ;
    $dob_hex = bin2hex($encrypted_dob) ;
    $addr1_hex = bin2hex($encrypted_addr1) ;
    $addr2_hex = bin2hex($encrypted_addr2) ;
    $county_hex = bin2hex($encrypted_county) ;
    $eir_hex = bin2hex($encrypted_eir) ;
    $condition_hex = bin2hex($encrypted_condition) ;
    $email_hex = bin2hex($encrypted_email) ;
    $iv_hex = bin2hex($iv) ;

    #Hash the Password
    $passwd = $_POST['password'] ;
    $hash = password_hash($passwd, PASSWORD_DEFAULT) ;

    #Execute the sql query
    $isAdmin = 0 ;
    $prepStatement = $conn->prepare("INSERT INTO users (PPS, firstName, lastName, dateOfBirth, AddrLine1, AddrLine2, County, eirCode, email, conditions, passWord, isAdmin, iv) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)") ;
    $prepStatement->bind_param("sssssssssssis", $pps_hex, $firstName_hex, $lastName_hex, $dob_hex, $addr1_hex, $addr2_hex, $county_hex, $eir_hex, $email_hex, $condition_hex, $hash, $isAdmin, $iv_hex) ;

    if ($prepStatement->execute() === TRUE)
    {
      echo "<h2>Account Created</h2>" ;
    }
    else
    {
      die("Error: ".$prepStatement->error) ;
    }
  }
 ?>
<h2>Create a COVID Booking Account</h2>
<h2>As in accordance to the GDPR, your data is kept secure and is only utilised in the scope of this website</h2>
<h2>The data you provide is used to determine if you would be considered at risk, how to contact you for your results, and how to reach you if needed</h2>


<div class="formstyle">
<form method="post">
  <label for="pps" class="inline">PPS Number</label>
  <input type="text" id="pps" name='pps' required class="inputsize" pattern="[0-9]{7}[A-Z]{1,2}" title="PPS must be seven numbers followed by one or two capital letters"><br>
  <br>
  <label for="firstName" class="inline">First Name</label>
  <input type="text" id="firstName" name="firstName" required class="inputsize" pattern="[A-Za-z' ]{1,}"><br>
  <br>
  <label for="lastName" class="inline">Last Name</label>
  <input type="text" id="lastName" name="lastName" required class="inputsize" pattern="[A-Za-z' ]{1,}"><br>
  <br>
  <label for="dob" class="inline">Date of Birth</label>
  <input type="date" id="dob" name="dob" required class="inputsize"><br>
  <br>
  <label for="addrLine1" class="inline">Address Line 1</label>
  <input type="text" id="addrLine1" name="addrLine1" required class="inputsize" pattern="[A-Za-z0-9]{1,}"><br>
  <br>
  <label for="addrLine2" class="inline">Address Line 2</label>
  <input type="text" id="addrLine2" name="addrLine2" required class="inputsize" pattern="[A-Za-z0-9]{1,}"><br>
  <br>
  <label for="county" class="inline">County</label>
  <input type="text" id="county" name="county" required class="inputsize" pattern="[A-Za-z]{4,13}"><br>
  <br>
  <label for="eirCode" class="inline">Eircode</label>
  <input type="text" id="eirCode" name="eirCode" class="inputsize" pattern="[A-Z0-9]{7}"><br>
  <br>
  <label for="email" class="inline">E-mail</label>
  <input type="email" id="email" name="email" class="inputsize" required><br>
  <br>
  <label for="password" class="inline">Password</label>
  <input type="password" id="password" name="password" class="inputsize"><br>
  <br>
  <br><br>
  <label for="condition" class="inline">Please list any relevant conditions you have</label><br>
  <input type="text" id ="condition" name="condition" class="condition" pattern="[A-Za-z]{175}">
  <br><br>
  <h2>By selecting "Create Account", you consent to providing your personal infromation to utilize the services of this COVID-19 Booking Application</h2>
  <button type="submit" name="newUser">Create Account</button>
</form>
</div>
</body>
</html>
