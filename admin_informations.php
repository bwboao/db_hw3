<?php session_start(); ?>

<?php
  include("connect_database.php");
  include("_form.php");

  if(check_is_admin() != 1){
    print_p_with_div("alert", "Permission denied, this page is only for administrator", 2, "index.php");
  }
  else{
    $rs = information_show_all();
    if(isset($_POST['delete_information_by_button'])){
      information_delete($_POST['delete_information_by_button']);
      print_p_with_div("notice", "Delete success", 1, "admin_informations.php");
    }
    if(isset($_POST['create_information_by_button'])){
      information_create($_POST['create_information_by_button']);
      print_p_with_div("notice", "Create success", 1, "admin_informations.php");
    }
?>
    <div id="welcome">
      <h1>Welcome to your information manage page!</h1>
      <div id="transbutton">
        <p class="margin">
          <input type="submit" onclick="location.href='user.php'" value="首頁"></input>
        </p>
      </div>
    </div>
    <div id="table">
      <table>
        <tbody>
        <form method="post" action="admin_informations.php">
        <td class="adjust" colspan='2'>
          <input name="create_information_by_button" type="text" placeholder="keyword">
        </td>
        <td>
          <input class="adjust" type="submit" value="create">
        </td>
        </form>

<?php
        $has_information = 0;

        while($table = $rs->fetchObject()){
          if($has_information == 0){
            $has_information = 1;
?>
            <tr>
              <td>id</td>
              <td>information</td>
              <td>option</td>
            </tr>
<?php          
          }
?>
            <tr>
              <td><?php echo $table->id; ?></td>
              <td><?php echo $table->information; ?></td> 
              <td><?php button_with_form("admin_informations.php", "delete_information_by_button", $table->id, "delete");?></td>
            </tr>
<?php
        }
        if($has_information == 0){
?>
        <h3>no information</h3>
<?php
        }
?>
        </tbody>
      </table>
    </div>
<?php
  
  }
?>
