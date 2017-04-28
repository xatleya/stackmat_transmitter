<?php
  require_once 'config.php';

  session_start();
  $session_id = $_SESSION['id'];

  $connect = new mysqli(SQL_SERVER, SQL_USER, SQL_PASSWORD, SQL_DBNAME);

  if (mysqli_connect_errno()) {
      printf("Échec de la connexion : %s\n", mysqli_connect_error());
      exit();
  }

  $selectquery = $connect->query("SELECT login FROM users WHERE id='$session_id';");
  $selectresult = $selectquery->fetch_assoc();
  $username = $selectresult["login"];

  echo "Hello ".$username;
  $connect->close();

?>
<br/>
<a href="deconnect.php">Deconnection</a>
<form id="login_fields" action="update_info.php" method="POST">
    <input type="submit" value="update_info" />
</form>
