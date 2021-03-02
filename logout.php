<?php
if (session_status() == PHP_SESSION_ACTIVE) {
}
else {
  session_start() ;
}
?>
<html>
<head>
<link rel="stylesheet" href="styling.css" type="text/css">
<title>Logout</title>
</head>
<body>
<?php
  if ($_SESSION["loggedIn"] == "SET")
  {
    session_destroy() ;
    echo "<h2> You are now logged out</h2>" ;
  }
  else
  {
    include "index.php" ;
    exit() ;
  }

 ?>
</body>
</html>
