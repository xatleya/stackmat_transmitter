<?php

include_once 'connection.php';

    class Score{
        private $db;
        private $connection;
        
        function __construct() {
            $this -> db = new DB_Connection();
            $this -> connection = $this->db->getConnection();
        }

        public function send_score($login, $pass, $comp, $cat, $round, $times, $times_count){
            $select = "select id, cubecomps_id from users where login = '".$login."'";
            $select = mysqli_query($this->connection, $select);
            $select = mysqli_fetch_assoc($select);
            $competition_id = $select['id'];
            $cubecomps_id = $select['cubecomps_id'];

            $url_identification = "http://test.cubecomps.com/identification.php";
            $url_data = "id=".$cubecomps_id."&pw=".$pass."";

            $select_comp = "select id from competitors".$competition_id." where name='".$comp."'";
            $select_comp = mysqli_query($this->connection, $select_comp);
            $select_comp = mysqli_fetch_assoc($select_comp);
            $comp_id = $select_comp['id'];

            $select_cat = "select id from categories where name='".$cat."'";
            $select_cat = mysqli_query($this->connection, $select_cat);
            $select_cat = mysqli_fetch_assoc($select_cat);
            $cat_id = $select_cat['id'];

            $output = login($url_identification, $url_data, $comp_id, $cat_id, $round, $times, $times_count);
            if($output == "OK"){
                /*$insert_time = "insert into times".$competition_id."(cat_id, round, comp_id, t1, t2, t3, t4, t5) values(".$cat_id.", ".$round.", ".$comp_id.", ".$times[0].", ".$times[1].", ".$times[2].", ".$times[3].", ".$times[4].")";
                mysqli_query($this->connection, $insert_time);*/

                $json['success'] = "success";
                echo json_encode($json);
            }
            else{
                $json['success'] = "error";
                echo json_encode($json);
            }
        }
    }

    function login($url, $data, $comp_id, $cat_id, $round, $times, $times_count){
        $fp = fopen("cookie.txt", "w");
        fclose($fp);
        $login = curl_init();
        curl_setopt($login, CURLOPT_COOKIEJAR, "cookie.txt");
        curl_setopt($login, CURLOPT_COOKIEFILE, "cookie.txt");
        curl_setopt($login, CURLOPT_TIMEOUT, 40000);
        curl_setopt($login, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($login, CURLOPT_URL, $url);
        curl_setopt($login, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($login, CURLOPT_REFERER, "http://test.cubecomps.com/admin.php");
        curl_setopt($login, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($login, CURLOPT_POST, TRUE);
        curl_setopt($login, CURLOPT_POSTFIELDS, $data);
        ob_start();
        curl_exec ($login);
        ob_end_clean();
        curl_close ($login);
        unset($login);  

        $sh = curl_init(); 
        curl_setopt($sh, CURLOPT_RETURNTRANSFER,1); 
        curl_setopt($sh, CURLOPT_COOKIEFILE, "cookie.txt"); 
        curl_setopt($sh, CURLOPT_URL,"http://test.cubecomps.com/results.php?cat_id=".$cat_id); 
        curl_setopt($sh, CURLOPT_REFERER, "http://test.cubecomps.com/admin.php");
        curl_exec($sh) ;
        curl_close($sh);

        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); 
        curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt"); 
        if($times_count == "1"){
            curl_setopt($ch, CURLOPT_URL,"test.cubecomps.com/settimes.php?comp_id=".$comp_id."&cat_id=".$cat_id."&round=".$round."&t1=".$times[0]." HTTP/1.1");
        }
        elseif ($times_count == "2") {
            curl_setopt($ch, CURLOPT_URL,"test.cubecomps.com/settimes.php?comp_id=".$comp_id."&cat_id=".$cat_id."&round=".$round."&t1=".$times[0]."&t2=".$times[1]." HTTP/1.1");
        }
        elseif ($times_count == "3") {
            curl_setopt($ch, CURLOPT_URL,"test.cubecomps.com/settimes.php?comp_id=".$comp_id."&cat_id=".$cat_id."&round=".$round."&t1=".$times[0]."&t2=".$times[1]."&t3=".$times[2]." HTTP/1.1");
        }
        elseif ($times_count == "5") {
            curl_setopt($ch, CURLOPT_URL,"test.cubecomps.com/settimes.php?comp_id=".$comp_id."&cat_id=".$cat_id."&round=".$round."&t1=".$times[0]."&t2=".$times[1]."&t3=".$times[2]."&t4=".$times[3]."&t5=".$times[4]." HTTP/1.1");
        }
        /*curl_setopt($ch, CURLOPT_URL,"test.cubecomps.com/settimes.php?comp_id=1&cat_id=1&round=1&t1=000:00.01&t2=000:00.01&t3=000:00.01&t4=000:00.01&t5=000:00.01 HTTP/1.1");*/ 
        curl_setopt($ch, CURLOPT_REFERER, "http://test.cubecomps.com/admin.php");
        return curl_exec($ch) ;
        curl_close($ch);

    }       

    function check_dnf_dns($time){
    	if($time == "DNF" || $time == "dnf"){
        	$time = "DNF";
        }
        else if($time == "DNS" || $time == "dns"){
        	$time = "DNS";
        }
        else{
        	$time = substr_replace("0".$time,".", 6, 1);
        }
        return $time;
    }



?>     

<?php
    $score = new Score();
    if(isset($_POST['login'], $_POST['password'], $_POST['comp'], $_POST['event'], $_POST['round'], $_POST['format'], $_POST['t1'], $_POST['t2'], $_POST['t3'], $_POST['t4'], $_POST['t5'])){

        $login = addslashes($_POST['login']);
        $password = $_POST['password'];
        $comp = addslashes($_POST['comp']);
        $event = addslashes($_POST['event']);
        $round = $_POST['round'];
        $format = $_POST['format'];

        $t1 = check_dnf_dns($_POST['t1']);
        $t2 = check_dnf_dns($_POST['t2']);
        $t3 = check_dnf_dns($_POST['t3']);
        $t4 = check_dnf_dns($_POST['t4']);
        $t5 = check_dnf_dns($_POST['t5']);

        if($format == "1"){
            $tab = array($t1);
        }
        elseif ($format == "2") {
            $tab = array($t1, $t2);
        }
        elseif ($format == "3") {
            $tab = array($t1, $t2, $t3);
        }
        elseif ($format == "5") {
            $tab = array($t1, $t2, $t3, $t4, $t5);
        }
        if(!empty($login) && !empty($password) && !empty($comp) && !empty($event) && !empty($round) && !empty($format) && !empty($tab)){
            $score->send_score($login, $password, $comp, $event, $round, $tab, $format);
        }
    }



?>        