<?php
	require_once 'config.php';

	session_start();

	if(isset($_SESSION['id'])){
		$session_id = $_SESSION['id'];
		
		$connect = new mysqli(SQL_SERVER, SQL_USER, SQL_PASSWORD, SQL_DBNAME);

		if (mysqli_connect_errno()) {
		    printf("Échec de la connexion : %s\n", mysqli_connect_error());
		    exit();
		}

		$selectlogin = $connect->query("SELECT id FROM users WHERE id='$session_id';");
		$selectcount = $selectlogin->num_rows;

		if($selectcount > 0){
			header('Refresh: 0; URL=profile.php');
		}
		
	}

?>

<html>
	<head>
	</head>
	
	<body>
		<?php
			if(empty($_SESSION['id'])){
				?>
				<form id="inscription_fields" action="inscription.php" method="POST">
					 <input type="text" name="login" placeholder="Login" required></input>
					 <input type="password" name="admin_password" placeholder="admin_Password" required></input>
					 <input type="password" name="judge_password" placeholder="judge_Password" required></input>
					 <input type="text" name="cubecomps_id" placeholder="Cubecomps_id" required></input>
					 <input type="button" name="button" value="Register" />
				</form>	

				<script>
					window.onload = function () {
						var form = document.getElementById("inscription_fields");
				        form.button.onclick = function (){
						    for(var i=0; i < form.elements.length; i++){
						        if(form.elements[i].value === '' && form.elements[i].hasAttribute('required')){
							        alert("There are some required fields!");
							        return false;
							    }
							}
						    form.submit();
						}; 
					};
				</script>	
						
				<br />
			

				<form id="login_fields" action="login.php" method="POST">
					 <input type="text" name="login" placeholder="Login" required></input>
					 <input type="password" name="password" placeholder="admin_Password" required></input>
					 <input type="submit" value="Login" />
				</form>


	
				<?php
			}
		?>
	</body>

</html>
