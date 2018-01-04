<?php session_start(); ?>

<?php
  include("connect_database.php");
  include("_form.php");

  if(check_is_admin($db) == -1){//1 is admin, 0 is member, -1 is others
    print_p_with_div("alert", "Please login.", 2, "index.php");
  }
  else{
    if(isset($_POST['change_house_by_button'])){
      store_post_as_session('try_to_change_house_id', 'change_house_by_button');
      $require = "h.id = $_SESSION[try_to_change_house_id]";
      $house_rs = house_show($require, "", array());
      $table = $house_rs->fetchObject();
      $_SESSION['try_to_change_house_name'] = $table->name;
      $_SESSION['try_to_change_house_price'] = $table->price;
      $_SESSION['try_to_change_house_location'] = $table->location;
      $_SESSION['is_try_to_update'] = 1;
    }
    else if(!isset($_SESSION['is_try_to_update']) || $_SESSION['is_try_to_update'] != 1){
      $_SESSION['is_try_to_update'] = 0;  
    }
    if(isset($_POST['price'])){
      store_post_as_session('try_to_change_house_name', 'house_name');
      store_post_as_session('try_to_change_house_price', 'price');
      store_post_as_session('try_to_change_house_location', 'location');

      $needto_reinput = 0;
      $needto_output = array();

      if($_SESSION['try_to_change_house_name'] == ""){
        array_push($needto_output, "house_name can't be null");
        $needto_reinput = 1;
      } 

      if($_SESSION['is_try_to_update'] == '1' && $needto_reinput == 0){
        house_update($_SESSION['try_to_change_house_id'], $_SESSION['try_to_change_house_name'], $_SESSION['try_to_change_house_price'], $_SESSION['try_to_change_house_location']);  
        information_delete_by_house_id($_SESSION['try_to_change_house_id']);
        for($i = 1;$i <= 10;$i++){
          if(isset($_POST[$i])){
            information_create($_SESSION['try_to_change_house_id'], $i);
          }
        }
        print_p_with_div("notice", "Update success", 1, "user_houses.php");
        unset_session('is_try_to_update');
      }
      else if($needto_reinput == 0){
        $_SESSION['try_to_change_house_id'] = house_create($_SESSION['try_to_change_house_name'], $_SESSION['try_to_change_house_price'], $_SESSION['try_to_change_house_location']); 
        for($i = 1;$i <= 10;$i++){
          if(isset($_POST[$i])){
            information_create($_SESSION['try_to_change_house_id'], $i);
          }
        } 
        print_p_with_div("notice", "Create success", 1, "user_houses.php");
      } 
      else{
        $needto_output_with_header = array();
        if($_SESSION['is_try_to_update'] == '1'){ 
          array_push($needto_output_with_header, "update failed");
        }
        else{
          array_push($needto_output_with_header, "create failed");
        }
        array_push($needto_output_with_header, $needto_output);
        print_p_with_div("alert", $needto_output_with_header, 1, "user_house_change.php");
      }
    }
  }

?>       
<html>
<head>
  <link rel="stylesheet" href="all.css">
  <meta http-equiv="Content-Type" content="text/html charset=utf-8" />
</head>

<body>
  <div id="regist"><!--copy from regist.php-->
  <form name="change_house" method="post" action="user_house_change.php">
  <h3><?php if(isset($_SESSION['is_try_to_update']) && $_SESSION['is_try_to_update'] == 1){echo "Update ";}else{echo "Add new ";} ?>house</h3> 
    <p><?php if(isset($_SESSION['is_try_to_update']) && $_SESSION['is_try_to_update'] == 1){echo "update ";}else{echo "add ";} ?>it~</p>
    <table class="noshadow">
      <tbody>
        <tr>
          <td>house_name</td>
          <td><input name="house_name" type="text" value="<?php print_session('try_to_change_house_name'); ?>"></td>
        </tr>
        <tr>
          <td>price</td>
          <td><input name="price" type="number" value="<?php print_session('try_to_change_house_price'); ?>"></td>
        </tr>
        <tr>
          <td>location</td>
          <td>
<?php
          print_location_selection();
?>
          </td>
        </tr>
      </tbody>
    </table>

<?php
    print_information_checkbox();
?>
    <p>
      <input name="button_to_create" type="submit" value=<?php if($_SESSION['is_try_to_update'] == '1'){echo "update";}else{echo "create";} ?>>
    </p>
  </form>
      <input type="button" onclick="location.href='user_houses.php'" value="cancel"></input>
  </div>
</body>
</html>
  

