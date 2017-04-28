<?php
	
include_once 'connection.php';

	class Competitors{
		private $db;
		private $connection;
		
		function __construct() {
			$this -> db = new DB_Connection();
			$this -> connection = $this->db->getConnection();
		}

		public function get_open_round($num, $comp_id, $event_id){
			$select_round = "select r".$num."_open from events".$comp_id." where id='".$event_id."'";
			$round_open = mysqli_query($this->connection, $select_round);
			$round_open = mysqli_fetch_assoc($round_open);
			return $round_open = $round_open['r'.$num.'_open'];
		}

		public function get_competitors($login, $event){
			$select = "select id from users where login = '$login'";
			$comp_id = mysqli_query($this->connection, $select);
			$comp_id = mysqli_fetch_assoc($comp_id);
			$comp_id = $comp_id['id'];

			$select_event_id = "select id from categories where name='".$event."'";
			$event_id = mysqli_query($this->connection, $select_event_id);
			$event_id = mysqli_fetch_assoc($event_id);
			$event_id = $event_id['id'];

			$round_open = $this->get_open_round("4", $comp_id, $event_id);
			if($round_open == "1"){
				$round = "4";
			}
			else{
				$round_open = $this->get_open_round("3", $comp_id, $event_id);
				if($round_open == "1"){
					$round = "3";
				}
				else{
					$round_open = $this->get_open_round("2", $comp_id, $event_id);
					if($round_open == "1"){
						$round = "2";
					}
					else{
						$round = "1";
					}
				}
			}
			$json["round"] = $round;

			$select_format_id = "select r".$round."_format, timelimit from events".$comp_id." where id='".$event_id."'";
			$select_format_id = mysqli_query($this->connection, $select_format_id);
			$select_format_id = mysqli_fetch_assoc($select_format_id);
			$format_id = $select_format_id['r'.$round.'_format'];
			$timelimit = $select_format_id['timelimit'];

			if($timelimit == "0"){
				$timelimit = "-1";
			}
			elseif($timelimit != -1){
				$timelimit = "0".$timelimit;
				$timelimit = substr_replace($timelimit,":", 5, 1);
			}

			$select_format = "select name, times from formats where id='".$format_id."'";
			$format = mysqli_query($this->connection, $select_format);
			$format = mysqli_fetch_assoc($format);
			$format_name = $format['name'];
			$format_times = $format['times'];

			$json["format"] = $format_name;
			$json["times"] = $format_times;
			$json["timelimit"] = $timelimit;


			$select_registrations = "select comp_id from registrations".$comp_id." where cat_id='".$event_id."' and round='".$round."'";
			$registrations = mysqli_query($this->connection, $select_registrations);
			$competitiors_count = mysqli_num_rows($registrations);
			$json["count"] = $competitiors_count;
			$index = 0;

			while($reg = mysqli_fetch_assoc($registrations)){
				$c_key = "comp".$index;
				$select_comp = "select name from competitors".$comp_id." where id='".$reg['comp_id']."'";
				$comp_name = mysqli_query($this->connection, $select_comp);
				$comp_name = mysqli_fetch_assoc($comp_name);
				$comp_name = $comp_name['name'];
				$json[$c_key] = $comp_name;

				$index = $index+1;
			}

			echo json_encode($json);
			mysqli_close($this->connection);

		}
	}
?>

<?php
	$competitors = new Competitors();
	if(isset($_POST['login'],$_POST['event'])){
		$login = addslashes ($_POST['login']);
		$event = addslashes ($_POST['event']);

		if(!empty($login) && !empty($event)){
			$competitors->get_competitors($login, $event);
		}
	}
?>