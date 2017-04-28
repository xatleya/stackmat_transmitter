<?php
	require_once "get_comp_info.php";

	session_start();
	$id = $_SESSION['id'];
	$pass = $_SESSION['pass'];
	$cubecomps_id = $_SESSION['cubecomps_id'];

	get_info($cubecomps_id, $pass, $id);
	echo "Update done";
	header( "Refresh:2; url=profile.php", true, 303);
?>