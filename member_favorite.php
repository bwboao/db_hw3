<?php session_start(); ?>

<?php
  include("connect_database.php");
  include("_form.php");
  unset_session('login_account');
   if(isset($_SESSION['account'])){
    $my_account = $_SESSION['account'];
    if($_SESSION['is_admin'] == 1){
      print_p_with_div("alert", "This account is admin, you DON'T belong here", 2, "admin.php");
    }
    else{ 
//delete part start
      if(isset($_POST['button_delete_fav'])){//delete part start
          $fav_id=$_POST['button_delete_fav'];
          unset_session('button_delete_fav');
          $sql="DELETE FROM favorite WHERE id ='$fav_id' ";
          $rs=$db->prepare($sql);
          $rs->execute();
          print_p_with_div("notice", "already delete", 1, "member_favorite.php");
      }
//delete part end


      $my_account = $_SESSION['account'];//for sql;
      $sql_find_account = "SELECT * FROM people WHERE account='$my_account'";
      $this_rs = $db->prepare($sql_find_account);
      $this_rs->execute();
      $table = $this_rs->fetch();
?>
      <div id="welcome">
        <h1>Welcome to your favorite page!</h1>
        <div id="transbutton">
          <p class="margin">
            <input type="submit" onclick="location.href='member.php'" value="首頁"></input>
          </p>
        </div>
      </div>
<!-- Personinfo part START-->
      <div id="personinfo">
        <p>Hello, <?php echo "$table[0]"; ?> ! </p>
        <table>
          <tbody>
            <tr>
              <th colspan="2">info</th>
            </tr>
            <tr>
              <td>name</td>
              <td><?php echo "$table[3]"; ?></td>
            </tr>
            <tr>
              <td>email</td>
              <td><?php echo "$table[4]"; ?></td>
            </tr>
          </tbody>
        </table>
        <p class="margin">
          <input type="button" onclick="location.href='logout.php'" value="logout"></input>
        </p>
      </div>
<!-- Personinfo part END-->
<!-- Search part START-->
<?php
      //echo "$table[5]";
      $user_id = $table[5] ;
      $sql_find_all = "SELECT *,house.id AS hid,house.name hname, people.name AS owner,favorite.id AS favid FROM `favorite` LEFT JOIN house ON house.id = favorite_id LEFT JOIN people ON owner_id = people.id WHERE user_id = $user_id";
      //$people_rs = $db->query($sql_find_all);
      $people_rs = $db->prepare($sql_find_all);
      $people_rs->execute();
?>

<!-- Search part END-->
<!-- Table part START-->
      <div id="table">
        <table>
          <h3>Your favorites</h3>
<?php
      $table = $people_rs->fetchObject();
      if($table == NULL)
      {
?>
      <h3>你尚未擁有任何最愛</h3>
<?php
      }
      else
      {
?>
          </td></tr>
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
          <tr>
	    <td><?php echo $table->hid; ?></td>
            <td><?php echo $table->hname; ?></td>
            <td><?php echo $table->price; ?></td>
            <td><?php echo $table->location; ?></td>
            <td><?php echo $table->time; ?></td>
            <td><?php echo $table->owner; ?></td>
            <td>
<?php
        $sql_find_info = "SELECT * FROM information AS info WHERE info.house_id= $table->hid " ;
        $info_rs = $db->query($sql_find_info);
        while($info = $info_rs->fetchObject()){
          echo "<p> $info->information </p>" ;
        }

?>
            </td>
            <td class="adjust">
              <form method="post" action="member_favorite.php" style="display:block;text-align:center">
                <input type="hidden" name="button_delete_fav" value="<?php echo $table->favid; ?>">
                <input class="adjust" value="delete" type="submit" >
              </form> 
            </td>
          </tr>
<?php
      }
      while($table = $people_rs->fetchObject()){
?>
          <tr>
	    <td><?php echo $table->hid; ?></td>
            <td><?php echo $table->hname; ?></td>
            <td><?php echo $table->price; ?></td>
            <td><?php echo $table->location; ?></td>
            <td><?php echo $table->time; ?></td>
            <td><?php echo $table->owner; ?></td>
            <td>
<?php
        $sql_find_info = "SELECT * FROM information AS info WHERE info.house_id= $table->hid " ;
        $info_rs = $db->query($sql_find_info);
        while($info = $info_rs->fetchObject()){
          echo "<p> $info->information </p>" ;
        }

?>
            </td>
            <td class="adjust">
              <form method="post" action="member_favorite.php" style="display:block;text-align:center">
                <input type="hidden" name="button_delete_fav" value="<?php echo $table->favid; ?>">
                <input class="adjust" value="delete" type="submit">
              </form>
            </td>
          </tr>
<?php
      }
?>
        </table>
      </div>
<!-- Table part END -->

<?php
    }
  }
  else{
    print_p_with_div("alert", "please login", 2, "index.php");
  }
?>

