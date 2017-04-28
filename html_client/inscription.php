<?php
	require_once 'config.php';
	require_once "get_comp_info.php";

    if(isset($_POST['login']) && isset($_POST['admin_password']) && isset($_POST['judge_password']) && isset($_POST['cubecomps_id'])){

		$login = addslashes($_POST['login']);
		$admin_password = md5(addslashes($_POST['admin_password']));
		$judge_password = md5(addslashes($_POST['judge_password']));
		$cubecomps_id = (addslashes($_POST['cubecomps_id']));

		$connect = new mysqli(SQL_SERVER, SQL_USER, SQL_PASSWORD, SQL_DBNAME);

		if (mysqli_connect_errno()) {
		    printf("Échec de la connexion : %s\n", mysqli_connect_error());
		    exit();
		}

		$selectlogin = $connect->query("SELECT login FROM users WHERE login='".$login."';");
		$selectlogin = $selectlogin->fetch_assoc();

		if($selectlogin == null){
			
			if(TRUE == $connect->query("INSERT INTO users(login, admin_pw, judge_pw, cubecomps_id) VALUES ('".$login."','".$admin_password."','".$judge_password."', '".$cubecomps_id."');")){
				echo "User created successfully !";
				$current_id = $connect->query("SELECT id FROM users WHERE login='".$login."';");
				$current_id = $current_id->fetch_assoc();

				$eventstable = "events".$current_id["id"];
				$compstable = "competitors".$current_id["id"];
				$regstable = "registrations".$current_id["id"];
				$timestable = "times".$current_id["id"];

				require_once "inc_initdb.php";

				$selectlogin = $connect->query("SELECT id FROM users WHERE login='$login';");
				$selectlogin = $selectlogin->fetch_assoc();
				$session_id = $selectlogin["id"];
				get_info($cubecomps_id, $_POST['admin_password'], $session_id);
			}
			else{
				echo $connect->error;
			}
			$connect->close();
			header( "Refresh:1; url=index.php", true, 303);
		}
		else{
			echo "Login already taken<br />";
			$connect->close();
			header( "Refresh:1; url=index.php", true, 303);
		}
    }
?>
