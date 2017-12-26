<?php session_start(); ?>

<?php
  include("connect_database.php");
  include("_form.php");
  if(check_is_admin($db) != -1){
    $account_using = find_account_using($db);
    $values=array(0,0,0,0,0,0,0,0,0,0);  
    if(!isset($_SESSION['is_update'])){
      $_SESSION['is_update'] = 0;
    }
    if(isset($_POST['button_change_house'])){
      $my_house = find_house($db, $_POST['button_change_house']);
      $_SESSION['change_house_id'] = $my_house[0];
      $_SESSION['change_house_name'] = $my_house[1];
      $_SESSION['change_house_price'] = $my_house[2];
      $_SESSION['change_house_location'] = $my_house[3];
      $_SESSION['is_update'] = 1;
      $values = show_info_array($db, $_SESSION['change_house_id'], $info_to_num);
    }
    if(isset($_POST['price'])){
      $change_house_name = $_POST['house_name'];
      $change_house_price = $_POST['price'];
      $change_house_location = $_POST['location'];
      $values = make_array();
      for($i = 0;$i < 10;$i++){
        if(isset($_POST[$i])){
          $values[$i] = 1;
        }
      }
      //print_r($values);
      $owner_id = $account_using[5];//people.id
      if($_SESSION['is_update'] == '1'){
        $change_house_id = $_SESSION['change_house_id'];
        update_house($db, $change_house_id, $change_house_name, $change_house_price, $change_house_location);  
        update_info($db, $change_house_id, $values, $num_to_info, $info_to_num);
      }
      else{
        create_house($db, $owner_id, $change_house_name, $change_house_price, $change_house_location);    
        $change_house_id = find_latest($db, "house");
        update_info($db, $change_house_id, $values, $num_to_info, $info_to_num);
        
      }
      //unset_session('change_house_id');
      //unset_session('change_house_name');
      //unset_session('change_house_price');
      //unset_session('change_house_location');
      //$values = make_array();
      if($_SESSION['is_update'] == '1'){
        print_p_with_div("notice", "update success", 1, "member_house.php");
      }
      else{
        print_p_with_div("notice", "create success", 1, "member_house.php");
      }
    }
  }
  else{
    print_p_with_div("alert", "Please login.", 2, "index.php");
  }

?>       
<html>
<head>
  <link rel="stylesheet" href="all.css">
  <meta http-equiv="Content-Type" content="text/html charset=utf-8" />
</head>

<body>
  <div id="regist"><!--copy from regist.php-->
  <form name="change_house" method="post" action="member_house_change.php">
  <h3><?php if($_SESSION['is_update'] == 1){echo "Update ";}else{echo "Add new ";} ?>house</h3> 
    <p><?php if($_SESSION['is_update'] == 1){echo "update ";}else{echo "add ";} ?>it~</p>
    <table class="noshadow">
      <tbody>
        <tr>
          <td>house_name</td>
          <td><input name="house_name" type="text" value="<?php print_session('change_house_name'); ?>"></td>
        </tr>
        <tr>
          <td>price</td>
          <td><input name="price" type="number" value="<?php print_session('change_house_price'); ?>"></td>
        </tr>
        <tr>
          <td>location</td>
          <td><input name="location" type="text" value="<?php print_session('change_house_location'); ?>"></td>
        </tr>
      </tbody>
    </table>

<?php
    print_information_checkbox($values, $num_to_info);
?>
    <p>
      <input name="button_to_create" type="submit" value=<?php if($_SESSION['is_update'] == '1'){echo "update";}else{echo "create";} ?>>
    </p>
  </form>
      <input  type="button" onclick="location.href='member_house.php'" value="cancel"></input>
  </div>
</body>
</html>
  


