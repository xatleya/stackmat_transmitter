<!DOCTYPE html>


<html lang="fr">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>

    <body>

<?php
require_once "config.php";

function get_page($url, $data, $url_dest){
    $fp = fopen("cookie.txt", "w");
    fclose($fp);
    $login = curl_init();
    curl_setopt($login, CURLOPT_COOKIEJAR, "cookie.txt");
    curl_setopt($login, CURLOPT_COOKIEFILE, "cookie.txt");
    curl_setopt($login, CURLOPT_TIMEOUT, 40000);
    curl_setopt($login, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($login, CURLOPT_URL, $url);
    curl_setopt($login, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
    curl_setopt($login, CURLOPT_REFERER, "http://cubecomps.com/admin.php");
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
    curl_setopt($sh, CURLOPT_URL, $url_dest); 
    curl_setopt($sh, CURLOPT_REFERER, "http://cubecomps.com/admin.php");
    return curl_exec($sh) ;
    curl_close($sh);
}
             

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function get_left_string($string, $end){
    $pos = strpos($string, $end);
    if ($pos == 0) return '';
    return substr($string, 0, $pos);
}

function get_right_string($string, $start){
    $pos = strpos($string, $start);
    if ($pos == 0) return '';
    return substr($string, $pos+strlen($start));
}

function get_events($url,$data,$comp_id){
    $output = get_page($url, $data, "http://cubecomps.com/events.php");

    $connect = new mysqli(SQL_SERVER, SQL_USER, SQL_PASSWORD, SQL_DBNAME);
    if (mysqli_connect_errno()) {
        printf("Échec de la connexion : %s\n", mysqli_connect_error());
        exit();
    }

    $comp_title = get_string_between($output, "<TITLE>", '</TITLE>');
    //echo $comp_title;
    //echo "<br/>";
    $connect->query("UPDATE users SET title='".$comp_title."' WHERE id='".$comp_id."' ;");


    //$events_table = get_string_between($output, "class=header>", "Add event");
    $events_table = get_string_between($output, "class=header>", "file:");
    $events_count = mb_substr_count($events_table, "class=header");

    for($i=0;$i<$events_count;$i++){
        $r2 = 0;
        $r3 = 0;
        $r4 = 0;

        $r1_format = "";
        $r2_format = "";
        $r3_format = "";
        $r4_format = "";

        $r1_open = 0;
        $r2_open = 0;
        $r3_open = 0;
        $r4_open = 0;

        $current_table = get_left_string($events_table, "class=header>");

        if(strpos($current_table, "cutoff") == false){
            $time_limit = "-1";
        }
        else if (strpos($current_table, "no cutoff") == false) {
            $time_limit = get_string_between($current_table, "cutoff ", " </span>");
        }
        else if(strpos($current_table, "cutoff") == true){
            $time_limit = "0";
        }

        $current_event = get_left_string($current_table, "</div>");
        
        echo $current_event;

        //echo " / ";

        //echo $time_limit;

        //echo " / ";
        $rounds_count = mb_substr_count($current_table, "<b>");
        //echo $rounds_count;

        //echo " / ";
        $current_event = get_left_string($current_table, "</div>");
        $rounds_count = mb_substr_count($current_table, "<b>");
        for($j=0;$j<$rounds_count;$j++){
            if($j==0){
                if(strpos($current_table, "round=1\");'>[open]") == false){
                    $r1_open = 1;
                }

                if($time_limit == -1){
                    $current_format = get_string_between($current_table, "<span", "people");
                    $current_format = get_string_between($current_format, "'> ", " </span>");
                }
                else{
                    $current_format = get_string_between($current_table, "cutoff", "people");
                    $current_format = get_string_between($current_format, "'> ", " </span>");
                }
            }
            else{
                if($j==1){
                    if(strpos($current_table, "round=2\");'>[open]") == false && $r1_open==1){
                        $r2_open = 1;
                    }
                    $r2 = 1;
                    if($rounds_count==3 || $rounds_count==4){
                        $round_name = "Second Round";
                    }
                    else if($rounds_count==2){
                        $round_name = "Final";
                    }
                }
                else if($j==2){
                    if(strpos($current_table, "round=3\");'>[open]") == false && $r2_open==1){
                        $r3_open = 1;
                    }
                    $r3 = 1;
                    if($rounds_count==4){
                        $round_name = "Semi Final";
                    }
                    else{
                        $round_name = "Final";
                    }
                }
                else if($j==3){
                    if(strpos($current_table, "round=4\");'>[open]") == false && $r3_open==1){
                        $r4_open = 1;
                    }
                    $r4 = 1;
                    $round_name = ">Final<";
                }
                $current_format = get_right_string($current_table, $round_name);
                $current_format = get_string_between($current_format, "<span", " </span>");
                $current_format = get_right_string($current_format, "'> ");      
            }
            $format_id = $connect->query("SELECT id FROM formats WHERE name='".$current_format."';");
            $format_id = $format_id->fetch_assoc();
            $format_id = $format_id["id"];
            if($j == 0){
                $r1_format = $format_id;
            }
            else if($j == 1){
                $r2_format = $format_id;
            }
            else if($j == 2){
                $r3_format = $format_id;
            }
            else if($j == 3){
                $r4_format = $format_id;
            }
            //echo $current_format;
            //echo " / ";
        }
        
        $current_event = addslashes ($current_event);
        $event_id = $connect->query("SELECT id FROM categories WHERE name='".$current_event."';");

        $event_id = $event_id->fetch_assoc();
        $event_id = $event_id["id"];

        $select = $connect->query("SELECT id FROM events".$comp_id." WHERE id='".$event_id."';");
        $select = $select->num_rows;
        if($select > 0){
            if($r4 == 1){
                $connect->query("UPDATE events".$comp_id." SET timelimit='".$time_limit."', r1='1', r1_format='".$r1_format."', r1_open='".$r1_open."',  r2='".$r2."', r2_format='".$r2_format."', r2_open='".$r2_open."', r3='".$r3."', r3_format='".$r3_format."', r3_open='".$r3_open."', r4='".$r4."', r4_format='".$r4_format."', r4_open='".$r4_open."'  WHERE id='".$event_id."';");
            }
            else if($r3 == 1){
                $connect->query("UPDATE events".$comp_id." SET timelimit='".$time_limit."', r1='1', r1_format='".$r1_format."', r1_open='".$r1_open."',  r2='".$r2."', r2_format='".$r2_format."', r2_open='".$r2_open."', r3='".$r3."', r3_format='".$r3_format."', r3_open='".$r3_open."' WHERE id='".$event_id."';");
            }
            else if($r2 == 1){
                $connect->query("UPDATE events".$comp_id." SET timelimit='".$time_limit."', r1='1', r1_format='".$r1_format."', r1_open='".$r1_open."', r2='".$r2."', r2_format='".$r2_format."', r2_open='".$r2_open."' WHERE id='".$event_id."';");
            }
            else {
                $connect->query("UPDATE events".$comp_id." SET timelimit='".$time_limit."', r1='1', r1_format='".$r1_format."', r1_open='".$r1_open."' WHERE id='".$event_id."';");
            }
        }
        else{
            if($r4 == 1){
                $connect->query("INSERT INTO events".$comp_id."(id, timelimit, r1, r1_format, r1_open, r2, r2_format, r2_open, r3, r3_format, r3_open, r4, r4_format, r4_open) VALUES ('".$event_id."', '".$time_limit."', '1', '".$r1_format."', '".$r1_open."', '".$r2."', '".$r2_format."', '".$r2_open."', '".$r3."', '".$r3_format."', '".$r3_open."', '".$r4."', '".$r4_format."', '".$r4_open."');");
            }
            else if($r3 == 1){
                $connect->query("INSERT INTO events".$comp_id."(id, timelimit, r1, r1_format, r1_open, r2, r2_format, r2_open, r3, r3_format, r3_open) VALUES ('".$event_id."', '".$time_limit."', '1', '".$r1_format."', '".$r1_open."', '".$r2."', '".$r2_format."', '".$r2_open."', '".$r3."', '".$r3_format."', '".$r3_open."');");
            }
            else if($r2 == 1){
                $connect->query("INSERT INTO events".$comp_id."(id, timelimit, r1, r1_format, r1_open, r2, r2_format, r2_open) VALUES ('".$event_id."', '".$time_limit."', '1', '".$r1_format."', '".$r1_open."', '".$r2."', '".$r2_format."', '".$r2_open."');");
            }
            else {
                $connect->query("INSERT INTO events".$comp_id."(id, timelimit, r1, r1_format, r1_open) VALUES ('".$event_id."', '".$time_limit."', '1', '".$r1_format."', '".$r1_open."');");
            }
        }

        $events_table = get_right_string($events_table, "class=header>");
        echo "</br>";
    }
    $connect->close();
}   

function get_competitors($url, $data, $compe_id){
    $output = get_page($url, $data, "http://cubecomps.com/competitors.php");

    //echo $output;

    $connect = new mysqli(SQL_SERVER, SQL_USER, SQL_PASSWORD, SQL_DBNAME);
    if (mysqli_connect_errno()) {
        printf("Échec de la connexion : %s\n", mysqli_connect_error());
        exit();
    }

    $comp_table = get_string_between($output, ">m/f<", "id=WCAid");
    $comp_table = get_right_string($comp_table, "</tr>");
    $comp_count = mb_substr_count($comp_table, "comprow");
    for($i=0;$i<$comp_count;$i++){
        $current_comp = get_string_between($comp_table, "<tr", '</tr>');
        
        $comp_id = get_string_between($current_comp, "class=col_cl>", "</div>");
        //echo $comp_id;

        //echo " / ";

        $current_comp = get_right_string($current_comp, $comp_id);
        $comp_WCAid = get_string_between($current_comp, "class=col_wi>", "</div>");
        if($comp_WCAid == ""){
            $comp_WCAid = "class=col_wi>";
        }
        else{
            //echo $comp_WCAid;
        }
        

        //echo " / ";

        $current_comp = get_right_string($current_comp, $comp_WCAid);
        $comp_name = get_string_between($current_comp, "class=col_nm>", "</div>");
        $comp_name = get_string_between($comp_name, "'>", "</a>");
        //echo $comp_name;

        //echo " / ";

        $current_comp = get_right_string($current_comp, $comp_name);
        $comp_birthday = get_string_between($current_comp, "class=col_bd>", "</div>");
        //echo $comp_birthday;

        //echo " / ";

        $current_comp = get_right_string($current_comp, $comp_birthday);
        $comp_country = get_string_between($current_comp, "class=col_ct>", "</div>");
        //echo $comp_country;

        //echo " / ";

        $current_comp = get_right_string($current_comp, $comp_country);
        $comp_gender = get_string_between($current_comp, "class=col_gd", "</div>");
        $comp_gender = get_right_string($comp_gender, ">");
        //echo $comp_gender;



        $comp_table = get_right_string($comp_table, "</tr>");
        //echo "</br>";

        if($comp_WCAid == "class=col_wi>"){
            $comp_WCAid = "";
        }

        $select_id = $connect->query("SELECT id FROM competitors".$compe_id." WHERE id='".$comp_id."';");
        $select_id = $select_id->num_rows;

        if($select_id > 0){
            $connect->query("UPDATE competitors".$compe_id." SET WCAid='".$comp_WCAid."', name='".$comp_name."', country='".$comp_country."', birthday='".$comp_birthday."', gender='".$comp_gender."' WHERE id='".$comp_id."';");
        }
        else{
            $connect->query("INSERT INTO competitors".$compe_id."(id, WCAid, name, country, birthday, gender) VALUES ('".$comp_id."', '".$comp_WCAid."', '".$comp_name."', '".$comp_country."', '".$comp_birthday."', '".$comp_gender."');");
        }
    }
    $connect->close();
}    

function get_registrations($url, $data, $comp_id){
    $connect = new mysqli(SQL_SERVER, SQL_USER, SQL_PASSWORD, SQL_DBNAME);
    if (mysqli_connect_errno()) {
        printf("Échec de la connexion : %s\n", mysqli_connect_error());
        exit();
    }

    $select_events_id = $connect->query("SELECT id FROM events".$comp_id." WHERE r1_open='1' AND r2_open='0' AND r3_open='0' AND r4_open='0';");
    add_registration($url, $data, $comp_id, $select_events_id, 1, $connect);

    $select_events_id = $connect->query("SELECT id FROM events".$comp_id." WHERE r2_open='1' AND r3_open='0' AND r4_open='0';");
    add_registration($url, $data, $comp_id, $select_events_id, 2, $connect);

    $select_events_id = $connect->query("SELECT id FROM events".$comp_id." WHERE r3_open='1' AND r4_open='0';");
    add_registration($url, $data, $comp_id, $select_events_id, 3, $connect);

    $select_events_id = $connect->query("SELECT id FROM events".$comp_id." WHERE r4_open='1';");
    add_registration($url, $data, $comp_id, $select_events_id, 4, $connect);

    $connect->close();
}  

function add_registration($url, $data, $comp_id, $select, $round, $connect){
    while($event_id = $select->fetch_assoc()){
        $output = get_page($url, $data, "http://cubecomps.com/results.php?cat_id=".$event_id["id"]);
        $comps_in_event = get_string_between($output, "var sList = [", "];");

        $competitors_count = mb_substr_count($comps_in_event, "[");
        for($i=0;$i<$competitors_count;$i++){
            $current_comp_id = get_string_between($comps_in_event, "[", ",");
            if($i != $competitors_count-1){
                $comps_in_event = get_right_string($comps_in_event, "],");
            }
            //echo $current_comp_id;
            //echo " / ";

            $select_test = $connect->query("SELECT cat_id FROM registrations".$comp_id." WHERE cat_id='".$event_id["id"]."' AND round='".$round."' AND comp_id='".$current_comp_id."';");
            $select_test = $select_test->num_rows;

            if($select_test <= 0){
                $connect->query("INSERT INTO registrations".$comp_id."(cat_id, round, comp_id) VALUES ('".$event_id["id"]."', '".$round."', '".$current_comp_id."');");
            }
        }
        //echo "<br/>";
    }

    //echo "<br/>";
}     
 
?>

<?php
    function get_info($id, $password, $comp_id){
        $url_identification = "http://cubecomps.com/identification.php";
        $url_data = "id=".$id."&pw=".$password;
        get_events($url_identification, $url_data, $comp_id);
        get_competitors($url_identification, $url_data, $comp_id);
        get_registrations($url_identification, $url_data, $comp_id);
    }
?>

        </body>
</html>