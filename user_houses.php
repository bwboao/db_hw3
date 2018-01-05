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
  unset_session("require_order");
  
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
    $require_order = "ORDER BY h.id ASC";
    //echo $require . "<br>";
    //$house_rs = reserve_show($require, "", array('owner' => $_SESSION['in_use_name']));
    //for($i=0;$i<$house_rs->rowCount();$i++)
    //  print_r($house_rs->fetchObject());
    //$house_rs = reserve_show($require, "", array('owner' => $_SESSION['in_use_name']));
    $house_rs = house_show($require, $require_order, array('owner' => $_SESSION['in_use_name']));
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
          <td class="adjust" colspan="8"></td>
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
          <th>check-in time</th>
          <th>check-out time</th>
          <th>visitor</th>
          <th>information</th>
          <th>option</th>
        </tr>
<?php
        }
        $require = "h.id IN (" . $table->id . ")";
        $require_order = "ORDER BY time_check_in  ASC";
        $reserve_rs = reserve_show($require, $require_order, array()); 
        $rows =  $reserve_rs->rowCount();
        $table = $reserve_rs->fetchObject();
        if($rows<1)
        {
?>  
        <tr>
          <td><?php echo $table->id; ?></td>
          <td><?php echo $table->name; ?></td>
          <td><?php echo $table->price; ?></td>
          <td><?php if($table->location != NULL){echo $table->location;}else{echo "未知";} ?></td>
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
        else
        {
?>
        <tr>
          <td rowspan=<?php echo "'$rows'> $table->id"; ?></td>
          <td rowspan=<?php echo "'$rows'>$table->name"; ?></td>
          <td rowspan=<?php echo "'$rows'> $table->price"; ?></td>
<?php
          if($table->location != NULL){
?>
          <td rowspan=<?php echo "'$rows'> $table->location"; ?></td>
<?php
          }
          else{
            echo "<td rowspan='$rows'>未知</td>";
          }
  ?>
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
          <td rowspan=<?php echo "'$rows'";?> >
<?php
          $info_rs = information_show($table->id);
          while($info = $info_rs->fetchObject()){
            echo "<p> $info->information </p>" ;
          }
?>
          </td>
          <td class="adjust" rowspan=<?php echo "'$rows'";?> >
            <?php button_with_form("user_houses.php", "delete_house_by_button", $table->id, "delete"); ?>
            <?php button_with_form("user_house_change.php", "change_house_by_button", $table->id, "change"); ?>
          </td>
        </tr>

<?php
          for($i=1;$i<$rows;$i++)
          {
          $table = $reserve_rs->fetchObject();
?>
        <tr>
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
        </tr>
<?php
          }
        }
        
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
