<?php session_start(); ?>

<?php
  include("connect_database.php");
  include("_form.php");
  include("_personinfo.php");
  unset_session("require_order");

  if(check_is_admin() == -1){
    print_p_with_div("alert", "Please login.", 2, "user.php");
  }
  else{
    //delete part start
    if(isset($_POST['delete_favorite_by_button'])){//delete part start
      favorite_delete($_SESSION['in_use_id'], $_POST['delete_favorite_by_button']);
      print_p_with_div("notice", "Delete success", 1, "user_favorites.php");
    }

    $require = "h.id IN (". str_house_select_by('favorite') . ")";
    $require_order = "ORDER BY h.id ASC";
    $house_rs = house_show($require, $require_order, array('id' => $_SESSION['in_use_id']));
?>
    <div id="welcome">
      <h1>Welcome to your favorite page!</h1>
      <div id="transbutton">
        <p class="margin">
          <input type="submit" onclick="location.href='user.php'" value="首頁"></input>
        </p>
      </div>
    </div>
    <div id="table">
<?php
      $has_favorite = 0;
      while($table = $house_rs->fetchObject()){
      if($has_favorite == 0){
      $has_favorite = 1;
?>
      <table>
        <h3>Your favorites</h3>
        <tr>
          <th>id</th>
          <th>name</th>
          <th>price</th>
          <th>location</th>
          <th>time</th>
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
          <td><?php echo $table->time; ?></td>
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
            <?php button_with_form("user_favorites.php", "delete_favorite_by_button", $table->id, "取消最愛"); ?>
          </td>
        </tr>
<?php
        }   
        if($has_favorite == 0){
          print_p("alert", "You don't have any favorite");
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
