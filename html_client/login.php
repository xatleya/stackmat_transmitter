<?php
	require_once 'config.php';

    if(isset($_POST['login']) && isset($_POST['password'])){

		$login = addslashes($_POST['login']);
		$password = md5(addslashes($_POST['password']));

		$connect = new mysqli(SQL_SERVER, SQL_USER, SQL_PASSWORD, SQL_DBNAME);

		if (mysqli_connect_errno()) {
		    printf("Échec de la connexion : %s\n", mysqli_connect_error());
		    exit();
		}

		$selectlogin = $connect->query("SELECT id, cubecomps_id  FROM users WHERE login='$login' AND admin_pw='$password';");
		$selectcount = $selectlogin->num_rows;
		

		if($selectcount > 0){
			$selectlogin = $selectlogin->fetch_assoc();
			$session_id = $selectlogin["id"];
			$pass = $_POST['password'];
			$cubecomps_id = $selectlogin["cubecomps_id"];
			session_start();
			$_SESSION['id'] = $session_id;
			$_SESSION['pass'] = $pass;
			$_SESSION['cubecomps_id'] = $cubecomps_id;
			echo "Connected";
			$connect->close();
			header('Refresh: 1; URL=index.php');
		}
		else{
			echo "Bad login or password man !";
			$connect->close();
			header('Refresh: 2; URL=index.php');
		}
    }
?>
