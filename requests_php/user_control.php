<?php

include_once 'connection.php';
	
	class User {		
		private $db;
		private $connection;
		
		function __construct() {
			$this -> db = new DB_Connection();
			$this -> connection = $this->db->getConnection();
		}
		
		public function does_user_exist($login,$password)
		{
			$query = "Select * from users where login='$login' and judge_pw = '$password' ";
			$result = mysqli_query($this->connection, $query);
			if(mysqli_num_rows($result)>0){
				$json['success'] = ' Welcome '.$login;
				echo json_encode($json);
				mysqli_close($this -> connection);
			}else{
				$json['error'] = 'Wrong password';
				echo json_encode($json);
				mysqli_close($this->connection);
			}
			
		}
		
	}
	
	
	$user = new User();
	if(isset($_POST['login'],$_POST['password'])) {
		$login = $_POST['login'];
		$password = $_POST['password'];
		
		if(!empty($login) && !empty($password)){
			
			$encrypted_password = md5(addslashes($password));
			$user-> does_user_exist($login,$encrypted_password);
			
		}else{
			echo json_encode("you must type both inputs");
		}
		
	}

?>
