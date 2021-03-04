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

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require_once 'C:\xampp\Composer\vendor\autoload.php' ;
//PHPMailer Object
$mail = new PHPMailer(true); //Argument true in constructor enables exceptions
$mail->IsSMTP();
$mail->Host = "smtp.gmail.com" ;
$mail->SMTPAuth = true;
$mail->SMTPSecure = 'tls';
$mail->Port = 587;
$mail->Username = 'covidbookingireland@gmail.com';
$mail->Password = 'thyrbfvcguwdf63r4t75hbf cbvcgf7rt4gurbfn';

//From email address and name
$mail->From = "covidbookingireland@gmail.com";
$mail->FromName = "Covid Booking";
$mail->isHTML(true);
$mail->Subject = "Covid Test Appointment";

date_default_timezone_set("Europe/Dublin") ;
?>
<html>
<head>
  <title>Create an Appointment</title>
  <link rel="stylesheet" href="styling.css" type="text/css">
</head>
<body>
<?php
if ($_SESSION["loggedIn"] == "SET")
{
    if (isset($_POST['theDate']))
    {
      $sql = "SELECT * from appointments WHERE PPS = '$_SESSION[pps]'" ;

      if ($result = $conn->query($sql))
      {

        if ($result -> num_rows > 0)
        {
          echo "<h2>You already have an appointment set</h2>" ;
        }
        else
        {
          #Check that the time entered is between the opening hours of testing centres
          $timeEntered = $_POST['theTime'] ;
          $check = FALSE ;
          if ($timeEntered >= "09:30:00" && $timeEntered <= "18:30:00")
          {
            $check = TRUE ;
          }
          if ($check === FALSE)
          {
            echo "<h2>Appointment can only be set between 9:30 AM and 6:30 PM</h2>" ;
          }
          #Check that the date entered is at least the day after today
          else
          {
            $dateEntered = $_POST['theDate'] ;
            $todaysDate = date("Y-m-d") ;
            $formattedDate = date("Y-m-d", strtotime($dateEntered)) ;
            if ($formattedDate <= $todaysDate)
            {
              $check = FALSE ;
            }
            $check = TRUE ;
            if ($check === FALSE)
            {
              echo "<h2>Date for Appointment must be a future date</h2>" ;
            }
            else
            {
              #Retrieve the ivs from the sql database
              $sql = "SELECT * from appointments";
              if ($result = $conn->query($sql))
              {
                #Encrypt the data
                $iv = random_bytes(16) ;
                $escaped_date = $conn -> real_escape_string($dateEntered) ;
                $escaped_time = $conn -> real_escape_string($timeEntered) ;
                $escaped_location = $conn -> real_escape_string($_POST['location']) ;

                #Encrypt the Data
                $encrypted_date = openssl_encrypt($escaped_date, $cipher, $key, OPENSSL_RAW_DATA, $iv);
                $encrypted_time = openssl_encrypt($escaped_time, $cipher, $key, OPENSSL_RAW_DATA, $iv);
                $encrypted_location = openssl_encrypt($escaped_location, $cipher, $key, OPENSSL_RAW_DATA, $iv);

                #Change the data to hex
                $date_hex = bin2hex($encrypted_date) ;
                $time_hex = bin2hex($encrypted_time) ;
                $location_hex = bin2hex($encrypted_location) ;
                $iv_hex = bin2hex($iv) ;

                if ($result -> num_rows == 0)#There are currently no registered appointments
                {


                  #Execute the Query
                  $prepStatement = $conn->prepare("INSERT INTO appointments (date, time, branch, PPS, iv) VALUES (?, ?, ?, ?, ?)") ;
                  $prepStatement ->bind_param("sssss", $date_hex, $time_hex, $location_hex, $_SESSION["pps"], $iv_hex) ;
                  if ($prepStatement->execute() === TRUE)
                  {
                    echo "<h2>Appointment Set</h2>" ;
                    $msg = "Your apppointment is at ".$escaped_date." at ".$escaped_time." at the ".$escaped_location." " ;
                    $sql = "SELECT * from users WHERE PPS = '$_SESSION[pps]'" ;
                    $result = $conn->query($sql) ;
                    $row = $result->fetch_assoc() ;
                    $encrypted_email = hex2bin($row["email"]) ;
                    $theIV = hex2bin($row["iv"]) ;
                    $decrypt_email = openssl_decrypt($encrypted_email, $cipher, $key, OPENSSL_RAW_DATA, $theIV) ;
                    $mail->addAddress($decrypt_email);
                    $mail->Body = "<b>$msg</b>";

                    try {
                        $mail->send();

                    } catch (Exception $e) {
                        echo "Mailer Error: " . $mail->ErrorInfo;
                    }

                  }
                  else
                  {
                    die("Error: ".$prepStatement->error) ;
                  }

                }
                else
                {
                  $prepStatement2 = $conn->prepare("SELECT * from appointments WHERE date = ? AND time = ? AND branch = ?") ;
                  $prepStatement2 -> bind_param("sss", $date_hex, $time_hex, $location_hex) ;
                  if ($prepStatement2->execute() === TRUE)
                  {
                    $prepStatement2->store_result() ;
                    if ($prepStatement2->num_rows > 0)
                    {
                      echo "<h2>An appointment for this date, time and location has already been booked</h2>" ;
                    }
                    else
                    {
                      #Execute the Query
                      $prepStatement = $conn->prepare("INSERT INTO appointments (date, time, branch, PPS, iv) VALUES (?, ?, ?, ?, ?)") ;
                      $prepStatement ->bind_param("sssss", $date_hex, $time_hex, $location_hex, $_SESSION["pps"], $iv_hex) ;
                      if ($prepStatement->execute() === TRUE)
                      {
                        echo "<h2>Appointment Set</h2>" ;
                        $msg = "Your apppointment is at ".$escaped_date." at ".$escaped_time." at the ".$escaped_location." " ;
                        $sql = "SELECT * from users WHERE PPS = '$_SESSION[pps]'" ;
                        $result = $conn->query($sql) ;
                        $row = $result->fetch_assoc() ;
                        $encrypted_email = hex2bin($row["email"]) ;
                        $theIV = hex2bin($row["iv"]) ;
                        $decrypt_email = openssl_decrypt($encrypted_email, $cipher, $key, OPENSSL_RAW_DATA, $theIV) ;
                        $mail->addAddress($decrypt_email);
                        $mail->Body = "<b>$msg</b>";

                        try {
                            $mail->send();
                        } catch (Exception $e) {
                            echo "Mailer Error: " . $mail->ErrorInfo;
                        }
                      }
                      else
                      {
                        die("Error: ".$prepStatement->error) ;
                      }
                    }
                  }
                  else
                  {
                    die("Error: ".$prepStatement->error) ;
                  }

                }
              }

            }#Else Statement

          }#Else Statement
        }

      }
      else
      {
        die('Error using database: ' . $conn->error);
      }
    }
    echo "<h2>Book a Covid Appointment</h2>" ;
    echo "<div class='formstyle'><form method='post' action='createAppointment.php'>
          <label for='theDate' class='inline'>Date of Appointment</label>
          <input type='date' name='theDate' id='theDate' required class='inputsize'><br>
          <label for='theTime' class='inline'>Time of Appointment</label>
          <input type='time' name='theTime' id='theTime' required class='inputsize'><br><br>
          <label for='location' class='inline'>Select a Test Centre</label>
          <select name='location' id='location'>
          <option value='Wexford Test Centre'>Wexford Test Centre</option>
          <option value='Carlow Test Centre'>Carlow Test Centre</option>
          <option value='Waterford Test Centre'>Waterford Test Centre</option>
          </select>
          <br>
          <input type='submit' value='Submit Appointment'></form></div>";
}
else {
  include "index.php" ;
}
 ?>

</body>
</html>
