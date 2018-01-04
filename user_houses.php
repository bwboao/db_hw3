<?php session_start(); ?>

<?php
  include("connect_database.php");
  include("_form.php");
  include("_personinfo.php");
  unset_session('is_try_to_update');
  unset_session('try_to_change_house_id');
  unset_session('try_to_change_house_name');
  unset_session('try_to_change_house_price');
  unset_session('try_to_change_house_location');
  
  if(check_is_admin() == -1){
    print_p_with_div("alert", "Please login", 2, "index.php");
  }
  else{
    //delete part start
    if(isset($_POST['delete_house_by_button'])){
      house_delete($_POST['delete_house_by_button']);
      print_p_with_div("notice", "Delete success", 1, "user_houses.php");
    }
    $require = "h.id IN (" . str_house_select_by("owner") . ")"; 
    $house_rs = reserve_show($require, "", array('owner' => $_SESSION['in_use_name']));
?>
    <div id="welcome">
      <h1>Welcome to your house manage page!</h1>
      <div id="transbutton">
        <p class="margin">
          <input type="submit" onclick="location.href='user.php'" value="首頁"></input>
        </p>
      </div>
    </div>
    <div id="table">
      <table>
        <h3>Your houses</h3>
        <tr>
          <td class="adjust" colspan="7"></td>
          <td class="adjust">
            <input type="submit" onclick="location.href='user_house_change.php'" value="新增"></input>
          </td>
        </tr>
<?php
        $has_house = 0;
        while($table = $house_rs->fetchObject()){
        if($table != NULL && $has_house == 0){
        $has_house = 1;
?>
        <tr>
          <th>id</th>
          <th>name</th>
          <th>price</th>
          <th>location</th>
          <th>time_check_in</th>
          <th>time_check_out</th>
          <th>visitor</th>
          <th>information</th>
          <th>option</th>
        </tr>
<?php
        }
?>  
        <tr>
          <td><?php echo $table->id; ?></td>
          <td><?php echo $table->name; ?></td>
          <td><?php echo $table->price; ?></td>
          <td><?php echo $table->location; ?></td>
          <td><?php echo $table->time_check_in; ?></td>
          <td><?php echo $table->time_check_out; ?></td>
          <td>
<?php
          if($table->customer_id != ""){
            $customer = account_show_by_id($table->customer_id);
            echo $customer['name'];
          }
?>
          </td>
          <td>
<?php
          $info_rs = information_show($table->id);
          while($info = $info_rs->fetchObject()){
            echo "<p> $info->information </p>" ;
          }
?>
          </td>
          <td class="adjust">
            <?php button_with_form("user_houses.php", "delete_house_by_button", $table->id, "delete"); ?>
            <?php button_with_form("user_house_change.php", "change_house_by_button", $table->id, "change"); ?>
          </td>
        </tr>
<?php
        }
        if($has_house == 0){
          print_p("notice", "您尚未擁有任何房子");
        }
?>
      </table>
    </div>
<!-- Table part END -->

<?php
  }
?>
