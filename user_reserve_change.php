<?php session_start(); ?>

<?php
  include("connect_database.php");
  include("_form.php");

  if(check_is_admin($db) == -1){//1 is admin, 0 is member, -1 is others
    print_p_with_div("alert", "Please login.", 2, "index.php");
  }
  else{
    if(isset($_POST['edit_reserve_by_button'])){
      store_post_as_session('try_to_change_reserve_id', 'edit_reserve_by_button');
    }
    $now = date('Y-m-d');
    $require = "p_h_reserve.id = :reserve_id";
    $rs = reserve_show($require, "", array('reserve_id' => $_SESSION['try_to_change_reserve_id']));
    $table = $rs->fetchObject();
    $house_require = "h.id = $table->id AND p_h_reserve.id <> $_SESSION[try_to_change_reserve_id]";
    $house_rs = reserve_show($house_require, "", array());
    if(isset($_POST['try_to_update_reserve'])){
      if($_POST['time_check_in'] != "" && $_POST['time_check_out'] != ""){
        $needto_reinput = 1;
        if($_POST['time_check_in'] < $now){
          print_p_with_div("alert", "check in time must be bigger then today", 1, "user_reserve_change.php");
        }
        else if($_POST['time_check_in'] >= $_POST['time_check_out']){
          print_p_with_div("alert", "check in time must be smaller then check out time", 1, "user_reserve_change.php");
        }
        else{
          $check_reserve_require = "h.id NOT IN (" . str_house_select_by('time') . ") AND h.id = $table->id";
          $check_reserve_rs = house_show($require, "", array('time_check_in' => $_POST['time_check_in'], 'time_check_out' => $_POST['time_check_out']));
          print_r($check_reserve_rs);
          echo $check_reserve_rs->rowCount();
          if($check_reserve_rs->rowCount() == 0){
            print_p_with_div("alert", "there is a visitor in this time interval, change another time", 10, "user_reserve_change.php");
          }
          else{
            reserve_update($_SESSION['try_to_change_reserve_id'], $_POST['time_check_in'], $_POST['time_check_out']);
            unset_session('try_to_change_reserve_id');
            print_p_with_div("notice", "Update success", 1, "user_reserves.php");
          }
        }
      }
      else if($_POST['time_check_in'] != ""){
        print_p_with_div("alert", "please set check out time", 1, "user_reserve_change.php");
      }
      else{
        print_p_with_div("alert", "please set check in time", 1, "user_reserve_change.php");
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
  <div><!--copy from regist.php-->
  <form name="change_reserve" method="post" action="user_reserve_change.php">
  <h3>edit reserve</h3> 
    <table>
      <tbody>
        <tr>
          <th>id</th>
          <th>name</th>
          <th>price</th>
          <th>location</th>
          <th>time_check_in</th>
          <th>time_check_out</th>
          <th>owner</th>
          <th>information</th>
          <th>option</th>
        </tr>
        <tr>
          <td><?php echo $table->id; ?></td>
          <td><?php echo $table->name; ?></td>
          <td><?php echo $table->price; ?></td>
          <td><?php echo $table->location; ?></td>
          <td class="adjust">
            <input  name="time_check_in" type="text" onfocus="(this.type='date')" onblur="(this.type='text')" placeholder="check-in" <?php check_post_value("time_check_in"); ?>>
          </td>
          <td class="adjust">
            <input  name="time_check_out" type="text" onfocus="(this.type='date')" onblur="(this.type='text')" placeholder="check-out" <?php check_post_value("time_check_out"); ?>>
          </td>
          <td><?php echo $table->owner; ?></td>
          <td>
<?php
          $info_rs = information_show($table->id);
          while($info = $info_rs->fetchObject()){
            echo "<p> $info->information </p>" ;
          }
?>
          </td>
          <td>
            <input type='hidden' name='try_to_update_reserve' value='1'>
            <input name="button_to_create" type="submit" value='update'>
            <input type="button" onclick="location.href='user_reserves.php'" value="cancel"></input>
          </td>
        </tr>
      </tbody>
    </table>
  </form>
  </div>
  <div>
    <h3>In these time this house has visitor:</h3>
    <table>
<?php
      $has_house = 0;
      while($table = $house_rs->fetchObject()){
      if($has_house == 0){
      $has_house = 1;
?>
      <tr>
        <td>time_check_in</td>
        <td>time_check_out</td>
      </tr>
<?php
      }
?>
      <tr>
        <td><?php echo $table->time_check_in; ?></td>
        <td><?php echo $table->time_check_out; ?></td>
      </tr>
<?php
      }
      if($has_house == 0){
?>
      <tr>
        <td>no other reservation on this house</td>
      </tr>
<?php
    }
?>
    <table>
  </div>
</body>
</html>
  

