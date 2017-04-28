<?php

include_once 'connection.php';
	
	class Events {		
		private $db;
		private $connection;
		
		function __construct() {
			$this -> db = new DB_Connection();
			$this -> connection = $this->db->getConnection();
		}

		public function get_events($login){
			$select = "select id from users where login = '$login'";
			$comp_id = mysqli_query($this->connection, $select);
			$comp_id = mysqli_fetch_assoc($comp_id);
			$comp_id = $comp_id['id'];

			$select_events = "select id from events".$comp_id." where r1_open='1'";
			$events_id = mysqli_query($this->connection, $select_events);
			$events_count = mysqli_num_rows($events_id);
			$json['count'] = $events_count;
			$index = 0;

			while($e_id = mysqli_fetch_assoc($events_id)){
				$e_key = "event".$index;
				$select_categories = "select name from categories where id='".$e_id['id']."'";
				$event_name = mysqli_query($this->connection, $select_categories);
				$event_name = mysqli_fetch_assoc($event_name);
				$event_name = $event_name['name'];
				$json[$e_key] = $event_name;

				$index = $index+1;
			}

			echo json_encode($json);
			mysqli_close($this->connection);
		}

	}

?>

<?php
	$events = new Events();
	if(isset($_POST['login'])){
		$login = $_POST['login'];

		if(!empty($login)){
			$events->get_events($login);
		}
	}
?>