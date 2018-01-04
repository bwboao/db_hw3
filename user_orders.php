<?php session_start(); ?>

<?php
  include("connect_database.php");
  include("_form.php");
  include("_personinfo.php");
  
  if(check_is_admin() == -1){
    print_p_with_div("alert", "Please login.", 2, "user.php");
  }
  else{
    //delete part start
    if(isset($_POST['edit_order_by_button'])){//delete part start
      print_p_with_div("notice", "update success", 1, "user_orders.php");
    }

    $require = "h.id IN (". str_house_select_by('order') . ")";
    $require_order = "ORDER BY h.id ASC";
    $house_rs = order_show($require, $require_order, array('customer_id' => $_SESSION['in_use_id']));
?>
    <div id="welcome">
      <h1>Welcome to your ORDERS page!</h1>
      <div id="transbutton">
        <p class="margin">
          <input type="submit" onclick="location.href='user.php'" value="首頁"></input>
        </p>
      </div>
    </div>
    <div id="table">
<?php
      $has_order = 0;
      while($table = $house_rs->fetchObject()){
      if($has_order == 0){
      $has_order = 1;
?>
      <table>
        <h3>Your Rervations</h3>
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
          <td><?php echo $table->owner; ?></td>
          <td>
<?php
          $info_rs = information_show($table->id);
          while($info = $info_rs->fetchObject()){
            echo "<p> $info->information </p>" ;
          }

?>
          </td>
          <td class="adjust">
            <?php button_with_form_disabled("user_orders.php", "edit_order_by_button", $table->id, "edit"); ?>
          </td>
        </tr>
<?php
        }   
        if($has_order == 0){
          print_p("alert", "You don't have any order");
        }
        else{
?>
      </table>
<?php        
        }
?>
    </div>
<?php
  }
?>
