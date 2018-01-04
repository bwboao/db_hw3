<link rel="stylesheet" href="all.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css?family=Arimo" rel="stylesheet" >
<link href="https://fonts.googleapis.com/earlyaccess/notosanstc.css" rel="stylesheet" >

<?php
  include("connect_database.php");
    
  function unset_session($session_to_delete){
    if(isset($_SESSION[$session_to_delete])){
      unset($_SESSION[$session_to_delete]);
    }
  }

  function store_post_as_session($session_name, $post_name){
    $_SESSION[$session_name] = $_POST[$post_name];
  }
   
  function check_is_admin(){
    include("connect_database.php");
    if(isset($_SESSION['in_use_is_admin'])){
      return $_SESSION['in_use_is_admin'];
    }
    else{
      return -1;
    }
  }
  
  function check_is_favorite($people_id, $house_id){
    $rs = favorite_show($people_id, $house_id);
    $table = $rs->fetchObject();
    $array = array();
    if($table == $array){
      return 0;
    }
    else{
      return 1;
    }
  }

  function check_is_account_exist($account){
    $table = account_show_by_account($account);
    if($table == array()){
      return 0;
    }
    else{
      return 1;
    }
  }

//house's action

  function house_show($require, $require_order, $array_for_execute){
    include("connect_database.php");
    $sql = "SELECT h.id id, h.name name, price, location, time, p.name owner, p.id owner_id FROM house AS h LEFT JOIN people_house_has AS p_h_has ON h.id = p_h_has.house_id LEFT JOIN people AS p ON p_h_has.people_id = p.id LEFT JOIN house_location_has AS h_l_has ON h.id = h_l_has.house_id LEFT JOIN normal_location AS l ON h_l_has.location_id = l.id WHERE " . $require . " " . $require_order;
    //echo $sql;
    $rs = $db->prepare($sql);
    $rs->execute($array_for_execute);
    return $rs;
  }

  function house_delete($house_id){
    include("connect_database.php");
    $sql = "DELETE FROM people_house_has WHERE house_id = $house_id;
            DELETE FROM people_house_favorite WHERE house_id = $house_id;
            DELETE FROM people_house_reserve WHERE house_id = $house_id;
            DELETE FROM house_information_has WHERE house_id = $house_id;
            DELETE FROM house_location_has WHERE house_id = $house_id;
            DELETE FROM house WHERE id = $house_id";
    $db->query($sql);
  }

  function house_update($house_id, $house_name, $house_price, $location_id){
    include("connect_database.php");
    $sql = "UPDATE house_location_has SET location_id = $location_id WHERE house_id = $house_id;
            UPDATE house SET name = :house_name, price = :house_price WHERE id = $house_id";
    $rs = $db->prepare($sql); 
    $rs->execute(array('house_name' => $house_name, 'house_price' => $house_price));
  }

  function house_create($house_name, $house_price, $location_id){
    include("connect_database.php");
    $time = date('Y-m-d');
    $sql = "INSERT INTO house (name, price, time) VALUES (:house_name, :house_price, :time);";
    $rs = $db->prepare($sql);
    $rs->execute(array('house_name' => $house_name, 'house_price' => $house_price, 'time' => $time));
    
    $sql = "SELECT MAX(id) FROM house;";
    $rs = $db->query($sql);
    $table = $rs->fetch();
    $house_id = $table[0];
    
    $sql = "INSERT INTO house_location_has (house_id, location_id) VALUES ($house_id, $location_id);
            INSERT INTO people_house_has (people_id, house_id) VALUES ($_SESSION[in_use_id], $house_id)";
    $rs = $db->query($sql);
    return $house_id;
  }

  function str_house_select_by($condition){
    switch($condition){
      case 'id':
        return "SELECT id FROM house WHERE id = :id";
      case 'name':
        return "SELECT id FROM house WHERE name = :name";
      case 'time':
        return "SELECT house_id id FROM people_house_reserve WHERE (:time_check_in <= time_check_in AND time_check_in < :time_check_out) OR (:time_check_in < time_check_out AND time_check_out <= :time_check_out) OR (time_check_in <= :time_check_in AND :time_check_out <= time_check_out)";
      case 'location':
        return "SELECT house_id id FROM house_location_has AS h_l_has LEFT JOIN normal_location AS l ON h_l_has.location_id = l.id WHERE location = :location";
      case 'owner':
        return "SELECT house_id id FROM people_house_has AS p_h_has LEFT JOIN people AS p ON p_h_has.people_id = p.id where p.name = :owner";
      case 'favorite':
        return "SELECT house_id id FROM people_house_favorite WHERE people_id = :id";
      case 'customer':
        return "SELECT house_id id FROM people_house_reserve WHERE people_id = :customer_id";
      default://use for information
        return "SELECT house_id id FROM house_information_has WHERE information_id IN $condition";
    }  
  }
  
//reserve's action

  function reserve_show($require, $require_order, $array_for_execute){
    include("connect_database.php");
    $sql = "SELECT h.id id, h.name name, price, location, time, p.name owner, p.id owner_id, time_check_in, time_check_out, p_h_reserve.people_id customer_id, p_h_reserve.id reserve_id FROM house AS h LEFT JOIN people_house_has AS p_h_has ON h.id = p_h_has.house_id LEFT JOIN people AS p ON p_h_has.people_id = p.id LEFT JOIN house_location_has AS h_l_has ON h.id = h_l_has.house_id LEFT JOIN normal_location AS l ON h_l_has.location_id = l.id LEFT JOIN people_house_reserve AS p_h_reserve ON h.id = p_h_reserve.house_id WHERE " . $require . " " . $require_order;
    //echo $sql;
    $rs = $db->prepare($sql);
    $rs->execute($array_for_execute);
    return $rs;
  }

  function reserve_create($people_id, $house_id, $time_check_in, $time_check_out){
    include("connect_database.php");
    $sql = "INSERT INTO people_house_reserve (people_id, house_id, time_check_in, time_check_out) VALUES ($people_id, $house_id, :time_check_in, :time_check_out)";
    $rs = $db->prepare($sql);
    $rs->execute(array('time_check_in' => $time_check_in, 'time_check_out' => $time_check_out));
  }

  function reserve_update($reserve_id, $time_check_in, $time_check_out){
    include("connect_database.php");
    $sql = "UPDATE people_house_reserve SET time_check_in = :time_check_in, time_check_out = :time_check_out WHERE id = $reserve_id";
    $rs = $db->prepare($sql);
    $rs->execute(array('time_check_in' => $time_check_in, 'time_check_out' => $time_check_out));
  }

  function reserve_delete($reserve_id){
    include("connect_database.php");
    $sql = "DELETE FROM people_house_reserve WHERE id = $reserve_id";
    //echo $sql;
    $db->query($sql);
  }

//information's action

  function information_show($house_id){
    include("connect_database.php");
    $sql = "SELECT information_id, information FROM house_information_has AS h_i_has LEFT JOIN normal_information AS i ON h_i_has.information_id = i.id WHERE h_i_has.house_id = $house_id ORDER BY information_id";
    $rs = $db->query($sql);
    return $rs;
  }

  function information_show_all(){
    include("connect_database.php");
    $sql = "SELECT * FROM normal_information";
    $rs = $db->query($sql);
    return $rs;
  }

  function information_delete_by_house_id($house_id){
    include("connect_database.php");
    $sql = "DELETE FROM house_information_has WHERE house_id = $house_id";
    $db->query($sql);
  }

  function information_create($house_id, $information_id){
    include("connect_database.php");
    $sql = "INSERT INTO house_information_has (house_id, information_id) VALUES ($house_id, $information_id);";
    $db->query($sql);
  }

//location's action

  function location_show($house_id){
    include("connect_database.php");
    $sql = "SELECT * FROM house_location_has WHERE house_id = $house_id";
    $rs = $db->query($sql);
    return $rs;
  }

  function location_show_all(){
    include("connect_database.php");
    $sql = "SELECT * FROM normal_location";
    $rs = $db->query($sql);
    return $rs;
  }

//favorite's action
  
  function favorite_show($people_id, $house_id){
    include("connect_database.php");
    $sql = "SELECT * FROM people_house_favorite WHERE people_id = $people_id AND house_id = $house_id";
    $rs = $db->query($sql);
    return $rs;
  }
  
  function favorite_create($people_id, $house_id){
    include("connect_database.php");
    $sql = "INSERT INTO people_house_favorite (people_id, house_id) VALUES ($people_id, $house_id)";
    $db->query($sql); 
  }

  function favorite_delete($people_id, $house_id){
    include("connect_database.php");
    $sql = "DELETE FROM people_house_favorite WHERE people_id = $people_id AND house_id = $house_id";
    $db->query($sql);
  }
  
//account's action

  function account_show_all(){
    include("connect_database.php");
    $sql = "SELECT * FROM people";
    $rs = $db->query($sql);
    return $rs;
  }

  function account_show_by_account($account){
    include("connect_database.php");
    $sql = "SELECT * FROM people WHERE account = :account";
    $rs = $db->prepare($sql);
    $rs->execute(array('account' => $account));
    $table = $rs->fetch();
    return $table;
  }

  function account_show_by_id($id){
    include("connect_database.php");
    $sql = "SELECT * FROM people WHERE id = $id";
    $rs = $db->query($sql);
    $table = $rs->fetch();
    return $table;
  }

  function account_create($account, $password,  $is_admin, $name, $email){
    include("connect_database.php");
    $hash_password = hash('sha256', $password);
    $sql = "INSERT INTO people (account, password, is_admin, name, email) VALUES (:account, :hash_password, :is_admin, :name, :email)";
    $rs = $db->prepare($sql);
    $rs->execute(array('account' => $account, 'hash_password' => $hash_password, 'is_admin' => $is_admin, 'name' => $name, 'email' => $email)); 
  }

  function account_delete($id){
    include("connect_database.php");
    $sql = "DELETE FROM people WHERE id = $id;
            DELETE FROM house_location_has WHERE house_id IN (SELECT house_id FROM people_house_has WHERE people_id = $id);
            DELETE FROM house_information_has WHERE house_id IN (SELECT house_id FROM people_house_has WHERE people_id = $id);
            DELETE FROM house WHERE id IN (SELECT house_id id FROM people_house_has WHERE people_id = $id);
            DELETE FROM people_house_favorite WHERE people_id = $id;
            DELETE FROM people_house_reserve WHERE people_id = $id;
            DELETE FROM people_house_reserve WHERE house_id IN (SELECT house_id FROM people_house_reserve WHERE people_id = $id);
            DELETE FROM people_house_has WHERE people_id = $id";
    $db->query($sql); 
  }

  function account_change($id){
    include("connect_database.php");
    $table = account_show_by_id($id);
    if($table[2] == 1){
      $new_is_admin = 0;
    }
    else{
      $new_is_admin = 1;
    }
    $sql = "UPDATE people SET is_admin = $new_is_admin WHERE id = $id";
    $db->query($sql);
  }

//print something

  function button_with_form($post_to, $name, $value, $button_name){
    echo "<form method='post' action=$post_to>";
    echo "<input type='hidden' name=$name value='$value'>";
    echo "<input class='adjust' value='$button_name' type='submit'>";
    echo "</form>";
  }
  
  function button_with_form_disabled($post_to, $name, $value, $button_name){
    echo "<form method='post' action=$post_to>";
    echo "<input type='hidden' name=$name value='$value' disabled>";
    echo "<input class='adjust' value='$button_name' type='submit' disabled>";
    echo "</form>";
  }

  function print_session($session_name){
    if(isset($_SESSION[$session_name])){
      echo $_SESSION[$session_name];
    }
  }
  
  function print_h($h_num, $content){
    echo "<h$h_num>$content</h$h_num>";
  }

  function print_p($class_p, $content){
    echo "<p class = $class_p>$content</p>";
  }

  function print_p_with_div($class_p, $content, $redirect_time, $redirect_url){
    echo "<div class='transport'>";
    if(is_array($content)){
      print_p("notice", $content[0]);
      foreach($content[1] as $key => $value){
        print_p("alert", $value);
      }
    }
    else{
      print_p($class_p, $content);
    }
    echo "<meta http-equiv=REFRESH CONTENT=$redirect_time;url=$redirect_url>";
    echo "</div>";
  }

  function print_information_checkbox(){
    $rs = information_show_all();
    if(isset($_SESSION['try_to_change_house_id'])){
      $house_informations = information_show($_SESSION['try_to_change_house_id']);
    }
    if(isset($house_informations)){
      $table_house_information = $house_informations->fetchObject();
    }
    $i = 0;
    echo "<div class=\"nobackground\">";
    while($table = $rs->fetchObject()){
      if(isset($table_house_information) && $table_house_information != array() && $table->id == $table_house_information->information_id){
        echo "<input type = 'checkbox' name = $table->id checked>$table->information</input>";
        $table_house_information = $house_informations->fetchObject();
      } 
      else{
        echo "<input type = 'checkbox' name = $table->id>$table->information</input>";
      }
      $i += 1;
      if($i == 3 || $i == 5 || $i == 8){
        echo "<br>";
      }
    }
    echo "</div>";
  }

  function print_location_selection(){
    if(isset($_SESSION['try_to_change_house_id'])){
      $house_location_rs = location_show($_SESSION['try_to_change_house_id']);
      $table_house_location = $house_location_rs->fetchObject();
      $house_location_id = $table_house_location->location_id;
    }
    $rs = location_show_all();
    echo "<select class = 'search' name = 'location' placeholder = 'keywords'>";
    while($table = $rs->fetchObject()){
      echo "<option value=$table->id";
      if(isset($house_location_id) && $house_location_id == $table->id){
        echo " selected = 'true'";
      }
      echo ">$table->location</option>";
    }  
    echo "</select>";
  }

//bwboao's function

  function print_pagination($rows){
    if(isset($_POST['page_num']))
      $page_num = $_POST['page_num'];
    else
      $page_num = 1;
    echo "<button type='submit' form='searchform' class='page' name='page_num' value=" . prev_page($page_num) . ">prev</button> ";
    for($i=1;$i<=$rows;$i++)
      if($i == $page_num)
        echo "<button type='submit' form='searchform' class='page active' name='page_num' value='$i'> $i </button>";
      else
        echo "<button type='submit' form='searchform' class='page' name='page_num' value='$i'> $i </button>";
    echo "<button type='submit' form='searchform' class='page' name='page_num' value=" . next_page($page_num,$rows) . ">next</button> ";
    return $page_num;
  }
    function prev_page($page_num){
      if($page_num>1)
        $page_num--;
      return $page_num;
  }
  function next_page($page_num,$rows){
    if($page_num<$rows)
      $page_num++;
    return $page_num;
  }
  function check_post_value($post_name){
    if(isset($_POST[$post_name])){
      $temp = $_POST[$post_name];
    echo " value = \"$temp\" ";
    }
  }
  function check_post_select($post_name, $value){
    if(isset($_POST[$post_name])){
      $temp = $_POST[$post_name];
      if($temp == $value){
        echo " selected = \"true\" ";
      }
    }
  }

  function check_post_multiselect($post_name, $value){
    if(isset($_POST[$post_name])){
      foreach($_POST[$post_name] as $post_value)
      if($post_value == $value){
        echo " selected = \"true\" ";
      }
    }
  }
?>


