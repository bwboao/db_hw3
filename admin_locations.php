<?php session_start(); ?>

<?php
  include("connect_database.php");
  include("_form.php");

  if(check_is_admin() != 1){
    print_p_with_div("alert", "Permission denied, this page is only for administrator", 2, "index.php");
  }
  else{
    $rs = location_show_all();
    if(isset($_POST['delete_location_by_button'])){
      location_delete($_POST['delete_location_by_button']);
      print_p_with_div("notice", "Delete success", 1, "admin_locations.php");
    }
    if(isset($_POST['create_location_by_button'])){
      location_create($_POST['create_location_by_button']);
      print_p_with_div("notice", "Create success", 1, "admin_locations.php");
    }
?>
    <div id="welcome">
      <h1>Welcome to your location manage page!</h1>
      <div id="transbutton">
        <p class="margin">
          <input type="submit" onclick="location.href='user.php'" value="首頁"></input>
        </p>
      </div>
    </div>
    <div id="table">
      <table>
        <tbody>
        <form method="post" action="admin_locations.php">
        <td class="adjust" colspan='2'>
          <input name="create_location_by_button" type="text" placeholder="keyword">
        </td>
        <td class="adjust" >
          <input class="adjust" type="submit" value="create">
        </td>
        </form>

<?php
        $has_location = 0;

        while($table = $rs->fetchObject()){
          if($has_location == 0){
            $has_location = 1;
?>
            <tr>
              <th>id</th>
              <th>location</th>
              <th>option</th>
            </tr>
<?php          
          }
?>
            <tr>
              <td><?php echo $table->id; ?></td>
              <td><?php echo $table->location; ?></td> 
              <td class="adjust" ><?php button_with_form("admin_locations.php", "delete_location_by_button", $table->id, "delete");?></td>
            </tr>
<?php
        }
        if($has_location == 0){
?>
        <h3>no location</h3>
<?php
        }
?>
        </tbody>
      </table>
    </div>
<?php
  
  }
?>
