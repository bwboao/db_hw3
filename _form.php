<link rel="stylesheet" href="all.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css?family=Arimo" rel="stylesheet" >
<link href="https://fonts.googleapis.com/earlyaccess/notosanstc.css" rel="stylesheet" >

<?php
  include("connect_database.php");

  //if(session_status() == PHP_SESSION_NONE){
  //  session_start();
  //}
    
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
    $sql = "SELECT * FROM house WHERE " . $require . $require_order;
    $sql = "SELECT h.id id, h.name name, price, location, time, p.name owner FROM house AS h LEFT JOIN people AS p ON h.owner_id = p.id LEFT JOIN house_to_location AS h_to_l ON h.id = h_to_l.house_id LEFT JOIN normal_location AS l ON h_to_l.location_id = l.id WHERE " . $require . " " . $require_order;
    //echo $sql;
    $rs = $db->prepare($sql);
    $rs->execute($array_for_execute);
    return $rs;
  }

  function house_favorite($people_id, $house_id){
    include("connect_database.php");
    $sql = "INSERT INTO people_to_house (people_id, house_id) VALUES ($people_id, $house_id)";
    $db->query($sql); 
  }

  function house_delete($house_id){
    include("connect_database.php");
    $sql = "DELETE FROM people_to_house WHERE house_id = $house_id;
            DELETE FROM house_to_information WHERE house_id = $house_id;
            DELETE FROM house WHERE id = $house_id";
    $db->query($sql);
  }

  function house_update($house_id, $house_name, $house_price, $location_id){
    include("connect_database.php");
    $sql = "UPDATE house_to_location SET location_id = $location_id WHERE house_id = $house_id;
            UPDATE house SET name = :house_name, price = :house_price WHERE id = $house_id";
    $rs = $db->prepare($sql); 
    $rs->execute(array('house_name' => $house_name, 'house_price' => $house_price));
  }

  function house_create($house_name, $house_price, $location_id){
    include("connect_database.php");
    $time = date('Y-m-d');
    $sql = "INSERT INTO house (name, price, time, owner_id) VALUES (:house_name, :house_price, :time, $_SESSION[in_use_id]);";
    $rs = $db->prepare($sql);
    $rs->execute(array('house_name' => $house_name, 'house_price' => $house_price, 'time' => $time));
    
    $sql = "SELECT MAX(id) FROM house;";
    $rs = $db->query($sql);
    $table = $rs->fetch();
    $house_id = $table[0];
    
    $sql = "INSERT INTO house_to_location (house_id, location_id) VALUES ($house_id, $location_id);";
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
        return "SELECT id FROM house WHERE time = :time";
      case 'location':
        return "SELECT house_id id FROM house_to_location AS h_to_l LEFT JOIN normal_location AS l ON h_to_l.location_id = l.id WHERE location = :location";
      case 'owner':
        return "SELECT h.id id FROM house AS h LEFT JOIN people AS p ON owner_id = p.id where p.name = :owner";
      case 'favorite':
        return "SELECT house_id id FROM people_to_house WHERE people_id = :id";
      default://use for information
        return "SELECT house_id id FROM house_to_information WHERE information_id IN $condition";
    }  
  }

//information's action

  function information_show($house_id){
    include("connect_database.php");
    $sql = "SELECT information_id, information FROM house_to_information AS h_to_i LEFT JOIN normal_information AS i ON h_to_i.information_id = i.id WHERE h_to_i.house_id = $house_id ORDER BY information_id";
    $rs = $db->query($sql);
    return $rs;
  }

  function information_show_all(){
    include("connect_database.php");
    $sql = "SELECT * FROM normal_information";
    $rs = $db->query($sql);
    return $rs;
  }

  function information_delete($house_id){
    include("connect_database.php");
    $sql = "DELETE FROM house_to_information WHERE house_id = $house_id";
    $db->query($sql);
  }

  function information_create($house_id, $information_id){
    include("connect_database.php");
    $sql = "INSERT INTO house_to_information (house_id, information_id) VALUES ($house_id, $information_id);";
    $db->query($sql);
  }

//location's action

  function location_show($house_id){
    include("connect_database.php");
    $sql = "SELECT * FROM house_to_location WHERE house_id = $house_id";
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
    $sql = "SELECT * FROM people_to_house WHERE people_id = $people_id AND house_id = $house_id";
    $rs = $db->query($sql);
    return $rs;
  }

  function favorite_delete($people_id, $house_id){
    include("connect_database.php");
    $sql = "DELETE FROM people_to_house WHERE people_id = $people_id AND house_id = $house_id";
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
            DELETE FROM house_to_location WHERE house_id IN (SELECT id house_id FROM house WHERE owner_id = $id);
            DELETE FROM house_to_information WHERE house_id IN (SELECT id house_id FROM house WHERE owner_id = $id);
            DELETE FROM house WHERE owner_id = $id;
            DELETE FROM people_to_house WHERE people_id = $id";
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
    echo "<input class='adjust' value=$button_name type='submit'>";
    echo "</form>";
  }
  
  function button_with_form_disabled($post_to, $name, $value, $button_name){
    echo "<form method='post' action=$post_to>";
    echo "<input type='hidden' name=$name value='$value' disabled>";
    echo "<input class='adjust' value=$button_name type='submit' disabled>";
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
      if($i == 4 || $i == 8){
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


